<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Cart;
use App\Models\EbookTransaction;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class EbookController extends Controller
{
    private const QR_EXPIRE_MINUTES = 5;

    public function buy(Book $book): View
    {
        if (! $book->file_ebook) {
            abort(404, 'eBook tidak tersedia untuk buku ini.');
        }

        $transaction = EbookTransaction::where('user_id', auth()->id())
            ->where('book_id', $book->id)
            ->latest()
            ->first();

        return view('ebook.buy', compact('book', 'transaction'));
    }

    public function paySingle(Request $request, Book $book): RedirectResponse
    {
        if (! $book->file_ebook) {
            return redirect()->back()->with('error', 'eBook tidak tersedia untuk buku ini.');
        }

        if (! $book->price || $book->price <= 0) {
            return redirect()->back()->with('error', 'Harga eBook belum ditentukan.');
        }

        $checkoutId = 'EC' . strtoupper(Str::random(10));
        $invoiceCode = 'EB' . strtoupper(Str::random(10));
        $amount = $book->price;
        $expiresAt = Carbon::now()->addMinutes(self::QR_EXPIRE_MINUTES);

        $qrContent = sprintf(
            'QRIS|invoice:%s|amount:%s|user:%s|book:%s|checkout:%s|expires:%s',
            $invoiceCode,
            number_format($amount, 2, '.', ''),
            auth()->id(),
            $book->id,
            $checkoutId,
            $expiresAt->timestamp
        );

        $qrFileName = "ebook_qr/{$checkoutId}.svg";
        Storage::disk('public')->put($qrFileName, QrCode::format('svg')->size(300)->generate($qrContent));

        EbookTransaction::create([
            'user_id' => auth()->id(),
            'book_id' => $book->id,
            'invoice_code' => $invoiceCode,
            'checkout_id' => $checkoutId,
            'qr_code' => $qrFileName,
            'amount' => $amount,
            'qty' => 1,
            'status' => 'pending',
            'expires_at' => $expiresAt,
        ]);

        return redirect()->route('ebook.buy', $book)
            ->with('success', 'QR Code pembayaran berhasil dibuat. Silakan lakukan pembayaran dan konfirmasi.');
    }

    public function addToCart(Request $request, Book $book): RedirectResponse
    {
        if (! $book->file_ebook) {
            return redirect()->back()->with('error', 'eBook tidak tersedia untuk buku ini.');
        }

        $previousUrl = (string) $request->input('redirect_to', url()->previous());
        $previousPath = parse_url($previousUrl, PHP_URL_PATH) ?: '';
        $previousHost = parse_url($previousUrl, PHP_URL_HOST);

        if (
            $previousUrl === '' ||
            ($previousHost && $previousHost !== $request->getHost()) ||
            str_starts_with($previousPath, '/ebook/cart')
        ) {
            $previousUrl = url(route('books.show', $book, false));
        }

        $cartItem = Cart::where('user_id', auth()->id())
            ->where('book_id', $book->id)
            ->first();

        if ($cartItem) {
            $cartItem->increment('qty');

            return redirect()->to($previousUrl)
                ->with('cart_toast', 'Jumlah eBook di keranjang ditambah.')
                ->with('cart_toast_type', 'info');
        }

        Cart::create([
            'user_id' => auth()->id(),
            'book_id' => $book->id,
            'qty' => 1,
        ]);

        return redirect()->to($previousUrl)
            ->with('cart_toast', 'Buku berhasil ditambahkan ke keranjang.')
            ->with('cart_toast_type', 'success');
    }

    public function cart(): View
    {
        $cartItems = collect();

        if (auth()->check()) {
            $cartItems = Cart::with('book')
                ->where('user_id', auth()->id())
                ->whereHas('book')
                ->get();
        }

        return view('ebook.cart', compact('cartItems'));
    }

    public function removeFromCart(Book $book): RedirectResponse
    {
        Cart::where('user_id', auth()->id())
            ->where('book_id', $book->id)
            ->delete();

        return redirect()->route('ebook.cart')->with('success', 'Item keranjang berhasil dihapus.');
    }

    public function checkout(Request $request): View
    {
        $checkoutId = session('checkout_id');
        $pendingTransactions = collect();
        $paidTransactions = collect();

        if ($checkoutId) {
            $pendingTransactions = EbookTransaction::with('book')
                ->where('user_id', auth()->id())
                ->where('checkout_id', $checkoutId)
                ->get();
        }

        if ($pendingTransactions->isEmpty()) {
            $pendingTransactions = EbookTransaction::with('book')
                ->where('user_id', auth()->id())
                ->where('status', 'pending')
                ->latest('created_at')
                ->take(10)
                ->get();
        }

        if ($pendingTransactions->isNotEmpty()) {
            $paidTransactions = $pendingTransactions->where('status', 'paid');
            $checkoutId = $pendingTransactions->first()->checkout_id;
        } else {
            $paidTransactions = EbookTransaction::with('book')
                ->where('user_id', auth()->id())
                ->where('status', 'paid')
                ->latest('created_at')
                ->take(10)
                ->get();
        }

        $cartItems = Cart::with('book')
            ->where('user_id', auth()->id())
            ->whereHas('book')
            ->get();

        return view('ebook.checkout', compact('cartItems', 'pendingTransactions', 'paidTransactions', 'checkoutId'));
    }

    public function processCheckout(Request $request): RedirectResponse
    {
        $cartItems = Cart::with('book')
            ->where('user_id', auth()->id())
            ->whereHas('book')
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('ebook.cart')->with('error', 'Keranjang ebook Anda kosong.');
        }

        $checkoutId = 'EC' . strtoupper(Str::random(10));
        $amountTotal = $cartItems->sum(fn ($item) => ($item->book->price ?? 0) * max((int) $item->qty, 1));
        $invoiceCodes = [];

        foreach ($cartItems as $item) {
            $invoiceCodes[] = 'EB' . strtoupper(Str::random(10));
        }

        $qrContent = sprintf(
            'QRIS|checkout:%s|amount:%s|user:%s|items:%s|expires:%s',
            $checkoutId,
            number_format($amountTotal, 2, '.', ''),
            auth()->id(),
            implode(',', array_map(fn ($code) => $code, $invoiceCodes)),
            Carbon::now()->addMinutes(self::QR_EXPIRE_MINUTES)->timestamp
        );

        $qrFileName = "ebook_qr/{$checkoutId}.svg";
        Storage::disk('public')->put($qrFileName, QrCode::format('svg')->size(300)->generate($qrContent));

        foreach ($cartItems as $index => $item) {
            $qty = max((int) $item->qty, 1);
            EbookTransaction::create([
                'user_id' => auth()->id(),
                'book_id' => $item->book->id,
                'invoice_code' => $invoiceCodes[$index],
                'checkout_id' => $checkoutId,
                'qr_code' => $qrFileName,
                'amount' => ($item->book->price ?? 0) * $qty,
                'qty' => $qty,
                'status' => 'pending',
                'expires_at' => Carbon::now()->addMinutes(self::QR_EXPIRE_MINUTES),
            ]);
        }

        return redirect()->route('ebook.checkout')
            ->with('success', 'QRIS berhasil dibuat. Silakan lakukan pembayaran.')
            ->with('checkout_id', $checkoutId);
    }

    public function confirmPayment(string $checkoutId): RedirectResponse
    {
        $transactions = EbookTransaction::with('book')
            ->where('user_id', auth()->id())
            ->where('checkout_id', $checkoutId)
            ->get();

        if ($transactions->isEmpty()) {
            return redirect()->route('ebook.checkout')->with('error', 'Transaksi tidak ditemukan.');
        }

        if ($transactions->first()->isExpired()) {
            $transactions->each(fn ($transaction) => $transaction->update(['status' => 'expired']));
            return redirect()->route('ebook.checkout')->with('error', 'QR Code sudah kedaluwarsa. Silakan lakukan checkout ulang.');
        }

        $transactions->each(fn ($transaction) => $transaction->update(['status' => 'paid']));

        $bookIds = $transactions->pluck('book_id')->filter()->all();

        if (! empty($bookIds)) {
            Cart::where('user_id', auth()->id())
                ->whereIn('book_id', $bookIds)
                ->delete();
        }

        return redirect()->to(route('dashboard') . '#riwayat-ebook')
            ->with('success', 'Pembayaran berhasil dikonfirmasi. Lihat Riwayat Pembelian eBook untuk download.');
    }

    public function refreshCheckoutQr(string $checkoutId): JsonResponse
    {
        $transactions = EbookTransaction::with('book')
            ->where('user_id', auth()->id())
            ->where('checkout_id', $checkoutId)
            ->where('status', 'pending')
            ->get();

        if ($transactions->isEmpty()) {
            return response()->json([
                'message' => 'Transaksi pending tidak ditemukan.',
            ], 404);
        }

        $amountTotal = $transactions->sum('amount');
        $expiresAt = Carbon::now()->addMinutes(self::QR_EXPIRE_MINUTES);
        $invoiceCodes = $transactions->pluck('invoice_code')->all();

        $qrContent = sprintf(
            'QRIS|checkout:%s|amount:%s|user:%s|items:%s|expires:%s',
            $checkoutId,
            number_format($amountTotal, 2, '.', ''),
            auth()->id(),
            implode(',', $invoiceCodes),
            $expiresAt->timestamp
        );

        $qrFileName = "ebook_qr/{$checkoutId}.svg";
        Storage::disk('public')->put($qrFileName, QrCode::format('svg')->size(300)->generate($qrContent));

        $transactions->each(function (EbookTransaction $transaction) use ($qrFileName, $expiresAt): void {
            $transaction->update([
                'qr_code' => $qrFileName,
                'expires_at' => $expiresAt,
            ]);
        });

        return response()->json([
            'qr_url' => asset('storage/' . $qrFileName) . '?v=' . now()->timestamp,
            'expires_at' => $expiresAt->toIso8601String(),
            'expires_in_seconds' => max(now()->diffInSeconds($expiresAt, false), 0),
        ]);
    }

    public function download(Request $request, Book $book): View|\Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $payment = EbookTransaction::where('user_id', auth()->id())
            ->where('book_id', $book->id)
            ->where('status', 'paid')
            ->latest()
            ->first();

        if (! $payment) {
            return redirect()->route('ebook.buy', $book)->with('error', 'Anda belum membayar eBook ini.');
        }

        if ($request->query('download')) {
            $absolutePath = $this->resolveEbookAbsolutePath($book->file_ebook);

            if (! $absolutePath) {
                return redirect()->route('ebook.download', $book)
                    ->with('error', 'File eBook tidak ditemukan di server.');
            }

            $ext = pathinfo($book->file_ebook, PATHINFO_EXTENSION);
            $safeName = Str::slug($book->title) ?: 'ebook';

            return response()->download($absolutePath, $safeName . ($ext ? '.' . $ext : ''));
        }

        return view('ebook.download', compact('book', 'payment'));
    }

    private function resolveEbookAbsolutePath(?string $storedPath): ?string
    {
        if (! $storedPath) {
            return null;
        }

        $normalized = ltrim(str_replace('\\', '/', $storedPath), '/');
        $candidates = [$normalized];

        if (str_starts_with($normalized, 'storage/')) {
            $candidates[] = ltrim(substr($normalized, strlen('storage/')), '/');
        } else {
            $candidates[] = 'storage/' . $normalized;
        }

        foreach (array_unique($candidates) as $candidate) {
            if (Storage::disk('public')->exists($candidate)) {
                return Storage::disk('public')->path($candidate);
            }

            $publicStoragePath = public_path($candidate);

            if (is_file($publicStoragePath)) {
                return $publicStoragePath;
            }
        }

        return null;
    }
}

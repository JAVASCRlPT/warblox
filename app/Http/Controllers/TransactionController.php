<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\EbookTransaction;
use App\Models\Book;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TransactionController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display all transactions for admin.
     */
    public function index(Request $request): View
    {
        $query = Transaction::with(['user', 'book']);
        $ebookQuery = EbookTransaction::with(['user', 'book'])
            ->where('status', 'paid');

        // Filter berdasarkan pencarian mahasiswa
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('nim', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            });

            $ebookQuery->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('nim', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        // Filter berdasarkan status denda
        if ($request->filled('fine_status')) {
            if ($request->fine_status === 'denda') {
                $query->where('fine', '>', 0);
            } elseif ($request->fine_status === 'lunas') {
                $query->where('fine', '=', 0);
            }
        }

        // Exclude pending borrow requests from the main transaction list
        $query->where('status', '!=', 'pending_borrow');

        $transactions = $query->orderByDesc('created_at')->paginate(15)->withQueryString();
        $ebookTransactions = $ebookQuery
            ->orderByDesc('created_at')
            ->take(15)
            ->get();

        $stats = [
            'total' => Transaction::count(),
            'borrowed' => Transaction::where('status', 'dipinjam')->count(),
            'pending_return' => Transaction::where('status', 'pending_return')->count(),
            'pending_borrow' => Transaction::where('status', 'pending_borrow')->count(),
            'returned' => Transaction::where('status', 'kembali')->count(),
            'late' => Transaction::where('status', 'terlambat')->count(),
            'total_fine' => Transaction::sum('fine'),
            'users_with_fines' => Transaction::where('fine', '>', 0)->distinct('user_id')->count(),
            'total_fine_amount' => Transaction::where('fine', '>', 0)->sum('fine'),
            'ebook_revenue' => EbookTransaction::where('status', 'paid')->sum('amount'),
        ];

        return view('admin.transactions.index', compact('transactions', 'ebookTransactions', 'stats'));
    }

    /**
     * Display user's borrowing history.
     */
    public function history(): View
    {
        $transactions = auth()->user()->transactions()
            ->with('book')
            ->orderByDesc('borrow_date')
            ->paginate(10);

        return view('transactions.history', compact('transactions'));
    }

    /**
     * Borrow a book (create new transaction).
     */
    public function borrow(Book $book): RedirectResponse
    {
        // Check if user is mahasiswa
        if (auth()->user()->role !== 'mahasiswa') {
            return redirect()->back()->with('error', 'Hanya mahasiswa yang dapat meminjam buku!');
        }

        // Check book stock
        if ($book->stock <= 0) {
            return redirect()->back()->with('error', 'Stok buku sudah habis!');
        }

        // Check if user already borrowing or requesting this book
        $existingTransaction = Transaction::where('user_id', auth()->id())
            ->where('book_id', $book->id)
            ->whereIn('status', ['dipinjam', 'pending_borrow', 'terlambat'])
            ->first();

        if ($existingTransaction) {
            return redirect()->back()->with('error', 'Anda sudah meminjam atau menunggu persetujuan untuk buku ini!');
        }

        // Create transaction with pending_borrow status
        $borrowDate = Carbon::now();
        $dueDate = $borrowDate->copy()->addDays($book->loan_duration);

        $transaction = Transaction::create([
            'user_id' => auth()->id(),
            'book_id' => $book->id,
            'borrow_date' => $borrowDate,
            'due_date' => $dueDate,
            'status' => 'pending_borrow',
        ]);

        // Do NOT decrease stock until admin approves

        return redirect()->route('transactions.history')
            ->with('success', 'Permintaan peminjaman buku berhasil dikirim! Silahkan temui Admin untuk persetujuan.');
    }

    /**
     * Return a borrowed book.
     */
    public function return(Transaction $transaction): RedirectResponse
    {
        // Check authorization
        if ($transaction->user_id !== auth()->id()) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses!');
        }

        // Update transaction to pending return
        $transaction->status = 'pending_return';
        $transaction->save();

        return redirect()->route('transactions.history')
            ->with('success', 'Permintaan pengembalian buku telah dikirim ke admin untuk konfirmasi.');
    }

    /**
     * Admin view all transactions.
     */
    public function adminIndex(): View
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Anda tidak berhak mengakses halaman ini!');
        }

        $transactions = Transaction::with(['user', 'book'])
            ->orderByDesc('borrow_date')
            ->paginate(15);

        $stats = [
            'total' => Transaction::count(),
            'borrowed' => Transaction::where('status', 'dipinjam')->count(),
            'pending_return' => Transaction::where('status', 'pending_return')->count(),
            'pending_borrow' => Transaction::where('status', 'pending_borrow')->count(),
            'returned' => Transaction::where('status', 'kembali')->count(),
            'late' => Transaction::where('status', 'terlambat')->count(),
            'total_fine' => Transaction::sum('fine'),
        ];

        return view('admin.transactions.index', compact('transactions', 'stats'));
    }

    /**
     * Show transaction details for a specific user (admin only).
     */
    public function show(User $user): View
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Anda tidak berhak mengakses halaman ini!');
        }

        $transactions = $user->transactions()
            ->with('book.category')
            ->orderByDesc('borrow_date')
            ->get();

        $totalFine = $transactions->sum('fine');

        return view('admin.transactions.show', compact('user', 'transactions', 'totalFine'));
    }

    /**
     * Pay all fines for a user (admin only).
     */
    public function payFine(User $user): \Illuminate\Http\JsonResponse
    {
        if (auth()->user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak berhak mengakses halaman ini!'
            ], 403);
        }

        // Reset all fines for this user
        $user->transactions()->update(['fine' => 0]);

        return response()->json([
            'success' => true,
            'message' => 'Denda berhasil dilunasi!'
        ]);
    }

    /**
     * Approve return request (admin only).
     */
    public function approveReturn(Transaction $transaction)
    {
        if (auth()->user()->role !== 'admin') {
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak berhak mengakses halaman ini!'
                ], 403);
            }
            abort(403, 'Anda tidak berhak mengakses halaman ini!');
        }

        if ($transaction->status !== 'pending_return') {
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaksi tidak dalam status pending return!'
                ], 400);
            }
            return redirect()->back()->with('error', 'Transaksi tidak dalam status pending return!');
        }

        $returnDate = Carbon::now();
        $isLate = $returnDate->greaterThan($transaction->due_date);

        // Update transaction
        $transaction->return_date = $returnDate;
        $transaction->status = $isLate ? 'terlambat' : 'kembali';

        // Calculate fine if late
        if ($isLate) {
            $transaction->calculateFine();
        }

        $transaction->save();

        // Increase stock
        $transaction->book->increment('stock');

        $message = 'Pengembalian buku telah disetujui!';
        if ($isLate) {
            $message .= ' Denda keterlambatan: Rp ' . number_format($transaction->fine, 0, ',', '.');
        }

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Reject return request (admin only).
     */
    public function rejectReturn(Request $request, Transaction $transaction)
    {
        if (auth()->user()->role !== 'admin') {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak berhak mengakses halaman ini!'
                ], 403);
            }
            abort(403, 'Anda tidak berhak mengakses halaman ini!');
        }

        if ($transaction->status !== 'pending_return') {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaksi tidak dalam status pending return!'
                ], 400);
            }
            return redirect()->back()->with('error', 'Transaksi tidak dalam status pending return!');
        }

        // Validate request
        try {
            $validated = $request->validate([
                'fine' => 'required|numeric|min:0',
                'reject_reason' => 'required|string|max:500'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal: ' . implode(', ', array_values(array_merge(...array_values($e->errors()))))
                ], 422);
            }
            throw $e;
        }

        // Update transaction with rejection details
        $transaction->fine = $request->fine;
        $transaction->reject_reason = $request->reject_reason;
        $transaction->status = 'ditolak';
        $transaction->save();

        $message = 'Pengembalian buku telah ditolak dengan denda Rp ' . number_format($request->fine, 0, ',', '.') . '.';

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Approve borrow request (admin only).
     */
    public function approveBorrow(Request $request, Transaction $transaction)
    {
        if (auth()->user()->role !== 'admin') {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak berhak mengakses halaman ini!'
                ], 403);
            }
            abort(403, 'Anda tidak berhak mengakses halaman ini!');
        }

        if ($transaction->status !== 'pending_borrow') {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaksi tidak dalam status pending borrow!'
                ], 400);
            }
            return redirect()->back()->with('error', 'Transaksi tidak dalam status pending borrow!');
        }

        // Check if stock is available
        if ($transaction->book->stock <= 0) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stok buku tidak tersedia!'
                ], 400);
            }
            return redirect()->back()->with('error', 'Stok buku tidak tersedia!');
        }

        // Update transaction status to dipinjam
        $transaction->status = 'dipinjam';
        $transaction->save();

        // Decrease stock
        $transaction->book->decrement('stock');

        $message = 'Permintaan peminjaman buku berhasil disetujui!';

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Reject borrow request (admin only).
     */
    public function rejectBorrow(Request $request, Transaction $transaction)
    {
        if (auth()->user()->role !== 'admin') {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak berhak mengakses halaman ini!'
                ], 403);
            }
            abort(403, 'Anda tidak berhak mengakses halaman ini!');
        }

        if ($transaction->status !== 'pending_borrow') {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaksi tidak dalam status pending borrow!'
                ], 400);
            }
            return redirect()->back()->with('error', 'Transaksi tidak dalam status pending borrow!');
        }

        // Validate request
        try {
            $validated = $request->validate([
                'reject_reason' => 'required|string|max:500'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal: ' . implode(', ', array_values(array_merge(...array_values($e->errors()))))
                ], 422);
            }
            throw $e;
        }

        // Update transaction with rejection details
        $transaction->reject_reason = $request->reject_reason;
        $transaction->status = 'ditolak';
        $transaction->save();

        $message = 'Permintaan peminjaman buku telah ditolak.';

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Approve all pending borrow requests from a user (admin only).
     */
    public function approveAllBorrow(Request $request, User $user)
    {
        if (auth()->user()->role !== 'admin') {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak berhak mengakses halaman ini!'
                ], 403);
            }
            abort(403, 'Anda tidak berhak mengakses halaman ini!');
        }

        // Get all pending borrow requests from user
        $pendingTransactions = Transaction::where('user_id', $user->id)
            ->where('status', 'pending_borrow')
            ->get();

        if ($pendingTransactions->isEmpty()) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada permintaan peminjaman yang menunggu!'
                ], 400);
            }
            return redirect()->back()->with('error', 'Tidak ada permintaan peminjaman yang menunggu!');
        }

        // Track approval results
        $approved = 0;
        $failed = 0;
        $failedBooks = [];

        foreach ($pendingTransactions as $transaction) {
            // Check if stock is available
            if ($transaction->book->stock <= 0) {
                $failed++;
                $failedBooks[] = $transaction->book->title;
                continue;
            }

            // Update transaction status to dipinjam
            $transaction->status = 'dipinjam';
            $transaction->save();

            // Decrease stock
            $transaction->book->decrement('stock');
            $approved++;
        }

        $message = "Permintaan peminjaman berhasil disetujui: $approved buku";
        if ($failed > 0) {
            $message .= ", gagal: $failed buku (stok habis: " . implode(', ', $failedBooks) . ")";
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'approved' => $approved,
                'failed' => $failed
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Reject all pending borrow requests from a user (admin only).
     */
    public function rejectAllBorrow(Request $request, User $user)
    {
        if (auth()->user()->role !== 'admin') {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak berhak mengakses halaman ini!'
                ], 403);
            }
            abort(403, 'Anda tidak berhak mengakses halaman ini!');
        }

        $request->validate([
            'reject_reason' => 'required|string'
        ]);

        // Get all pending borrow requests from user
        $pendingTransactions = Transaction::where('user_id', $user->id)
            ->where('status', 'pending_borrow')
            ->get();

        if ($pendingTransactions->isEmpty()) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada permintaan peminjaman yang menunggu!'
                ], 400);
            }
            return redirect()->back()->with('error', 'Tidak ada permintaan peminjaman yang menunggu!');
        }

        // Reject all transactions
        $rejected = 0;
        foreach ($pendingTransactions as $transaction) {
            $transaction->status = 'ditolak';
            $transaction->reject_reason = $request->reject_reason;
            $transaction->save();
            $rejected++;
        }

        $message = "Permintaan peminjaman berhasil ditolak: $rejected buku";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'rejected' => $rejected
            ]);
        }

        return redirect()->back()->with('success', $message);
    }
}


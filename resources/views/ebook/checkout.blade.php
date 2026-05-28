@extends('layouts.app')

@section('title', 'Checkout eBook')

@section('content')
<div class="page-header">
    <h1><i class="bi bi-credit-card"></i> Checkout eBook</h1>
    <p class="text-muted mb-0">Bayar semua eBook di keranjang Anda dalam satu QRIS.</p>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-body">
                @if($cartItems->isEmpty() && $pendingTransactions->isEmpty())
                    <div class="text-center py-5">
                        <i class="bi bi-cart-x" style="font-size: 3rem;"></i>
                        <p class="mt-3 text-muted">Keranjang kosong atau tidak ada transaksi checkout saat ini.</p>
                        <a href="{{ route('books.index') }}" class="btn btn-primary">Telusuri Buku</a>
                    </div>
                @else
                    <h5 class="mb-3">Rincian Checkout</h5>
                    @if($pendingTransactions->isNotEmpty())
                        <div class="alert alert-warning">
                            <strong>Transaksi Pending:</strong> Silakan selesaikan pembayaran dengan QRIS di bawah.
                        </div>
                        <table class="table table-borderless mb-3">
                            <tbody>
                                @foreach($pendingTransactions as $transaction)
                                    <tr>
                                        <td>{{ $transaction->book->title }}</td>
                                        <td>Rp {{ number_format($transaction->amount, 0, ',', '.') }}</td>
                                        <td><span class="badge bg-secondary">{{ ucfirst($transaction->status) }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="text-center mb-3">
                            <img id="checkout-qr-image" src="{{ asset('storage/' . $pendingTransactions->first()->qr_code) }}" alt="QR Code Checkout" class="img-fluid" style="max-width: 320px;">
                        </div>
                        <p class="text-center mb-2">
                            <strong>Sisa Waktu QR:</strong>
                            <span id="qris-timer" class="badge bg-dark">05:00</span>
                        </p>
                        <p><strong>Invoice Group:</strong> {{ $checkoutId }}</p>
                        <p><strong>Total:</strong> Rp {{ number_format($pendingTransactions->sum('amount'), 0, ',', '.') }}</p>
                        <form method="POST" action="{{ route('ebook.confirm', $checkoutId) }}">
                            @csrf
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Konfirmasi Pembayaran
                            </button>
                        </form>
                    @else
                        <p>Anda memiliki {{ $cartItems->sum(fn($item) => max((int) $item->qty, 1)) }} eBook di keranjang.</p>
                        <ul class="list-group mb-3">
                            @foreach($cartItems as $item)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        {{ $item->book->title }}
                                        <small class="text-muted d-block">Qty: {{ max((int) $item->qty, 1) }}</small>
                                    </div>
                                    <span>Rp {{ number_format(($item->book->price ?? 0) * max((int) $item->qty, 1), 0, ',', '.') }}</span>
                                </li>
                            @endforeach
                        </ul>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <strong>Total Pembayaran:</strong>
                            <strong>Rp {{ number_format($cartItems->sum(fn($item) => ($item->book->price ?? 0) * max((int) $item->qty, 1)), 0, ',', '.') }}</strong>
                        </div>
                        <form method="POST" action="{{ route('ebook.checkout.process') }}">
                            @csrf
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-credit-card"></i> Buat QRIS Pembayaran
                            </button>
                        </form>
                    @endif
                @endif
            </div>
        </div>

    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h5>Informasi Pembayaran</h5>
                <p>QRIS akan berlaku selama 5 menit sejak dibuat.</p>
                <p>Setelah berhasil bayar, klik "Konfirmasi Pembayaran".</p>
                <p>Jika pembayaran gagal atau kadaluarsa, lakukan checkout ulang.</p>
            </div>
        </div>
    </div>
</div>
@endsection

@if($pendingTransactions->isNotEmpty())
@push('scripts')
<script>
(() => {
    const timerEl = document.getElementById('qris-timer');
    const qrImage = document.getElementById('checkout-qr-image');
    const refreshUrl = @json(route('ebook.checkout.refresh-qr', $checkoutId));
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    let expiresAt = new Date(@json(optional($pendingTransactions->first()->expires_at)->toIso8601String()));
    let isRefreshing = false;

    function formatTime(totalSeconds) {
        const minutes = Math.floor(totalSeconds / 60).toString().padStart(2, '0');
        const seconds = (totalSeconds % 60).toString().padStart(2, '0');
        return `${minutes}:${seconds}`;
    }

    function setTimerBadge(secondsLeft) {
        timerEl.textContent = formatTime(Math.max(secondsLeft, 0));
    }

    async function refreshQr() {
        if (isRefreshing) {
            return;
        }

        isRefreshing = true;
        timerEl.textContent = 'Memuat...';

        try {
            const response = await fetch(refreshUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({}),
            });

            if (!response.ok) {
                throw new Error('Gagal refresh QR');
            }

            const data = await response.json();
            expiresAt = new Date(data.expires_at);
            qrImage.src = data.qr_url;

            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'info',
                title: 'QRIS diperbarui otomatis.',
                showConfirmButton: false,
                timer: 1800,
            });
        } catch (error) {
            timerEl.textContent = '00:00';
            Swal.fire({
                icon: 'error',
                title: 'Gagal refresh QR',
                text: 'Silakan muat ulang halaman checkout.',
            });
        } finally {
            isRefreshing = false;
        }
    }

    setInterval(() => {
        const now = new Date();
        const secondsLeft = Math.floor((expiresAt.getTime() - now.getTime()) / 1000);

        if (secondsLeft <= 0) {
            setTimerBadge(0);
            refreshQr();
            return;
        }

        setTimerBadge(secondsLeft);
    }, 1000);
})();
</script>
@endpush
@endif

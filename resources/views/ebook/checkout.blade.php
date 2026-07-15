@extends('layouts.app')

@section('title', 'Checkout eBook')

@section('content')
<div class="page-header">
    <h1><i class="bi bi-credit-card"></i> Checkout eBook</h1>
    <p class="text-muted mb-0">Pilih metode pembayaran, lalu konfirmasi pembayaran melalui WhatsApp.</p>
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
                            <strong>Transaksi Pending:</strong> Silakan selesaikan pembayaran melalui WhatsApp.
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
                        <p><strong>Metode Pembayaran:</strong></p>
                        <form method="POST" action="{{ route('ebook.checkout.process') }}">
                            @csrf
                            <input type="hidden" name="checkout_id" value="{{ $checkoutId }}">
                            <div class="mb-3">
                                <select id="payment_method" name="payment_method" class="form-select" required>
                                    <option value="" disabled selected>Pilih Metode Pembayaran</option>
                                    <option value="DANA">DANA</option>
                                    <option value="Gopay">Gopay</option>
                                    <option value="ShopeePay">ShopeePay</option>
                                    <option value="Transfer Bank">Transfer Bank</option>
                                    <option value="QRIS">QRIS</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">Simpan Metode Pembayaran</button>
                        </form>
                        @if($paymentMethod)
                            <p><strong>Biaya Admin:</strong> Rp {{ number_format($checkoutFee, 0, ',', '.') }}</p>
                            <p><strong>Subtotal:</strong> Rp {{ number_format($pendingTransactions->sum('amount'), 0, ',', '.') }}</p>
                            <p><strong>Total Bayar:</strong> Rp {{ number_format($pendingTransactions->sum('amount') + $checkoutFee, 0, ',', '.') }}</p>
                            <p><strong>Checkout ID:</strong> {{ $checkoutId }}</p>
                            <div class="text-center mt-3">
                                <a href="{{ route('ebook.request-confirmation', $checkoutId) }}" target="_blank" class="btn btn-success btn-lg">
                                    <i class="bi bi-whatsapp"></i> Konfirmasi Pembayaran via WhatsApp
                                </a>
                            </div>
                        @endif
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
                            <div class="mb-3">
                                <label class="form-label"><strong>Metode Pembayaran:</strong></label>
                                <select id="payment_method" name="payment_method" class="form-select" required>
                                    <option value="" disabled selected>Pilih Metode Pembayaran</option>
                                    <option value="DANA">DANA</option>
                                    <option value="Gopay">Gopay</option>
                                    <option value="ShopeePay">ShopeePay</option>
                                    <option value="Transfer Bank">Transfer Bank</option>
                                    <option value="QRIS">QRIS</option>
                                </select>
                            </div>
                            <div class="alert alert-info">
                                <strong>Biaya Admin:</strong> Rp 100 - Rp 500 per transaksi.
                            </div>
                            <button type="submit" class="btn btn-success" id="checkout-submit-button" disabled>
                                <i class="bi bi-check2-circle"></i> Buat Checkout dan Konfirmasi via WhatsApp
                            </button>
                        </form>
                        <script>
                            document.addEventListener('DOMContentLoaded', function () {
                                const paymentMethodSelect = document.getElementById('payment_method');
                                const submitButton = document.getElementById('checkout-submit-button');
                                if (!paymentMethodSelect || !submitButton) {
                                    return;
                                }

                                paymentMethodSelect.addEventListener('change', function () {
                                    submitButton.disabled = paymentMethodSelect.value === '';
                                });
                            });
                        </script>
                    @endif
                @endif
            </div>
        </div>

    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h5>Informasi Pembayaran</h5>
                <p>Pilih opsi pembayaran di bawah tulisan <strong>Metode Pembayaran:</strong>.</p>
                <p>Biaya Admin: Rp 100 - Rp 500 per transaksi.</p>
                <p>Setelah checkout dibuat, konfirmasi pembayaran melalui tombol WhatsApp.</p>
            </div>
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Keranjang eBook')

@section('content')
<div class="page-header">
    <h1><i class="bi bi-cart"></i> Keranjang eBook</h1>
    <p class="text-muted mb-0">Lihat daftar eBook yang ingin Anda beli</p>
</div>

<div class="card">
    <div class="card-body">
        @if($cartItems->isEmpty())
            <div class="text-center py-5">
                <i class="bi bi-cart-x" style="font-size: 3rem;"></i>
                <p class="mt-3 text-muted">Keranjang Anda kosong.</p>
                <a href="{{ route('books.index') }}" class="btn btn-primary">Telusuri eBook</a>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead style="background: #f8f9fa;">
                        <tr>
                            <th>Judul</th>
                            <th>Harga</th>
                            <th>Qty</th>
                            <th>Subtotal</th>
                            <th>Format</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cartItems as $item)
                        <tr>
                            <td>
                                <strong>{{ $item->book->title }}</strong><br>
                                <small class="text-muted">{{ $item->book->author }}</small>
                            </td>
                            <td>Rp {{ number_format($item->book->price, 0, ',', '.') }}</td>
                            <td>{{ max((int) $item->qty, 1) }}</td>
                            <td>Rp {{ number_format(($item->book->price ?? 0) * max((int) $item->qty, 1), 0, ',', '.') }}</td>
                            <td>{{ pathinfo($item->book->file_ebook, PATHINFO_EXTENSION) }}</td>
                            <td>
                                <form method="POST" action="{{ route('ebook.cart.remove', $item->book) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="bi bi-trash"></i> Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-4">
                <div>
                    <div><strong>Total Item:</strong> {{ $cartItems->sum(fn($item) => max((int) $item->qty, 1)) }}</div>
                    <div><strong>Total:</strong> Rp {{ number_format($cartItems->sum(fn($item) => ($item->book->price ?? 0) * max((int) $item->qty, 1)), 0, ',', '.') }}</div>
                </div>
                <a href="{{ route('ebook.checkout') }}" class="btn btn-success">
                    <i class="bi bi-credit-card"></i> Lanjutkan ke Checkout
                </a>
            </div>
        @endif
    </div>
</div>
@endsection

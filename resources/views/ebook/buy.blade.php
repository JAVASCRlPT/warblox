@extends('layouts.app')

@section('title', 'Beli eBook - ' . $book->title)

@section('content')
<div class="page-header">
    <h1><i class="bi bi-cart4"></i> Beli eBook</h1>
    <p class="text-muted mb-0">Proses pembelian eBook untuk <strong>{{ $book->title }}</strong></p>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body p-4 text-center">
                @if($book->cover)
                    <img src="{{ asset('storage/' . $book->cover) }}" alt="Cover {{ $book->title }}" class="img-fluid mb-3">
                @else
                    <div class="cover-placeholder mb-3">
                        <i class="bi bi-book" style="font-size: 6rem;"></i>
                    </div>
                @endif
                <h4>{{ $book->title }}</h4>
                <p class="text-muted">{{ $book->author }}</p>
                <div class="mb-3">
                    <span class="badge bg-success">eBook</span>
                </div>
                <div class="mb-3">
                    <strong>Harga eBook:</strong><br>
                    Rp {{ number_format($book->price, 0, ',', '.') }}
                </div>
                <div class="mb-3">
                    <strong>Format:</strong><br>
                    {{ pathinfo($book->file_ebook, PATHINFO_EXTENSION) ?? 'PDF/ePub' }}
                </div>

                @if(auth()->check() && auth()->user()->isMahasiswa())
                    <form method="POST" action="{{ url(route('ebook.cart.add', $book, false)) }}">
                        @csrf
                        <input type="hidden" name="redirect_to" value="{{ url()->full() }}">
                        <button type="submit" class="btn btn-success w-100 mb-2">
                            <i class="bi bi-cart-plus"></i> Masukkan ke Keranjang
                        </button>
                    </form>
                    <a href="{{ url(route('ebook.cart', [], false)) }}" class="btn btn-outline-primary w-100 mb-2">
                        <i class="bi bi-cart3"></i> Lihat Keranjang
                    </a>
                    <a href="{{ url(route('ebook.checkout', [], false)) }}" class="btn btn-outline-success w-100">
                        <i class="bi bi-credit-card"></i> Lanjut Checkout
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-success w-100 mb-2">
                        <i class="bi bi-box-arrow-in-right"></i> Login untuk Beli
                    </a>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-8">
        @if($transaction && $transaction->status === 'paid')
            <div class="alert alert-success">
                <h5><i class="bi bi-check-circle"></i> Pembayaran berhasil</h5>
                <p>eBook sudah dibayar. Silakan unduh dari halaman berikut.</p>
                <a href="{{ route('ebook.download', $book) }}" class="btn btn-success">
                    <i class="bi bi-download"></i> Unduh eBook
                </a>
            </div>
        @elseif($transaction && $transaction->status === 'pending')
            <div class="alert alert-info">
                <h5><i class="bi bi-info-circle"></i> Anda punya checkout yang belum selesai</h5>
                <p>Lanjutkan pembayaran dari halaman checkout agar semua eBook diproses sekaligus.</p>
                <a href="{{ route('ebook.checkout') }}" class="btn btn-primary">
                    <i class="bi bi-credit-card"></i> Buka Checkout
                </a>
            </div>
        @endif

        <div class="card">
            <div class="card-header" style="background: #f8f9fa; border-bottom: 2px solid #3498db;">
                <h5 class="card-title mb-0">Ringkasan eBook</h5>
            </div>
            <div class="card-body">
                <p><strong>Judul:</strong> {{ $book->title }}</p>
                <p><strong>Penulis:</strong> {{ $book->author }}</p>
                <p><strong>Penerbit:</strong> {{ $book->publisher }}</p>
                <p><strong>Tahun:</strong> {{ $book->year }}</p>
                <p><strong>Harga:</strong> Rp {{ number_format($book->price, 0, ',', '.') }}</p>
                <p><strong>File eBook:</strong> {{ $book->file_ebook ? pathinfo($book->file_ebook, PATHINFO_BASENAME) : 'Belum tersedia' }}</p>
            </div>
        </div>
    </div>
</div>
@endsection

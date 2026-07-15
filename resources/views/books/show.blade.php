@extends('layouts.app')

@section('title', $book->title)

@section('content')
<div class="page-header">
    <h1><i class="bi bi-book"></i> {{ $book->title }}
        @if($book->file_ebook)
            <span class="badge bg-success ms-2">eBook</span>
        @endif
    </h1>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body p-4">
                <div class="book-cover mb-4" style="height: 300px;">
                    @if($book->cover)
                        <img src="{{ asset('storage/' . $book->cover) }}" alt="Cover {{ $book->title }}">
                    @else
                        <div class="cover-placeholder">
                            <i class="bi bi-book" style="font-size: 6rem;"></i>
                        </div>
                    @endif
                </div>

                @if($book->stock > 0)
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle"></i> Tersedia
                        <strong>({{ $book->stock }} copies)</strong>
                    </div>
                @else
                    <div class="alert alert-danger">
                        <i class="bi bi-x-circle"></i> Stok Habis
                    </div>
                @endif

                <div class="mb-3">
                    <strong>Kategori:</strong> 
                    <a href="{{ route('books.index', ['category_id' => $book->category_id]) }}" class="btn btn-sm btn-outline-primary">
                        {{ $book->category->name }}
                    </a>
                </div>

                @auth
                    @if(auth()->user()->isMahasiswa())
                        <div class="d-grid gap-2 d-md-flex">
                            @if($book->stock > 0)
                                <form method="POST" action="{{ route('books.borrow', $book) }}" class="flex-fill">
                                    @csrf
                                    <button type="submit" class="btn btn-primary w-100" onclick="return confirm('Pinjam buku ini?')">
                                        <i class="bi bi-bag-plus"></i> Pinjam Buku
                                    </button>
                                </form>
                            @endif

                            @if($book->file_ebook)
                                <form method="POST" action="{{ url(route('ebook.cart.add', $book, false)) }}" class="flex-fill">
                                    @csrf
                                    <input type="hidden" name="redirect_to" value="{{ url()->full() }}">
                                    <button type="submit" class="btn btn-success w-100" title="Tambahkan ke keranjang eBook">
                                        <i class="bi bi-cart-plus"></i> Beli eBook
                                    </button>
                                </form>
                            @endif
                        </div>

                        @if($book->file_ebook)
                            <div class="text-center mt-3">
                                <a href="{{ route('ebook.preview', $book) }}" target="_blank" class="btn btn-outline-secondary">
                                    <i class="bi bi-eye"></i> Preview eBook
                                </a>
                            </div>
                        @endif

                    @elseif(auth()->user()->isAdmin())
                        <div class="d-grid gap-2">
                            <a href="{{ route('admin.books.edit', $book) }}" class="btn btn-warning w-100 mb-2">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                            <form method="POST" action="{{ route('admin.books.destroy', $book) }}" style="display: block; width: 100%;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Hapus buku ini?')">
                                    <i class="bi bi-trash"></i> Hapus
                                </button>
                            </form>
                        </div>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="btn btn-primary w-100">
                        <i class="bi bi-bag-plus"></i> Pinjam Buku (Login)
                    </a>
                    @if($book->file_ebook)
                        <div class="text-center mt-3">
                            <a href="{{ route('ebook.preview', $book) }}" target="_blank" class="btn btn-outline-secondary">
                                <i class="bi bi-eye"></i> Preview eBook
                            </a>
                        </div>
                    @endif
                @endauth

                <hr class="my-4">

                <div class="mb-3">
                    <strong><i class="bi bi-pen"></i> Penulis:</strong><br>
                    {{ $book->author }}
                </div>

                <div class="mb-3">
                    <strong><i class="bi bi-building"></i> Penerbit:</strong><br>
                    {{ $book->publisher }}
                </div>

                <div class="mb-3">
                    <strong><i class="bi bi-calendar"></i> Tahun Terbit:</strong><br>
                    {{ $book->year }}
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header" style="background: #f8f9fa; border-bottom: 2px solid #3498db;">
                <h5 class="card-title mb-0">Deskripsi Buku</h5>
            </div>
            <div class="card-body">
                @if($book->description)
                    <div class="book-description">{{ $book->description }}</div>
                @else
                    <p class="text-muted"><i>Tidak ada deskripsi untuk buku ini</i></p>
                @endif
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header" style="background: #f8f9fa; border-bottom: 2px solid #3498db;">
                <h5 class="card-title mb-0">Informasi Tambahan</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>ID Buku:</strong> #{{ $book->id }}</p>
                        <p><strong>Kategori:</strong> {{ $book->category->name }}</p>
                        <p><strong>Durasi Peminjaman:</strong> {{ $book->loan_duration }} hari</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Total Stok:</strong> {{ $book->stock }}</p>
                        <p><strong>Ditambahkan:</strong> {{ $book->created_at->format('d-m-Y') }}</p>
                    </div>
                </div>
            </div>
        </div>

        @if($recommendedBooks->isNotEmpty())
            <div class="card mt-4">
                <div class="card-header" style="background: #f8f9fa; border-bottom: 2px solid #3498db;">
                    <h5 class="card-title mb-0">Rekomendasi Buku Lainnya</h5>
                </div>
                <div class="card-body">
                    <div class="row gy-3">
                        @foreach($recommendedBooks as $recommended)
                            <div class="col-md-6">
                                <div class="card h-100 shadow-sm">
                                    <div class="card-body p-3 d-flex align-items-center gap-3">
                                        <div class="recommendation-thumbnail flex-shrink-0" style="width: 70px; height: 100px; overflow: hidden; border-radius: 0.5rem; background: #f8f9fa; display: grid; place-items: center;">
                                            @if($recommended->cover)
                                                <img src="{{ asset('storage/' . $recommended->cover) }}" alt="Cover {{ $recommended->title }}" style="width: 100%; height: 100%; object-fit: cover;">
                                            @else
                                                <i class="bi bi-book" style="font-size: 1.5rem; color: #6c757d;"></i>
                                            @endif
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="card-title mb-2">{{ Str::limit($recommended->title, 60) }}</h6>
                                            <p class="card-text mb-2 text-muted" style="font-size: 0.9rem;">
                                                {{ $recommended->author }} — {{ $recommended->publisher }}
                                            </p>
                                            <a href="{{ route('books.show', $recommended) }}" class="btn btn-sm btn-outline-primary">
                                                Lihat Buku
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<div class="mt-4">
    <a href="{{ route('books.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Kembali ke Koleksi
    </a>
</div>

@endsection

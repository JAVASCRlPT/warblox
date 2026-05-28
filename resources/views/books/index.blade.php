@extends('layouts.app')

@section('title', 'Koleksi Buku')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="bi bi-book"></i> Koleksi Buku</h1>
            <p class="text-muted mb-0">Jelajahi dan cari buku di perpustakaan kami</p>
        </div>
        @if(auth()->check() && auth()->user()->isAdmin())
        <a href="{{ route('admin.books.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Tambah Buku
        </a>
        @endif
    </div>
</div>

<!-- Search and Filter -->
<div id="search-card" class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('books.index') }}" class="row g-3">
            <div class="col-md-6">
                <input type="text" class="form-control" name="search" placeholder="Cari buku (judul, penulis)..." 
                       value="{{ request('search') }}">
            </div>
            <div class="col-md-4">
                <select class="form-select" name="category_id" onchange="this.form.submit()">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Cari
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Books Grid -->
@if($books->isEmpty())
<div class="alert alert-info text-center py-5">
    <i class="bi bi-info-circle" style="font-size: 2rem;"></i>
    <p class="mt-3">Tidak ada buku yang sesuai dengan pencarian Anda</p>
</div>
@else
<div class="row books-grid">
    @foreach($books as $book)
    <div class="col-md-6 col-lg-4 col-xl-3 mb-4">
        <div class="card book-card h-100">
            <div class="book-cover">
                @if($book->cover)
                    <img src="{{ asset('storage/' . $book->cover) }}" alt="Cover {{ $book->title }}">
                @else
                    <div class="cover-placeholder">
                        <i class="bi bi-book"></i>
                    </div>
                @endif
            </div>
            <div class="book-title">{{ $book->title }}</div>
            <div class="book-author">
                <small><i class="bi bi-pen"></i> {{ $book->author }}</small>
            </div>
            <div class="book-author">
                <small><i class="bi bi-building"></i> {{ $book->publisher }}</small>
            </div>
            <div class="book-author">
                <small><i class="bi bi-calendar"></i> {{ $book->year }}</small>
            </div>
            @if($book->file_ebook)
            <div class="mt-2">
                <span class="badge bg-success">eBook</span>
            </div>
            @endif
            <div class="mt-auto">
                @if($book->stock > 0)
                    <div class="stock-badge mb-3">
                        <span class="badge bg-success"><i class="bi bi-check-circle"></i> Tersedia ({{ $book->stock }})</span>
                    </div>
                @else
                    <div class="stock-badge mb-3">
                        <span class="badge bg-danger"><i class="bi bi-x-circle"></i> Habis</span>
                    </div>
                @endif
                <a href="{{ route('books.show', $book) }}" class="btn btn-info btn-sm w-100 mb-2">
                    <i class="bi bi-eye"></i> Lihat Detail
                </a>
                
                @auth
                    @if(auth()->user()->isMahasiswa())
                        <div class="d-grid gap-2">
                            <div class="d-flex gap-2">
                                @if($book->stock > 0)
                                    <form method="POST" action="{{ route('books.borrow', $book) }}" class="flex-grow-1">
                                        @csrf
                                        <button type="submit" class="btn btn-primary btn-sm w-100" onclick="return confirm('Pinjam buku ini?')">
                                            <i class="bi bi-bag-plus"></i> Pinjam
                                        </button>
                                    </form>
                                @endif

                                @if($book->file_ebook)
                                    <form method="POST" action="{{ url(route('ebook.cart.add', $book, false)) }}" class="flex-grow-1">
                                        @csrf
                                        <input type="hidden" name="redirect_to" value="{{ url()->full() }}">
                                        <button type="submit" class="btn btn-success btn-sm w-100">
                                            <i class="bi bi-cart-plus"></i> Beli eBook
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @elseif(auth()->user()->isAdmin())
                        <div class="btn-group w-100" role="group">
                            <a href="{{ route('admin.books.edit', $book) }}" class="btn btn-warning btn-sm flex-grow-1">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.books.destroy', $book) }}" style="display: inline; flex-grow: 1;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm w-100" onclick="return confirm('Hapus buku ini?')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="btn btn-primary btn-sm w-100">
                        <i class="bi bi-bag-plus"></i> Pinjam (Login)
                    </a>
                @endauth
            </div>
        </div>
    </div>
    @endforeach
</div>

<!-- Load More Books -->
@if($books->hasMorePages())
<div class="text-center mt-4">
    <button id="load-more-btn" type="button" class="btn btn-primary" data-next-page="{{ $books->currentPage() + 1 }}" data-url="{{ route('books.index') }}" onclick="loadMoreBooks(this)">
        <i class="bi bi-arrow-down-circle"></i> Load More Books
    </button>
    <div id="loading-spinner" class="d-none mt-2">
        <div class="spinner-border spinner-border-sm" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        Loading more books...
    </div>
</div>
@else
<div class="text-center mt-4">
    <span class="text-muted">All books loaded</span>
</div>
@endif
@endif

<script>
let bookAutoLoader = null;
let isLoadingBooks = false;

function loadMoreBooks(btn) {
    if (!bookAutoLoader) {
        setupAutoScroll(btn);
    }
    loadNextPage(btn);
}

function setupAutoScroll(btn) {
    bookAutoLoader = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting && !isLoadingBooks) {
                loadNextPage(btn);
            }
        });
    }, {
        root: null,
        rootMargin: '0px 0px 200px 0px',
        threshold: 0.1,
    });

    bookAutoLoader.observe(btn);
}

function loadNextPage(btn) {
    const spinner = document.getElementById('loading-spinner');
    const container = document.querySelector('.books-grid');
    const nextPage = btn.getAttribute('data-next-page');
    const baseUrl = btn.getAttribute('data-url');

    if (!nextPage || isLoadingBooks) {
        return;
    }

    isLoadingBooks = true;
    btn.disabled = true;
    if (spinner) spinner.classList.remove('d-none');

    const currentUrl = new URL(window.location.href);
    const searchParams = new URLSearchParams(currentUrl.search);
    searchParams.set('page', nextPage);
    const url = baseUrl + '?' + searchParams.toString();

    fetch(url)
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newBooks = doc.querySelectorAll('.books-grid > .col-md-6');

            newBooks.forEach(book => {
                container.appendChild(book.cloneNode(true));
            });

            const nextBtn = doc.querySelector('#load-more-btn');
            if (nextBtn) {
                btn.setAttribute('data-next-page', nextBtn.getAttribute('data-next-page'));
                btn.disabled = false;
            } else {
                if (bookAutoLoader) {
                    bookAutoLoader.disconnect();
                    bookAutoLoader = null;
                }
                btn.parentElement.innerHTML = '<span class="text-muted">All books loaded</span>';
            }
        })
        .catch(error => {
            console.error('Error loading books:', error);
            btn.disabled = false;
        })
        .finally(() => {
            if (spinner) spinner.classList.add('d-none');
            isLoadingBooks = false;
        });
}
</script>

@endsection

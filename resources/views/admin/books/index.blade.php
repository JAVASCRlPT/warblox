@extends('layouts.app')

@section('title', 'Manajemen Buku')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="bi bi-collection"></i> Manajemen Buku</h1>
            <p class="text-muted mb-0">Kelola koleksi buku perpustakaan</p>
        </div>
        <a href="{{ route('admin.books.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Tambah Buku
        </a>
    </div>
</div>

<!-- Search -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.books.index') }}" class="row g-3">
            <div class="col-md-8">
                <input type="text" class="form-control" name="search" placeholder="Cari buku (judul, penulis)..." 
                       value="{{ request('search') }}">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Cari
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Books Table -->
<div class="card">
    <div class="card-body">
        @if($books->isEmpty())
        <p class="text-muted text-center py-5">Belum ada buku</p>
        @else
        <div class="table-responsive">
            <table class="table table-hover">
                <thead style="background: #f8f9fa;">
                    <tr>
                        <th>Judul</th>
                        <th>Penulis</th>
                        <th>Kategori</th>
                        <th>Tahun</th>
                        <th>Durasi</th>
                        <th>Stok</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($books as $book)
                    <tr>
                        <td><strong>{{ $book->title }}</strong></td>
                        <td>{{ $book->author }}</td>
                        <td>{{ $book->category->name }}</td>
                        <td>{{ $book->year }}</td>
                        <td>{{ $book->loan_duration }} hari</td>
                        <td>
                            @if($book->stock > 3)
                                <span class="badge bg-success">{{ $book->stock }}</span>
                            @elseif($book->stock > 0)
                                <span class="badge bg-warning">{{ $book->stock }}</span>
                            @else
                                <span class="badge bg-danger">{{ $book->stock }}</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('books.show', $book) }}" class="btn btn-sm btn-info" title="Lihat">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('admin.books.edit', $book) }}" class="btn btn-sm btn-warning" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.books.destroy', $book) }}" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" title="Hapus" onclick="return confirm('Hapus buku ini?')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

<!-- Pagination -->
@if($books->hasPages())
<div class="text-center mt-4">
    @if($books->hasMorePages())
        <a href="{{ $books->nextPageUrl() }}" class="btn btn-primary">
            <i class="bi bi-arrow-down-circle"></i> Load More Books
        </a>
    @else
        <span class="text-muted">All books loaded</span>
    @endif
</div>
@endif

@endsection

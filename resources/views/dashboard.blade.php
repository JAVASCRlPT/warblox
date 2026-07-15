@extends('layouts.app')

@section('title', 'Dashboard Mahasiswa')

@section('content')
<div class="page-header">
    <h1><i class="bi bi-speedometer2"></i> Dashboard Mahasiswa</h1>
    <p class="text-muted mb-0">Selamat datang, {{ auth()->user()->name }}!</p>
</div>

<!-- Statistics -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="stat-card">
            <div class="stat-number">{{ $stats['borrowed_count'] }}</div>
            <div class="stat-label">Buku Dipinjam</div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="stat-card">
            <div class="stat-number">{{ $stats['returned_count'] }}</div>
            <div class="stat-label">Buku Dikembalikan</div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="stat-card">
            <div class="stat-number" style="color: #e74c3c;">{{ $stats['overdue_count'] }}</div>
            <div class="stat-label">Buku Terlambat</div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="stat-card">
            <div class="stat-number" style="color: #e74c3c;">Rp {{ number_format($stats['total_fine'], 0, ',', '.') }}</div>
            <div class="stat-label">Total Denda</div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="stat-card">
            <div class="stat-number">{{ $stats['ebook_paid'] }}</div>
            <div class="stat-label">eBook Dibayar</div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="stat-card">
            <div class="stat-number" style="color: #f39c12;">{{ $stats['ebook_pending'] }}</div>
            <div class="stat-label">eBook Pending</div>
        </div>
    </div>
</div>

<!-- Currently Borrowed Books -->
<div id="riwayat-ebook" class="card mb-4">
    <div class="card-header" style="background: #f8f9fa; border-bottom: 2px solid #3498db;">
        <h5 class="card-title mb-0"><i class="bi bi-book-fill"></i> Buku yang Sedang Dipinjam</h5>
    </div>
    <div class="card-body">
        @if($borrowedBooks->isEmpty())
            <p class="text-muted text-center py-4">Anda belum meminjam buku apapun</p>
            <div class="text-center">
                <a href="{{ route('books.index') }}" class="btn btn-primary">
                    <i class="bi bi-search"></i> Cari Buku
                </a>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead style="background: #f8f9fa;">
                        <tr>
                            <th>Judul Buku</th>
                            <th>Penulis</th>
                            <th>Tanggal Pinjam</th>
                            <th>Tenggat Kembali</th>
                            <th>Hari Tersisa</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($borrowedBooks as $transaction)
                        <tr>
                            <td><strong>{{ $transaction->book->title }}</strong></td>
                            <td>{{ $transaction->book->author }}</td>
                            <td>{{ $transaction->borrow_date->format('d-m-Y') }}</td>
                            <td>{{ $transaction->due_date->format('d-m-Y') }}</td>
                            <td>
                                @if($transaction->isOverdue())
                                    <span class="badge bg-danger">
                                        <i class="bi bi-exclamation-circle"></i>
                                        Terlambat {{ abs($transaction->daysUntilDue()) }} hari
                                    </span>
                                @else
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle"></i>
                                        {{ $transaction->daysUntilDue() }} hari
                                    </span>
                                @endif
                            </td>
                            <td>
                                <form method="POST" action="{{ route('transactions.return', $transaction) }}" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Kembalikan buku ini?')">
                                        <i class="bi bi-check-circle"></i> Kembalikan
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

<!-- Riwayat Pembelian eBook -->
<div class="card mb-4">
    <div class="card-header" style="background: #f8f9fa; border-bottom: 2px solid #27ae60;">
        <h5 class="card-title mb-0"><i class="bi bi-journal-check"></i> Riwayat Pembelian eBook</h5>
    </div>
    <div class="card-body">
        @if($ebookPurchases->isEmpty())
            <p class="text-muted text-center py-3">Belum ada pembelian eBook yang selesai.</p>
        @else
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead style="background: #f8f9fa;">
                        <tr>
                            <th>Judul eBook</th>
                            <th>Qty</th>
                            <th>Total Bayar</th>
                            <th>Tanggal Pembelian</th>
                            <th>Invoice</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ebookPurchases as $purchase)
                            <tr>
                                <td>{{ $purchase->book->title }}</td>
                                <td>{{ max((int) $purchase->qty, 1) }}</td>
                                <td>Rp {{ number_format($purchase->amount, 0, ',', '.') }}</td>
                                <td>
                                    @if($purchase->updated_at)
                                        {{ $purchase->updated_at->format('d-m-Y') }}
                                    @elseif($purchase->created_at)
                                        {{ $purchase->created_at->format('d-m-Y') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td><small>{{ $purchase->invoice_code }}</small></td>
                                <td>
                                    <a href="{{ route('ebook.download', ['book' => $purchase->book, 'download' => 1]) }}" class="btn btn-sm btn-success">
                                        <i class="bi bi-download"></i> Download
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<!-- Quick Links -->
<div class="row">
    <div class="col-md-6 mb-3">
        <div class="card text-center p-4">
            <i class="bi bi-search" style="font-size: 2.5rem; color: #3498db;"></i>
            <h5 class="mt-3">Cari Buku</h5>
            <p class="text-muted">Temukan buku yang Anda inginkan di perpustakaan kami</p>
            <a href="{{ route('books.index') }}" class="btn btn-primary">Jelajahi Buku</a>
        </div>
    </div>
    <div class="col-md-6 mb-3">
        <div class="card text-center p-4">
            <i class="bi bi-file-text" style="font-size: 2.5rem; color: #27ae60;"></i>
            <h5 class="mt-3">Riwayat Peminjaman</h5>
            <p class="text-muted">Lihat semua riwayat peminjaman dan pengembalian buku Anda</p>
            <a href="{{ route('transactions.history') }}" class="btn btn-success">Lihat Riwayat</a>
        </div>
    </div>
</div>

@endsection

@extends('layouts.app')

@section('title', 'Riwayat Peminjaman')

@section('content')
<div class="page-header">
    <h1><i class="bi bi-file-text"></i> Riwayat Peminjaman Saya</h1>
    <p class="text-muted mb-0">Lihat semua peminjaman dan pengembalian buku Anda</p>
</div>

<div class="card">
    <div class="card-body">
        @if($transactions->isEmpty())
        <p class="text-muted text-center py-5">Anda belum memiliki riwayat peminjaman</p>
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
                        <th>Buku</th>
                        <th>Tanggal Pinjam</th>
                        <th>Tanggal Kembali</th>
                        <th>Hari Tersisa</th>
                        <th>Status</th>
                        <th>Denda</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transactions as $transaction)
                    <tr>
                        <td>
                            <strong>{{ $transaction->book->title }}</strong><br>
                            <small class="text-muted">{{ $transaction->book->author }}</small>
                        </td>
                        <td>{{ $transaction->borrow_date->format('d-m-Y') }}</td>
                        <td>
                            @if($transaction->return_date)
                                {{ $transaction->return_date->format('d-m-Y') }}
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if($transaction->status === 'dipinjam')
                                @php
                                    $daysLeft = \Carbon\Carbon::now()->diffInDays($transaction->due_date, false);
                                    $dueDateFormatted = $transaction->due_date->format('d-m-Y');
                                @endphp
                                @if($daysLeft > 0)
                                    <span class="badge bg-success">{{ ceil($daysLeft) }} hari</span><br>
                                    <small class="text-muted">({{ $dueDateFormatted }})</small>
                                @elseif($daysLeft === 0)
                                    <span class="badge bg-warning">Hari ini</span><br>
                                    <small class="text-muted">({{ $dueDateFormatted }})</small>
                                @else
                                    <span class="badge bg-danger">Terlambat {{ abs(ceil($daysLeft)) }} hari</span><br>
                                    <small class="text-muted">({{ $dueDateFormatted }})</small>
                                @endif
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($transaction->status === 'dipinjam')
                                <span class="badge bg-info">
                                    <i class="bi bi-info-circle"></i> Dipinjam
                                </span>
                            @elseif($transaction->status === 'pending_borrow')
                                <span class="badge bg-secondary">
                                    <i class="bi bi-clock-history"></i> Pending
                                </span>
                            @elseif($transaction->status === 'pending_return')
                                <span class="badge bg-warning">
                                    <i class="bi bi-clock"></i> Menunggu Konfirmasi
                                </span>
                            @elseif($transaction->status === 'kembali')
                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle"></i> Kembali
                                </span>
                            @elseif($transaction->status === 'ditolak')
                                <span class="badge bg-danger">
                                    <i class="bi bi-x-circle"></i> Ditolak
                                </span>
                            @else
                                <span class="badge bg-danger">
                                    <i class="bi bi-exclamation-circle"></i> Terlambat
                                </span>
                            @endif
                        </td>
                        <td>
                            @if($transaction->fine > 0)
                                <strong style="color: #e74c3c;">Rp {{ number_format($transaction->fine, 0, ',', '.') }}</strong>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($transaction->status === 'dipinjam')
                                <form method="POST" action="{{ route('transactions.return', $transaction) }}" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Kirim permintaan pengembalian buku ini?')">
                                        <i class="bi bi-check-circle"></i> Kembalikan
                                    </button>
                                </form>
                            @elseif($transaction->status === 'pending_return')
                                <small class="text-muted">Menunggu konfirmasi admin</small>
                            @elseif($transaction->status === 'ditolak')
                                <small class="text-danger">
                                    <strong>Ditolak:</strong> {{ $transaction->reject_reason ?? 'Tidak ada alasan' }}
                                </small>
                            @else
                                <small class="text-muted">Selesai</small>
                            @endif
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
<div class="d-flex justify-content-center mt-4">
    {{ $transactions->links() }}
</div>

@endsection

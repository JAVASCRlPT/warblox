@extends('layouts.app')

@section('title', 'Detail Transaksi - ' . $user->name)

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="bi bi-person-lines-fill"></i> Detail Transaksi: {{ $user->name }}</h1>
            <p class="text-muted mb-0">Riwayat peminjaman dan pengembalian buku</p>
        </div>
        <a href="{{ route('admin.transactions.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>
</div>

<!-- User Info -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header" style="background: #f8f9fa; border-bottom: 2px solid #3498db;">
                <h5 class="card-title mb-0">Informasi Mahasiswa</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <strong>Nama:</strong><br>
                        {{ $user->name }}
                    </div>
                    <div class="col-md-3">
                        <strong>Email:</strong><br>
                        {{ $user->email }}
                    </div>
                    <div class="col-md-3">
                        <strong>NIM:</strong><br>
                        {{ $user->nim ?? '-' }}
                    </div>
                    <div class="col-md-3">
                        <strong>Telepon:</strong><br>
                        {{ $user->phone ?? '-' }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Fine Summary -->
@if($totalFine > 0)
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card border-danger">
            <div class="card-header bg-danger text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-exclamation-triangle"></i> Total Denda: Rp {{ number_format($totalFine, 0, ',', '.') }}
                    </h5>
                    <button type="button" class="btn btn-light" onclick="payFine({{ $user->id }}, {{ $totalFine }})">
                        <i class="bi bi-cash"></i> Lunasi Denda
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Transactions Table -->
<div class="card">
    <div class="card-header" style="background: #f8f9fa; border-bottom: 2px solid #3498db;">
        <h5 class="card-title mb-0">Riwayat Transaksi</h5>
    </div>
    <div class="card-body">
        @if($transactions->isEmpty())
        <p class="text-muted text-center py-4">Belum ada transaksi untuk mahasiswa ini</p>
        @else
        <div class="table-responsive">
            <table class="table table-hover">
                <thead style="background: #f8f9fa;">
                    <tr>
                        <th>Buku</th>
                        <th>Tanggal Pinjam</th>
                        <th>Tenggat Kembali</th>
                        <th>Tanggal Kembali</th>
                        <th>Status</th>
                        <th>Denda</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transactions as $transaction)
                    <tr>
                        <td>
                            <strong>{{ $transaction->book->title }}</strong><br>
                            <small class="text-muted">{{ $transaction->book->author }} • {{ $transaction->book->category->name }}</small>
                        </td>
                        <td>{{ $transaction->borrow_date->format('d-m-Y') }}</td>
                        <td>{{ $transaction->due_date->format('d-m-Y') }}</td>
                        <td>
                            @if($transaction->return_date)
                                {{ $transaction->return_date->format('d-m-Y') }}
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if($transaction->status === 'dipinjam')
                                <span class="badge bg-info">Dipinjam</span>
                            @elseif($transaction->status === 'pending_return')
                                <span class="badge bg-warning">Menunggu Konfirmasi</span>
                            @elseif($transaction->status === 'kembali')
                                <span class="badge bg-success">Dikembalikan</span>
                            @else
                                <span class="badge bg-danger">Terlambat</span>
                            @endif
                        </td>
                        <td>
                            @if($transaction->fine > 0)
                                <strong style="color: #e74c3c;">Rp {{ number_format($transaction->fine, 0, ',', '.') }}</strong>
                            @else
                                <span class="text-muted">-</span>
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

@endsection

@push('scripts')
<script>
function payFine(userId, totalFine) {
    Swal.fire({
        title: 'Konfirmasi Pelunasan Denda',
        html: `
            <div class="text-center">
                <p><strong>Mahasiswa:</strong> {{ $user->name }}</p>
                <p><strong>Total Denda:</strong> Rp ${totalFine.toLocaleString('id-ID')}</p>
                <p>Apakah Anda yakin ingin melunasi semua denda mahasiswa ini?</p>
                <div class="alert alert-info">
                    <small>Denda yang sudah dilunasi tidak dapat dikembalikan.</small>
                </div>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Lunasi',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading
            Swal.fire({
                title: 'Memproses...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Send AJAX request
            fetch(`/admin/transactions/${userId}/pay-fine`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) {
                    return response.text().then(text => {
                        throw new Error(`HTTP ${response.status}: ${text}`);
                    });
                }
                return response.json();
            })
            .then(data => {
                Swal.fire({
                    title: 'Berhasil!',
                    text: data.message,
                    icon: 'success',
                    confirmButtonColor: '#28a745'
                }).then(() => {
                    // Reload page
                    window.location.reload();
                });
            })
            .catch(error => {
                Swal.fire({
                    title: 'Error!',
                    text: error.message || 'Terjadi kesalahan saat memproses pelunasan denda',
                    icon: 'error',
                    confirmButtonColor: '#dc3545'
                });
            });
        }
    });
}
</script>
@endpush

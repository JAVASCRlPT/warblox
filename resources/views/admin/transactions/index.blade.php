@extends('layouts.app')

@section('title', 'Manajemen Transaksi')

@push('styles')
<style>
.table-warning {
    background-color: #fff3cd !important;
    border-left: 4px solid #ffc107;
}

.table-warning:hover {
    background-color: #ffeaa7 !important;
}

.fine-indicator {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.search-highlight {
    background-color: #e3f2fd;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-weight: 500;
}
</style>
@endpush

@section('content')
<div class="page-header">
    <h1><i class="bi bi-file-text"></i> Manajemen Transaksi</h1>
    <p class="text-muted mb-0">Lihat semua transaksi peminjaman dan pengembalian buku</p>
</div>

<!-- Statistics -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="stat-card">
            <div class="stat-number">{{ $stats['total'] }}</div>
            <div class="stat-label">Total Transaksi</div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="stat-card">
            <div class="stat-number" style="color: #3498db;">{{ $stats['borrowed'] }}</div>
            <div class="stat-label">Sedang Dipinjam</div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="stat-card">
            <div class="stat-number" style="color: #27ae60;">{{ $stats['returned'] }}</div>
            <div class="stat-label">Sudah Dikembali</div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="stat-card">
            <div class="stat-number" style="color: #e74c3c;">{{ $stats['users_with_fines'] }}</div>
            <div class="stat-label">Mahasiswa Berdenda</div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-6 mb-3">
        <div class="stat-card">
            <div class="stat-number" style="color: #e74c3c;">Rp {{ number_format($stats['total_fine_amount'], 0, ',', '.') }}</div>
            <div class="stat-label">Total Denda Aktif</div>
        </div>
    </div>
    <div class="col-md-6 mb-3">
        <div class="stat-card">
            <div class="stat-number" style="color: #27ae60;">Rp {{ number_format($stats['ebook_revenue'], 0, ',', '.') }}</div>
            <div class="stat-label">Pendapatan eBook</div>
        </div>
    </div>
</div>

<!-- Search and Filter Form -->
<div class="card mb-4">
    <div class="card-header" style="background: #f8f9fa; border-bottom: 2px solid #3498db;">
        <h5 class="card-title mb-0"><i class="bi bi-search"></i> Pencarian & Filter</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.transactions.index') }}" class="row g-3">
            <div class="col-md-6">
                <label for="search" class="form-label">Cari Mahasiswa</label>
                <input type="text" class="form-control" id="search" name="search"
                       value="{{ request('search') }}"
                       placeholder="Nama, NIM, atau Email mahasiswa">
            </div>
            <div class="col-md-4">
                <label for="fine_status" class="form-label">Status Denda</label>
                <select class="form-select" id="fine_status" name="fine_status">
                    <option value="">Semua Status</option>
                    <option value="denda" {{ request('fine_status') === 'denda' ? 'selected' : '' }}>
                        Ada Denda
                    </option>
                    <option value="lunas" {{ request('fine_status') === 'lunas' ? 'selected' : '' }}>
                        Lunas
                    </option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="bi bi-search"></i> Cari
                </button>
                <a href="{{ route('admin.transactions.index') }}" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Reset
                </a>
            </div>
        </form>

        <!-- Quick Filters -->
        <div class="row mt-3">
            <div class="col-12">
                <small class="text-muted">Filter Cepat:</small>
                <div class="btn-group btn-group-sm ms-2" role="group">
                    <a href="{{ route('admin.transactions.index', ['fine_status' => 'denda']) }}" class="btn btn-outline-danger btn-sm">
                        <i class="bi bi-exclamation-triangle"></i> Berdenda ({{ $stats['users_with_fines'] }})
                    </a>
                    <a href="{{ route('admin.transactions.index', ['fine_status' => 'lunas']) }}" class="btn btn-outline-success btn-sm">
                        <i class="bi bi-check-circle"></i> Lunas
                    </a>
                    <a href="{{ route('admin.transactions.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-grid"></i> Semua
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Transactions Table -->
<div class="card">
    <div class="card-header" style="background: #f8f9fa; border-bottom: 2px solid #3498db;">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0"><i class="bi bi-table"></i> Daftar Transaksi</h5>
            @if(request('search') || request('fine_status'))
            <small class="text-muted">
                Menampilkan {{ $transactions->count() }} dari {{ $transactions->total() }} transaksi
                @if(request('search'))
                    untuk "<strong>{{ request('search') }}</strong>"
                @endif
                @if(request('fine_status'))
                    dengan status "<strong>{{ request('fine_status') === 'denda' ? 'Ada Denda' : 'Lunas' }}</strong>"
                @endif
            </small>
            @endif
        </div>
    </div>
    <div class="card-body">
        @if($transactions->isEmpty())
        <div class="text-center py-5">
            <i class="bi bi-inbox" style="font-size: 3rem; color: #6c757d;"></i>
            <p class="text-muted mt-3">
                @if(request('search') || request('fine_status'))
                    Tidak ada transaksi yang sesuai dengan kriteria pencarian
                @else
                    Belum ada transaksi
                @endif
            </p>
            @if(request('search') || request('fine_status'))
            <a href="{{ route('admin.transactions.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Tampilkan Semua
            </a>
            @endif
        </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover">
                <thead style="background: #f8f9fa;">
                    <tr>
                        <th>Mahasiswa</th>
                        <th>Buku</th>
                        <th>Tanggal Pinjam</th>
                        <th>Tenggat Kembali</th>
                        <th>Tanggal Kembali</th>
                        <th>Status</th>
                        <th>Denda</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transactions as $transaction)
                    <tr class="{{ $transaction->fine > 0 ? 'table-warning' : '' }}">
                        <td>
                            <strong>{{ $transaction->user->name }}</strong><br>
                            <small class="text-muted">{{ $transaction->user->nim ?? '-' }}</small>
                        </td>
                        <td>
                            <strong>{{ $transaction->book->title }}</strong><br>
                            <small class="text-muted">{{ $transaction->book->author }}</small>
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
                            @if($transaction->status === 'pending_borrow')
                                <span class="badge bg-secondary">Menunggu Konfirmasi Pinjam</span>
                            @elseif($transaction->status === 'dipinjam')
                                <span class="badge bg-info">Dipinjam</span>
                            @elseif($transaction->status === 'pending_return')
                                <span class="badge bg-warning">Menunggu Konfirmasi Kembali</span>
                            @elseif($transaction->status === 'kembali')
                                <span class="badge bg-success">Kembali</span>
                            @elseif($transaction->status === 'terlambat')
                                <span class="badge bg-danger">Terlambat</span>
                            @else
                                <span class="badge bg-danger">Ditolak</span>
                            @endif
                        </td>
                        <td>
                            @if($transaction->fine > 0)
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>
                                    <strong style="color: #e74c3c;">Rp {{ number_format($transaction->fine, 0, ',', '.') }}</strong>
                                </div>
                            @else
                                <span class="text-success">
                                    <i class="bi bi-check-circle-fill me-1"></i>Lunas
                                </span>
                            @endif
                        </td>
                        <td>
                            @if($transaction->status === 'pending_borrow')
                                @if($transaction->book->stock > 0)
                                    <form method="POST" action="{{ route('admin.transactions.approve-borrow', $transaction) }}" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Setujui peminjaman buku ini?')">
                                            <i class="bi bi-check-circle"></i> Setujui
                                        </button>
                                    </form>
                                @else
                                    <button type="button" class="btn btn-sm btn-secondary" disabled title="Stok habis">
                                        <i class="bi bi-lock"></i> Stok Habis
                                    </button>
                                @endif
                                <button type="button" class="btn btn-sm btn-danger" onclick="openRejectBorrowModal({{ $transaction->id }}, '{{ $transaction->user->name }}', '{{ $transaction->book->title }}')">
                                    <i class="bi bi-x-circle"></i> Tolak
                                </button>
                            @elseif($transaction->status === 'pending_return')
                                <form method="POST" action="{{ route('admin.transactions.approve-return', $transaction) }}" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Setujui pengembalian buku ini?')">
                                        <i class="bi bi-check-circle"></i> Setujui
                                    </button>
                                </form>
                                <button type="button" class="btn btn-sm btn-danger" onclick="openRejectModal({{ $transaction->id }}, '{{ $transaction->book->title }}')">
                                    <i class="bi bi-x-circle"></i> Tolak
                                </button>
                            @else
                                <a href="{{ route('admin.transactions.show', $transaction->user) }}" class="btn btn-sm btn-info">
                                    <i class="bi bi-eye"></i> Detail User
                                </a>
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

<!-- eBook Purchases Section -->
@if($ebookTransactions->isNotEmpty())
<div class="card mt-4">
    <div class="card-header" style="background: #f8f9fa; border-bottom: 2px solid #27ae60;">
        <h5 class="card-title mb-0"><i class="bi bi-journal-check"></i> Pembelian eBook</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead style="background: #f8f9fa;">
                    <tr>
                        <th>Mahasiswa</th>
                        <th>Buku</th>
                        <th>Tanggal Pembelian</th>
                        <th>Qty</th>
                        <th>Total Bayar</th>
                        <th>Invoice</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($ebookTransactions as $transaction)
                    <tr>
                        <td>
                            <strong>{{ $transaction->user->name }}</strong><br>
                            <small class="text-muted">{{ $transaction->user->nim ?? '-' }}</small>
                        </td>
                        <td>
                            <strong>{{ $transaction->book->title }}</strong><br>
                            <small class="text-muted">{{ $transaction->book->author }}</small>
                        </td>
                        <td>{{ $transaction->created_at->format('d-m-Y H:i') }}</td>
                        <td>{{ max((int) $transaction->qty, 1) }}</td>
                        <td>Rp {{ number_format($transaction->amount, 0, ',', '.') }}</td>
                        <td><small>{{ $transaction->invoice_code }}</small></td>
                        <td>
                            <span class="badge bg-success">{{ ucfirst($transaction->status) }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

<!-- Pagination -->
<div class="d-flex justify-content-center mt-4">
    {{ $transactions->links() }}
</div>

@endsection

@push('scripts')
<script>
// Auto-submit form when pressing Enter in search field
document.getElementById('search').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        this.closest('form').submit();
    }
});

// Auto-submit form when changing fine status filter
document.getElementById('fine_status').addEventListener('change', function() {
    this.closest('form').submit();
});

// Highlight search terms in results
document.addEventListener('DOMContentLoaded', function() {
    const searchTerm = '{{ request("search") }}';
    if (searchTerm) {
        highlightSearchTerms(searchTerm);
    }
});

function highlightSearchTerms(term) {
    const elements = document.querySelectorAll('td strong');
    elements.forEach(element => {
        if (element.textContent.toLowerCase().includes(term.toLowerCase())) {
            element.classList.add('search-highlight');
        }
    });
}

function openRejectBorrowModal(transactionId, userName, bookTitle) {
    Swal.fire({
        title: 'Tolak Permintaan Peminjaman',
        html: `
            <div class="mb-3">
                <p><strong>Mahasiswa:</strong> ${userName}</p>
                <p><strong>Buku:</strong> ${bookTitle}</p>
            </div>
            <div class="mb-3">
                <label for="reject_reason_borrow" class="form-label">Alasan Penolakan</label>
                <select class="form-select" id="reject_reason_borrow" required>
                    <option value="">Pilih alasan penolakan</option>
                    <option value="Stok tidak tersedia">Stok tidak tersedia</option>
                    <option value="User sudah meminjam buku lain">User sudah meminjam buku lain</option>
                    <option value="User memiliki denda">User memiliki denda</option>
                    <option value="Buku tidak valid">Buku tidak valid</option>
                    <option value="Lainnya">Lainnya</option>
                </select>
            </div>
        `,
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Tolak Peminjaman',
        cancelButtonText: 'Batal',
        preConfirm: () => {
            const rejectReason = document.getElementById('reject_reason_borrow').value;

            if (!rejectReason) {
                Swal.showValidationMessage('Alasan penolakan harus dipilih');
                return false;
            }

            return { reject_reason: rejectReason };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Create and submit form
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/transactions/${transactionId}/reject-borrow`;

            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            const reasonInput = document.createElement('input');
            reasonInput.type = 'hidden';
            reasonInput.name = 'reject_reason';
            reasonInput.value = result.value.reject_reason;

            form.appendChild(csrfToken);
            form.appendChild(reasonInput);
            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>
@endpush

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rejectModalLabel">Tolak Pengembalian Buku</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="rejectForm" method="POST">
                @csrf
                <div class="modal-body">
                    <p id="rejectBookTitle"></p>
                    <div class="mb-3">
                        <label for="fine" class="form-label">Denda (Rp)</label>
                        <input type="number" class="form-control" id="fine" name="fine" min="0" step="1000" required>
                        <div class="form-text">Masukkan jumlah denda yang harus dibayar mahasiswa</div>
                    </div>
                    <div class="mb-3">
                        <label for="reject_reason" class="form-label">Alasan Penolakan</label>
                        <textarea class="form-control" id="reject_reason" name="reject_reason" rows="3" maxlength="500" required></textarea>
                        <div class="form-text">Jelaskan alasan mengapa pengembalian buku ditolak</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Tolak Pengembalian</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openRejectModal(transactionId, bookTitle) {
    document.getElementById('rejectBookTitle').textContent = `Buku: ${bookTitle}`;
    document.getElementById('rejectForm').action = `/admin/transactions/${transactionId}/reject-return`;
    document.getElementById('fine').value = '';
    document.getElementById('reject_reason').value = '';
    
    const modal = new bootstrap.Modal(document.getElementById('rejectModal'));
    modal.show();
}
</script>

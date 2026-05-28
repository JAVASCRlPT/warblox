@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="page-header">
    <h1><i class="bi bi-speedometer2"></i> Admin Dashboard</h1>
    <p class="text-muted mb-0">Ringkasan statistik perpustakaan kampus</p>
</div>

<!-- Statistics -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="stat-card">
            <div class="stat-number">{{ $stats['total_books'] }}</div>
            <div class="stat-label">Total Buku</div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="stat-card">
            <div class="stat-number">{{ $stats['total_users'] }}</div>
            <div class="stat-label">Total Mahasiswa</div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="stat-card">
            <div class="stat-number" style="color: #3498db;">{{ $stats['borrowed_books'] }}</div>
            <div class="stat-label">Sedang Dipinjam</div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="stat-card">
            <div class="stat-number" style="color: #e74c3c;">{{ $stats['overdue_books'] }}</div>
            <div class="stat-label">Terlambat</div>
        </div>
    </div>
</div>

<!-- Additional Stats -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="stat-card">
            <div class="stat-number">{{ $stats['total_categories'] }}</div>
            <div class="stat-label">Kategori</div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="stat-card">
            <div class="stat-number" style="color: #f39c12;">{{ $stats['pending_returns'] + $stats['pending_borrows'] }}</div>
            <div class="stat-label">Menunggu Konfirmasi</div>
        </div>
    </div>
</div>

<!-- Pending Confirmations (Combined) -->
@php
    $allPending = collect();
    foreach($pendingReturns as $transaction) {
        $transaction->type = 'return';
        $allPending->push($transaction);
    }
    foreach($pendingBorrows as $transaction) {
        $transaction->type = 'borrow';
        $allPending->push($transaction);
    }
    $allPending = $allPending->sortByDesc('created_at');
@endphp

@if($allPending->isNotEmpty())
<div class="card mb-4">
    <div class="card-header" style="background: #fff3cd; border-bottom: 2px solid #ffc107;">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0"><i class="bi bi-bell"></i> Pemberitahuan Menunggu Konfirmasi</h5>
            <a href="{{ route('admin.transactions.index') }}" class="btn btn-sm btn-warning">Lihat Semua</a>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead style="background: #f8f9fa;">
                    <tr>
                        <th>Mahasiswa</th>
                        <th>Buku</th>
                        <th>Tipe</th>
                        <th>Info</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Grouped Borrow Requests by User -->
                    @foreach($pendingBorrows as $userId => $userTransactions)
                        @php
                            $user = $userTransactions->first()->user;
                            $borrowCount = $userTransactions->count();
                        @endphp
                        <tr data-user-id="{{ $userId }}" class="table-light">
                            <td>
                                <strong>{{ $user->name }}</strong><br>
                                <small class="text-muted">{{ $user->nim ?? '-' }}</small>
                                @if($borrowCount > 1)
                                    <br><small class="text-primary"><i class="bi bi-stack"></i> {{ $borrowCount }} buku</small>
                                @endif
                            </td>
                            <td colspan="3" class="fw-bold text-primary">
                                <i class="bi bi-book"></i> Permintaan Peminjaman Buku
                                <div class="mt-2">
                                    <button type="button" class="btn btn-sm btn-success me-2" onclick="approveAllBorrowsAjax({{ $userId }}, '{{ $user->name }}')">
                                        <i class="bi bi-check-circle-fill"></i> Setujui Semua
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger me-2" onclick="rejectAllBorrowsAjax({{ $userId }}, '{{ $user->name }}')">
                                        <i class="bi bi-x-circle-fill"></i> Tolak Semua
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-info" onclick="toggleBookDetails({{ $userId }})">
                                        <i class="bi bi-eye" id="icon-{{ $userId }}"></i> Detail Buku
                                    </button>
                                </div>
                            </td>
                            <td></td>
                        </tr>
                        @foreach($userTransactions as $transaction)
                        <tr data-user-id="{{ $userId }}" class="book-detail-row" id="book-row-{{ $transaction->id }}" style="display: none;">
                            <td></td>
                            <td>{{ $transaction->book->title }}</td>
                            <td><span class="badge bg-secondary">Peminjaman</span></td>
                            <td>
                                @if($transaction->book->stock > 0)
                                    <span class="badge bg-success">{{ $transaction->book->stock }} tersedia</span>
                                @else
                                    <span class="badge bg-danger">Stok Habis</span>
                                @endif
                            </td>
                            <td>
                                @if($transaction->book->stock > 0)
                                    <button type="button" class="btn btn-sm btn-success" onclick="approveBorrowAjax({{ $transaction->id }}, '{{ $user->name }}', '{{ $transaction->book->title }}')">
                                        <i class="bi bi-check-circle"></i> Setujui
                                    </button>
                                @else
                                    <button type="button" class="btn btn-sm btn-secondary" disabled title="Stok habis">
                                        <i class="bi bi-lock"></i> Stok Habis
                                    </button>
                                @endif
                                <button type="button" class="btn btn-sm btn-danger" onclick="rejectBorrowAjax({{ $transaction->id }}, '{{ $user->name }}', '{{ $transaction->book->title }}')">
                                    <i class="bi bi-x-circle"></i> Tolak
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    @endforeach

                    <!-- Individual Return Requests -->
                    @foreach($pendingReturns as $transaction)
                    <tr data-transaction-id="{{ $transaction->id }}">
                        <td>
                            <strong>{{ $transaction->user->name }}</strong><br>
                            <small class="text-muted">{{ $transaction->user->nim ?? '-' }}</small>
                        </td>
                        <td>{{ $transaction->book->title }}</td>
                        <td><span class="badge bg-warning">Pengembalian</span></td>
                        <td><span class="text-muted">{{ $transaction->borrow_date->format('d-m-Y') }}</span></td>
                        <td>
                            <button type="button" class="btn btn-sm btn-success" onclick="approveReturnAjax({{ $transaction->id }}, '{{ $transaction->user->name }}', '{{ $transaction->book->title }}')">
                                <i class="bi bi-check-circle"></i> Setujui
                            </button>
                            <button type="button" class="btn btn-sm btn-danger" onclick="rejectReturnAjax({{ $transaction->id }}, '{{ $transaction->user->name }}', '{{ $transaction->book->title }}')">
                                <i class="bi bi-x-circle"></i> Tolak
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

<!-- Recent Transactions -->
<div class="card mb-4">
    <div class="card-header" style="background: #f8f9fa; border-bottom: 2px solid #3498db;">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0"><i class="bi bi-clock-history"></i> Transaksi Terbaru</h5>
            <a href="{{ route('admin.transactions.index') }}" class="btn btn-sm btn-primary">Lihat Semua</a>
        </div>
    </div>
    <div class="card-body">
        @if($recentTransactions->isEmpty())
            <p class="text-muted text-center py-4">Belum ada transaksi</p>
        @else
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead style="background: #f8f9fa;">
                        <tr>
                            <th>Mahasiswa</th>
                            <th>Buku</th>
                            <th>Tanggal Transaksi</th>
                            <th>Jenis</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentTransactions as $transaction)
                        <tr>
                            <td><strong>{{ $transaction->user->name }}</strong><br><small class="text-muted">{{ $transaction->user->nim ?? '-' }}</small></td>
                            <td>{{ $transaction->book->title }}</td>
                            <td>{{ $transaction->date->format('d-m-Y H:i') }}</td>
                            <td>
                                @if($transaction->type === 'ebook')
                                    <span class="badge bg-success">Pembelian eBook</span>
                                @else
                                    <span class="badge bg-primary">Peminjaman Buku</span>
                                @endif
                            </td>
                            <td>
                                @if($transaction->type === 'ebook')
                                    <span class="badge bg-success">Lunas · Rp {{ number_format($transaction->amount ?? 0, 0, ',', '.') }}</span>
                                @else
                                    @if($transaction->status === 'dipinjam')
                                        <span class="badge bg-info">Dipinjam</span>
                                    @elseif($transaction->status === 'kembali')
                                        <span class="badge bg-success">Kembali</span>
                                    @else
                                        <span class="badge bg-danger">Terlambat</span>
                                    @endif
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.transactions.show', $transaction->user) }}" class="btn btn-sm btn-info">
                                    <i class="bi bi-eye"></i>
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

<!-- Low Stock Books -->
<div class="card mb-4">
    <div class="card-header" style="background: #fff3cd; border-bottom: 2px solid #ffc107;">
        <h5 class="card-title mb-0"><i class="bi bi-exclamation-triangle"></i> Buku Stok Rendah</h5>
    </div>
    <div class="card-body">
        @if($lowStockBooks->isNotEmpty())
        <div class="table-responsive">
            <table class="table table-hover">
                <thead style="background: #f8f9fa;">
                    <tr>
                        <th>Judul</th>
                        <th>Kategori</th>
                        <th>Stok</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($lowStockBooks as $book)
                    <tr>
                        <td><strong>{{ $book->title }}</strong></td>
                        <td>{{ $book->category->name }}</td>
                        <td>
                            <span class="badge bg-warning">{{ $book->stock }} items</span>
                        </td>
                        <td>
                            <a href="{{ route('admin.books.edit', $book) }}" class="btn btn-sm btn-warning">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-4">
            <i class="bi bi-check-circle" style="font-size: 3rem; color: #27ae60;"></i>
            <p class="text-muted mt-3">Semua buku memiliki stok yang cukup</p>
        </div>
        @endif
    </div>
</div>

<!-- Quick Actions -->
<div class="row">
    <div class="col-md-3 mb-3">
        <a href="{{ route('admin.books.create') }}" class="text-decoration-none text-dark">
            <div class="card text-center p-4 h-100">
                <i class="bi bi-book" style="font-size: 2.5rem; color: #3498db;"></i>
                <h5 class="mt-3">Tambah Buku</h5>
                <div class="btn btn-sm btn-primary mt-2">Tambah</div>
            </div>
        </a>
    </div>
    <div class="col-md-3 mb-3">
        <a href="{{ route('admin.categories.index') }}" class="text-decoration-none text-dark">
            <div class="card text-center p-4 h-100">
                <i class="bi bi-tags" style="font-size: 2.5rem; color: #27ae60;"></i>
                <h5 class="mt-3">Kategori</h5>
                <div class="btn btn-sm btn-success mt-2">Lihat</div>
            </div>
        </a>
    </div>
    <div class="col-md-3 mb-3">
        <a href="{{ route('admin.users.index') }}" class="text-decoration-none text-dark">
            <div class="card text-center p-4 h-100">
                <i class="bi bi-people" style="font-size: 2.5rem; color: #e74c3c;"></i>
                <h5 class="mt-3">Mahasiswa</h5>
                <div class="btn btn-sm btn-danger mt-2">Kelola</div>
            </div>
        </a>
    </div>
    <div class="col-md-3 mb-3">
        <a href="{{ route('admin.transactions.index') }}" class="text-decoration-none text-dark">
            <div class="card text-center p-4 h-100">
                <i class="bi bi-file-text" style="font-size: 2.5rem; color: #f39c12;"></i>
                <h5 class="mt-3">Transaksi</h5>
                <div class="btn btn-sm btn-warning mt-2">Lihat</div>
            </div>
        </a>
    </div>
</div>

<script>
function approveReturnAjax(transactionId, userName, bookTitle) {
    Swal.fire({
        title: 'Setujui Pengembalian Buku',
        html: `
            <div class="text-center">
                <p><strong>Mahasiswa:</strong> ${userName}</p>
                <p><strong>Buku:</strong> ${bookTitle}</p>
                <p>Apakah Anda yakin ingin menyetujui pengembalian buku ini?</p>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Setujui',
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
            fetch(`/admin/transactions/${transactionId}/approve-return`, {
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
                    // Remove the row from the table
                    const row = document.querySelector(`tr[data-transaction-id="${transactionId}"]`);
                    if (row) {
                        row.style.display = 'none';
                    }
                    
                    // Reload page after short delay
                    setTimeout(() => {
                        window.location.reload();
                    }, 500);
                });
            })
            .catch(error => {
                Swal.fire({
                    title: 'Error!',
                    text: error.message || 'Terjadi kesalahan saat memproses permintaan',
                    icon: 'error',
                    confirmButtonColor: '#dc3545'
                });
            });
        }
    });
}

function rejectReturnAjax(transactionId, userName, bookTitle) {
    Swal.fire({
        title: 'Tolak Pengembalian Buku',
        html: `
            <div class="mb-3">
                <p><strong>Mahasiswa:</strong> ${userName}</p>
                <p><strong>Buku:</strong> ${bookTitle}</p>
            </div>
            <div class="mb-3">
                <label for="fine" class="form-label">Denda (Rp)</label>
                <input type="number" class="form-control" id="fine" min="0" step="1000" placeholder="Masukkan nominal denda" required>
            </div>
            <div class="mb-3">
                <label for="reject_reason" class="form-label">Alasan Penolakan</label>
                <select class="form-select" id="reject_reason" required>
                    <option value="">Pilih alasan penolakan</option>
                    <option value="Buku rusak atau hilang">Buku rusak atau hilang</option>
                    <option value="Buku tidak sesuai kondisi awal">Buku tidak sesuai kondisi awal</option>
                    <option value="Pengembalian terlambat">Pengembalian terlambat</option>
                    <option value="Data tidak valid">Data tidak valid</option>
                    <option value="Lainnya">Lainnya</option>
                </select>
            </div>
        `,
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Tolak Pengembalian',
        cancelButtonText: 'Batal',
        preConfirm: () => {
            const fine = document.getElementById('fine').value;
            const rejectReason = document.getElementById('reject_reason').value;

            if (!fine || fine < 0) {
                Swal.showValidationMessage('Denda harus diisi dengan angka positif');
                return false;
            }

            if (!rejectReason) {
                Swal.showValidationMessage('Alasan penolakan harus dipilih');
                return false;
            }

            return { fine: fine, reject_reason: rejectReason };
        }
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
            fetch(`/admin/transactions/${transactionId}/reject-return`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    fine: result.value.fine,
                    reject_reason: result.value.reject_reason
                })
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
                    // Remove the row from the table
                    const row = document.querySelector(`tr[data-transaction-id="${transactionId}"]`);
                    if (row) {
                        row.style.display = 'none';
                    }
                    
                    // Reload page after short delay
                    setTimeout(() => {
                        window.location.reload();
                    }, 500);
                });
            })
            .catch(error => {
                Swal.fire({
                    title: 'Error!',
                    text: error.message || 'Terjadi kesalahan saat memproses permintaan',
                    icon: 'error',
                    confirmButtonColor: '#dc3545'
                });
            });
        }
    });
}

function approveReturn(transactionId, userName, bookTitle) {
    Swal.fire({
        title: 'Setujui Pengembalian Buku',
        html: `
            <div class="text-center">
                <p><strong>Mahasiswa:</strong> ${userName}</p>
                <p><strong>Buku:</strong> ${bookTitle}</p>
                <p>Apakah Anda yakin ingin menyetujui pengembalian buku ini?</p>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Setujui',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            // Create and submit form
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/transactions/${transactionId}/approve-return`;

            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            form.appendChild(csrfToken);
            document.body.appendChild(form);
            form.submit();
        }
    });
}

function rejectReturn(transactionId, userName, bookTitle) {
    Swal.fire({
        title: 'Tolak Pengembalian Buku',
        html: `
            <div class="mb-3">
                <p><strong>Mahasiswa:</strong> ${userName}</p>
                <p><strong>Buku:</strong> ${bookTitle}</p>
            </div>
            <div class="mb-3">
                <label for="fine" class="form-label">Denda (Rp)</label>
                <input type="number" class="form-control" id="fine" min="0" step="1000" placeholder="Masukkan nominal denda" required>
            </div>
            <div class="mb-3">
                <label for="reject_reason" class="form-label">Alasan Penolakan</label>
                <select class="form-select" id="reject_reason" required>
                    <option value="">Pilih alasan penolakan</option>
                    <option value="Buku rusak atau hilang">Buku rusak atau hilang</option>
                    <option value="Buku tidak sesuai kondisi awal">Buku tidak sesuai kondisi awal</option>
                    <option value="Pengembalian terlambat">Pengembalian terlambat</option>
                    <option value="Data tidak valid">Data tidak valid</option>
                    <option value="Lainnya">Lainnya</option>
                </select>
            </div>
        `,
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Tolak Pengembalian',
        cancelButtonText: 'Batal',
        preConfirm: () => {
            const fine = document.getElementById('fine').value;
            const rejectReason = document.getElementById('reject_reason').value;

            if (!fine || fine < 0) {
                Swal.showValidationMessage('Denda harus diisi dengan angka positif');
                return false;
            }

            if (!rejectReason) {
                Swal.showValidationMessage('Alasan penolakan harus dipilih');
                return false;
            }

            return { fine: fine, reject_reason: rejectReason };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Create and submit form
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/transactions/${transactionId}/reject-return`;

            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            const fineInput = document.createElement('input');
            fineInput.type = 'hidden';
            fineInput.name = 'fine';
            fineInput.value = result.value.fine;

            const reasonInput = document.createElement('input');
            reasonInput.type = 'hidden';
            reasonInput.name = 'reject_reason';
            reasonInput.value = result.value.reject_reason;

            form.appendChild(csrfToken);
            form.appendChild(fineInput);
            form.appendChild(reasonInput);
            document.body.appendChild(form);
            form.submit();
        }
    });
}

function approveBorrowAjax(transactionId, userName, bookTitle) {
    Swal.fire({
        title: 'Setujui Peminjaman Buku',
        html: `
            <div class="text-center">
                <p><strong>Mahasiswa:</strong> ${userName}</p>
                <p><strong>Buku:</strong> ${bookTitle}</p>
                <p>Apakah Anda yakin ingin menyetujui peminjaman buku ini?</p>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Setujui',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/admin/transactions/${transactionId}/approve-borrow`, {
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
                    // Remove the row from the table
                    const row = document.querySelector(`tr[data-transaction-id="${transactionId}"]`);
                    if (row) {
                        row.style.display = 'none';
                    }
                    
                    // Reload page after short delay
                    setTimeout(() => {
                        window.location.reload();
                    }, 500);
                });
            })
            .catch(error => {
                Swal.fire({
                    title: 'Error!',
                    text: error.message || 'Terjadi kesalahan saat memproses permintaan',
                    icon: 'error',
                    confirmButtonColor: '#dc3545'
                });
            });
        }
    });
}

function rejectBorrowAjax(transactionId, userName, bookTitle) {
    Swal.fire({
        title: 'Tolak Peminjaman Buku',
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

function approveAllBorrowsAjax(userId, userName) {
    Swal.fire({
        title: 'Setujui Semua Peminjaman',
        html: `
            <div class="text-center">
                <p><strong>Mahasiswa:</strong> ${userName}</p>
                <p>Apakah Anda yakin ingin menyetujui semua peminjaman buku dari mahasiswa ini?</p>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Setujui Semua',
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
            fetch(`/admin/transactions/${userId}/approve-all-borrow`, {
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
                    // Remove all rows for this user
                    const userRows = document.querySelectorAll(`tr[data-user-id="${userId}"]`);
                    userRows.forEach(row => row.style.display = 'none');
                    
                    // Reload page after short delay
                    setTimeout(() => {
                        window.location.reload();
                    }, 500);
                });
            })
            .catch(error => {
                Swal.fire({
                    title: 'Error!',
                    text: error.message || 'Terjadi kesalahan saat memproses permintaan',
                    icon: 'error',
                    confirmButtonColor: '#dc3545'
                });
            });
        }
    });
}

function rejectAllBorrowsAjax(userId, userName) {
    Swal.fire({
        title: 'Tolak Semua Peminjaman',
        html: `
            <div class="mb-3">
                <p><strong>Mahasiswa:</strong> ${userName}</p>
            </div>
            <div class="mb-3">
                <label for="reject_reason_all_borrow" class="form-label">Alasan Penolakan</label>
                <select class="form-select" id="reject_reason_all_borrow" required>
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
        confirmButtonText: 'Tolak Semua Peminjaman',
        cancelButtonText: 'Batal',
        preConfirm: () => {
            const rejectReason = document.getElementById('reject_reason_all_borrow').value;

            if (!rejectReason) {
                Swal.showValidationMessage('Alasan penolakan harus dipilih');
                return false;
            }

            return { reject_reason: rejectReason };
        }
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
            fetch(`/admin/transactions/${userId}/reject-all-borrow`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    reject_reason: result.value.reject_reason
                })
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
                    // Remove all rows for this user
                    const userRows = document.querySelectorAll(`tr[data-user-id="${userId}"]`);
                    userRows.forEach(row => row.style.display = 'none');
                    
                    // Reload page after short delay
                    setTimeout(() => {
                        window.location.reload();
                    }, 500);
                });
            })
            .catch(error => {
                Swal.fire({
                    title: 'Error!',
                    text: error.message || 'Terjadi kesalahan saat memproses permintaan',
                    icon: 'error',
                    confirmButtonColor: '#dc3545'
                });
            });
        }
    });
}

// Toggle icon for book details collapse
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('[data-bs-toggle="collapse"]').forEach(button => {
        const collapseElement = document.querySelector(button.getAttribute('data-bs-target'));
        const icon = button.querySelector('i');

        collapseElement.addEventListener('shown.bs.collapse', function() {
            icon.className = 'bi bi-eye-slash';
        });

        collapseElement.addEventListener('hidden.bs.collapse', function() {
            icon.className = 'bi bi-eye';
        });
    });
});

// Toggle book details rows
function toggleBookDetails(userId) {
    const bookRows = document.querySelectorAll(`tr.book-detail-row[data-user-id="${userId}"]`);
    const icon = document.getElementById(`icon-${userId}`);
    let isVisible = false;

    // Check if any row is currently visible
    bookRows.forEach(row => {
        if (row.style.display !== 'none') {
            isVisible = true;
        }
    });

    if (isVisible) {
        // Hide all book detail rows
        bookRows.forEach(row => {
            row.style.display = 'none';
        });
        icon.className = 'bi bi-eye';
    } else {
        // Show all book detail rows
        bookRows.forEach(row => {
            row.style.display = 'table-row';
        });
        icon.className = 'bi bi-eye-slash';
    }
}
</script>

@endsection

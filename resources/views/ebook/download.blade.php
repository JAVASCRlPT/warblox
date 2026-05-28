@extends('layouts.app')

@section('title', 'Download eBook - ' . $book->title)

@section('content')
<div class="page-header">
    <h1><i class="bi bi-download"></i> Download eBook</h1>
    <p class="text-muted mb-0">Unduh eBook setelah pembayaran berhasil.</p>
</div>

<div class="card">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-4 text-center">
                @if($book->cover)
                    <img src="{{ asset('storage/' . $book->cover) }}" alt="Cover {{ $book->title }}" class="img-fluid mb-3">
                @else
                    <div class="cover-placeholder mb-3">
                        <i class="bi bi-book" style="font-size: 4rem;"></i>
                    </div>
                @endif
            </div>
            <div class="col-md-8">
                <h3>{{ $book->title }}</h3>
                <p class="text-muted">{{ $book->author }}</p>
                <p><strong>Invoice:</strong> {{ $payment->invoice_code }}</p>
                <p><strong>Status:</strong>
                    <span class="badge bg-success">{{ ucfirst($payment->status) }}</span>
                </p>
                <a href="{{ route('ebook.download', $book, ['download' => 1]) }}" class="btn btn-success">
                    <i class="bi bi-box-arrow-down"></i> Download eBook
                </a>
                <p class="text-muted mt-3">File akan diunduh langsung jika pembayaran sudah diverifikasi.</p>
            </div>
        </div>
    </div>
</div>
@endsection

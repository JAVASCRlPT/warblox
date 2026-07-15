@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="container" style="max-width: 400px; margin: 50px auto;">
    <div class="card shadow-lg">
        <div class="card-body p-5">
            <div class="text-center mb-4">
                <h2 class="card-title" style="color: #2c3e50;">Login Perpustakaan v.2.1</h2>
                <p class="text-muted">Masukkan email dan password Anda</p>
            </div>

            @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Gagal Login!</strong>
                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                           id="email" name="email" value="{{ old('email') }}" required autofocus>
                    @error('email')
                    <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control @error('password') is-invalid @enderror" 
                           id="password" name="password" required>
                    @error('password')
                    <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="remember" name="remember">
                    <label class="form-check-label" for="remember">
                        Remember me
                    </label>
                </div>

                <button type="submit" class="btn btn-primary w-100 mb-3">
                    <i class="bi bi-box-arrow-in-right"></i> Login
                </button>
            </form>

            <hr>

            <p class="text-center mb-0">
                Belum punya akun? 
                <a href="{{ route('register') }}" style="color: #3498db; text-decoration: none;">Daftar di sini</a>
            </p>
        </div>
    </div>
</div>
@endsection

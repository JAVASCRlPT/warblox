<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - Perpustakaan Kampus Merdeka</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #1565c0; /* Material Blue 700 */
            --secondary-color: #0288d1; /* Material Light Blue 600 */
            --surface: #ffffff;
            --on-surface: #263238;
            --elevation-1: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24);
            --sidebar-width: 250px;
        }

        body {
            font-family: 'Roboto', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            padding-top: 56px; /* Add padding for fixed navbar height */
        }

        .navbar {
            background: linear-gradient(90deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            box-shadow: var(--elevation-1);
            position: fixed;
            top: 10px;
            left: 16px;
            right: 16px;
            z-index: 1030;
            transition: all 0.22s cubic-bezier(.4,0,.2,1);
            width: calc(100% - 32px);
            border-radius: 12px;
            height: 64px;
            display: flex;
            align-items: center;
            padding: 0 18px;
        }

        .navbar.scrolled {
            box-shadow: 0 8px 24px rgba(0,0,0,0.18);
            backdrop-filter: blur(6px);
            transform: translateY(-2px);
        }

        .navbar-brand {
            font-weight: 500;
            font-size: 1.1rem;
            letter-spacing: 0.2px;
            transition: all 0.2s ease;
            color: white;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .navbar-brand .brand-icon {
            height: 40px;
            width: 40px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(255,255,255,0.12);
            color: white;
            box-shadow: 0 2px 6px rgba(0,0,0,0.12);
            font-size: 20px;
        }

        .navbar.scrolled .navbar-brand {
            font-size: 1.4rem;
        }

        .navbar .nav-link {
            color: rgba(255,255,255,0.95);
            padding: 8px 12px;
            border-radius: 8px;
            transition: background 0.18s ease, transform 0.12s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
        }

        .navbar .nav-link:hover {
            background: rgba(255,255,255,0.08);
            transform: translateY(-1px);
            text-decoration: none;
        }

        .nav-icon-btn {
            height: 40px;
            width: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: rgba(255,255,255,0.06);
            color: white;
            transition: background 0.18s ease, transform 0.12s ease;
        }

        .nav-icon-btn:hover { background: rgba(255,255,255,0.14); transform: translateY(-2px); }

        .badge-material {
            background: #ff5252;
            color: white;
            font-weight: 600;
            box-shadow: 0 2px 6px rgba(0,0,0,0.12);
        }

        .admin-container {
            display: flex;
            min-height: calc(100vh - 56px);
        }

        .sidebar {
            width: var(--sidebar-width);
            background: var(--primary-color);
            color: white;
            padding: 20px 0;
            box-shadow: 2px 0 8px rgba(0,0,0,0.1);
            position: fixed;
            height: calc(100vh - 56px);
            overflow-y: auto;
        }

        .sidebar .nav-link {
            color: rgba(255,255,255,0.7);
            padding: 12px 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background-color: rgba(255,255,255,0.1);
            color: white;
            border-left-color: var(--secondary-color);
        }

        .sidebar .nav-category {
            padding: 10px 20px;
            font-size: 0.8rem;
            font-weight: bold;
            text-transform: uppercase;
            color: rgba(255,255,255,0.5);
            margin-top: 20px;
        }

        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: 30px;
        }

        .card {
            border: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.12);
        }

        .book-card {
            text-align: center;
            padding: 15px;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .book-cover {
            width: 100%;
            height: 250px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin-bottom: 15px;
            font-size: 3rem;
            font-weight: bold;
            overflow: hidden;
            position: relative;
        }

        .book-cover img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .book-cover .cover-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3rem;
            text-align: center;
            padding: 20px;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.95) 0%, rgba(118, 75, 162, 0.95) 100%);
        }

        .book-title {
            font-weight: bold;
            color: var(--primary-color);
            margin-bottom: 5px;
            line-height: 1.3;
        }

        .book-author {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 10px;
        }

        .book-description {
            white-space: pre-wrap;
            line-height: 1.6;
            color: #333;
            margin-bottom: 0;
            font-size: 1rem;
        }

        .stock-badge {
            margin: 10px 0;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
        }

        .btn-primary:hover {
            opacity: 0.9;
        }

        .page-header {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .page-header h1 {
            color: var(--primary-color);
            margin: 0;
            font-weight: bold;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: var(--secondary-color);
        }

        .stat-label {
            color: #666;
            font-size: 0.95rem;
            margin-top: 10px;
        }

        .alert {
            border-radius: 8px;
            border: none;
        }

        .form-control, .form-select {
            border-radius: 8px;
            border: 1px solid #ddd;
            padding: 10px 15px;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }

        .cart-dropdown-menu {
            width: 380px;
            max-width: calc(100vw - 24px);
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.18);
            border-radius: 12px;
            overflow: hidden;
        }

        .cart-item-title {
            font-size: 0.9rem;
            line-height: 1.3;
        }

        .cart-item-price {
            font-size: 0.85rem;
            color: #6c757d;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 0;
                margin-left: -var(--sidebar-width);
                z-index: 999;
            }
            .main-content {
                margin-left: 0;
                padding: 15px;
            }

            .admin-container.sidebar-open .sidebar {
                width: var(--sidebar-width);
                margin-left: 0;
            }

            .navbar {
                left: 8px;
                right: 8px;
                width: calc(100% - 16px);
                height: 56px;
                padding: 0 12px;
                border-radius: 8px;
            }

            .navbar-toggler {
                border: none;
                background: rgba(255,255,255,0.06);
                color: white;
                width: 44px;
                height: 44px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 8px;
                box-shadow: none;
            }

            .navbar-toggler .material-icons {
                font-size: 22px;
                line-height: 1;
            }

            .navbar {
                top: 8px;
            }

            body { padding-top: 80px; }

            /* Ensure collapsed menu appears below navbar and is full width */
            .navbar-collapse {
                position: absolute;
                top: 72px;
                left: 0;
                right: 0;
                background: linear-gradient(90deg, var(--primary-color) 0%, var(--secondary-color) 100%);
                padding: 12px 16px;
                border-radius: 0 0 12px 12px;
                z-index: 1040;
            }

            .navbar .navbar-nav {
                flex-direction: column;
                gap: 8px;
            }

            .cart-dropdown-menu {
                width: calc(100% - 32px);
                right: 16px;
                left: 16px;
            }

            /* Move hamburger to right and center brand */
            .navbar .container-fluid { position: relative; }
            .navbar-toggler { position: absolute; right: 12px; top: 10px; z-index: 1050; }
            .navbar-brand { margin: 0 auto; padding-right: 56px; }
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('books.index') }}">
                <span class="brand-icon"><i class="material-icons">menu_book</i></span>
                <span>Perpustakaan Kampus Merdeka</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="material-icons">menu</span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    @auth
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('dashboard') }}">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        </li>
                        @if(auth()->user()->isMahasiswa())
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle position-relative" href="#" id="cartDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-cart3"></i> Keranjang
                                @if(isset($cartCount) && $cartCount > 0)
                                    <span class="position-absolute top-0 start-0 translate-center badge rounded-pill bg-danger">
                                        {{ $cartCount }}
                                    </span>
                                @endif
                            </a>
                            <div class="dropdown-menu dropdown-menu-end cart-dropdown-menu p-0" aria-labelledby="cartDropdown">
                                <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                                    <strong>Keranjang eBook</strong>
                                    <a href="{{ url(route('ebook.cart', [], false)) }}" class="small text-decoration-none">Lihat semua</a>
                                </div>

                                @if(($cartCount ?? 0) === 0)
                                    <div class="p-3 text-center text-muted">
                                        Keranjang masih kosong.
                                    </div>
                                @else
                                    <div class="list-group list-group-flush">
                                        @foreach($cartDropdownItems as $item)
                                            <div class="list-group-item">
                                                <div class="d-flex justify-content-between gap-2">
                                                    <div class="me-2">
                                                        <div class="cart-item-title fw-semibold">{{ $item->book->title }}</div>
                                                        <div class="cart-item-price">
                                                            {{ max((int) $item->qty, 1) }} x Rp {{ number_format($item->book->price ?? 0, 0, ',', '.') }}
                                                            = Rp {{ number_format(($item->book->price ?? 0) * max((int) $item->qty, 1), 0, ',', '.') }}
                                                        </div>
                                                    </div>
                                                    <form method="POST" action="{{ url(route('ebook.cart.remove', $item->book, false)) }}">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus dari keranjang">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    @if(($cartRemainingCount ?? 0) > 0)
                                        <div class="px-3 py-2 text-muted small border-top">
                                            +{{ $cartRemainingCount }} item lainnya
                                        </div>
                                    @endif

                                    <div class="p-3 border-top bg-light">
                                        <div class="d-flex justify-content-between mb-2">
                                            <strong>Total</strong>
                                            <strong>Rp {{ number_format($cartTotal ?? 0, 0, ',', '.') }}</strong>
                                        </div>
                                        <a href="{{ url(route('ebook.checkout', [], false)) }}" class="btn btn-success btn-sm w-100">
                                            <i class="bi bi-credit-card"></i> Checkout
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </li>
                        @endif
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle"></i> {{ auth()->user()->name }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                @if(auth()->user()->isAdmin())
                                    <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}">Admin Panel</a></li>
                                @else
                                    <li><a class="dropdown-item" href="{{ route('transactions.history') }}">Riwayat Peminjaman</a></li>
                                    <li><a class="dropdown-item" href="{{ url(route('ebook.cart', [], false)) }}">Keranjang eBook</a></li>
                                @endif
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item">Logout</button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('register') }}">Register</a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <!-- Sidebar for Admin -->
    @if(auth()->check() && auth()->user()->isAdmin())
    <div class="admin-container">
        <div class="sidebar">
            <div class="nav-category">Menu Utama</div>
            <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a href="{{ route('books.index') }}" class="nav-link {{ request()->routeIs('books.index') ? 'active' : '' }}">
                <i class="bi bi-collection"></i> Lihat Buku
            </a>

            <div class="nav-category">Manajemen</div>
            <a href="{{ route('admin.books.create') }}" class="nav-link {{ request()->routeIs('admin.books.create') ? 'active' : '' }}">
                <i class="bi bi-book"></i>Tambah Buku
            </a>
            <a href="{{ route('admin.categories.index') }}" class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                <i class="bi bi-tags"></i> Kategori
            </a>
            <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <i class="bi bi-people"></i> User
            </a>
            <a href="{{ route('admin.transactions.index') }}" class="nav-link {{ request()->routeIs('admin.transactions.*') ? 'active' : '' }}">
                <i class="bi bi-file-text"></i> Transaksi
            </a>
        </div>
        <div class="main-content">
            @yield('content')
        </div>
    </div>
    @else
    <!-- Regular content without sidebar -->
    <div style="padding: 30px;">
        @yield('content')
    </div>
    @endif

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>

    @if($message = Session::get('success'))
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: '{{ $message }}',
            confirmButtonColor: '#3498db'
        });
    </script>
    @endif

    @if($message = Session::get('error'))
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Gagal',
            text: '{{ $message }}',
            confirmButtonColor: '#3498db'
        });
    </script>
    @endif

    @if($message = Session::get('cart_toast'))
    <script>
        Swal.fire({
            icon: '{{ Session::get('cart_toast_type', 'success') }}',
            title: 'eBook Masuk Keranjang',
            text: '{{ Session::get('cart_toast_type') === 'success' ? 'eBook sudah masuk keranjang, silakan checkout atau lanjut menjelajah.' : 'eBook sudah ada di keranjang, silakan checkout atau lanjut menjelajah.' }}',
            showDenyButton: true,
            confirmButtonText: 'Checkout',
            denyButtonText: 'Lanjut Menjelajah',
            confirmButtonColor: '#198754',
            denyButtonColor: '#6c757d'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '{{ route('ebook.checkout') }}';
            }
        });
    </script>
    @endif

    @stack('scripts')

    <script>
        // Floating navbar on scroll
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Open cart dropdown on hover for desktop, keep click behavior from Bootstrap.
        const cartDropdown = document.getElementById('cartDropdown');
        if (cartDropdown && window.matchMedia('(min-width: 992px)').matches) {
            const navItem = cartDropdown.closest('.dropdown');
            const dropdown = bootstrap.Dropdown.getOrCreateInstance(cartDropdown);

            navItem.addEventListener('mouseenter', () => dropdown.show());
            navItem.addEventListener('mouseleave', () => dropdown.hide());
        }
    </script>
</body>
</html>

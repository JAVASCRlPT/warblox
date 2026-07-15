<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EbookController;
use App\Http\Controllers\AdminUserController;

// Public routes
Route::get('/', function () {
    return redirect()->route('books.index');
});

Route::get('/books', [BookController::class, 'index'])->name('books.index');
Route::get('/books/{book}', [BookController::class, 'show'])->name('books.show');
Route::redirect('/ebook/cart/', '/ebook/cart', 301);
Route::get('/ebook/cart', [EbookController::class, 'cart'])->name('ebook.cart');
Route::get('/ebook/preview/{book}', [EbookController::class, 'preview'])->name('ebook.preview');
Route::get('/ebook/preview-file/{book}', [EbookController::class, 'previewFile'])->name('ebook.preview.file');

// Protected routes (require authentication)
Route::middleware('auth')->group(function () {
    // Dashboard routes
    Route::get('/dashboard', function () {
        if (auth()->user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }
        return app(DashboardController::class)->mahasiswaDashboard();
    })->name('dashboard');

    // Mahasiswa routes
    Route::post('/books/{book}/borrow', [TransactionController::class, 'borrow'])->name('books.borrow');
    Route::get('/ebook/beli/{book}', [EbookController::class, 'buy'])->name('ebook.buy');
    Route::post('/ebook/beli/{book}', [EbookController::class, 'paySingle'])->name('ebook.buy.pay');
    Route::post('/ebook/cart/{book}', [EbookController::class, 'addToCart'])->name('ebook.cart.add');
    Route::post('/ebook/cart/{book}/remove', [EbookController::class, 'removeFromCart'])->name('ebook.cart.remove');
    Route::get('/ebook/checkout', [EbookController::class, 'checkout'])->name('ebook.checkout');
    Route::post('/ebook/checkout', [EbookController::class, 'processCheckout'])->name('ebook.checkout.process');
    Route::post('/ebook/confirm/{checkoutId}', [EbookController::class, 'confirmPayment'])->name('ebook.confirm');
    Route::get('/ebook/download/{book}', [EbookController::class, 'download'])->name('ebook.download');
    Route::get('/transactions', [TransactionController::class, 'history'])->name('transactions.history');
    Route::post('/transactions/{transaction}/return', [TransactionController::class, 'return'])->name('transactions.return');
});

// Admin routes
Route::prefix('admin')
    ->middleware(['auth', 'is_admin'])
    ->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'adminDashboard'])->name('admin.dashboard');
        
        // Books management
        Route::get('/books', [BookController::class, 'index'])->name('admin.books.index');
        Route::get('/books/create', [BookController::class, 'create'])->name('admin.books.create');
        Route::post('/books', [BookController::class, 'store'])->name('admin.books.store');
        Route::get('/books/{book}/edit', [BookController::class, 'edit'])->name('admin.books.edit');
        Route::put('/books/{book}', [BookController::class, 'update'])->name('admin.books.update');
        Route::delete('/books/{book}', [BookController::class, 'destroy'])->name('admin.books.destroy');
        
        // Categories management
        Route::get('/categories', [CategoryController::class, 'index'])->name('admin.categories.index');
        Route::get('/categories/create', [CategoryController::class, 'create'])->name('admin.categories.create');
        Route::post('/categories', [CategoryController::class, 'store'])->name('admin.categories.store');
        Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('admin.categories.edit');
        Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('admin.categories.update');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('admin.categories.destroy');
        
        // Transactions management
        Route::get('/transactions', [TransactionController::class, 'index'])->name('admin.transactions.index');
        Route::get('/transactions/{user}', [TransactionController::class, 'show'])->name('admin.transactions.show');
        Route::post('/transactions/{user}/pay-fine', [TransactionController::class, 'payFine'])->name('admin.transactions.pay-fine');
        Route::post('/transactions/{transaction}/approve-return', [TransactionController::class, 'approveReturn'])->name('admin.transactions.approve-return');
        Route::post('/transactions/{transaction}/reject-return', [TransactionController::class, 'rejectReturn'])->name('admin.transactions.reject-return');
        Route::post('/transactions/{transaction}/approve-borrow', [TransactionController::class, 'approveBorrow'])->name('admin.transactions.approve-borrow');
        Route::post('/transactions/{transaction}/reject-borrow', [TransactionController::class, 'rejectBorrow'])->name('admin.transactions.reject-borrow');
        Route::post('/transactions/{user}/approve-all-borrow', [TransactionController::class, 'approveAllBorrow'])->name('admin.transactions.approve-all-borrow');
        Route::post('/transactions/{user}/reject-all-borrow', [TransactionController::class, 'rejectAllBorrow'])->name('admin.transactions.reject-all-borrow');
        
        // Users management
        Route::get('/users', [AdminUserController::class, 'index'])->name('admin.users.index');
        Route::get('/users/create', [AdminUserController::class, 'create'])->name('admin.users.create');
        Route::post('/users', [AdminUserController::class, 'store'])->name('admin.users.store');
        Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('admin.users.edit');
        Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('admin.users.update');
        Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('admin.users.destroy');
});

// Authentication routes (Laravel Breeze or default)
require __DIR__.'/auth.php';

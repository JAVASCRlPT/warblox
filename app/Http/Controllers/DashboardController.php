<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use App\Models\EbookTransaction;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Admin dashboard.
     */
    public function adminDashboard(): View
    {
        $stats = [
            'total_books' => Book::count(),
            'total_users' => User::where('role', 'mahasiswa')->count(),
            'borrowed_books' => Transaction::where('status', 'dipinjam')->count(),
            'overdue_books' => Transaction::where('status', 'terlambat')->count(),
            'total_categories' => Category::count(),
            'pending_returns' => Transaction::where('status', 'pending_return')->count(),
            'pending_borrows' => Transaction::where('status', 'pending_borrow')->count(),
        ];

        // Recent library transactions (exclude pending statuses)
        $recentLibraryTransactions = Transaction::with(['user', 'book'])
            ->whereNotIn('status', ['pending_borrow', 'pending_return'])
            ->latest()
            ->take(5)
            ->get();

        // Recent ebook purchases (paid only)
        $recentEbookTransactions = EbookTransaction::with(['user', 'book'])
            ->where('status', 'paid')
            ->latest()
            ->take(5)
            ->get();

        $recentTransactions = $recentLibraryTransactions
            ->map(function (Transaction $transaction) {
                return (object) [
                    'type' => 'library',
                    'user' => $transaction->user,
                    'book' => $transaction->book,
                    'date' => $transaction->borrow_date,
                    'status' => $transaction->status,
                    'amount' => null,
                ];
            })
            ->merge(
                $recentEbookTransactions->map(function (EbookTransaction $transaction) {
                    return (object) [
                        'type' => 'ebook',
                        'user' => $transaction->user,
                        'book' => $transaction->book,
                        'date' => $transaction->created_at,
                        'status' => $transaction->status,
                        'amount' => $transaction->amount,
                    ];
                })
            )
            ->sortByDesc('date')
            ->take(8)
            ->values();

        // Books low on stock
        $lowStockBooks = Book::where('stock', '<=', 5)
            ->orderBy('stock', 'asc')
            ->get();

        // Pending return requests
        $pendingReturns = Transaction::with(['user', 'book'])
            ->where('status', 'pending_return')
            ->latest()
            ->take(5)
            ->get();

        // Pending borrow requests grouped by user
        $pendingBorrows = Transaction::with(['user', 'book'])
            ->where('status', 'pending_borrow')
            ->latest()
            ->get()
            ->groupBy('user_id');

        return view('admin.dashboard', compact('stats', 'recentTransactions', 'lowStockBooks', 'pendingReturns', 'pendingBorrows'));
    }

    /**
     * Mahasiswa dashboard.
     */
    public function mahasiswaDashboard(): View
    {
        $user = auth()->user();

        $stats = [
            'borrowed_count' => Transaction::where('user_id', $user->id)
                ->where('status', 'dipinjam')
                ->count(),
            'returned_count' => Transaction::where('user_id', $user->id)
                ->where('status', 'kembali')
                ->count(),
            'overdue_count' => Transaction::where('user_id', $user->id)
                ->where('status', 'terlambat')
                ->count(),
            'total_fine' => Transaction::where('user_id', $user->id)
                ->sum('fine'),
            'ebook_paid' => EbookTransaction::where('user_id', $user->id)
                ->where('status', 'paid')
                ->count(),
            'ebook_pending' => EbookTransaction::where('user_id', $user->id)
                ->where('status', 'pending')
                ->count(),
        ];

        // Current borrowed books
        $borrowedBooks = Transaction::where('user_id', $user->id)
            ->where('status', 'dipinjam')
            ->with('book')
            ->get();

        $ebookPurchases = EbookTransaction::where('user_id', $user->id)
            ->where('status', 'paid')
            ->with('book')
            ->latest()
            ->take(10)
            ->get();

        return view('dashboard', compact('stats', 'borrowedBooks', 'ebookPurchases'));
    }
}

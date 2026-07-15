<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class BookController extends Controller
{
    /**
     * Display a listing of books (for mahasiswa) or all books (for admin).
     */
    public function index(Category $category = null): View
    {
        // Check if user is admin - show management view
        if (auth()->check() && auth()->user()->isAdmin()) {
            $query = Book::with('category');

            // Search functionality
            if (request('search')) {
                $search = request('search');
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('author', 'like', "%{$search}%");
                });
            }

            $books = $query->orderBy('updated_at', 'desc')->paginate(4);

            return view('admin.books.index', compact('books'));
        }

        // Public view for mahasiswa
        $categories = Category::all();
        $query = Book::query();

        // Filter by category
        if (request('category_id')) {
            $query->where('category_id', request('category_id'));
        }

        // Search functionality
        if (request('search')) {
            $search = request('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('author', 'like', "%{$search}%");
            });
        }

        $books = $query->orderBy('updated_at', 'desc')->paginate(4);

        return view('books.index', compact('books', 'categories', 'category'));
    }

    /**
     * Show the form for creating a new book (admin only).
     */
    public function create(): View
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized');
        }
        $categories = Category::all();

        return view('books.create', compact('categories'));
    }

    /**
     * Store a newly created book in storage (admin only).
     */
    public function store(StoreBookRequest $request): RedirectResponse
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validated();

        // Handle cover upload
        if ($request->hasFile('cover')) {
            $validated['cover'] = $request->file('cover')->store('covers', 'public');
        }

        // Handle ebook file upload
        if ($request->hasFile('file_ebook')) {
            $validated['file_ebook'] = $request->file('file_ebook')->store('ebooks', 'public');
        }

        Book::create($validated);

        return redirect()->route('admin.books.index')
            ->with('success', 'Buku berhasil ditambahkan!');
    }

    /**
     * Display the specified book.
     */
    public function show(Book $book): View
    {
        $recommendedBooks = Book::where('category_id', $book->category_id)
            ->where('id', '!=', $book->id)
            ->take(4)
            ->get();

        return view('books.show', compact('book', 'recommendedBooks'));
    }

    /**
     * Show the form for editing the specified book (admin only).
     */
    public function edit(Book $book): View
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized');
        }
        $categories = Category::all();

        return view('books.edit', compact('book', 'categories'));
    }

    /**
     * Update the specified book in storage (admin only).
     */
    public function update(UpdateBookRequest $request, Book $book): RedirectResponse
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validated();

        // Handle cover upload
        if ($request->hasFile('cover')) {
            if ($book->cover) {
                \Storage::disk('public')->delete($book->cover);
            }
            $validated['cover'] = $request->file('cover')->store('covers', 'public');
        }

        // Handle ebook file upload
        if ($request->hasFile('file_ebook')) {
            if ($book->file_ebook) {
                \Storage::disk('public')->delete($book->file_ebook);
            }
            $validated['file_ebook'] = $request->file('file_ebook')->store('ebooks', 'public');
        }

        $book->update($validated);

        return redirect()->route('admin.books.index')
            ->with('success', 'Buku berhasil diperbarui!');
    }

    /**
     * Remove the specified book from storage (admin only).
     */
    public function destroy(Book $book): RedirectResponse
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized');
        }

        // Delete cover file
        if ($book->cover) {
            \Storage::disk('public')->delete($book->cover);
        }

        $book->delete();

        return redirect()->route('admin.books.index')
            ->with('success', 'Buku berhasil dihapus!');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\EbookTransaction;
use App\Models\Cart;

class Book extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'author',
        'publisher',
        'year',
        'stock',
        'loan_duration',
        'price',
        'cover',
        'file_ebook',
        'category_id',
        'description',
    ];

    /**
     * Get the category that owns the book.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get all transactions for this book.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function ebookTransactions(): HasMany
    {
        return $this->hasMany(EbookTransaction::class);
    }

    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }
}

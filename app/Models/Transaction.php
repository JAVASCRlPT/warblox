<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Transaction extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'book_id',
        'borrow_date',
        'return_date',
        'due_date',
        'status',
        'fine',
        'reject_reason',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'borrow_date' => 'date',
        'return_date' => 'date',
        'due_date' => 'date',
    ];

    /**
     * Get the user that owns the transaction.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the book for this transaction.
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * Calculate and update fine for late returns.
     * Fine rate: 5000 per day
     */
    public function calculateFine(): void
    {
        if ($this->status === 'terlambat' && $this->return_date) {
            $daysLate = $this->due_date->diffInDays($this->return_date);
            $fineRate = 5000; // Configurable in config
            $this->fine = $daysLate * $fineRate;
            $this->save();
        }
    }

    /**
     * Check if transaction is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->status === 'dipinjam' && Carbon::now()->greaterThan($this->due_date);
    }

    /**
     * Get days until due date (negative means overdue).
     */
    public function daysUntilDue(): int
    {
        return Carbon::now()->diffInDays($this->due_date, false);
    }
}

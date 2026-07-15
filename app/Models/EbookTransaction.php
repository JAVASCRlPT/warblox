<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EbookTransaction extends Model
{
    protected $fillable = [
        'user_id',
        'book_id',
        'invoice_code',
        'checkout_id',
        'qr_code',
        'payment_method',
        'admin_fee',
        'amount',
        'qty',
        'status',
        'expires_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'admin_fee' => 'integer',
        'qty' => 'integer',
        'expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }
}

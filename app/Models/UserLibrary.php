<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserLibrary extends Model
{
    protected $fillable = [
        'user_id',
        'book_id',
        'purchase_date',
        'purchase_price',
        'payment_method',
        'transaction_id',
        'is_gift',
        'gift_from',
    ];

    protected $casts = [
        'purchase_date' => 'datetime',
        'purchase_price' => 'decimal:2',
        'is_gift' => 'boolean',
    ];

    /**
     * Get the user that owns the library entry.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the book in the library.
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * Scope to get recent purchases.
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('purchase_date', '>=', now()->subDays($days));
    }

    /**
     * Scope to get gift books.
     */
    public function scopeGifts($query)
    {
        return $query->where('is_gift', true);
    }

    /**
     * Scope to get paid books.
     */
    public function scopePaid($query)
    {
        return $query->where('purchase_price', '>', 0);
    }

    /**
     * Scope to get free books.
     */
    public function scopeFree($query)
    {
        return $query->where('purchase_price', 0);
    }
} 
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WishlistItem extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'wishlist_items';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'book_id',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'added_at' => 'datetime',
    ];

    /**
     * Get the user who added this item to wishlist.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the book in the wishlist.
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * Scope for recent wishlist items.
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('added_at', '>=', now()->subDays($days));
    }

    /**
     * Scope for items with notes.
     */
    public function scopeWithNotes($query)
    {
        return $query->whereNotNull('notes')->where('notes', '!=', '');
    }

    /**
     * Get formatted added date.
     */
    public function getFormattedAddedDateAttribute(): string
    {
        return $this->added_at->format('M j, Y');
    }

    /**
     * Get note preview.
     */
    public function getNotePreviewAttribute(): ?string
    {
        return $this->notes ? \Str::limit($this->notes, 100) : null;
    }

    /**
     * Check if item has notes.
     */
    public function hasNotes(): bool
    {
        return !empty($this->notes);
    }
} 
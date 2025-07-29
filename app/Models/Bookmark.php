<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bookmark extends Model
{
    protected $fillable = [
        'user_id',
        'book_id',
        'page_number',
        'chapter',
        'note',
    ];

    protected $casts = [
        'page_number' => 'integer',
    ];

    /**
     * Get the user that owns the bookmark.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the book for this bookmark.
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * Scope to get bookmarks for a specific book.
     */
    public function scopeForBook($query, $bookId)
    {
        return $query->where('book_id', $bookId);
    }

    /**
     * Scope to get bookmarks for a specific user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to get bookmarks with notes.
     */
    public function scopeWithNotes($query)
    {
        return $query->whereNotNull('note')->where('note', '!=', '');
    }

    /**
     * Scope to get recent bookmarks.
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Get formatted page number.
     */
    public function getFormattedPageAttribute(): string
    {
        return "Page {$this->page_number}";
    }

    /**
     * Get short note preview.
     */
    public function getNotePreviewAttribute(): ?string
    {
        if (!$this->note) {
            return null;
        }

        return strlen($this->note) > 100 
            ? substr($this->note, 0, 100) . '...' 
            : $this->note;
    }
} 
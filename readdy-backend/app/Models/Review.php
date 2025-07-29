<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Review extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'book_id',
        'rating',
        'title',
        'content',
        'is_verified_purchase',
        'helpful_votes',
        'is_approved',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'rating' => 'integer',
        'is_verified_purchase' => 'boolean',
        'helpful_votes' => 'integer',
        'is_approved' => 'boolean',
    ];

    /**
     * Get the user who wrote the review.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the book being reviewed.
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * Scope for approved reviews.
     */
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    /**
     * Scope for verified purchases.
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified_purchase', true);
    }

    /**
     * Scope for recent reviews.
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope for helpful reviews.
     */
    public function scopeHelpful($query)
    {
        return $query->orderBy('helpful_votes', 'desc');
    }

    /**
     * Get formatted rating stars.
     */
    public function getRatingStarsAttribute(): string
    {
        return str_repeat('★', $this->rating) . str_repeat('☆', 5 - $this->rating);
    }

    /**
     * Get review excerpt.
     */
    public function getExcerptAttribute(): string
    {
        return \Str::limit($this->content, 150);
    }

    /**
     * Check if review is helpful.
     */
    public function isHelpful(): bool
    {
        return $this->helpful_votes > 0;
    }

    /**
     * Increment helpful votes.
     */
    public function markAsHelpful(): void
    {
        $this->increment('helpful_votes');
    }
}

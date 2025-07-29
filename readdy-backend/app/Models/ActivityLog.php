<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ActivityLog extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'activity_logs';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'action',
        'model_type',
        'model_id',
        'properties',
        'ip_address',
        'user_agent',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'properties' => 'array',
    ];

    /**
     * Get the user who performed the activity.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for recent activities.
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope for specific actions.
     */
    public function scopeAction($query, $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope for specific model types.
     */
    public function scopeModelType($query, $modelType)
    {
        return $query->where('model_type', $modelType);
    }

    /**
     * Scope for authenticated users only.
     */
    public function scopeAuthenticated($query)
    {
        return $query->whereNotNull('user_id');
    }

    /**
     * Scope for anonymous users only.
     */
    public function scopeAnonymous($query)
    {
        return $query->whereNull('user_id');
    }

    /**
     * Get formatted action description.
     */
    public function getActionDescriptionAttribute(): string
    {
        return match($this->action) {
            'book_viewed' => 'Viewed book',
            'book_purchased' => 'Purchased book',
            'book_added_to_wishlist' => 'Added book to wishlist',
            'book_removed_from_wishlist' => 'Removed book from wishlist',
            'review_created' => 'Created review',
            'review_updated' => 'Updated review',
            'bookmark_created' => 'Created bookmark',
            'reading_progress_updated' => 'Updated reading progress',
            'reading_session_started' => 'Started reading session',
            'reading_session_ended' => 'Ended reading session',
            'goal_set' => 'Set reading goal',
            'goal_achieved' => 'Achieved reading goal',
            'search_performed' => 'Performed search',
            'profile_updated' => 'Updated profile',
            'login' => 'Logged in',
            'logout' => 'Logged out',
            'register' => 'Registered account',
            default => ucfirst(str_replace('_', ' ', $this->action)),
        };
    }

    /**
     * Get action icon.
     */
    public function getActionIconAttribute(): string
    {
        return match($this->action) {
            'book_viewed' => '👁️',
            'book_purchased' => '💰',
            'book_added_to_wishlist' => '❤️',
            'book_removed_from_wishlist' => '💔',
            'review_created' => '⭐',
            'review_updated' => '✏️',
            'bookmark_created' => '🔖',
            'reading_progress_updated' => '📖',
            'reading_session_started' => '▶️',
            'reading_session_ended' => '⏹️',
            'goal_set' => '🎯',
            'goal_achieved' => '🏆',
            'search_performed' => '🔍',
            'profile_updated' => '👤',
            'login' => '🔑',
            'logout' => '🚪',
            'register' => '📝',
            default => '📊',
        };
    }

    /**
     * Get related model instance.
     */
    public function getRelatedModelAttribute()
    {
        if (!$this->model_type || !$this->model_id) {
            return null;
        }

        return $this->model_type::find($this->model_id);
    }

    /**
     * Check if activity is recent (within last 24 hours).
     */
    public function isRecent(): bool
    {
        return $this->created_at->isAfter(now()->subDay());
    }

    /**
     * Get time ago description.
     */
    public function getTimeAgoAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }
} 
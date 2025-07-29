<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notification extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'read_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    /**
     * Get the user who owns the notification.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for unread notifications.
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope for read notifications.
     */
    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    /**
     * Scope for recent notifications.
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope by notification type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead(): void
    {
        $this->update(['read_at' => now()]);
    }

    /**
     * Mark notification as unread.
     */
    public function markAsUnread(): void
    {
        $this->update(['read_at' => null]);
    }

    /**
     * Check if notification is read.
     */
    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    /**
     * Check if notification is unread.
     */
    public function isUnread(): bool
    {
        return $this->read_at === null;
    }

    /**
     * Get notification icon based on type.
     */
    public function getIconAttribute(): string
    {
        return match($this->type) {
            'book_added' => '📚',
            'reading_goal' => '🎯',
            'achievement' => '🏆',
            'price_drop' => '💰',
            'new_release' => '🆕',
            'recommendation' => '💡',
            'system' => '⚙️',
            default => '📢',
        };
    }

    /**
     * Get notification color based on type.
     */
    public function getColorAttribute(): string
    {
        return match($this->type) {
            'book_added' => 'blue',
            'reading_goal' => 'green',
            'achievement' => 'yellow',
            'price_drop' => 'red',
            'new_release' => 'purple',
            'recommendation' => 'indigo',
            'system' => 'gray',
            default => 'blue',
        };
    }
} 
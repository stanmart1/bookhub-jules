<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalyticsEvent extends Model
{
    protected $fillable = [
        'user_id',
        'event_type',
        'event_data',
        'session_id',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'event_data' => 'array',
    ];

    /**
     * Get the user that owns the analytics event.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get events by type.
     */
    public function scopeByType($query, string $eventType)
    {
        return $query->where('event_type', $eventType);
    }

    /**
     * Scope to get events by user.
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to get events by session.
     */
    public function scopeBySession($query, string $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    /**
     * Scope to get events within date range.
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Get event statistics.
     */
    public static function getEventStatistics($startDate = null, $endDate = null)
    {
        $query = static::query();

        if ($startDate && $endDate) {
            $query->inDateRange($startDate, $endDate);
        }

        return $query->selectRaw('event_type, COUNT(*) as count')
            ->groupBy('event_type')
            ->orderByDesc('count')
            ->get();
    }
} 
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ReadingSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'book_id',
        'started_at',
        'ended_at',
        'duration_minutes',
        'pages_read',
        'device_type',
        'session_type',
        'session_data',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'session_data' => 'array',
    ];

    /**
     * Get the user that owns the reading session.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the book for this reading session.
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * Scope for active sessions.
     */
    public function scopeActive($query)
    {
        return $query->whereNull('ended_at');
    }

    /**
     * Scope for completed sessions.
     */
    public function scopeCompleted($query)
    {
        return $query->whereNotNull('ended_at');
    }

    /**
     * Scope for sessions within a date range.
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('started_at', [$startDate, $endDate]);
    }

    /**
     * Scope for sessions by device type.
     */
    public function scopeByDeviceType($query, $deviceType)
    {
        return $query->where('device_type', $deviceType);
    }

    /**
     * Calculate session duration in minutes.
     */
    public function getDurationInMinutesAttribute(): int
    {
        if ($this->ended_at && $this->started_at) {
            return $this->ended_at->diffInMinutes($this->started_at);
        }
        return $this->duration_minutes;
    }

    /**
     * Check if session is active.
     */
    public function isActive(): bool
    {
        return is_null($this->ended_at);
    }

    /**
     * End the reading session.
     */
    public function endSession(int $pagesRead = 0, int $durationMinutes = 0): void
    {
        $this->update([
            'ended_at' => now(),
            'pages_read' => $pagesRead,
            'duration_minutes' => $durationMinutes,
        ]);
    }
}

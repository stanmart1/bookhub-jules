<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReadingProgress extends Model
{
    protected $fillable = [
        'user_id',
        'book_id',
        'current_page',
        'total_pages',
        'progress_percentage',
        'reading_time_minutes',
        'last_read_at',
        'is_finished',
        'finished_at',
    ];

    protected $casts = [
        'current_page' => 'integer',
        'total_pages' => 'integer',
        'progress_percentage' => 'decimal:2',
        'reading_time_minutes' => 'integer',
        'last_read_at' => 'datetime',
        'is_finished' => 'boolean',
        'finished_at' => 'datetime',
    ];

    /**
     * Get the user that owns the reading progress.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the book for this reading progress.
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * Scope to get finished books.
     */
    public function scopeFinished($query)
    {
        return $query->where('is_finished', true);
    }

    /**
     * Scope to get currently reading books.
     */
    public function scopeCurrentlyReading($query)
    {
        return $query->where('is_finished', false);
    }

    /**
     * Scope to get recent reading activity.
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('last_read_at', '>=', now()->subDays($days));
    }

    /**
     * Get formatted reading time.
     */
    public function getFormattedReadingTimeAttribute(): string
    {
        $hours = floor($this->reading_time_minutes / 60);
        $minutes = $this->reading_time_minutes % 60;

        if ($hours > 0) {
            return "{$hours}h {$minutes}m";
        }

        return "{$minutes}m";
    }

    /**
     * Get estimated time to finish.
     */
    public function getEstimatedTimeToFinishAttribute(): ?string
    {
        if (!$this->total_pages || !$this->current_page || $this->is_finished) {
            return null;
        }

        $remainingPages = $this->total_pages - $this->current_page;
        $pagesPerMinute = $this->reading_time_minutes > 0 ? $this->current_page / $this->reading_time_minutes : 1;
        $estimatedMinutes = $remainingPages / $pagesPerMinute;

        $hours = floor($estimatedMinutes / 60);
        $minutes = round($estimatedMinutes % 60);

        if ($hours > 0) {
            return "~{$hours}h {$minutes}m";
        }

        return "~{$minutes}m";
    }
} 
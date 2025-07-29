<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentPerformance extends Model
{
    protected $fillable = [
        'book_id',
        'views_count',
        'reading_time_avg',
        'completion_rate',
        'engagement_score',
        'download_count',
        'review_count',
        'average_rating',
    ];

    protected $casts = [
        'completion_rate' => 'decimal:2',
        'engagement_score' => 'decimal:2',
        'average_rating' => 'decimal:2',
    ];

    /**
     * Get the book that this performance data belongs to.
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * Scope to get high performing content.
     */
    public function scopeHighPerforming($query, float $threshold = 0.7)
    {
        return $query->where('engagement_score', '>=', $threshold);
    }

    /**
     * Scope to get content by engagement level.
     */
    public function scopeByEngagementLevel($query, string $level)
    {
        return match ($level) {
            'high' => $query->where('engagement_score', '>=', 0.8),
            'medium' => $query->whereBetween('engagement_score', [0.5, 0.8]),
            'low' => $query->where('engagement_score', '<', 0.5),
            default => $query,
        };
    }

    /**
     * Scope to get content by completion rate.
     */
    public function scopeByCompletionRate($query, float $minRate = 0.0, float $maxRate = 1.0)
    {
        return $query->whereBetween('completion_rate', [$minRate, $maxRate]);
    }

    /**
     * Update performance metrics.
     */
    public function updateMetrics(array $metrics): void
    {
        $this->update($metrics);
    }

    /**
     * Calculate engagement score.
     */
    public function calculateEngagementScore(): float
    {
        $viewsWeight = 0.2;
        $readingTimeWeight = 0.3;
        $completionWeight = 0.3;
        $ratingWeight = 0.2;

        $viewsScore = min($this->views_count / 1000, 1.0); // Normalize to 0-1
        $readingTimeScore = min($this->reading_time_avg / 60, 1.0); // Normalize to 0-1
        $completionScore = $this->completion_rate / 100; // Already 0-1
        $ratingScore = $this->average_rating / 5; // Normalize to 0-1

        return ($viewsScore * $viewsWeight) +
               ($readingTimeScore * $readingTimeWeight) +
               ($completionScore * $completionWeight) +
               ($ratingScore * $ratingWeight);
    }

    /**
     * Get top performing content.
     */
    public static function getTopPerforming(int $limit = 10)
    {
        return static::with('book')
            ->orderByDesc('engagement_score')
            ->limit($limit)
            ->get();
    }

    /**
     * Get content performance statistics.
     */
    public static function getPerformanceStatistics()
    {
        return static::selectRaw('
            AVG(views_count) as avg_views,
            AVG(reading_time_avg) as avg_reading_time,
            AVG(completion_rate) as avg_completion_rate,
            AVG(engagement_score) as avg_engagement_score,
            AVG(average_rating) as avg_rating,
            COUNT(*) as total_content
        ')->first();
    }
} 
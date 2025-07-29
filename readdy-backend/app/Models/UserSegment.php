<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSegment extends Model
{
    protected $fillable = [
        'user_id',
        'segment_type',
        'segment_value',
        'confidence_score',
    ];

    protected $casts = [
        'confidence_score' => 'decimal:2',
    ];

    /**
     * Get the user that belongs to this segment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get segments by type.
     */
    public function scopeByType($query, string $segmentType)
    {
        return $query->where('segment_type', $segmentType);
    }

    /**
     * Scope to get segments by value.
     */
    public function scopeByValue($query, string $segmentValue)
    {
        return $query->where('segment_value', $segmentValue);
    }

    /**
     * Scope to get high confidence segments.
     */
    public function scopeHighConfidence($query, float $threshold = 0.8)
    {
        return $query->where('confidence_score', '>=', $threshold);
    }

    /**
     * Get segment statistics.
     */
    public static function getSegmentStatistics()
    {
        return static::selectRaw('segment_type, segment_value, COUNT(*) as user_count, AVG(confidence_score) as avg_confidence')
            ->groupBy('segment_type', 'segment_value')
            ->orderByDesc('user_count')
            ->get();
    }

    /**
     * Get users by segment.
     */
    public static function getUsersBySegment(string $segmentType, string $segmentValue)
    {
        return static::where('segment_type', $segmentType)
            ->where('segment_value', $segmentValue)
            ->with('user')
            ->get()
            ->pluck('user');
    }
} 
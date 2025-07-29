<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class ScheduledReport extends Model
{
    protected $fillable = [
        'report_type',
        'frequency',
        'recipients',
        'parameters',
        'last_generated',
        'next_generation',
        'is_active',
        'status',
        'last_error',
    ];

    protected $casts = [
        'recipients' => 'array',
        'parameters' => 'array',
        'last_generated' => 'datetime',
        'next_generation' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Scope to get active reports.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get reports by type.
     */
    public function scopeByType($query, string $reportType)
    {
        return $query->where('report_type', $reportType);
    }

    /**
     * Scope to get reports by frequency.
     */
    public function scopeByFrequency($query, string $frequency)
    {
        return $query->where('frequency', $frequency);
    }

    /**
     * Scope to get reports due for generation.
     */
    public function scopeDueForGeneration($query)
    {
        return $query->where('next_generation', '<=', now())
            ->where('is_active', true)
            ->where('status', 'active');
    }

    /**
     * Update next generation time based on frequency.
     */
    public function updateNextGeneration(): void
    {
        $this->next_generation = match ($this->frequency) {
            'daily' => now()->addDay(),
            'weekly' => now()->addWeek(),
            'monthly' => now()->addMonth(),
            default => now()->addDay(),
        };
        
        $this->save();
    }

    /**
     * Mark report as generated.
     */
    public function markAsGenerated(): void
    {
        $this->last_generated = now();
        $this->status = 'active';
        $this->last_error = null;
        $this->updateNextGeneration();
    }

    /**
     * Mark report as failed.
     */
    public function markAsFailed(string $error): void
    {
        $this->status = 'error';
        $this->last_error = $error;
        $this->save();
    }

    /**
     * Get report types.
     */
    public static function getReportTypes(): array
    {
        return [
            'sales' => 'Sales Report',
            'delivery' => 'Delivery Report',
            'customer' => 'Customer Report',
            'comprehensive' => 'Comprehensive Report',
            'analytics' => 'Analytics Report',
        ];
    }

    /**
     * Get frequency options.
     */
    public static function getFrequencyOptions(): array
    {
        return [
            'daily' => 'Daily',
            'weekly' => 'Weekly',
            'monthly' => 'Monthly',
        ];
    }

    /**
     * Get status options.
     */
    public static function getStatusOptions(): array
    {
        return [
            'active' => 'Active',
            'paused' => 'Paused',
            'error' => 'Error',
        ];
    }

    /**
     * Get reports statistics.
     */
    public static function getReportStatistics()
    {
        return static::selectRaw('
            report_type,
            frequency,
            status,
            COUNT(*) as count,
            COUNT(CASE WHEN last_generated IS NOT NULL THEN 1 END) as generated_count
        ')
        ->groupBy('report_type', 'frequency', 'status')
        ->get();
    }
} 
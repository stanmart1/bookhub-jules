<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ReadingGoal extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'target',
        'current',
        'start_date',
        'end_date',
        'period',
        'is_active',
        'is_completed',
        'completed_at',
        'milestones',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'completed_at' => 'date',
        'is_active' => 'boolean',
        'is_completed' => 'boolean',
        'milestones' => 'array',
    ];

    /**
     * Get the user that owns the reading goal.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for active goals.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for completed goals.
     */
    public function scopeCompleted($query)
    {
        return $query->where('is_completed', true);
    }

    /**
     * Scope for goals by type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for goals by period.
     */
    public function scopeByPeriod($query, string $period)
    {
        return $query->where('period', $period);
    }

    /**
     * Calculate progress percentage.
     */
    public function getProgressPercentageAttribute(): float
    {
        if ($this->target <= 0) {
            return 0;
        }
        return round(($this->current / $this->target) * 100, 2);
    }

    /**
     * Check if goal is completed.
     */
    public function isCompleted(): bool
    {
        return $this->current >= $this->target;
    }

    /**
     * Check if goal is overdue.
     */
    public function isOverdue(): bool
    {
        return !$this->is_completed && $this->end_date->isPast();
    }

    /**
     * Get days remaining.
     */
    public function getDaysRemainingAttribute(): int
    {
        return max(0, now()->diffInDays($this->end_date, false));
    }

    /**
     * Get daily target to complete goal.
     */
    public function getDailyTargetAttribute(): float
    {
        $daysRemaining = $this->days_remaining;
        if ($daysRemaining <= 0) {
            return 0;
        }
        $remaining = $this->target - $this->current;
        return round($remaining / $daysRemaining, 2);
    }

    /**
     * Update goal progress.
     */
    public function updateProgress(int $increment = 1): void
    {
        $this->increment('current', $increment);
        
        // Check if goal is completed
        if ($this->fresh()->isCompleted() && !$this->is_completed) {
            $this->update([
                'is_completed' => true,
                'completed_at' => now(),
            ]);
        }
    }

    /**
     * Add milestone.
     */
    public function addMilestone(string $milestone, $value = null): void
    {
        $milestones = $this->milestones ?? [];
        $milestones[] = [
            'milestone' => $milestone,
            'value' => $value,
            'achieved_at' => now()->toISOString(),
        ];
        $this->update(['milestones' => $milestones]);
    }

    /**
     * Get goal status.
     */
    public function getStatusAttribute(): string
    {
        if ($this->is_completed) {
            return 'completed';
        }
        if ($this->isOverdue()) {
            return 'overdue';
        }
        if ($this->progress_percentage >= 75) {
            return 'on_track';
        }
        if ($this->progress_percentage >= 50) {
            return 'moderate';
        }
        return 'behind';
    }

    /**
     * Get goal type label.
     */
    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'books' => 'Books',
            'pages' => 'Pages',
            'time' => 'Reading Time',
            'streak' => 'Reading Streak',
            default => ucfirst($this->type),
        };
    }

    /**
     * Get period label.
     */
    public function getPeriodLabelAttribute(): string
    {
        return match($this->period) {
            'daily' => 'Daily',
            'weekly' => 'Weekly',
            'monthly' => 'Monthly',
            'yearly' => 'Yearly',
            default => ucfirst($this->period),
        };
    }
}

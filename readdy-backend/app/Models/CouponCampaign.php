<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class CouponCampaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'description', 'coupon_id', 'start_date', 'end_date',
        'target_audience', 'campaign_rules', 'is_active', 'budget_limit',
        'budget_used', 'metadata'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'target_audience' => 'array',
        'campaign_rules' => 'array',
        'is_active' => 'boolean',
        'budget_limit' => 'integer',
        'budget_used' => 'decimal:2',
        'metadata' => 'array'
    ];

    /**
     * Get the coupon associated with this campaign
     */
    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    /**
     * Scope for active campaigns
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for campaigns currently running
     */
    public function scopeRunning($query)
    {
        $now = now();
        return $query->where('is_active', true)
            ->where('start_date', '<=', $now)
            ->where('end_date', '>=', $now);
    }

    /**
     * Scope for campaigns by date range
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->where(function ($q) use ($startDate, $endDate) {
            $q->whereBetween('start_date', [$startDate, $endDate])
                ->orWhereBetween('end_date', [$startDate, $endDate])
                ->orWhere(function ($subQ) use ($startDate, $endDate) {
                    $subQ->where('start_date', '<=', $startDate)
                        ->where('end_date', '>=', $endDate);
                });
        });
    }

    /**
     * Check if campaign is currently running
     */
    public function isRunning(): bool
    {
        $now = now();
        return $this->is_active && 
               $this->start_date <= $now && 
               $this->end_date >= $now;
    }

    /**
     * Check if campaign has started
     */
    public function hasStarted(): bool
    {
        return $this->start_date <= now();
    }

    /**
     * Check if campaign has ended
     */
    public function hasEnded(): bool
    {
        return $this->end_date < now();
    }

    /**
     * Check if campaign is scheduled for future
     */
    public function isScheduled(): bool
    {
        return $this->start_date > now();
    }

    /**
     * Get campaign duration in days
     */
    public function getDurationInDays(): int
    {
        return $this->start_date->diffInDays($this->end_date);
    }

    /**
     * Get remaining days in campaign
     */
    public function getRemainingDays(): int
    {
        $now = now();
        if ($now > $this->end_date) {
            return 0;
        }
        return $now->diffInDays($this->end_date);
    }

    /**
     * Get campaign progress percentage
     */
    public function getProgressPercentage(): float
    {
        $totalDuration = $this->getDurationInDays();
        if ($totalDuration <= 0) {
            return 0;
        }

        $elapsed = $this->start_date->diffInDays(now());
        $progress = ($elapsed / $totalDuration) * 100;

        return min(100, max(0, $progress));
    }

    /**
     * Check if campaign has budget remaining
     */
    public function hasBudgetRemaining(): bool
    {
        return !$this->budget_limit || $this->budget_used < $this->budget_limit;
    }

    /**
     * Get remaining budget
     */
    public function getRemainingBudget(): ?float
    {
        if (!$this->budget_limit) {
            return null;
        }

        return max(0, $this->budget_limit - $this->budget_used);
    }

    /**
     * Get budget usage percentage
     */
    public function getBudgetUsagePercentage(): float
    {
        if (!$this->budget_limit) {
            return 0;
        }

        return ($this->budget_used / $this->budget_limit) * 100;
    }

    /**
     * Check if user is in target audience
     */
    public function isUserInTargetAudience(int $userId): bool
    {
        if (!$this->target_audience) {
            return true; // No restrictions
        }

        // This is a placeholder - would need more complex logic based on target_audience structure
        // Could include user segments, categories, demographics, etc.
        return true;
    }

    /**
     * Increment budget usage
     */
    public function incrementBudgetUsage(float $amount): void
    {
        $this->increment('budget_used', $amount);
    }

    /**
     * Get campaign performance metrics
     */
    public function getPerformanceMetrics(): array
    {
        $coupon = $this->coupon;
        $usageCount = $coupon ? $coupon->usages()->count() : 0;
        $totalDiscount = $coupon ? $coupon->usages()->sum('discount_amount') : 0;

        return [
            'usage_count' => $usageCount,
            'total_discount' => $totalDiscount,
            'budget_used' => $this->budget_used,
            'budget_remaining' => $this->getRemainingBudget(),
            'budget_usage_percentage' => $this->getBudgetUsagePercentage(),
            'progress_percentage' => $this->getProgressPercentage(),
            'remaining_days' => $this->getRemainingDays(),
        ];
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'name', 'description', 'type', 'value', 'min_amount',
        'max_discount', 'usage_limit', 'used_count', 'user_limit',
        'per_user_limit', 'starts_at', 'expires_at', 'is_active',
        'is_public', 'applicable_books', 'excluded_books', 'metadata'
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'min_amount' => 'decimal:2',
        'max_discount' => 'decimal:2',
        'usage_limit' => 'integer',
        'used_count' => 'integer',
        'user_limit' => 'integer',
        'per_user_limit' => 'integer',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'is_public' => 'boolean',
        'applicable_books' => 'array',
        'excluded_books' => 'array',
        'metadata' => 'array'
    ];

    // Coupon types
    const TYPE_PERCENTAGE = 'percentage';
    const TYPE_FIXED = 'fixed';
    const TYPE_BOGO = 'bogo';

    /**
     * Get the coupon usage records
     */
    public function usages(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }

    /**
     * Get the campaign associated with this coupon
     */
    public function campaign(): HasOne
    {
        return $this->hasOne(CouponCampaign::class);
    }

    /**
     * Scope for active coupons
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for public coupons
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope for valid coupons (not expired and within usage limits)
     */
    public function scopeValid($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->where(function ($q) {
                $q->whereNull('usage_limit')
                    ->orWhere('used_count', '<', $q->raw('usage_limit'));
            });
    }

    /**
     * Scope for coupons by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Check if coupon is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if coupon has started
     */
    public function hasStarted(): bool
    {
        return !$this->starts_at || $this->starts_at->isPast();
    }

    /**
     * Check if coupon is currently valid
     */
    public function isValid(): bool
    {
        return $this->is_active && 
               $this->hasStarted() && 
               !$this->isExpired() && 
               $this->hasUsageRemaining();
    }

    /**
     * Check if coupon has usage remaining
     */
    public function hasUsageRemaining(): bool
    {
        return !$this->usage_limit || $this->used_count < $this->usage_limit;
    }

    /**
     * Check if user can use this coupon
     */
    public function canBeUsedByUser(int $userId): bool
    {
        if (!$this->isValid()) {
            return false;
        }

        // Check user limit
        if ($this->user_limit) {
            $userUsageCount = $this->usages()->where('user_id', $userId)->count();
            if ($userUsageCount >= $this->user_limit) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if coupon applies to specific book
     */
    public function appliesToBook(int $bookId): bool
    {
        // Check excluded books
        if ($this->excluded_books && in_array($bookId, $this->excluded_books)) {
            return false;
        }

        // Check applicable books (if specified, must be in the list)
        if ($this->applicable_books && !in_array($bookId, $this->applicable_books)) {
            return false;
        }

        return true;
    }

    /**
     * Calculate discount amount for given order total
     */
    public function calculateDiscount(float $orderTotal): float
    {
        if ($orderTotal < $this->min_amount) {
            return 0;
        }

        $discount = 0;

        switch ($this->type) {
            case self::TYPE_PERCENTAGE:
                $discount = ($orderTotal * $this->value) / 100;
                break;
            
            case self::TYPE_FIXED:
                $discount = $this->value;
                break;
            
            case self::TYPE_BOGO:
                // Buy one get one logic - this would need more complex implementation
                $discount = 0; // Placeholder
                break;
        }

        // Apply maximum discount limit
        if ($this->max_discount && $discount > $this->max_discount) {
            $discount = $this->max_discount;
        }

        // Ensure discount doesn't exceed order total
        return min($discount, $orderTotal);
    }

    /**
     * Increment usage count
     */
    public function incrementUsage(): void
    {
        $this->increment('used_count');
    }

    /**
     * Get remaining usage count
     */
    public function getRemainingUsage(): ?int
    {
        if (!$this->usage_limit) {
            return null;
        }

        return max(0, $this->usage_limit - $this->used_count);
    }

    /**
     * Get usage percentage
     */
    public function getUsagePercentage(): float
    {
        if (!$this->usage_limit) {
            return 0;
        }

        return ($this->used_count / $this->usage_limit) * 100;
    }

    /**
     * Check if coupon is fully used
     */
    public function isFullyUsed(): bool
    {
        return $this->usage_limit && $this->used_count >= $this->usage_limit;
    }
}

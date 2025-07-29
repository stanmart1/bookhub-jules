<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CouponUsage extends Model
{
    use HasFactory;

    protected $table = 'coupon_usage';

    protected $fillable = [
        'coupon_id', 'user_id', 'order_id', 'discount_amount',
        'order_total_before', 'order_total_after', 'applied_at', 'metadata'
    ];

    protected $casts = [
        'discount_amount' => 'decimal:2',
        'order_total_before' => 'decimal:2',
        'order_total_after' => 'decimal:2',
        'applied_at' => 'datetime',
        'metadata' => 'array'
    ];

    /**
     * Get the coupon that was used
     */
    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    /**
     * Get the user who used the coupon
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the order where the coupon was applied
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Scope for usage by user
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for usage by coupon
     */
    public function scopeByCoupon($query, int $couponId)
    {
        return $query->where('coupon_id', $couponId);
    }

    /**
     * Scope for usage by order
     */
    public function scopeByOrder($query, int $orderId)
    {
        return $query->where('order_id', $orderId);
    }

    /**
     * Scope for usage within date range
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('applied_at', [$startDate, $endDate]);
    }

    /**
     * Get the savings percentage
     */
    public function getSavingsPercentage(): float
    {
        if ($this->order_total_before <= 0) {
            return 0;
        }

        return ($this->discount_amount / $this->order_total_before) * 100;
    }

    /**
     * Check if this usage is recent (within last 30 days)
     */
    public function isRecent(): bool
    {
        return $this->applied_at->diffInDays(now()) <= 30;
    }
}

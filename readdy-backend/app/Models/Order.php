<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'payment_id',
        'order_number',
        'total_amount',
        'currency',
        'status',
        'delivery_status',
        'metadata',
        'completed_at',
        'cancelled_at',
        'refunded_at',
        'delivered_at',
        'delivery_attempted_at',
        'delivery_token',
        'delivery_attempts',
        'delivery_metadata',
        'confirmation_email_sent',
        'confirmation_sms_sent',
        'confirmation_email_sent_at',
        'confirmation_sms_sent_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'delivery_metadata' => 'array',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'refunded_at' => 'datetime',
        'delivered_at' => 'datetime',
        'delivery_attempted_at' => 'datetime',
        'delivery_attempts' => 'integer',
        'confirmation_email_sent' => 'boolean',
        'confirmation_sms_sent' => 'boolean',
        'confirmation_email_sent_at' => 'datetime',
        'confirmation_sms_sent_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function couponUsage()
    {
        return $this->hasOne(CouponUsage::class);
    }

    // Order status constants
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_REFUNDED = 'refunded';

    // Delivery status constants
    const DELIVERY_STATUS_PENDING = 'pending';
    const DELIVERY_STATUS_PROCESSING = 'processing';
    const DELIVERY_STATUS_DELIVERED = 'delivered';
    const DELIVERY_STATUS_FAILED = 'failed';

    /**
     * Get the delivery logs for this order
     */
    public function deliveryLogs()
    {
        return $this->hasMany(DeliveryLog::class);
    }

    /**
     * Get the download logs for this order
     */
    public function downloadLogs()
    {
        return $this->hasMany(DownloadLog::class);
    }

    // Status scopes
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', self::STATUS_PROCESSING);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    public function scopeRefunded($query)
    {
        return $query->where('status', self::STATUS_REFUNDED);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // Delivery scopes
    public function scopeDeliveryPending($query)
    {
        return $query->where('delivery_status', self::DELIVERY_STATUS_PENDING);
    }

    public function scopeDeliveryProcessing($query)
    {
        return $query->where('delivery_status', self::DELIVERY_STATUS_PROCESSING);
    }

    public function scopeDeliveryDelivered($query)
    {
        return $query->where('delivery_status', self::DELIVERY_STATUS_DELIVERED);
    }

    public function scopeDeliveryFailed($query)
    {
        return $query->where('delivery_status', self::DELIVERY_STATUS_FAILED);
    }

    // Status helper methods
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isProcessing(): bool
    {
        return $this->status === self::STATUS_PROCESSING;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function isRefunded(): bool
    {
        return $this->status === self::STATUS_REFUNDED;
    }

    // Delivery helper methods
    public function isDeliveryPending(): bool
    {
        return $this->delivery_status === self::DELIVERY_STATUS_PENDING;
    }

    public function isDeliveryProcessing(): bool
    {
        return $this->delivery_status === self::DELIVERY_STATUS_PROCESSING;
    }

    public function isDeliveryDelivered(): bool
    {
        return $this->delivery_status === self::DELIVERY_STATUS_DELIVERED;
    }

    public function isDeliveryFailed(): bool
    {
        return $this->delivery_status === self::DELIVERY_STATUS_FAILED;
    }

    public function canBeDelivered(): bool
    {
        return $this->isCompleted() && !$this->isDeliveryDelivered();
    }

    public function needsDeliveryRetry(): bool
    {
        return $this->isDeliveryFailed() && $this->delivery_attempts < 3;
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_PROCESSING]);
    }

    public function canBeRefunded(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    // Status update methods
    public function markAsProcessing(): void
    {
        $this->update(['status' => self::STATUS_PROCESSING]);
    }

    public function markAsCompleted(): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);
    }

    public function markAsCancelled(string $reason = null): void
    {
        $this->update([
            'status' => self::STATUS_CANCELLED,
            'cancelled_at' => now(),
            'metadata' => array_merge($this->metadata ?? [], [
                'cancellation_reason' => $reason,
                'cancelled_by' => auth()->id(),
            ]),
        ]);
    }

    public function markAsRefunded(string $reason = null): void
    {
        $this->update([
            'status' => self::STATUS_REFUNDED,
            'refunded_at' => now(),
            'metadata' => array_merge($this->metadata ?? [], [
                'refund_reason' => $reason,
                'refunded_at' => now()->toISOString()
            ])
        ]);
    }

    // Delivery status update methods
    public function markDeliveryAsProcessing(): void
    {
        $this->update([
            'delivery_status' => self::DELIVERY_STATUS_PROCESSING,
            'delivery_attempted_at' => now(),
            'delivery_attempts' => $this->delivery_attempts + 1
        ]);
    }

    public function markDeliveryAsDelivered(): void
    {
        $this->update([
            'delivery_status' => self::DELIVERY_STATUS_DELIVERED,
            'delivered_at' => now()
        ]);
    }

    public function markDeliveryAsFailed(string $reason = null): void
    {
        $this->update([
            'delivery_status' => self::DELIVERY_STATUS_FAILED,
            'delivery_attempted_at' => now(),
            'delivery_attempts' => $this->delivery_attempts + 1,
            'delivery_metadata' => array_merge($this->delivery_metadata ?? [], [
                'failure_reason' => $reason,
                'failed_at' => now()->toISOString()
            ])
        ]);
    }

    public function generateDeliveryToken(): void
    {
        $this->update([
            'delivery_token' => \Illuminate\Support\Str::random(64)
        ]);
    }

    // Display attributes
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'warning',
            self::STATUS_PROCESSING => 'info',
            self::STATUS_COMPLETED => 'success',
            self::STATUS_CANCELLED => 'danger',
            self::STATUS_REFUNDED => 'secondary',
            default => 'secondary',
        };
    }

    public function getStatusDisplayAttribute(): string
    {
        return ucfirst($this->status);
    }

    public function getFormattedTotalAttribute(): string
    {
        return number_format($this->total_amount, 2) . ' ' . $this->currency;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id', 'user_id', 'delivery_type', 'delivery_method', 'status',
        'recipient', 'subject', 'content', 'metadata', 'sent_at', 'delivered_at',
        'failed_at', 'failure_reason', 'retry_count'
    ];

    protected $casts = [
        'metadata' => 'array',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'failed_at' => 'datetime',
        'retry_count' => 'integer'
    ];

    // Delivery types
    const TYPE_EMAIL = 'email';
    const TYPE_SMS = 'sms';
    const TYPE_DOWNLOAD = 'download';
    const TYPE_NOTIFICATION = 'notification';

    // Delivery methods
    const METHOD_EMAIL = 'email';
    const METHOD_SMS = 'sms';
    const METHOD_IN_APP = 'in_app';
    const METHOD_WEBHOOK = 'webhook';

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_SENT = 'sent';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_FAILED = 'failed';
    const STATUS_BOUNCED = 'bounced';

    /**
     * Get the order associated with this delivery log
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the user associated with this delivery log
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for delivery type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('delivery_type', $type);
    }

    /**
     * Scope for delivery method
     */
    public function scopeByMethod($query, string $method)
    {
        return $query->where('delivery_method', $method);
    }

    /**
     * Scope for status
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for successful deliveries
     */
    public function scopeSuccessful($query)
    {
        return $query->whereIn('status', [self::STATUS_SENT, self::STATUS_DELIVERED]);
    }

    /**
     * Scope for failed deliveries
     */
    public function scopeFailed($query)
    {
        return $query->whereIn('status', [self::STATUS_FAILED, self::STATUS_BOUNCED]);
    }

    /**
     * Scope for recent deliveries
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Check if delivery was successful
     */
    public function isSuccessful(): bool
    {
        return in_array($this->status, [self::STATUS_SENT, self::STATUS_DELIVERED]);
    }

    /**
     * Check if delivery failed
     */
    public function isFailed(): bool
    {
        return in_array($this->status, [self::STATUS_FAILED, self::STATUS_BOUNCED]);
    }

    /**
     * Check if delivery is pending
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Get delivery duration in seconds
     */
    public function getDeliveryDuration(): ?int
    {
        if (!$this->sent_at || !$this->delivered_at) {
            return null;
        }

        return $this->sent_at->diffInSeconds($this->delivered_at);
    }

    /**
     * Get delivery success rate for a specific type and method
     */
    public static function getSuccessRate(string $type, string $method, int $days = 30): float
    {
        $total = self::where('delivery_type', $type)
            ->where('delivery_method', $method)
            ->where('created_at', '>=', now()->subDays($days))
            ->count();

        if ($total === 0) {
            return 0;
        }

        $successful = self::where('delivery_type', $type)
            ->where('delivery_method', $method)
            ->where('created_at', '>=', now()->subDays($days))
            ->successful()
            ->count();

        return ($successful / $total) * 100;
    }

    /**
     * Get delivery statistics
     */
    public static function getStatistics(int $days = 30): array
    {
        $query = self::where('created_at', '>=', now()->subDays($days));

        return [
            'total_deliveries' => $query->count(),
            'successful_deliveries' => $query->successful()->count(),
            'failed_deliveries' => $query->failed()->count(),
            'pending_deliveries' => $query->byStatus(self::STATUS_PENDING)->count(),
            'success_rate' => $query->count() > 0 ? 
                ($query->successful()->count() / $query->count()) * 100 : 0,
            'by_type' => $query->selectRaw('delivery_type, COUNT(*) as count')
                ->groupBy('delivery_type')
                ->pluck('count', 'delivery_type')
                ->toArray(),
            'by_method' => $query->selectRaw('delivery_method, COUNT(*) as count')
                ->groupBy('delivery_method')
                ->pluck('count', 'delivery_method')
                ->toArray(),
            'by_status' => $query->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray()
        ];
    }
}

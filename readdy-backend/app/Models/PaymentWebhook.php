<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PaymentWebhook extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'gateway_name',
        'event_type',
        'webhook_reference',
        'payload',
        'processed_data',
        'status',
        'error_message',
        'retry_count',
        'processed_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'payload' => 'array',
        'processed_data' => 'array',
        'processed_at' => 'datetime',
    ];

    /**
     * Webhook status constants.
     */
    const STATUS_RECEIVED = 'received';
    const STATUS_PROCESSING = 'processing';
    const STATUS_PROCESSED = 'processed';
    const STATUS_FAILED = 'failed';

    /**
     * Event type constants.
     */
    const EVENT_PAYMENT_SUCCESSFUL = 'payment.successful';
    const EVENT_PAYMENT_FAILED = 'payment.failed';
    const EVENT_PAYMENT_CANCELLED = 'payment.cancelled';
    const EVENT_TRANSFER_SUCCESSFUL = 'transfer.successful';
    const EVENT_TRANSFER_FAILED = 'transfer.failed';

    /**
     * Scope for received webhooks.
     */
    public function scopeReceived($query)
    {
        return $query->where('status', self::STATUS_RECEIVED);
    }

    /**
     * Scope for processing webhooks.
     */
    public function scopeProcessing($query)
    {
        return $query->where('status', self::STATUS_PROCESSING);
    }

    /**
     * Scope for processed webhooks.
     */
    public function scopeProcessed($query)
    {
        return $query->where('status', self::STATUS_PROCESSED);
    }

    /**
     * Scope for failed webhooks.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    /**
     * Scope for webhooks by gateway.
     */
    public function scopeByGateway($query, $gatewayName)
    {
        return $query->where('gateway_name', $gatewayName);
    }

    /**
     * Scope for webhooks by event type.
     */
    public function scopeByEventType($query, $eventType)
    {
        return $query->where('event_type', $eventType);
    }

    /**
     * Scope for recent webhooks.
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope for retryable webhooks.
     */
    public function scopeRetryable($query)
    {
        return $query->whereIn('status', [self::STATUS_FAILED, self::STATUS_RECEIVED])
                    ->where('retry_count', '<', 3);
    }

    /**
     * Check if webhook is received.
     */
    public function isReceived(): bool
    {
        return $this->status === self::STATUS_RECEIVED;
    }

    /**
     * Check if webhook is processing.
     */
    public function isProcessing(): bool
    {
        return $this->status === self::STATUS_PROCESSING;
    }

    /**
     * Check if webhook is processed.
     */
    public function isProcessed(): bool
    {
        return $this->status === self::STATUS_PROCESSED;
    }

    /**
     * Check if webhook is failed.
     */
    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * Check if webhook can be retried.
     */
    public function canRetry(): bool
    {
        return in_array($this->status, [self::STATUS_FAILED, self::STATUS_RECEIVED]) &&
               $this->retry_count < 3;
    }

    /**
     * Mark webhook as processing.
     */
    public function markAsProcessing(): void
    {
        $this->update(['status' => self::STATUS_PROCESSING]);
    }

    /**
     * Mark webhook as processed.
     */
    public function markAsProcessed(array $processedData = []): void
    {
        $this->update([
            'status' => self::STATUS_PROCESSED,
            'processed_data' => $processedData,
            'processed_at' => now(),
        ]);
    }

    /**
     * Mark webhook as failed.
     */
    public function markAsFailed(string $errorMessage = null): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'error_message' => $errorMessage,
            'retry_count' => $this->retry_count + 1,
        ]);
    }

    /**
     * Increment retry count.
     */
    public function incrementRetryCount(): void
    {
        $this->increment('retry_count');
    }

    /**
     * Get webhook status badge.
     */
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            self::STATUS_RECEIVED => 'info',
            self::STATUS_PROCESSING => 'warning',
            self::STATUS_PROCESSED => 'success',
            self::STATUS_FAILED => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Get event type display name.
     */
    public function getEventTypeDisplayAttribute(): string
    {
        return match($this->event_type) {
            self::EVENT_PAYMENT_SUCCESSFUL => 'Payment Successful',
            self::EVENT_PAYMENT_FAILED => 'Payment Failed',
            self::EVENT_PAYMENT_CANCELLED => 'Payment Cancelled',
            self::EVENT_TRANSFER_SUCCESSFUL => 'Transfer Successful',
            self::EVENT_TRANSFER_FAILED => 'Transfer Failed',
            default => ucfirst(str_replace('.', ' ', $this->event_type)),
        };
    }

    /**
     * Get gateway display name.
     */
    public function getGatewayDisplayNameAttribute(): string
    {
        return match($this->gateway_name) {
            'flutterwave' => 'Flutterwave',
            'paystack' => 'PayStack',
            'stripe' => 'Stripe',
            default => ucfirst($this->gateway_name),
        };
    }

    /**
     * Generate a unique webhook reference.
     */
    public static function generateReference(): string
    {
        do {
            $reference = 'WEBHOOK-' . strtoupper(uniqid()) . '-' . time();
        } while (self::where('webhook_reference', $reference)->exists());

        return $reference;
    }

    /**
     * Get payload value by key.
     */
    public function getPayloadValue(string $key, $default = null)
    {
        return data_get($this->payload, $key, $default);
    }

    /**
     * Get processed data value by key.
     */
    public function getProcessedDataValue(string $key, $default = null)
    {
        return data_get($this->processed_data, $key, $default);
    }
}

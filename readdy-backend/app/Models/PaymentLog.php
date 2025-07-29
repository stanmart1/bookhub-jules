<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PaymentLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'payment_id',
        'user_id',
        'action',
        'gateway_name',
        'payment_reference',
        'request_data',
        'response_data',
        'ip_address',
        'user_agent',
        'status',
        'error_message',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'request_data' => 'array',
        'response_data' => 'array',
        'metadata' => 'array',
    ];

    /**
     * Log status constants.
     */
    const STATUS_SUCCESS = 'success';
    const STATUS_ERROR = 'error';
    const STATUS_WARNING = 'warning';

    /**
     * Action constants.
     */
    const ACTION_PAYMENT_INITIATED = 'payment.initiated';
    const ACTION_PAYMENT_VERIFIED = 'payment.verified';
    const ACTION_PAYMENT_FAILED = 'payment.failed';
    const ACTION_WEBHOOK_RECEIVED = 'webhook.received';
    const ACTION_WEBHOOK_PROCESSED = 'webhook.processed';
    const ACTION_WEBHOOK_FAILED = 'webhook.failed';
    const ACTION_PAYMENT_CANCELLED = 'payment.cancelled';
    const ACTION_PAYMENT_EXPIRED = 'payment.expired';
    const ACTION_PAYMENT_REFUNDED = 'payment.refunded';

    /**
     * Get the payment this log belongs to.
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * Get the user this log belongs to.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for successful logs.
     */
    public function scopeSuccess($query)
    {
        return $query->where('status', self::STATUS_SUCCESS);
    }

    /**
     * Scope for error logs.
     */
    public function scopeError($query)
    {
        return $query->where('status', self::STATUS_ERROR);
    }

    /**
     * Scope for warning logs.
     */
    public function scopeWarning($query)
    {
        return $query->where('status', self::STATUS_WARNING);
    }

    /**
     * Scope for logs by action.
     */
    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope for logs by gateway.
     */
    public function scopeByGateway($query, $gatewayName)
    {
        return $query->where('gateway_name', $gatewayName);
    }

    /**
     * Scope for logs by payment reference.
     */
    public function scopeByPaymentReference($query, $paymentReference)
    {
        return $query->where('payment_reference', $paymentReference);
    }

    /**
     * Scope for recent logs.
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Check if log is successful.
     */
    public function isSuccess(): bool
    {
        return $this->status === self::STATUS_SUCCESS;
    }

    /**
     * Check if log is error.
     */
    public function isError(): bool
    {
        return $this->status === self::STATUS_ERROR;
    }

    /**
     * Check if log is warning.
     */
    public function isWarning(): bool
    {
        return $this->status === self::STATUS_WARNING;
    }

    /**
     * Get log status badge.
     */
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            self::STATUS_SUCCESS => 'success',
            self::STATUS_ERROR => 'danger',
            self::STATUS_WARNING => 'warning',
            default => 'secondary',
        };
    }

    /**
     * Get action display name.
     */
    public function getActionDisplayAttribute(): string
    {
        return match($this->action) {
            self::ACTION_PAYMENT_INITIATED => 'Payment Initiated',
            self::ACTION_PAYMENT_VERIFIED => 'Payment Verified',
            self::ACTION_PAYMENT_FAILED => 'Payment Failed',
            self::ACTION_WEBHOOK_RECEIVED => 'Webhook Received',
            self::ACTION_WEBHOOK_PROCESSED => 'Webhook Processed',
            self::ACTION_WEBHOOK_FAILED => 'Webhook Failed',
            self::ACTION_PAYMENT_CANCELLED => 'Payment Cancelled',
            self::ACTION_PAYMENT_EXPIRED => 'Payment Expired',
            self::ACTION_PAYMENT_REFUNDED => 'Payment Refunded',
            default => ucfirst(str_replace('.', ' ', $this->action)),
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
            default => ucfirst($this->gateway_name ?? 'Unknown'),
        };
    }

    /**
     * Get request data value by key.
     */
    public function getRequestDataValue(string $key, $default = null)
    {
        return data_get($this->request_data, $key, $default);
    }

    /**
     * Get response data value by key.
     */
    public function getResponseDataValue(string $key, $default = null)
    {
        return data_get($this->response_data, $key, $default);
    }

    /**
     * Get metadata value by key.
     */
    public function getMetadataValue(string $key, $default = null)
    {
        return data_get($this->metadata, $key, $default);
    }

    /**
     * Get formatted timestamp.
     */
    public function getFormattedTimestampAttribute(): string
    {
        return $this->created_at->format('M j, Y g:i A');
    }

    /**
     * Get time ago.
     */
    public function getTimeAgoAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Check if log has request data.
     */
    public function hasRequestData(): bool
    {
        return !empty($this->request_data);
    }

    /**
     * Check if log has response data.
     */
    public function hasResponseData(): bool
    {
        return !empty($this->response_data);
    }

    /**
     * Check if log has error message.
     */
    public function hasErrorMessage(): bool
    {
        return !empty($this->error_message);
    }

    /**
     * Get error message preview.
     */
    public function getErrorMessagePreviewAttribute(): ?string
    {
        if (!$this->hasErrorMessage()) {
            return null;
        }

        return \Str::limit($this->error_message, 100);
    }
}

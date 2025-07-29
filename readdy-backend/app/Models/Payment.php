<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'book_id',
        'payment_reference',
        'gateway_reference',
        'gateway_name',
        'amount',
        'currency',
        'payment_method',
        'status',
        'gateway_response',
        'metadata',
        'paid_at',
        'expires_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'gateway_response' => 'array',
        'metadata' => 'array',
        'paid_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Payment status constants.
     */
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_SUCCESSFUL = 'successful';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_EXPIRED = 'expired';

    /**
     * Get the user who made the payment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the book being purchased.
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * Get the payment logs for this payment.
     */
    public function logs(): HasMany
    {
        return $this->hasMany(PaymentLog::class);
    }

    /**
     * Scope for pending payments.
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope for successful payments.
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', self::STATUS_SUCCESSFUL);
    }

    /**
     * Scope for failed payments.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    /**
     * Scope for expired payments.
     */
    public function scopeExpired($query)
    {
        return $query->where('status', self::STATUS_EXPIRED);
    }

    /**
     * Scope for recent payments.
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope for payments by gateway.
     */
    public function scopeByGateway($query, $gatewayName)
    {
        return $query->where('gateway_name', $gatewayName);
    }

    /**
     * Check if payment is pending.
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if payment is successful.
     */
    public function isSuccessful(): bool
    {
        return $this->status === self::STATUS_SUCCESSFUL;
    }

    /**
     * Check if payment is failed.
     */
    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * Check if payment is expired.
     */
    public function isExpired(): bool
    {
        return $this->status === self::STATUS_EXPIRED || 
               ($this->expires_at && $this->expires_at->isPast());
    }

    /**
     * Check if payment can be retried.
     */
    public function canRetry(): bool
    {
        return in_array($this->status, [self::STATUS_FAILED, self::STATUS_CANCELLED]) &&
               !$this->isExpired();
    }

    /**
     * Mark payment as successful.
     */
    public function markAsSuccessful(): void
    {
        $this->update([
            'status' => self::STATUS_SUCCESSFUL,
            'paid_at' => now(),
        ]);
    }

    /**
     * Mark payment as failed.
     */
    public function markAsFailed(string $errorMessage = null): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'metadata' => array_merge($this->metadata ?? [], [
                'error_message' => $errorMessage,
                'failed_at' => now()->toISOString(),
            ]),
        ]);
    }

    /**
     * Mark payment as expired.
     */
    public function markAsExpired(): void
    {
        $this->update([
            'status' => self::STATUS_EXPIRED,
        ]);
    }

    /**
     * Get formatted amount.
     */
    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 2) . ' ' . $this->currency;
    }

    /**
     * Get payment status badge.
     */
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'warning',
            self::STATUS_PROCESSING => 'info',
            self::STATUS_SUCCESSFUL => 'success',
            self::STATUS_FAILED => 'danger',
            self::STATUS_CANCELLED => 'secondary',
            self::STATUS_EXPIRED => 'dark',
            default => 'secondary',
        };
    }

    /**
     * Get payment method display name.
     */
    public function getPaymentMethodDisplayAttribute(): string
    {
        return match($this->payment_method) {
            'card' => 'Credit/Debit Card',
            'bank_transfer' => 'Bank Transfer',
            'mobile_money' => 'Mobile Money',
            'digital_wallet' => 'Digital Wallet',
            default => ucfirst(str_replace('_', ' ', $this->payment_method ?? 'unknown')),
        };
    }

    /**
     * Generate a unique payment reference.
     */
    public static function generateReference(): string
    {
        do {
            $reference = 'PAY-' . strtoupper(uniqid()) . '-' . time();
        } while (self::where('payment_reference', $reference)->exists());

        return $reference;
    }
}

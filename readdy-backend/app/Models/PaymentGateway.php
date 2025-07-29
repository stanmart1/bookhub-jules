<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PaymentGateway extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'display_name',
        'description',
        'config',
        'is_active',
        'is_test_mode',
        'supported_currencies',
        'supported_payment_methods',
        'priority',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'config' => 'array',
        'is_active' => 'boolean',
        'is_test_mode' => 'boolean',
        'supported_currencies' => 'array',
        'supported_payment_methods' => 'array',
    ];

    /**
     * Gateway name constants.
     */
    const GATEWAY_FLUTTERWAVE = 'flutterwave';
    const GATEWAY_PAYSTACK = 'paystack';
    const GATEWAY_STRIPE = 'stripe';

    /**
     * Scope for active gateways.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for test mode gateways.
     */
    public function scopeTestMode($query)
    {
        return $query->where('is_test_mode', true);
    }

    /**
     * Scope for production mode gateways.
     */
    public function scopeProductionMode($query)
    {
        return $query->where('is_test_mode', false);
    }

    /**
     * Scope for gateways by priority.
     */
    public function scopeByPriority($query)
    {
        return $query->orderBy('priority', 'asc');
    }

    /**
     * Check if gateway supports a specific currency.
     */
    public function supportsCurrency(string $currency): bool
    {
        if (empty($this->supported_currencies)) {
            return true; // If not specified, assume all currencies supported
        }

        return in_array(strtoupper($currency), array_map('strtoupper', $this->supported_currencies));
    }

    /**
     * Check if gateway supports a specific payment method.
     */
    public function supportsPaymentMethod(string $paymentMethod): bool
    {
        if (empty($this->supported_payment_methods)) {
            return true; // If not specified, assume all methods supported
        }

        return in_array($paymentMethod, $this->supported_payment_methods);
    }

    /**
     * Get gateway configuration value.
     */
    public function getConfig(string $key, $default = null)
    {
        return data_get($this->config, $key, $default);
    }

    /**
     * Set gateway configuration value.
     */
    public function setConfig(string $key, $value): void
    {
        $config = $this->config ?? [];
        data_set($config, $key, $value);
        $this->update(['config' => $config]);
    }

    /**
     * Get API key for the gateway.
     */
    public function getApiKey(): ?string
    {
        $key = $this->is_test_mode ? 'test_public_key' : 'live_public_key';
        return $this->getConfig($key);
    }

    /**
     * Get secret key for the gateway.
     */
    public function getSecretKey(): ?string
    {
        $key = $this->is_test_mode ? 'test_secret_key' : 'live_secret_key';
        return $this->getConfig($key);
    }

    /**
     * Get webhook secret for the gateway.
     */
    public function getWebhookSecret(): ?string
    {
        $key = $this->is_test_mode ? 'test_webhook_secret' : 'live_webhook_secret';
        return $this->getConfig($key);
    }

    /**
     * Check if gateway is Flutterwave.
     */
    public function isFlutterwave(): bool
    {
        return $this->name === self::GATEWAY_FLUTTERWAVE;
    }

    /**
     * Check if gateway is PayStack.
     */
    public function isPayStack(): bool
    {
        return $this->name === self::GATEWAY_PAYSTACK;
    }

    /**
     * Check if gateway is Stripe.
     */
    public function isStripe(): bool
    {
        return $this->name === self::GATEWAY_STRIPE;
    }

    /**
     * Get gateway status badge.
     */
    public function getStatusBadgeAttribute(): string
    {
        if (!$this->is_active) {
            return 'danger';
        }

        return $this->is_test_mode ? 'warning' : 'success';
    }

    /**
     * Get gateway status text.
     */
    public function getStatusTextAttribute(): string
    {
        if (!$this->is_active) {
            return 'Inactive';
        }

        return $this->is_test_mode ? 'Test Mode' : 'Live Mode';
    }

    /**
     * Get supported currencies display.
     */
    public function getSupportedCurrenciesDisplayAttribute(): string
    {
        if (empty($this->supported_currencies)) {
            return 'All Currencies';
        }

        return implode(', ', $this->supported_currencies);
    }

    /**
     * Get supported payment methods display.
     */
    public function getSupportedPaymentMethodsDisplayAttribute(): string
    {
        if (empty($this->supported_payment_methods)) {
            return 'All Methods';
        }

        return implode(', ', array_map(function($method) {
            return ucfirst(str_replace('_', ' ', $method));
        }, $this->supported_payment_methods));
    }
}

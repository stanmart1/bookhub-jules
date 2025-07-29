<?php

namespace App\Services\Payment;

use App\Models\Payment;
use App\Models\PaymentGateway;

interface GatewayServiceInterface
{
    /**
     * Initialize a payment with the gateway.
     */
    public function initializePayment(Payment $payment, PaymentGateway $gateway): array;

    /**
     * Verify a payment with the gateway.
     */
    public function verifyPayment(Payment $payment, PaymentGateway $gateway): array;

    /**
     * Process webhook from the gateway.
     */
    public function processWebhook(array $payload, PaymentGateway $gateway): array;

    /**
     * Verify webhook signature.
     */
    public function verifyWebhookSignature(array $payload, string $signature, PaymentGateway $gateway): bool;
} 
<?php

namespace App\Services\Payment;

use App\Models\Payment;
use App\Models\PaymentGateway;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayStackService implements GatewayServiceInterface
{
    /**
     * Initialize a payment with PayStack.
     */
    public function initializePayment(Payment $payment, PaymentGateway $gateway): array
    {
        try {
            $apiKey = $gateway->getSecretKey();
            $baseUrl = $gateway->is_test_mode ? 'https://api.paystack.co' : 'https://api.paystack.co';

            $payload = [
                'amount' => $payment->amount * 100, // PayStack expects amount in kobo (smallest currency unit)
                'email' => $payment->metadata['user_email'] ?? '',
                'reference' => $payment->payment_reference,
                'currency' => $payment->currency,
                'callback_url' => config('app.url') . '/api/v1/payments/verify/paystack',
                'metadata' => [
                    'payment_reference' => $payment->payment_reference,
                    'book_id' => $payment->book_id,
                    'user_id' => $payment->user_id,
                    'book_title' => $payment->metadata['book_title'] ?? '',
                    'book_author' => $payment->metadata['book_author'] ?? '',
                ],
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->post($baseUrl . '/transaction/initialize', $payload);

            $responseData = $response->json();

            if ($response->successful() && $responseData['status']) {
                return [
                    'success' => true,
                    'reference' => $responseData['data']['reference'],
                    'redirect_url' => $responseData['data']['authorization_url'],
                    'gateway_response' => $responseData,
                ];
            } else {
                Log::error('PayStack payment initialization failed', [
                    'payment_reference' => $payment->payment_reference,
                    'response' => $responseData,
                ]);

                return [
                    'success' => false,
                    'error' => $responseData['message'] ?? 'Payment initialization failed',
                    'gateway_response' => $responseData,
                ];
            }

        } catch (\Exception $e) {
            Log::error('PayStack payment initialization error: ' . $e->getMessage(), [
                'payment_reference' => $payment->payment_reference,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Payment initialization failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Verify a payment with PayStack.
     */
    public function verifyPayment(Payment $payment, PaymentGateway $gateway): array
    {
        try {
            $apiKey = $gateway->getSecretKey();
            $baseUrl = $gateway->is_test_mode ? 'https://api.paystack.co' : 'https://api.paystack.co';

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->get($baseUrl . '/transaction/verify/' . $payment->gateway_reference);

            $responseData = $response->json();

            if ($response->successful() && $responseData['status']) {
                $transaction = $responseData['data'];

                // Verify payment details
                if ($transaction['reference'] !== $payment->payment_reference) {
                    return [
                        'success' => false,
                        'error' => 'Payment reference mismatch',
                        'gateway_response' => $responseData,
                    ];
                }

                if ($transaction['amount'] != ($payment->amount * 100)) { // Convert to kobo
                    return [
                        'success' => false,
                        'error' => 'Payment amount mismatch',
                        'gateway_response' => $responseData,
                    ];
                }

                if ($transaction['currency'] !== $payment->currency) {
                    return [
                        'success' => false,
                        'error' => 'Payment currency mismatch',
                        'gateway_response' => $responseData,
                    ];
                }

                // Check payment status
                if ($transaction['status'] === 'success') {
                    return [
                        'success' => true,
                        'message' => 'Payment verified successfully',
                        'gateway_response' => $responseData,
                    ];
                } elseif ($transaction['status'] === 'failed') {
                    return [
                        'success' => false,
                        'error' => 'Payment failed: ' . ($transaction['gateway_response'] ?? 'Unknown error'),
                        'gateway_response' => $responseData,
                    ];
                } else {
                    return [
                        'success' => false,
                        'error' => 'Payment status: ' . $transaction['status'],
                        'gateway_response' => $responseData,
                    ];
                }
            } else {
                Log::error('PayStack payment verification failed', [
                    'payment_reference' => $payment->payment_reference,
                    'response' => $responseData,
                ]);

                return [
                    'success' => false,
                    'error' => $responseData['message'] ?? 'Payment verification failed',
                    'gateway_response' => $responseData,
                ];
            }

        } catch (\Exception $e) {
            Log::error('PayStack payment verification error: ' . $e->getMessage(), [
                'payment_reference' => $payment->payment_reference,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Payment verification failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Process webhook from PayStack.
     */
    public function processWebhook(array $payload, PaymentGateway $gateway): array
    {
        try {
            // Verify webhook signature
            if (!$this->verifyWebhookSignature($payload, request()->getContent(), $gateway)) {
                return [
                    'success' => false,
                    'error' => 'Invalid webhook signature',
                ];
            }

            $event = $payload['event'] ?? '';
            $transaction = $payload['data'] ?? [];

            switch ($event) {
                case 'charge.success':
                    return $this->handlePaymentSuccess($transaction);
                case 'charge.failed':
                    return $this->handlePaymentFailure($transaction);
                default:
                    return [
                        'success' => true,
                        'message' => 'Webhook processed (unhandled event)',
                    ];
            }

        } catch (\Exception $e) {
            Log::error('PayStack webhook processing error: ' . $e->getMessage(), [
                'payload' => $payload,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Webhook processing failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Verify webhook signature.
     */
    public function verifyWebhookSignature(array $payload, string $rawBody, PaymentGateway $gateway): bool
    {
        try {
            $webhookSecret = $gateway->getWebhookSecret();
            
            if (!$webhookSecret) {
                Log::warning('PayStack webhook secret not configured');
                return true; // Allow processing if secret not configured
            }

            $signature = request()->header('x-paystack-signature');
            
            if (!$signature) {
                Log::warning('PayStack webhook signature header not found');
                return false;
            }

            $expectedSignature = hash_hmac('sha512', $rawBody, $webhookSecret);
            
            return hash_equals($expectedSignature, $signature);

        } catch (\Exception $e) {
            Log::error('PayStack webhook signature verification error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Handle successful payment.
     */
    private function handlePaymentSuccess(array $transaction): array
    {
        $paymentReference = $transaction['reference'] ?? '';
        
        if (!$paymentReference) {
            return [
                'success' => false,
                'error' => 'Payment reference not found in webhook',
            ];
        }

        // Find and update payment
        $payment = Payment::where('payment_reference', $paymentReference)
            ->where('gateway_name', 'paystack')
            ->first();

        if (!$payment) {
            return [
                'success' => false,
                'error' => 'Payment not found',
            ];
        }

        if ($payment->isSuccessful()) {
            return [
                'success' => true,
                'message' => 'Payment already processed',
            ];
        }

        $payment->markAsSuccessful();

        return [
            'success' => true,
            'message' => 'Payment processed successfully',
            'payment_id' => $payment->id,
        ];
    }

    /**
     * Handle failed payment.
     */
    private function handlePaymentFailure(array $transaction): array
    {
        $paymentReference = $transaction['reference'] ?? '';
        
        if (!$paymentReference) {
            return [
                'success' => false,
                'error' => 'Payment reference not found in webhook',
            ];
        }

        // Find and update payment
        $payment = Payment::where('payment_reference', $paymentReference)
            ->where('gateway_name', 'paystack')
            ->first();

        if (!$payment) {
            return [
                'success' => false,
                'error' => 'Payment not found',
            ];
        }

        $failureReason = $transaction['gateway_response'] ?? 'Payment failed';
        $payment->markAsFailed($failureReason);

        return [
            'success' => true,
            'message' => 'Payment failure processed',
            'payment_id' => $payment->id,
        ];
    }
} 
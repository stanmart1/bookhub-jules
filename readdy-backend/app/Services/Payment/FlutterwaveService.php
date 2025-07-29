<?php

namespace App\Services\Payment;

use App\Models\Payment;
use App\Models\PaymentGateway;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FlutterwaveService implements GatewayServiceInterface
{
    /**
     * Initialize a payment with Flutterwave.
     */
    public function initializePayment(Payment $payment, PaymentGateway $gateway): array
    {
        try {
            $apiKey = $gateway->getSecretKey();
            $baseUrl = $gateway->is_test_mode ? 'https://sandbox-api.flutterwave.com' : 'https://api.flutterwave.com';

            $payload = [
                'tx_ref' => $payment->payment_reference,
                'amount' => $payment->amount,
                'currency' => $payment->currency,
                'redirect_url' => config('app.url') . '/api/v1/payments/verify/flutterwave',
                'customer' => [
                    'email' => $payment->metadata['user_email'] ?? '',
                    'name' => $payment->metadata['user_name'] ?? '',
                ],
                'customizations' => [
                    'title' => 'Readdy Book Purchase',
                    'description' => "Purchase: {$payment->metadata['book_title']} by {$payment->metadata['book_author']}",
                    'logo' => config('app.url') . '/images/logo.png',
                ],
                'meta' => [
                    'payment_reference' => $payment->payment_reference,
                    'book_id' => $payment->book_id,
                    'user_id' => $payment->user_id,
                ],
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->post($baseUrl . '/v3/payments', $payload);

            $responseData = $response->json();

            if ($response->successful() && $responseData['status'] === 'success') {
                return [
                    'success' => true,
                    'reference' => $responseData['data']['tx_ref'],
                    'redirect_url' => $responseData['data']['link'],
                    'gateway_response' => $responseData,
                ];
            } else {
                Log::error('Flutterwave payment initialization failed', [
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
            Log::error('Flutterwave payment initialization error: ' . $e->getMessage(), [
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
     * Verify a payment with Flutterwave.
     */
    public function verifyPayment(Payment $payment, PaymentGateway $gateway): array
    {
        try {
            $apiKey = $gateway->getSecretKey();
            $baseUrl = $gateway->is_test_mode ? 'https://sandbox-api.flutterwave.com' : 'https://api.flutterwave.com';

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->get($baseUrl . '/v3/transactions/' . $payment->gateway_reference . '/verify');

            $responseData = $response->json();

            if ($response->successful() && $responseData['status'] === 'success') {
                $transaction = $responseData['data'];

                // Verify payment details
                if ($transaction['tx_ref'] !== $payment->payment_reference) {
                    return [
                        'success' => false,
                        'error' => 'Payment reference mismatch',
                        'gateway_response' => $responseData,
                    ];
                }

                if ($transaction['amount'] != $payment->amount) {
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
                if ($transaction['status'] === 'successful') {
                    return [
                        'success' => true,
                        'message' => 'Payment verified successfully',
                        'gateway_response' => $responseData,
                    ];
                } elseif ($transaction['status'] === 'failed') {
                    return [
                        'success' => false,
                        'error' => 'Payment failed: ' . ($transaction['failure_reason'] ?? 'Unknown error'),
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
                Log::error('Flutterwave payment verification failed', [
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
            Log::error('Flutterwave payment verification error: ' . $e->getMessage(), [
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
     * Process webhook from Flutterwave.
     */
    public function processWebhook(array $payload, PaymentGateway $gateway): array
    {
        try {
            // Verify webhook signature
            if (!$this->verifyWebhookSignature($payload, request()->header('verif-hash') ?? '', $gateway)) {
                return [
                    'success' => false,
                    'error' => 'Invalid webhook signature',
                ];
            }

            $event = $payload['event'] ?? '';
            $transaction = $payload['data'] ?? [];

            switch ($event) {
                case 'charge.completed':
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
            Log::error('Flutterwave webhook processing error: ' . $e->getMessage(), [
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
    public function verifyWebhookSignature(array $payload, string $signature, PaymentGateway $gateway): bool
    {
        try {
            $webhookSecret = $gateway->getWebhookSecret();
            
            if (!$webhookSecret) {
                Log::warning('Flutterwave webhook secret not configured');
                return true; // Allow processing if secret not configured
            }

            $expectedSignature = hash_hmac('sha512', json_encode($payload), $webhookSecret);
            
            return hash_equals($expectedSignature, $signature);

        } catch (\Exception $e) {
            Log::error('Flutterwave webhook signature verification error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Handle successful payment.
     */
    private function handlePaymentSuccess(array $transaction): array
    {
        $paymentReference = $transaction['tx_ref'] ?? '';
        
        if (!$paymentReference) {
            return [
                'success' => false,
                'error' => 'Payment reference not found in webhook',
            ];
        }

        // Find and update payment
        $payment = Payment::where('payment_reference', $paymentReference)
            ->where('gateway_name', 'flutterwave')
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
        $paymentReference = $transaction['tx_ref'] ?? '';
        
        if (!$paymentReference) {
            return [
                'success' => false,
                'error' => 'Payment reference not found in webhook',
            ];
        }

        // Find and update payment
        $payment = Payment::where('payment_reference', $paymentReference)
            ->where('gateway_name', 'flutterwave')
            ->first();

        if (!$payment) {
            return [
                'success' => false,
                'error' => 'Payment not found',
            ];
        }

        $failureReason = $transaction['failure_reason'] ?? 'Payment failed';
        $payment->markAsFailed($failureReason);

        return [
            'success' => true,
            'message' => 'Payment failure processed',
            'payment_id' => $payment->id,
        ];
    }

    /**
     * Process refund for a payment.
     */
    public function processRefund(Payment $payment, float $refundAmount, string $refundReference, string $reason = null): array
    {
        try {
            $gateway = PaymentGateway::where('name', 'flutterwave')->first();
            
            if (!$gateway) {
                return [
                    'success' => false,
                    'message' => 'Flutterwave gateway not configured',
                ];
            }

            $config = $gateway->metadata;
            $secretKey = $config['secret_key'] ?? null;

            if (!$secretKey) {
                return [
                    'success' => false,
                    'message' => 'Flutterwave secret key not configured',
                ];
            }

            // Prepare refund data
            $refundData = [
                'transaction_id' => $payment->gateway_reference,
                'amount' => $refundAmount,
                'currency' => $payment->currency,
                'reason' => $reason ?? 'Customer request',
            ];

            // Make API call to Flutterwave
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $secretKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.flutterwave.com/v3/refunds', $refundData);

            $responseData = $response->json();

            if ($response->successful() && $responseData['status'] === 'success') {
                return [
                    'success' => true,
                    'refund_reference' => $refundReference,
                    'refund_amount' => $refundAmount,
                    'gateway_refund_id' => $responseData['data']['id'] ?? null,
                    'message' => 'Refund processed successfully',
                    'gateway_response' => $responseData,
                ];
            } else {
                return [
                    'success' => false,
                    'message' => $responseData['message'] ?? 'Refund failed',
                    'gateway_response' => $responseData,
                ];
            }

        } catch (\Exception $e) {
            Log::error('Flutterwave refund error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Refund processing failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Check if gateway supports partial refunds.
     */
    public function supportsPartialRefund(): bool
    {
        return true; // Flutterwave supports partial refunds
    }
} 
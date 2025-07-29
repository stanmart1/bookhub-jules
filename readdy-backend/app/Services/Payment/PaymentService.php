<?php

namespace App\Services\Payment;

use App\Models\Payment;
use App\Models\PaymentGateway;
use App\Models\PaymentLog;
use App\Models\User;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    /**
     * Initialize a new payment.
     */
    public function initializePayment(
        User $user,
        Book $book,
        string $gatewayName,
        string $paymentMethod = null,
        array $metadata = []
    ): array {
        try {
            DB::beginTransaction();

            // Get the payment gateway
            $gateway = PaymentGateway::where('name', $gatewayName)
                ->where('is_active', true)
                ->first();

            if (!$gateway) {
                throw new \Exception("Payment gateway '{$gatewayName}' is not available");
            }

            // Check if gateway supports the currency
            if (!$gateway->supportsCurrency($book->currency ?? 'NGN')) {
                throw new \Exception("Gateway '{$gatewayName}' does not support currency '{$book->currency}'");
            }

            // Check if gateway supports the payment method
            if ($paymentMethod && !$gateway->supportsPaymentMethod($paymentMethod)) {
                throw new \Exception("Gateway '{$gatewayName}' does not support payment method '{$paymentMethod}'");
            }

            // Create payment record
            $payment = Payment::create([
                'user_id' => $user->id,
                'book_id' => $book->id,
                'payment_reference' => Payment::generateReference(),
                'gateway_name' => $gatewayName,
                'amount' => $book->price,
                'currency' => $book->currency ?? 'NGN',
                'payment_method' => $paymentMethod,
                'status' => Payment::STATUS_PENDING,
                'metadata' => array_merge($metadata, [
                    'book_title' => $book->title,
                    'book_author' => $book->author,
                    'user_email' => $user->email,
                    'user_name' => $user->name,
                ]),
                'expires_at' => now()->addHours(24), // Payment expires in 24 hours
            ]);

            // Log payment initiation
            $this->logPaymentActivity(
                PaymentLog::ACTION_PAYMENT_INITIATED,
                $user->id,
                $payment->id,
                $gatewayName,
                $payment->payment_reference,
                null,
                null,
                PaymentLog::STATUS_SUCCESS,
                null,
                $metadata
            );

            // Initialize payment with gateway
            $gatewayService = $this->getGatewayService($gatewayName);
            $gatewayResponse = $gatewayService->initializePayment($payment, $gateway);

            // Update payment with gateway reference
            $payment->update([
                'gateway_reference' => $gatewayResponse['reference'] ?? null,
                'gateway_response' => $gatewayResponse,
            ]);

            DB::commit();

            return [
                'success' => true,
                'payment' => $payment,
                'gateway_response' => $gatewayResponse,
                'redirect_url' => $gatewayResponse['redirect_url'] ?? null,
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment initialization failed: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'book_id' => $book->id,
                'gateway' => $gatewayName,
                'error' => $e->getMessage(),
            ]);

            // Log the error
            $this->logPaymentActivity(
                PaymentLog::ACTION_PAYMENT_INITIATED,
                $user->id,
                null,
                $gatewayName,
                null,
                null,
                null,
                PaymentLog::STATUS_ERROR,
                $e->getMessage(),
                $metadata
            );

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Verify a payment.
     */
    public function verifyPayment(string $paymentReference, string $gatewayName): array
    {
        try {
            $payment = Payment::where('payment_reference', $paymentReference)
                ->where('gateway_name', $gatewayName)
                ->first();

            if (!$payment) {
                throw new \Exception('Payment not found');
            }

            // Get the payment gateway
            $gateway = PaymentGateway::where('name', $gatewayName)
                ->where('is_active', true)
                ->first();

            if (!$gateway) {
                throw new \Exception("Payment gateway '{$gatewayName}' is not available");
            }

            // Verify payment with gateway
            $gatewayService = $this->getGatewayService($gatewayName);
            $verificationResult = $gatewayService->verifyPayment($payment, $gateway);

            // Update payment status based on verification result
            if ($verificationResult['success']) {
                $payment->markAsSuccessful();
                
                // Add book to user's library
                $this->addBookToLibrary($payment->user_id, $payment->book_id);

                // Log successful payment
                $this->logPaymentActivity(
                    PaymentLog::ACTION_PAYMENT_VERIFIED,
                    $payment->user_id,
                    $payment->id,
                    $gatewayName,
                    $payment->payment_reference,
                    null,
                    $verificationResult,
                    PaymentLog::STATUS_SUCCESS,
                    null,
                    $verificationResult
                );

                return [
                    'success' => true,
                    'payment' => $payment,
                    'message' => 'Payment verified successfully',
                ];
            } else {
                $payment->markAsFailed($verificationResult['error'] ?? 'Payment verification failed');

                // Log failed payment
                $this->logPaymentActivity(
                    PaymentLog::ACTION_PAYMENT_FAILED,
                    $payment->user_id,
                    $payment->id,
                    $gatewayName,
                    $payment->payment_reference,
                    null,
                    $verificationResult,
                    PaymentLog::STATUS_ERROR,
                    $verificationResult['error'] ?? 'Payment verification failed',
                    $verificationResult
                );

                return [
                    'success' => false,
                    'error' => $verificationResult['error'] ?? 'Payment verification failed',
                ];
            }

        } catch (\Exception $e) {
            Log::error('Payment verification failed: ' . $e->getMessage(), [
                'payment_reference' => $paymentReference,
                'gateway' => $gatewayName,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get available payment gateways.
     */
    public function getAvailableGateways(string $currency = 'NGN'): array
    {
        $gateways = PaymentGateway::active()
            ->byPriority()
            ->get()
            ->filter(function ($gateway) use ($currency) {
                return $gateway->supportsCurrency($currency);
            })
            ->map(function ($gateway) {
                return [
                    'name' => $gateway->name,
                    'display_name' => $gateway->display_name,
                    'description' => $gateway->description,
                    'supported_payment_methods' => $gateway->supported_payment_methods,
                    'is_test_mode' => $gateway->is_test_mode,
                ];
            })
            ->values()
            ->toArray();

        return $gateways;
    }

    /**
     * Get payment history for a user.
     */
    public function getUserPaymentHistory(int $userId, int $limit = 20): array
    {
        $payments = Payment::where('user_id', $userId)
            ->with(['book:id,title,author,cover_image'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        return $payments->toArray();
    }

    /**
     * Get payment statistics.
     */
    public function getPaymentStatistics(): array
    {
        $totalPayments = Payment::count();
        $successfulPayments = Payment::successful()->count();
        $failedPayments = Payment::failed()->count();
        $pendingPayments = Payment::pending()->count();
        $totalRevenue = Payment::successful()->sum('amount');

        $recentPayments = Payment::recent(7)->count();
        $recentRevenue = Payment::successful()->recent(7)->sum('amount');

        return [
            'total_payments' => $totalPayments,
            'successful_payments' => $successfulPayments,
            'failed_payments' => $failedPayments,
            'pending_payments' => $pendingPayments,
            'total_revenue' => $totalRevenue,
            'recent_payments' => $recentPayments,
            'recent_revenue' => $recentRevenue,
            'success_rate' => $totalPayments > 0 ? round(($successfulPayments / $totalPayments) * 100, 2) : 0,
        ];
    }

    /**
     * Get gateway service instance.
     */
    private function getGatewayService(string $gatewayName): GatewayServiceInterface
    {
        return match($gatewayName) {
            'flutterwave' => new FlutterwaveService(),
            'paystack' => new PayStackService(),
            default => throw new \Exception("Unsupported payment gateway: {$gatewayName}"),
        };
    }

    /**
     * Add book to user's library.
     */
    private function addBookToLibrary(int $userId, int $bookId): void
    {
        // Check if book is already in user's library
        $existingLibrary = \App\Models\UserLibrary::where('user_id', $userId)
            ->where('book_id', $bookId)
            ->first();

        if (!$existingLibrary) {
            \App\Models\UserLibrary::create([
                'user_id' => $userId,
                'book_id' => $bookId,
                'purchased_at' => now(),
            ]);
        }
    }

    /**
     * Log payment activity.
     */
    private function logPaymentActivity(
        string $action,
        int $userId,
        ?int $paymentId,
        string $gatewayName,
        ?string $paymentReference,
        ?array $requestData,
        ?array $responseData,
        string $status,
        ?string $errorMessage,
        array $metadata = []
    ): void {
        try {
            PaymentLog::create([
                'payment_id' => $paymentId,
                'user_id' => $userId,
                'action' => $action,
                'gateway_name' => $gatewayName,
                'payment_reference' => $paymentReference,
                'request_data' => $requestData,
                'response_data' => $responseData,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'status' => $status,
                'error_message' => $errorMessage,
                'metadata' => $metadata,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log payment activity: ' . $e->getMessage());
        }
    }
} 
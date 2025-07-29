<?php

namespace App\Services\Payment;

use App\Models\Payment;
use App\Models\PaymentGateway;
use App\Models\PaymentLog;
use App\Models\User;
use App\Models\Book;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

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

                // Create order from successful payment
                $this->orderService->createOrderFromPayment($payment);

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
    public function getAvailableGateways(): array
    {
        return PaymentGateway::where('is_active', true)
            ->select(['id', 'name', 'display_name', 'description', 'supported_currencies', 'metadata'])
            ->get()
            ->toArray();
    }

    /**
     * Get gateway service instance.
     */
    public function getGatewayService(string $gatewayName): ?GatewayServiceInterface
    {
        return match ($gatewayName) {
            'flutterwave' => app(FlutterwaveService::class),
            'paystack' => app(PayStackService::class),
            default => null,
        };
    }

    /**
     * Process refund through payment gateway.
     */
    public function processRefund(Payment $payment, float $refundAmount, string $refundReference, string $reason = null): array
    {
        try {
            $gatewayService = $this->getGatewayService($payment->gateway_name);
            
            if (!$gatewayService) {
                return [
                    'success' => false,
                    'message' => 'Payment gateway service not found',
                ];
            }

            // Check if gateway supports refunds
            if (!method_exists($gatewayService, 'processRefund')) {
                return [
                    'success' => false,
                    'message' => 'Payment gateway does not support refunds',
                ];
            }

            // Process refund through gateway
            $refundResult = $gatewayService->processRefund($payment, $refundAmount, $refundReference, $reason);

            // Log refund attempt
            PaymentLog::create([
                'payment_id' => $payment->id,
                'action' => 'refund_attempt',
                'status' => $refundResult['success'] ? 'success' : 'failed',
                'gateway_name' => $payment->gateway_name,
                'gateway_reference' => $refundReference,
                'amount' => $refundAmount,
                'currency' => $payment->currency,
                'metadata' => [
                    'reason' => $reason,
                    'gateway_response' => $refundResult,
                ],
            ]);

            return $refundResult;

        } catch (\Exception $e) {
            Log::error('Error processing refund: ' . $e->getMessage());
            
            // Log refund failure
            PaymentLog::create([
                'payment_id' => $payment->id,
                'action' => 'refund_attempt',
                'status' => 'failed',
                'gateway_name' => $payment->gateway_name,
                'gateway_reference' => $refundReference,
                'amount' => $refundAmount,
                'currency' => $payment->currency,
                'metadata' => [
                    'reason' => $reason,
                    'error' => $e->getMessage(),
                ],
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get refund history for a payment.
     */
    public function getRefundHistory(Payment $payment): array
    {
        return PaymentLog::where('payment_id', $payment->id)
            ->where('action', 'refund_attempt')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($log) {
                return [
                    'refund_reference' => $log->gateway_reference,
                    'amount' => $log->amount,
                    'currency' => $log->currency,
                    'status' => $log->status,
                    'reason' => $log->metadata['reason'] ?? null,
                    'processed_at' => $log->created_at->format('Y-m-d H:i:s'),
                    'gateway_response' => $log->metadata['gateway_response'] ?? null,
                ];
            })
            ->toArray();
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
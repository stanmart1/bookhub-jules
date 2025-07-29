<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Payment;
use App\Models\PaymentGateway;
use App\Models\PaymentWebhook;
use App\Services\Payment\PaymentService;
use App\Services\Payment\FlutterwaveService;
use App\Services\Payment\PayStackService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Initialize a new payment.
     */
    public function initialize(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'book_id' => 'required|integer|exists:books,id',
                'gateway_name' => 'required|string|in:flutterwave,paystack',
                'payment_method' => 'nullable|string|in:card,bank_transfer,mobile_money,digital_wallet',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $user = $request->user();
            $book = Book::findOrFail($request->book_id);

            // Check if user already owns the book
            $existingLibrary = \App\Models\UserLibrary::where('user_id', $user->id)
                ->where('book_id', $book->id)
                ->first();

            if ($existingLibrary) {
                return response()->json([
                    'success' => false,
                    'message' => 'You already own this book',
                    'errors' => ['book' => ['This book is already in your library.']],
                ], 400);
            }

            // Initialize payment
            $result = $this->paymentService->initializePayment(
                $user,
                $book,
                $request->gateway_name,
                $request->payment_method,
                $request->only(['metadata'])
            );

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'payment' => $result['payment'],
                        'redirect_url' => $result['redirect_url'],
                        'gateway_response' => $result['gateway_response'],
                    ],
                    'message' => 'Payment initialized successfully',
                ], 201);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment initialization failed',
                    'error' => $result['error'],
                ], 400);
            }

        } catch (\Exception $e) {
            Log::error('Payment initialization error: ' . $e->getMessage(), [
                'user_id' => $request->user()->id,
                'book_id' => $request->book_id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Payment initialization failed',
                'error' => 'An error occurred while initializing payment.',
            ], 500);
        }
    }

    /**
     * Verify a payment.
     */
    public function verify(Request $request, string $gatewayName): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'payment_reference' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $result = $this->paymentService->verifyPayment(
                $request->payment_reference,
                $gatewayName
            );

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'payment' => $result['payment'],
                        'message' => $result['message'],
                    ],
                    'message' => 'Payment verified successfully',
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment verification failed',
                    'error' => $result['error'],
                ], 400);
            }

        } catch (\Exception $e) {
            Log::error('Payment verification error: ' . $e->getMessage(), [
                'payment_reference' => $request->payment_reference,
                'gateway' => $gatewayName,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Payment verification failed',
                'error' => 'An error occurred while verifying payment.',
            ], 500);
        }
    }

    /**
     * Handle webhook from payment gateway.
     */
    public function webhook(Request $request, string $gatewayName): JsonResponse
    {
        try {
            // Log webhook receipt
            $webhook = PaymentWebhook::create([
                'gateway_name' => $gatewayName,
                'event_type' => $request->input('event', 'unknown'),
                'webhook_reference' => PaymentWebhook::generateReference(),
                'payload' => $request->all(),
                'status' => PaymentWebhook::STATUS_RECEIVED,
            ]);

            // Get gateway configuration
            $gateway = PaymentGateway::where('name', $gatewayName)
                ->where('is_active', true)
                ->first();

            if (!$gateway) {
                $webhook->markAsFailed("Gateway '{$gatewayName}' not found or inactive");
                return response()->json(['message' => 'Gateway not found'], 404);
            }

            // Process webhook based on gateway
            $gatewayService = match($gatewayName) {
                'flutterwave' => new FlutterwaveService(),
                'paystack' => new PayStackService(),
                default => throw new \Exception("Unsupported gateway: {$gatewayName}"),
            };

            $result = $gatewayService->processWebhook($request->all(), $gateway);

            if ($result['success']) {
                $webhook->markAsProcessed($result);
                return response()->json(['message' => 'Webhook processed successfully']);
            } else {
                $webhook->markAsFailed($result['error']);
                return response()->json(['message' => 'Webhook processing failed'], 400);
            }

        } catch (\Exception $e) {
            Log::error('Webhook processing error: ' . $e->getMessage(), [
                'gateway' => $gatewayName,
                'payload' => $request->all(),
                'error' => $e->getMessage(),
            ]);

            if (isset($webhook)) {
                $webhook->markAsFailed($e->getMessage());
            }

            return response()->json(['message' => 'Webhook processing failed'], 500);
        }
    }

    /**
     * Get available payment gateways.
     */
    public function gateways(Request $request): JsonResponse
    {
        try {
            $currency = $request->get('currency', 'NGN');
            $gateways = $this->paymentService->getAvailableGateways($currency);

            return response()->json([
                'success' => true,
                'data' => $gateways,
                'message' => 'Payment gateways retrieved successfully',
            ]);

        } catch (\Exception $e) {
            Log::error('Get payment gateways error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve payment gateways',
                'error' => 'An error occurred while retrieving payment gateways.',
            ], 500);
        }
    }

    /**
     * Get payment history for authenticated user.
     */
    public function history(Request $request): JsonResponse
    {
        try {
            $limit = $request->get('limit', 20);
            $payments = $this->paymentService->getUserPaymentHistory($request->user()->id, $limit);

            return response()->json([
                'success' => true,
                'data' => $payments,
                'message' => 'Payment history retrieved successfully',
            ]);

        } catch (\Exception $e) {
            Log::error('Get payment history error: ' . $e->getMessage(), [
                'user_id' => $request->user()->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve payment history',
                'error' => 'An error occurred while retrieving payment history.',
            ], 500);
        }
    }

    /**
     * Get payment details.
     */
    public function show(Request $request, int $paymentId): JsonResponse
    {
        try {
            $payment = Payment::where('id', $paymentId)
                ->where('user_id', $request->user()->id)
                ->with(['book:id,title,author,cover_image'])
                ->first();

            if (!$payment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment not found',
                    'errors' => ['payment' => ['The specified payment does not exist.']],
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $payment,
                'message' => 'Payment details retrieved successfully',
            ]);

        } catch (\Exception $e) {
            Log::error('Get payment details error: ' . $e->getMessage(), [
                'payment_id' => $paymentId,
                'user_id' => $request->user()->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve payment details',
                'error' => 'An error occurred while retrieving payment details.',
            ], 500);
        }
    }

    /**
     * Get payment statistics (admin only).
     */
    public function statistics(Request $request): JsonResponse
    {
        try {
            // Check if user is admin
            if (!$request->user()->hasRole('admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                    'errors' => ['permission' => ['You do not have permission to access this resource.']],
                ], 403);
            }

            $statistics = $this->paymentService->getPaymentStatistics();

            return response()->json([
                'success' => true,
                'data' => $statistics,
                'message' => 'Payment statistics retrieved successfully',
            ]);

        } catch (\Exception $e) {
            Log::error('Get payment statistics error: ' . $e->getMessage(), [
                'user_id' => $request->user()->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve payment statistics',
                'error' => 'An error occurred while retrieving payment statistics.',
            ], 500);
        }
    }

    /**
     * Retry a failed payment.
     */
    public function retry(Request $request, int $paymentId): JsonResponse
    {
        try {
            $payment = Payment::where('id', $paymentId)
                ->where('user_id', $request->user()->id)
                ->first();

            if (!$payment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment not found',
                    'errors' => ['payment' => ['The specified payment does not exist.']],
                ], 404);
            }

            if (!$payment->canRetry()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment cannot be retried',
                    'errors' => ['payment' => ['This payment cannot be retried.']],
                ], 400);
            }

            // Get the book
            $book = Book::findOrFail($payment->book_id);

            // Initialize new payment
            $result = $this->paymentService->initializePayment(
                $request->user(),
                $book,
                $payment->gateway_name,
                $payment->payment_method,
                $payment->metadata
            );

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'payment' => $result['payment'],
                        'redirect_url' => $result['redirect_url'],
                    ],
                    'message' => 'Payment retry initialized successfully',
                ], 201);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment retry failed',
                    'error' => $result['error'],
                ], 400);
            }

        } catch (\Exception $e) {
            Log::error('Payment retry error: ' . $e->getMessage(), [
                'payment_id' => $paymentId,
                'user_id' => $request->user()->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Payment retry failed',
                'error' => 'An error occurred while retrying payment.',
            ], 500);
        }
    }
}

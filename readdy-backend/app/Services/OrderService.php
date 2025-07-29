<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\User;
use App\Models\Book;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Services\ReceiptService;
use App\Services\ActivityService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Services\Payment\PaymentService;
use App\Mail\OrderConfirmation;
use App\Mail\OrderCancellation;
use App\Mail\RefundProcessed;
use App\Mail\DeliveryNotification;
use App\Mail\DownloadReminder;
use Illuminate\Support\Facades\Mail;

class OrderService
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        private ReceiptService $receiptService,
        private PaymentService $paymentService
    ) {}

    /**
     * Create an order from a successful payment.
     */
    public function createOrderFromPayment(Payment $payment): Order
    {
        try {
            DB::beginTransaction();

            $order = Order::create([
                'user_id' => $payment->user_id,
                'payment_id' => $payment->id,
                'order_number' => $this->generateOrderNumber(),
                'total_amount' => $payment->amount,
                'currency' => $payment->currency,
                'status' => 'completed',
                'metadata' => [
                    'payment_reference' => $payment->payment_reference,
                    'gateway_name' => $payment->gateway_name,
                    'payment_method' => $payment->payment_method,
                ],
                'completed_at' => now(),
            ]);

            // Create order item
            OrderItem::create([
                'order_id' => $order->id,
                'book_id' => $payment->book_id,
                'price' => $payment->amount,
                'quantity' => 1,
                'title' => $payment->book->title,
                'author' => $payment->book->author,
                'cover_image' => $payment->book->cover_image,
                'metadata' => [
                    'isbn' => $payment->book->isbn,
                    'publisher' => $payment->book->publisher,
                ],
            ]);

            // Generate receipt for the order
            $this->receiptService->generateReceipt($order);

            // Send order confirmation notification
            $this->sendOrderConfirmationNotification($order);
            
            // Send order confirmation email
            $this->sendOrderConfirmationEmail($order);
            
            // Process digital delivery
            $this->processDigitalDelivery($order);

            DB::commit();
            return $order;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating order from payment: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate a unique order number.
     */
    private function generateOrderNumber(): string
    {
        do {
            $orderNumber = 'ORD-' . strtoupper(uniqid()) . '-' . time();
        } while (Order::where('order_number', $orderNumber)->exists());

        return $orderNumber;
    }

    /**
     * Get user's order history.
     */
    public function getUserOrders(User $user, Request $request): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = Order::with(['items.book', 'payment'])
            ->where('user_id', $user->id);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $query->orderBy('created_at', 'desc');

        return $query->paginate($request->get('per_page', 15));
    }

    /**
     * Get order details.
     */
    public function getOrderDetails(int $orderId, User $user = null): ?Order
    {
        $query = Order::with(['items.book', 'payment', 'user']);

        if ($user) {
            $query->where('user_id', $user->id);
        }

        return $query->find($orderId);
    }

    /**
     * Cancel an order.
     */
    public function cancelOrder(Order $order, string $reason = null): bool
    {
        try {
            DB::beginTransaction();

            // Check if order can be cancelled
            if (!$order->canBeCancelled()) {
                throw new \Exception('Order cannot be cancelled in its current status');
            }

            $order->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'metadata' => array_merge($order->metadata ?? [], [
                    'cancellation_reason' => $reason,
                    'cancelled_by' => auth()->id(),
                ]),
            ]);

            // If there's a payment, initiate refund
            if ($order->payment && $order->payment->isSuccessful()) {
                $refundResult = $this->initiateRefund($order, $reason);
                
                if ($refundResult['success']) {
                    // Update order with refund information
                    $order->update([
                        'status' => 'refunded',
                        'refunded_at' => now(),
                        'metadata' => array_merge($order->metadata ?? [], [
                            'refund_reference' => $refundResult['refund_reference'],
                            'refund_amount' => $refundResult['refund_amount'],
                            'refund_reason' => $reason,
                            'refunded_by' => auth()->id(),
                        ]),
                    ]);

                    // Send refund notification
                    $this->sendRefundNotification($order, $refundResult['refund_reference'], $refundResult['refund_amount']);
                } else {
                    // Log refund failure but still cancel the order
                    Log::error('Refund failed for order: ' . $order->id . ' - ' . $refundResult['message']);
                    
                    // Update order metadata with refund failure
                    $order->update([
                        'metadata' => array_merge($order->metadata ?? [], [
                            'refund_failed' => true,
                            'refund_error' => $refundResult['message'],
                        ]),
                    ]);
                }
            }

            // Send order cancellation notification
            $this->sendOrderCancellationNotification($order, $reason);

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error cancelling order: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Process refund for an order.
     */
    public function processRefund(Order $order, float $refundAmount, string $refundReference, string $reason = null): array
    {
        try {
            DB::beginTransaction();

            // Validate refund amount
            if ($refundAmount > $order->total_amount) {
                throw new \Exception('Refund amount cannot exceed order total');
            }

            if ($refundAmount <= 0) {
                throw new \Exception('Refund amount must be greater than zero');
            }

            // Check if order can be refunded
            if (!$order->canBeRefunded()) {
                throw new \Exception('Order cannot be refunded in its current status');
            }

            // If there's a payment, process refund through payment gateway
            if ($order->payment && $order->payment->isSuccessful()) {
                $refundResult = $this->processPaymentRefund($order, $refundAmount, $refundReference, $reason);
                
                if (!$refundResult['success']) {
                    throw new \Exception('Payment refund failed: ' . $refundResult['message']);
                }
            }

            // Update order status
            $order->update([
                'status' => 'refunded',
                'refunded_at' => now(),
                'metadata' => array_merge($order->metadata ?? [], [
                    'refund_amount' => $refundAmount,
                    'refund_reference' => $refundReference,
                    'refund_reason' => $reason,
                    'refunded_by' => auth()->id(),
                    'refund_processed_at' => now(),
                ]),
            ]);

            // Send refund notification
            $this->sendRefundNotification($order, $refundReference, $refundAmount);

            DB::commit();

            return [
                'success' => true,
                'refund_reference' => $refundReference,
                'refund_amount' => $refundAmount,
                'message' => 'Refund processed successfully',
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error processing refund: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Initiate refund through payment gateway.
     */
    private function initiateRefund(Order $order, string $reason = null): array
    {
        try {
            if (!$order->payment) {
                return [
                    'success' => false,
                    'message' => 'No payment associated with this order',
                ];
            }

            $refundAmount = $order->total_amount;
            $refundReference = 'REF_' . time() . '_' . $order->id;

            return $this->processPaymentRefund($order, $refundAmount, $refundReference, $reason);

        } catch (\Exception $e) {
            Log::error('Error initiating refund: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Process refund through payment gateway.
     */
    private function processPaymentRefund(Order $order, float $refundAmount, string $refundReference, string $reason = null): array
    {
        try {
            $payment = $order->payment;
            
            if (!$payment) {
                return [
                    'success' => false,
                    'message' => 'No payment associated with this order',
                ];
            }

            // Get the appropriate gateway service
            $gatewayService = $this->paymentService->getGatewayService($payment->gateway_name);
            
            if (!$gatewayService) {
                return [
                    'success' => false,
                    'message' => 'Payment gateway service not found',
                ];
            }

            // Process refund through gateway
            $refundResult = $gatewayService->processRefund($payment, $refundAmount, $refundReference, $reason);

            if ($refundResult['success']) {
                // Update payment status
                $payment->update([
                    'status' => 'refunded',
                    'metadata' => array_merge($payment->metadata ?? [], [
                        'refund_amount' => $refundAmount,
                        'refund_reference' => $refundReference,
                        'refund_reason' => $reason,
                        'refunded_at' => now(),
                    ]),
                ]);

                // Log refund activity
                ActivityService::log(
                    'payment_refunded',
                    $order->user_id,
                    'App\Models\Payment',
                    $payment->id,
                    [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'refund_amount' => $refundAmount,
                        'refund_reference' => $refundReference,
                        'gateway_name' => $payment->gateway_name,
                    ]
                );
            }

            return $refundResult;

        } catch (\Exception $e) {
            Log::error('Error processing payment refund: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get refund history for an order.
     */
    public function getRefundHistory(Order $order): array
    {
        try {
            $refunds = [];

            if ($order->payment) {
                // Get refund information from payment metadata
                $paymentMetadata = $order->payment->metadata ?? [];
                
                if (isset($paymentMetadata['refund_amount'])) {
                    $refunds[] = [
                        'refund_reference' => $paymentMetadata['refund_reference'] ?? 'N/A',
                        'refund_amount' => $paymentMetadata['refund_amount'],
                        'refund_reason' => $paymentMetadata['refund_reason'] ?? 'N/A',
                        'refunded_at' => $paymentMetadata['refunded_at'] ?? $order->refunded_at?->format('Y-m-d H:i:s'),
                        'gateway_name' => $order->payment->gateway_name,
                        'status' => 'completed',
                    ];
                }
            }

            return $refunds;

        } catch (\Exception $e) {
            Log::error('Error getting refund history: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Check if order can be partially refunded.
     */
    public function canPartiallyRefund(Order $order): bool
    {
        // Check if order is completed and has successful payment
        if ($order->status !== 'completed' || !$order->payment || !$order->payment->isSuccessful()) {
            return false;
        }

        // Check if already refunded
        if ($order->status === 'refunded') {
            return false;
        }

        // Check if payment gateway supports partial refunds
        $gatewayService = $this->paymentService->getGatewayService($order->payment->gateway_name);
        
        return $gatewayService && method_exists($gatewayService, 'supportsPartialRefund') 
            ? $gatewayService->supportsPartialRefund() 
            : false;
    }

    /**
     * Get maximum refundable amount for an order.
     */
    public function getMaxRefundableAmount(Order $order): float
    {
        if (!$order->canBeRefunded()) {
            return 0;
        }

        // If already partially refunded, calculate remaining amount
        $alreadyRefunded = 0;
        if ($order->payment && isset($order->payment->metadata['refund_amount'])) {
            $alreadyRefunded = $order->payment->metadata['refund_amount'];
        }

        return max(0, $order->total_amount - $alreadyRefunded);
    }

    /**
     * Get order statistics.
     */
    public function getOrderStatistics(Request $request = null): array
    {
        $dateRange = $request?->input('date_range', '30');
        $startDate = $request?->input('start_date');
        $endDate = $request?->input('end_date');

        // Calculate date range
        if ($startDate && $endDate) {
            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->endOfDay();
        } else {
            $end = Carbon::now()->endOfDay();
            $start = Carbon::now()->subDays($dateRange)->startOfDay();
        }

        // Basic statistics
        $totalOrders = Order::whereBetween('created_at', [$start, $end])->count();
        $totalRevenue = Order::whereBetween('created_at', [$start, $end])
            ->where('status', 'completed')
            ->sum('total_amount');
        
        $pendingOrders = Order::whereBetween('created_at', [$start, $end])
            ->where('status', 'pending')
            ->count();
        
        $completedOrders = Order::whereBetween('created_at', [$start, $end])
            ->where('status', 'completed')
            ->count();
        
        $cancelledOrders = Order::whereBetween('created_at', [$start, $end])
            ->where('status', 'cancelled')
            ->count();
        
        $refundedOrders = Order::whereBetween('created_at', [$start, $end])
            ->where('status', 'refunded')
            ->count();

        // Average order value
        $avgOrderValue = Order::whereBetween('created_at', [$start, $end])
            ->where('status', 'completed')
            ->avg('total_amount') ?? 0;

        // Conversion rate (completed vs total)
        $conversionRate = $totalOrders > 0 ? ($completedOrders / $totalOrders) * 100 : 0;

        return [
            'summary' => [
                'total_orders' => $totalOrders,
                'total_revenue' => $totalRevenue,
                'pending_orders' => $pendingOrders,
                'completed_orders' => $completedOrders,
                'cancelled_orders' => $cancelledOrders,
                'refunded_orders' => $refundedOrders,
                'average_order_value' => round($avgOrderValue, 2),
                'conversion_rate' => round($conversionRate, 2),
            ],
            'date_range' => [
                'start_date' => $start->format('Y-m-d'),
                'end_date' => $end->format('Y-m-d'),
            ],
        ];
    }

    /**
     * Get revenue trends by period.
     */
    public function getRevenueTrends(string $startDate, string $endDate, string $groupBy = 'day'): Collection
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        return Order::whereBetween('created_at', [$start, $end])
            ->where('status', 'completed')
            ->selectRaw($this->getGroupByClause($groupBy) . ' as period, SUM(total_amount) as revenue, COUNT(*) as orders')
            ->groupBy('period')
            ->orderBy('period')
            ->get();
    }

    /**
     * Get customer analytics.
     */
    public function getCustomerAnalytics(string $startDate, string $endDate, int $limit = 20): Collection
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        return Order::whereBetween('created_at', [$start, $end])
            ->selectRaw('user_id, COUNT(*) as order_count, SUM(total_amount) as total_spent, AVG(total_amount) as avg_order_value')
            ->groupBy('user_id')
            ->orderByDesc('total_spent')
            ->limit($limit)
            ->with('user:id,name,email')
            ->get();
    }

    /**
     * Get book performance analytics.
     */
    public function getBookPerformance(string $startDate, string $endDate, int $limit = 20): Collection
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        return OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$start, $end])
            ->where('orders.status', 'completed')
            ->selectRaw('order_items.title, order_items.author, COUNT(*) as sales_count, SUM(order_items.price) as revenue, AVG(order_items.price) as avg_price')
            ->groupBy('order_items.title', 'order_items.author')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->get();
    }

    /**
     * Get payment method analysis.
     */
    public function getPaymentAnalysis(string $startDate, string $endDate): Collection
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        return Order::join('payments', 'orders.payment_id', '=', 'payments.id')
            ->whereBetween('orders.created_at', [$start, $end])
            ->selectRaw('payments.gateway_name, payments.payment_method, COUNT(*) as usage_count, SUM(orders.total_amount) as total_amount, AVG(orders.total_amount) as avg_amount')
            ->groupBy('payments.gateway_name', 'payments.payment_method')
            ->orderByDesc('total_amount')
            ->get();
    }

    /**
     * Get order status trends.
     */
    public function getStatusTrends(string $startDate, string $endDate, string $groupBy = 'day'): Collection
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        return Order::whereBetween('created_at', [$start, $end])
            ->selectRaw($this->getGroupByClause($groupBy) . ' as period, status, COUNT(*) as count')
            ->groupBy('period', 'status')
            ->orderBy('period')
            ->get()
            ->groupBy('period');
    }

    /**
     * Get group by clause for analytics.
     */
    private function getGroupByClause(string $groupBy): string
    {
        return match ($groupBy) {
            'week' => 'YEARWEEK(created_at)',
            'month' => 'DATE_FORMAT(created_at, "%Y-%m")',
            default => 'DATE(created_at)',
        };
    }

    /**
     * Update order status.
     */
    public function updateOrderStatus(Order $order, string $status, string $reason = null): bool
    {
        try {
            DB::beginTransaction();

            $oldStatus = $order->status;

            switch ($status) {
                case Order::STATUS_PROCESSING:
                    $order->markAsProcessing();
                    break;
                case Order::STATUS_COMPLETED:
                    $order->markAsCompleted();
                    break;
                case Order::STATUS_CANCELLED:
                    if (!$order->canBeCancelled()) {
                        throw new \Exception('Order cannot be cancelled in its current status');
                    }
                    $order->markAsCancelled($reason);
                    break;
                case Order::STATUS_REFUNDED:
                    if (!$order->canBeRefunded()) {
                        throw new \Exception('Order cannot be refunded in its current status');
                    }
                    $order->markAsRefunded($reason);
                    break;
                default:
                    throw new \Exception('Invalid order status');
            }

            // Log status change
            $this->logOrderStatusChange($order, $status, $reason);

            // Send status change notification
            $this->sendOrderStatusChangeNotification($order, $oldStatus, $status, $reason);

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating order status: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get order status history.
     */
    public function getOrderStatusHistory(Order $order): array
    {
        // This would typically come from an order_status_logs table
        // For now, we'll return basic status information
        $history = [
            [
                'status' => $order->status,
                'timestamp' => $order->updated_at,
                'updated_by' => $order->metadata['updated_by'] ?? null,
                'reason' => $order->metadata['update_reason'] ?? null,
            ]
        ];

        if ($order->completed_at) {
            $history[] = [
                'status' => 'completed',
                'timestamp' => $order->completed_at,
                'updated_by' => null,
                'reason' => 'Payment successful',
            ];
        }

        if ($order->cancelled_at) {
            $history[] = [
                'status' => 'cancelled',
                'timestamp' => $order->cancelled_at,
                'updated_by' => $order->metadata['cancelled_by'] ?? null,
                'reason' => $order->metadata['cancellation_reason'] ?? null,
            ];
        }

        if ($order->refunded_at) {
            $history[] = [
                'status' => 'refunded',
                'timestamp' => $order->refunded_at,
                'updated_by' => $order->metadata['refunded_by'] ?? null,
                'reason' => $order->metadata['refund_reason'] ?? null,
            ];
        }

        return $history;
    }

    /**
     * Log order status change.
     */
    private function logOrderStatusChange(Order $order, string $status, string $reason = null): void
    {
        // This would typically log to an order_status_logs table
        Log::info('Order status changed', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'old_status' => $order->getOriginal('status'),
            'new_status' => $status,
            'reason' => $reason,
            'updated_by' => auth()->id(),
            'timestamp' => now(),
        ]);
    }

    /**
     * Get orders by status with pagination.
     */
    public function getOrdersByStatus(string $status, Request $request = null): LengthAwarePaginator
    {
        $query = Order::with(['user', 'payment', 'items.book'])
            ->where('status', $status)
            ->orderBy('created_at', 'desc');

        return $query->paginate($request?->get('per_page', 15) ?? 15);
    }

    /**
     * Send order confirmation notification.
     */
    private function sendOrderConfirmationNotification(Order $order): void
    {
        try {
            $order->load(['user', 'items.book']);

            ActivityService::notify(
                $order->user_id,
                'order_confirmation',
                'Order Confirmed',
                "Your order #{$order->order_number} has been confirmed successfully. You can now access your purchased books in your library.",
                [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'total_amount' => $order->total_amount,
                    'currency' => $order->currency,
                    'items_count' => $order->items->count(),
                    'books' => $order->items->map(function ($item) {
                        return [
                            'title' => $item->title,
                            'author' => $item->author,
                        ];
                    })->toArray(),
                ]
            );

            // Log activity
            ActivityService::log(
                'order_created',
                $order->user_id,
                'App\Models\Order',
                $order->id,
                [
                    'order_number' => $order->order_number,
                    'total_amount' => $order->total_amount,
                    'items_count' => $order->items->count(),
                ]
            );

        } catch (\Exception $e) {
            Log::error('Error sending order confirmation notification: ' . $e->getMessage());
        }
    }

    /**
     * Send order cancellation notification.
     */
    private function sendOrderCancellationNotification(Order $order, string $reason = null): void
    {
        try {
            $order->load(['user', 'items.book']);

            ActivityService::notify(
                $order->user_id,
                'order_cancelled',
                'Order Cancelled',
                "Your order #{$order->order_number} has been cancelled." . ($reason ? " Reason: {$reason}" : ''),
                [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'total_amount' => $order->total_amount,
                    'currency' => $order->currency,
                    'cancellation_reason' => $reason,
                    'refund_status' => 'pending', // Will be updated when refund is processed
                ]
            );

            // Log activity
            ActivityService::log(
                'order_cancelled',
                $order->user_id,
                'App\Models\Order',
                $order->id,
                [
                    'order_number' => $order->order_number,
                    'cancellation_reason' => $reason,
                    'cancelled_by' => auth()->id(),
                ]
            );

        } catch (\Exception $e) {
            Log::error('Error sending order cancellation notification: ' . $e->getMessage());
        }
    }

    /**
     * Send order status change notification.
     */
    private function sendOrderStatusChangeNotification(Order $order, string $oldStatus, string $newStatus, string $reason = null): void
    {
        try {
            $order->load(['user']);

            $statusMessages = [
                Order::STATUS_PROCESSING => 'Your order is being processed',
                Order::STATUS_COMPLETED => 'Your order has been completed successfully',
                Order::STATUS_REFUNDED => 'Your order has been refunded',
            ];

            $message = $statusMessages[$newStatus] ?? "Your order status has changed from {$oldStatus} to {$newStatus}";
            if ($reason) {
                $message .= ". Reason: {$reason}";
            }

            ActivityService::notify(
                $order->user_id,
                'order_status_change',
                'Order Status Updated',
                $message,
                [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'reason' => $reason,
                ]
            );

            // Log activity
            ActivityService::log(
                'order_status_changed',
                $order->user_id,
                'App\Models\Order',
                $order->id,
                [
                    'order_number' => $order->order_number,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'reason' => $reason,
                    'updated_by' => auth()->id(),
                ]
            );

        } catch (\Exception $e) {
            Log::error('Error sending order status change notification: ' . $e->getMessage());
        }
    }

    /**
     * Send refund notification.
     */
    public function sendRefundNotification(Order $order, string $refundReference, float $refundAmount): void
    {
        try {
            $order->load(['user']);

            ActivityService::notify(
                $order->user_id,
                'order_refunded',
                'Refund Processed',
                "Your refund for order #{$order->order_number} has been processed. Amount: {$order->currency} " . number_format($refundAmount, 2),
                [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'refund_amount' => $refundAmount,
                    'refund_reference' => $refundReference,
                    'currency' => $order->currency,
                ]
            );

            // Log activity
            ActivityService::log(
                'order_refunded',
                $order->user_id,
                'App\Models\Order',
                $order->id,
                [
                    'order_number' => $order->order_number,
                    'refund_amount' => $refundAmount,
                    'refund_reference' => $refundReference,
                ]
            );

        } catch (\Exception $e) {
            Log::error('Error sending refund notification: ' . $e->getMessage());
        }
    }

    /**
     * Send order confirmation email.
     */
    public function sendOrderConfirmationEmail(Order $order): void
    {
        try {
            $order->load(['user', 'items.book']);
            
            Mail::to($order->user->email)->send(new OrderConfirmation($order));
            
            Log::info('Order confirmation email sent', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'user_email' => $order->user->email,
            ]);
        } catch (\Exception $e) {
            Log::error('Error sending order confirmation email: ' . $e->getMessage());
        }
    }

    /**
     * Send order cancellation email.
     */
    public function sendOrderCancellationEmail(Order $order, string $reason = null): void
    {
        try {
            $order->load(['user']);
            
            Mail::to($order->user->email)->send(new OrderCancellation($order, $reason));
            
            Log::info('Order cancellation email sent', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'user_email' => $order->user->email,
                'reason' => $reason,
            ]);
        } catch (\Exception $e) {
            Log::error('Error sending order cancellation email: ' . $e->getMessage());
        }
    }

    /**
     * Send refund processed email.
     */
    public function sendRefundProcessedEmail(Order $order, array $refundInfo = []): void
    {
        try {
            $order->load(['user']);
            
            Mail::to($order->user->email)->send(new RefundProcessed($order, $refundInfo));
            
            Log::info('Refund processed email sent', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'user_email' => $order->user->email,
                'refund_amount' => $refundInfo['refund_amount'] ?? null,
            ]);
        } catch (\Exception $e) {
            Log::error('Error sending refund processed email: ' . $e->getMessage());
        }
    }

    /**
     * Send delivery notification email.
     */
    public function sendDeliveryNotificationEmail(Order $order, array $deliveredItems = []): void
    {
        try {
            $order->load(['user', 'items.book']);
            
            Mail::to($order->user->email)->send(new DeliveryNotification($order, $deliveredItems));
            
            Log::info('Delivery notification email sent', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'user_email' => $order->user->email,
                'delivered_items_count' => count($deliveredItems),
            ]);
        } catch (\Exception $e) {
            Log::error('Error sending delivery notification email: ' . $e->getMessage());
        }
    }

    /**
     * Send download reminder email.
     */
    public function sendDownloadReminderEmail(Order $order, array $undownloadedItems = []): void
    {
        try {
            $order->load(['user', 'items.book']);
            
            Mail::to($order->user->email)->send(new DownloadReminder($order, $undownloadedItems));
            
            Log::info('Download reminder email sent', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'user_email' => $order->user->email,
                'undownloaded_items_count' => count($undownloadedItems),
            ]);
        } catch (\Exception $e) {
            Log::error('Error sending download reminder email: ' . $e->getMessage());
        }
    }

    /**
     * Process digital delivery for an order.
     */
    public function processDigitalDelivery(Order $order): void
    {
        try {
            $order->load(['items.book.bookFile']);
            
            $deliveredItems = [];
            
            foreach ($order->items as $item) {
                if ($item->book && $item->book->bookFile) {
                    $deliveredItems[] = [
                        'book_id' => $item->book_id,
                        'book' => $item->book,
                        'order_item' => $item,
                    ];
                }
            }
            
            if (!empty($deliveredItems)) {
                // Send delivery notification email
                $this->sendDeliveryNotificationEmail($order, $deliveredItems);
                
                // Log delivery activity
                ActivityService::log(
                    'digital_delivery_processed',
                    $order->user_id,
                    'App\Models\Order',
                    $order->id,
                    [
                        'order_number' => $order->order_number,
                        'delivered_items_count' => count($deliveredItems),
                    ]
                );
            }
        } catch (\Exception $e) {
            Log::error('Error processing digital delivery: ' . $e->getMessage());
        }
    }

    /**
     * Schedule download reminders for orders.
     */
    public function scheduleDownloadReminders(): void
    {
        try {
            // Find orders that are 3 days old and haven't been fully downloaded
            $orders = Order::with(['user', 'items.book.bookFile'])
                ->where('status', 'completed')
                ->where('created_at', '<=', now()->subDays(3))
                ->where('created_at', '>', now()->subDays(7)) // Only orders from last 7 days
                ->get();
            
            foreach ($orders as $order) {
                $undownloadedItems = [];
                
                foreach ($order->items as $item) {
                    if ($item->book && $item->book->bookFile) {
                        // Check if book has been downloaded (this would need a download tracking system)
                        $downloadCount = 0; // This would come from download tracking
                        
                        if ($downloadCount === 0) {
                            $undownloadedItems[] = [
                                'book_id' => $item->book_id,
                                'book' => $item->book,
                                'order_item' => $item,
                            ];
                        }
                    }
                }
                
                if (!empty($undownloadedItems)) {
                    $this->sendDownloadReminderEmail($order, $undownloadedItems);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error scheduling download reminders: ' . $e->getMessage());
        }
    }
}

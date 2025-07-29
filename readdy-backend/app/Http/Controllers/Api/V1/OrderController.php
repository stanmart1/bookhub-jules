<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use App\Services\ReceiptService;
use Illuminate\Support\Facades\Log;
use App\Models\Order;

class OrderController extends Controller
{
    protected $orderService;
    protected $receiptService;

    public function __construct(OrderService $orderService, ReceiptService $receiptService)
    {
        $this->orderService = $orderService;
        $this->receiptService = $receiptService;
    }

    /**
     * Get user's order history.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $orders = $this->orderService->getUserOrders($request->user(), $request);

            return response()->json([
                'success' => true,
                'data' => $orders,
                'message' => 'Order history retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving order history',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get order details.
     */
    public function show(Request $request, int $orderId): JsonResponse
    {
        try {
            $order = $this->orderService->getOrderDetails($orderId, $request->user());

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $order,
                'message' => 'Order details retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving order details',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel an order.
     */
    public function cancel(Request $request, Order $order): JsonResponse
    {
        try {
            $request->validate([
                'reason' => 'nullable|string|max:500',
            ]);

            $user = $request->user();
            
            // Check if user owns this order
            if ($order->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to this order'
                ], 403);
            }

            // Check if order can be cancelled
            if (!$order->canBeCancelled()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order cannot be cancelled in its current status'
                ], 400);
            }

            $orderService = app(OrderService::class);
            $cancelled = $orderService->cancelOrder($order, $request->reason);

            if ($cancelled) {
                // Send cancellation email
                $orderService->sendOrderCancellationEmail($order, $request->reason);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Order cancelled successfully',
                    'data' => [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'status' => $order->status,
                        'cancelled_at' => $order->cancelled_at,
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to cancel order'
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Error cancelling order: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error cancelling order',
                'errors' => ['order' => ['An error occurred while cancelling the order.']]
            ], 500);
        }
    }

    /**
     * Get order status history.
     */
    public function statusHistory(Request $request, int $orderId): JsonResponse
    {
        try {
            $order = $this->orderService->getOrderDetails($orderId, $request->user());

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            $statusHistory = $this->orderService->getOrderStatusHistory($order);

            return response()->json([
                'success' => true,
                'data' => [
                    'order' => $order,
                    'status_history' => $statusHistory,
                ],
                'message' => 'Order status history retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving order status history',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get receipt for an order.
     */
    public function receipt(Request $request, int $orderId): JsonResponse
    {
        try {
            $order = $this->orderService->getOrderDetails($orderId, $request->user());

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            $receipt = $this->receiptService->getReceipt($order);

            return response()->json([
                'success' => true,
                'data' => [
                    'order' => $order,
                    'receipt' => $receipt,
                    'receipt_url' => $receipt->receipt_url,
                ],
                'message' => 'Receipt retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving receipt',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download receipt file.
     */
    public function downloadReceipt(Request $request, int $orderId): JsonResponse
    {
        try {
            $order = $this->orderService->getOrderDetails($orderId, $request->user());

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            $receipt = $this->receiptService->getReceipt($order);
            $filePath = $this->receiptService->downloadReceipt($receipt);

            if (!$filePath) {
                return response()->json([
                    'success' => false,
                    'message' => 'Receipt file not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'download_url' => $receipt->receipt_url,
                    'file_path' => $filePath,
                ],
                'message' => 'Receipt download ready'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error downloading receipt',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get order notifications.
     */
    public function notifications(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            $notifications = \App\Models\Notification::where('user_id', $user->id)
                ->whereIn('type', ['order_confirmation', 'order_cancelled', 'order_status_change', 'order_refunded'])
                ->orderBy('created_at', 'desc')
                ->paginate($request->get('per_page', 15));

            return response()->json([
                'success' => true,
                'data' => $notifications,
                'message' => 'Order notifications retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving order notifications',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark order notification as read.
     */
    public function markNotificationAsRead(Request $request, int $notificationId): JsonResponse
    {
        try {
            $user = $request->user();
            
            $notification = \App\Models\Notification::where('id', $notificationId)
                ->where('user_id', $user->id)
                ->whereIn('type', ['order_confirmation', 'order_cancelled', 'order_status_change', 'order_refunded'])
                ->first();

            if (!$notification) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notification not found'
                ], 404);
            }

            $notification->update(['read_at' => now()]);

            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error marking notification as read',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get unread order notifications count.
     */
    public function unreadNotificationsCount(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            $count = \App\Models\Notification::where('user_id', $user->id)
                ->whereIn('type', ['order_confirmation', 'order_cancelled', 'order_status_change', 'order_refunded'])
                ->whereNull('read_at')
                ->count();

            return response()->json([
                'success' => true,
                'data' => ['count' => $count],
                'message' => 'Unread notifications count retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving unread notifications count',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

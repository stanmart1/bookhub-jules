<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\OrderService;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use App\Models\OrderItem;

class OrderController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Display a listing of orders.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Order::with(['user', 'items.book', 'payment']);

            // Apply filters
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            if ($request->has('user_id')) {
                $query->where('user_id', $request->user_id);
            }

            if ($request->has('date_from')) {
                $query->where('created_at', '>=', $request->date_from);
            }

            if ($request->has('date_to')) {
                $query->where('created_at', '<=', $request->date_to);
            }

            // Apply sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->get('per_page', 15);
            $orders = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $orders,
                'message' => 'Orders retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving orders',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get order statistics.
     */
    public function statistics(Request $request): JsonResponse
    {
        try {
            $dateRange = $request->input('date_range', '30'); // days
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');

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

            // Daily order trends
            $dailyOrders = Order::whereBetween('created_at', [$start, $end])
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(total_amount) as revenue')
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            // Status distribution
            $statusDistribution = Order::whereBetween('created_at', [$start, $end])
                ->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->get()
                ->pluck('count', 'status')
                ->toArray();

            // Top selling books
            $topBooks = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
                ->whereBetween('orders.created_at', [$start, $end])
                ->where('orders.status', 'completed')
                ->selectRaw('order_items.title, order_items.author, COUNT(*) as sales_count, SUM(order_items.price) as total_revenue')
                ->groupBy('order_items.title', 'order_items.author')
                ->orderByDesc('sales_count')
                ->limit(10)
                ->get();

            // Revenue by payment gateway
            $revenueByGateway = Order::join('payments', 'orders.payment_id', '=', 'payments.id')
                ->whereBetween('orders.created_at', [$start, $end])
                ->where('orders.status', 'completed')
                ->selectRaw('payments.gateway_name, COUNT(*) as orders_count, SUM(orders.total_amount) as total_revenue')
                ->groupBy('payments.gateway_name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
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
                    'daily_trends' => $dailyOrders,
                    'status_distribution' => $statusDistribution,
                    'top_selling_books' => $topBooks,
                    'revenue_by_gateway' => $revenueByGateway,
                ],
                'message' => 'Order statistics retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving order statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get orders by status.
     */
    public function byStatus(Request $request, string $status): JsonResponse
    {
        try {
            $validStatuses = ['pending', 'processing', 'completed', 'cancelled', 'refunded'];
            
            if (!in_array($status, $validStatuses)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid status'
                ], 400);
            }

            $orders = Order::with(['user', 'payment', 'items.book'])
                ->where('status', $status)
                ->orderBy('created_at', 'desc')
                ->paginate($request->get('per_page', 15));

            return response()->json([
                'success' => true,
                'data' => $orders,
                'message' => "Orders with status '{$status}' retrieved successfully"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving orders by status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified order.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $order = Order::with(['user', 'items.book', 'payment'])->find($id);

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
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified order.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $order = Order::find($id);

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            $request->validate([
                'status' => 'required|in:pending,processing,completed,cancelled,refunded',
            ]);

            $oldStatus = $order->status;
            $newStatus = $request->status;

            $order->update([
                'status' => $newStatus,
                'metadata' => array_merge($order->metadata ?? [], [
                    'updated_by' => auth()->id(),
                    'update_reason' => $request->input('reason'),
                ]),
            ]);

            // Update timestamps based on status
            if ($newStatus === 'completed' && !$order->completed_at) {
                $order->update(['completed_at' => now()]);
            } elseif ($newStatus === 'cancelled' && !$order->cancelled_at) {
                $order->update(['cancelled_at' => now()]);
            } elseif ($newStatus === 'refunded' && !$order->refunded_at) {
                $order->update(['refunded_at' => now()]);
            }

            // Send notification to user about status change
            $this->orderService->sendOrderStatusChangeNotification($order, $oldStatus, $newStatus, $request->input('reason'));

            return response()->json([
                'success' => true,
                'data' => $order->fresh(),
                'message' => 'Order updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    /**
     * Get detailed order analytics report.
     */
    public function analyticsReport(Request $request): JsonResponse
    {
        try {
            $startDate = $request->input('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
            $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
            $groupBy = $request->input('group_by', 'day'); // day, week, month

            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->endOfDay();

            // Revenue trends
            $revenueTrends = Order::whereBetween('created_at', [$start, $end])
                ->where('status', 'completed')
                ->selectRaw($this->getGroupByClause($groupBy) . ' as period, SUM(total_amount) as revenue, COUNT(*) as orders')
                ->groupBy('period')
                ->orderBy('period')
                ->get();

            // Order status trends
            $statusTrends = Order::whereBetween('created_at', [$start, $end])
                ->selectRaw($this->getGroupByClause($groupBy) . ' as period, status, COUNT(*) as count')
                ->groupBy('period', 'status')
                ->orderBy('period')
                ->get()
                ->groupBy('period');

            // Customer analytics
            $customerAnalytics = Order::whereBetween('created_at', [$start, $end])
                ->selectRaw('user_id, COUNT(*) as order_count, SUM(total_amount) as total_spent, AVG(total_amount) as avg_order_value')
                ->groupBy('user_id')
                ->orderByDesc('total_spent')
                ->limit(20)
                ->with('user:id,name,email')
                ->get();

            // Book performance
            $bookPerformance = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
                ->whereBetween('orders.created_at', [$start, $end])
                ->where('orders.status', 'completed')
                ->selectRaw('order_items.title, order_items.author, COUNT(*) as sales_count, SUM(order_items.price) as revenue, AVG(order_items.price) as avg_price')
                ->groupBy('order_items.title', 'order_items.author')
                ->orderByDesc('revenue')
                ->limit(20)
                ->get();

            // Payment method analysis
            $paymentAnalysis = Order::join('payments', 'orders.payment_id', '=', 'payments.id')
                ->whereBetween('orders.created_at', [$start, $end])
                ->selectRaw('payments.gateway_name, payments.payment_method, COUNT(*) as usage_count, SUM(orders.total_amount) as total_amount, AVG(orders.total_amount) as avg_amount')
                ->groupBy('payments.gateway_name', 'payments.payment_method')
                ->orderByDesc('total_amount')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'report_period' => [
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                        'group_by' => $groupBy,
                    ],
                    'revenue_trends' => $revenueTrends,
                    'status_trends' => $statusTrends,
                    'customer_analytics' => $customerAnalytics,
                    'book_performance' => $bookPerformance,
                    'payment_analysis' => $paymentAnalysis,
                ],
                'message' => 'Analytics report generated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating analytics report',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export orders report.
     */
    public function exportReport(Request $request): JsonResponse
    {
        try {
            $startDate = $request->input('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
            $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
            $format = $request->input('format', 'csv'); // csv, json
            $status = $request->input('status'); // optional filter

            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->endOfDay();

            $query = Order::with(['user', 'payment', 'items.book'])
                ->whereBetween('created_at', [$start, $end]);

            if ($status) {
                $query->where('status', $status);
            }

            $orders = $query->get();

            // Transform data for export
            $exportData = $orders->map(function ($order) {
                return [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'user_name' => $order->user->name,
                    'user_email' => $order->user->email,
                    'status' => $order->status,
                    'total_amount' => $order->total_amount,
                    'currency' => $order->currency,
                    'payment_gateway' => $order->payment->gateway_name ?? 'N/A',
                    'payment_method' => $order->payment->payment_method ?? 'N/A',
                    'items_count' => $order->items->count(),
                    'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                    'completed_at' => $order->completed_at?->format('Y-m-d H:i:s') ?? 'N/A',
                    'items' => $order->items->map(function ($item) {
                        return $item->title . ' by ' . $item->author;
                    })->implode(', '),
                ];
            });

            if ($format === 'csv') {
                // Generate CSV content
                $csvContent = $this->generateCsvContent($exportData);
                
                return response()->json([
                    'success' => true,
                    'data' => [
                        'format' => 'csv',
                        'content' => $csvContent,
                        'filename' => "orders_report_{$startDate}_to_{$endDate}.csv",
                    ],
                    'message' => 'Orders report exported successfully'
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'format' => 'json',
                    'orders' => $exportData,
                    'total_count' => $exportData->count(),
                ],
                'message' => 'Orders report exported successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error exporting orders report',
                'error' => $e->getMessage()
            ], 500);
        }
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
     * Generate CSV content for export.
     */
    private function generateCsvContent($data): string
    {
        if ($data->isEmpty()) {
            return '';
        }

        $headers = array_keys($data->first());
        $csv = fopen('php://temp', 'r+');
        
        // Add headers
        fputcsv($csv, $headers);
        
        // Add data rows
        foreach ($data as $row) {
            fputcsv($csv, $row);
        }
        
        rewind($csv);
        $content = stream_get_contents($csv);
        fclose($csv);
        
        return $content;
    }

    /**
     * Cancel an order.
     */
    public function cancel(Request $request, string $id): JsonResponse
    {
        try {
            $order = Order::find($id);

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            $reason = $request->input('reason');
            $cancelled = $this->orderService->cancelOrder($order, $reason);

            if (!$cancelled) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to cancel order'
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Order cancelled successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error cancelling order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process refund for an order.
     */
    public function processRefund(Request $request, string $id): JsonResponse
    {
        try {
            $order = Order::find($id);

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            $request->validate([
                'refund_amount' => 'required|numeric|min:0|max:' . $order->total_amount,
                'refund_reference' => 'required|string|max:255',
                'reason' => 'nullable|string|max:500',
            ]);

            // Process refund through OrderService
            $refundResult = $this->orderService->processRefund(
                $order,
                $request->refund_amount,
                $request->refund_reference,
                $request->input('reason')
            );

            if (!$refundResult['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $refundResult['message']
                ], 400);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'order' => $order->fresh(),
                    'refund_details' => $refundResult,
                ],
                'message' => 'Refund processed successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error processing refund',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get refund information for an order.
     */
    public function getRefundInfo(string $id): JsonResponse
    {
        try {
            $order = Order::find($id);

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            $refundInfo = [
                'can_be_refunded' => $order->canBeRefunded(),
                'can_be_partially_refunded' => $this->orderService->canPartiallyRefund($order),
                'max_refundable_amount' => $this->orderService->getMaxRefundableAmount($order),
                'refund_history' => $this->orderService->getRefundHistory($order),
                'order_status' => $order->status,
                'total_amount' => $order->total_amount,
                'currency' => $order->currency,
            ];

            return response()->json([
                'success' => true,
                'data' => $refundInfo,
                'message' => 'Refund information retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving refund information',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

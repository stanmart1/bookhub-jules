<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\DownloadLog;
use App\Services\DeliveryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DeliveryController extends Controller
{
    public function __construct(
        private DeliveryService $deliveryService
    ) {}

    /**
     * Get user's delivery history
     */
    public function history(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            $orders = Order::with(['items.book', 'downloadLogs.book', 'downloadLogs.bookFile'])
                ->where('user_id', $user->id)
                ->where('status', Order::STATUS_COMPLETED)
                ->orderBy('created_at', 'desc')
                ->paginate(15);

            return response()->json([
                'success' => true,
                'data' => $orders,
                'message' => 'Delivery history retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving delivery history',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get delivery details for a specific order
     */
    public function show(Request $request, string $orderId): JsonResponse
    {
        try {
            $user = $request->user();
            
            $order = Order::with(['items.book', 'downloadLogs.book', 'downloadLogs.bookFile', 'deliveryLogs'])
                ->where('id', $orderId)
                ->where('user_id', $user->id)
                ->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found or access denied'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $order,
                'message' => 'Delivery details retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving delivery details',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get download URL for a book
     */
    public function download(Request $request, string $orderId, string $bookId): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'file_id' => 'sometimes|integer|exists:book_files,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = $request->user();
            
            $order = Order::where('id', $orderId)
                ->where('user_id', $user->id)
                ->where('status', Order::STATUS_COMPLETED)
                ->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found or access denied'
                ], 404);
            }

            // Find the download log for this book
            $downloadLog = DownloadLog::where('order_id', $orderId)
                ->where('book_id', $bookId)
                ->where('user_id', $user->id)
                ->when($request->file_id, function ($query, $fileId) {
                    return $query->where('book_file_id', $fileId);
                })
                ->first();

            if (!$downloadLog) {
                return response()->json([
                    'success' => false,
                    'message' => 'Download not found or access denied'
                ], 404);
            }

            if ($downloadLog->isExpired()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Download link has expired'
                ], 400);
            }

            if ($downloadLog->isFailed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Download is not available'
                ], 400);
            }

            // Generate download URL
            $downloadInfo = $this->deliveryService->validateDownloadToken($downloadLog->download_token);

            if (!$downloadInfo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Download is not available'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'download_url' => $downloadInfo['download_url'],
                    'book_title' => $downloadInfo['book']->title,
                    'file_name' => $downloadInfo['book_file']->file_name,
                    'file_size' => $downloadInfo['book_file']->file_size,
                    'expires_at' => $downloadLog->expires_at,
                    'download_token' => $downloadLog->download_token
                ],
                'message' => 'Download URL generated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating download URL',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Confirm download completion
     */
    public function confirmDownload(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'download_token' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $success = $this->deliveryService->recordDownloadCompletion($request->download_token);

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Download confirmed successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid download token'
                ], 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error confirming download',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get download statistics for user
     */
    public function statistics(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            $downloadStats = DownloadLog::where('user_id', $user->id)
                ->selectRaw('
                    COUNT(*) as total_downloads,
                    COUNT(CASE WHEN status = "completed" THEN 1 END) as completed_downloads,
                    COUNT(CASE WHEN status = "failed" THEN 1 END) as failed_downloads,
                    SUM(CASE WHEN status = "completed" THEN bytes_downloaded ELSE 0 END) as total_bytes_downloaded
                ')
                ->first();

            $recentDownloads = DownloadLog::with(['book', 'bookFile'])
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'statistics' => $downloadStats,
                    'recent_downloads' => $recentDownloads
                ],
                'message' => 'Download statistics retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving download statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Request delivery retry for an order
     */
    public function requestRetry(Request $request, string $orderId): JsonResponse
    {
        try {
            $user = $request->user();
            
            $order = Order::where('id', $orderId)
                ->where('user_id', $user->id)
                ->where('status', Order::STATUS_COMPLETED)
                ->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found or access denied'
                ], 404);
            }

            if (!$order->needsDeliveryRetry()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order does not need delivery retry'
                ], 400);
            }

            // Process delivery retry
            $result = $this->deliveryService->processDigitalDelivery($order);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Delivery retry processed successfully',
                    'data' => $result
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error processing delivery retry',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

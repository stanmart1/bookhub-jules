<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    /**
     * Get user's notifications.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            $query = Notification::where('user_id', $user->id);

            // Apply filters
            if ($request->boolean('unread_only')) {
                $query->unread();
            }

            if ($request->has('type')) {
                $query->ofType($request->type);
            }

            // Apply sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $notifications = $query->paginate($request->get('per_page', 15));

            return response()->json([
                'success' => true,
                'data' => $notifications,
                'message' => 'Notifications retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving notifications: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving notifications',
                'errors' => ['database' => ['An error occurred while retrieving notifications.']]
            ], 500);
        }
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead(Request $request, $notificationId): JsonResponse
    {
        try {
            $user = $request->user();
            
            $notification = Notification::where('id', $notificationId)
                ->where('user_id', $user->id)
                ->first();

            if (!$notification) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notification not found',
                    'errors' => ['notification' => ['Notification not found or you do not have permission to access it.']]
                ], 404);
            }

            $notification->markAsRead();

            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read'
            ]);

        } catch (\Exception $e) {
            Log::error('Error marking notification as read: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error marking notification as read',
                'errors' => ['database' => ['An error occurred while marking the notification as read.']]
            ], 500);
        }
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            Notification::where('user_id', $user->id)
                ->unread()
                ->update(['read_at' => now()]);

            return response()->json([
                'success' => true,
                'message' => 'All notifications marked as read'
            ]);

        } catch (\Exception $e) {
            Log::error('Error marking all notifications as read: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error marking all notifications as read',
                'errors' => ['database' => ['An error occurred while marking all notifications as read.']]
            ], 500);
        }
    }

    /**
     * Delete a notification.
     */
    public function destroy(Request $request, $notificationId): JsonResponse
    {
        try {
            $user = $request->user();
            
            $notification = Notification::where('id', $notificationId)
                ->where('user_id', $user->id)
                ->first();

            if (!$notification) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notification not found',
                    'errors' => ['notification' => ['Notification not found or you do not have permission to delete it.']]
                ], 404);
            }

            $notification->delete();

            return response()->json([
                'success' => true,
                'message' => 'Notification deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting notification: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error deleting notification',
                'errors' => ['database' => ['An error occurred while deleting the notification.']]
            ], 500);
        }
    }

    /**
     * Get notification statistics.
     */
    public function stats(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            $stats = Notification::where('user_id', $user->id)
                ->selectRaw('
                    COUNT(*) as total_notifications,
                    COUNT(CASE WHEN read_at IS NULL THEN 1 END) as unread_count,
                    COUNT(CASE WHEN read_at IS NOT NULL THEN 1 END) as read_count,
                    COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as this_week,
                    COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as this_month
                ')
                ->first();

            // Get notification type distribution
            $typeStats = Notification::where('user_id', $user->id)
                ->selectRaw('type, COUNT(*) as count')
                ->groupBy('type')
                ->orderBy('count', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'total_notifications' => (int) $stats->total_notifications,
                    'unread_count' => (int) $stats->unread_count,
                    'read_count' => (int) $stats->read_count,
                    'this_week' => (int) $stats->this_week,
                    'this_month' => (int) $stats->this_month,
                    'type_distribution' => $typeStats,
                ],
                'message' => 'Notification statistics retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving notification statistics: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving notification statistics',
                'errors' => ['database' => ['An error occurred while retrieving notification statistics.']]
            ], 500);
        }
    }

    /**
     * Get unread notification count.
     */
    public function unreadCount(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            $count = Notification::where('user_id', $user->id)
                ->unread()
                ->count();

            return response()->json([
                'success' => true,
                'data' => ['unread_count' => $count],
                'message' => 'Unread count retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving unread count: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving unread count',
                'errors' => ['database' => ['An error occurred while retrieving unread count.']]
            ], 500);
        }
    }
} 
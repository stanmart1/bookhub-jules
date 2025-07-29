<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Book; // Added this import for getFavoriteBooks

class ActivityService
{
    /**
     * Log user activity.
     */
    public static function log(
        string $action,
        ?int $userId = null,
        ?string $modelType = null,
        ?int $modelId = null,
        array $properties = [],
        ?Request $request = null
    ): void {
        try {
            ActivityLog::create([
                'user_id' => $userId,
                'action' => $action,
                'model_type' => $modelType,
                'model_id' => $modelId,
                'properties' => $properties,
                'ip_address' => $request?->ip(),
                'user_agent' => $request?->userAgent(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error logging activity: ' . $e->getMessage());
        }
    }

    /**
     * Create a notification for a user.
     */
    public static function notify(
        int $userId,
        string $type,
        string $title,
        string $message,
        array $data = []
    ): void {
        try {
            Notification::create([
                'user_id' => $userId,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating notification: ' . $e->getMessage());
        }
    }

    /**
     * Log book view activity.
     */
    public static function logBookView(int $userId, int $bookId, Request $request): void
    {
        self::log('book_viewed', $userId, 'App\Models\Book', $bookId, [], $request);
    }

    /**
     * Log book purchase activity.
     */
    public static function logBookPurchase(int $userId, int $bookId, float $price, Request $request): void
    {
        self::log('book_purchased', $userId, 'App\Models\Book', $bookId, [
            'price' => $price,
            'purchase_date' => now()->toISOString(),
        ], $request);

        // Create notification
        self::notify(
            $userId,
            'book_added',
            'Book Added to Library',
            'Your book has been successfully added to your library.',
            ['book_id' => $bookId]
        );
    }

    /**
     * Log wishlist activity.
     */
    public static function logWishlistActivity(string $action, int $userId, int $bookId, Request $request): void
    {
        self::log($action, $userId, 'App\Models\Book', $bookId, [], $request);

        if ($action === 'book_added_to_wishlist') {
            self::notify(
                $userId,
                'recommendation',
                'Book Added to Wishlist',
                'Book has been added to your wishlist for later purchase.',
                ['book_id' => $bookId]
            );
        }
    }

    /**
     * Log review activity.
     */
    public static function logReviewActivity(string $action, int $userId, int $bookId, int $reviewId, Request $request): void
    {
        self::log($action, $userId, 'App\Models\Review', $reviewId, [
            'book_id' => $bookId,
        ], $request);
    }

    /**
     * Log reading progress activity.
     */
    public static function logReadingProgress(int $userId, int $bookId, array $progressData, Request $request): void
    {
        self::log('reading_progress_updated', $userId, 'App\Models\Book', $bookId, $progressData, $request);
    }

    /**
     * Log reading session activity.
     */
    public static function logReadingSession(string $action, int $userId, int $bookId, array $sessionData, Request $request): void
    {
        self::log($action, $userId, 'App\Models\Book', $bookId, $sessionData, $request);
    }

    /**
     * Log goal activity.
     */
    public static function logGoalActivity(string $action, int $userId, array $goalData, Request $request): void
    {
        self::log($action, $userId, null, null, $goalData, $request);

        if ($action === 'goal_achieved') {
            self::notify(
                $userId,
                'achievement',
                'Goal Achieved! 🎉',
                'Congratulations! You have achieved your reading goal.',
                $goalData
            );
        }
    }

    /**
     * Log search activity.
     */
    public static function logSearch(int $userId, string $query, array $filters, Request $request): void
    {
        self::log('search_performed', $userId, null, null, [
            'query' => $query,
            'filters' => $filters,
        ], $request);
    }

    /**
     * Log authentication activity.
     */
    public static function logAuth(string $action, int $userId, Request $request): void
    {
        self::log($action, $userId, null, null, [], $request);
    }

    /**
     * Log profile update activity.
     */
    public static function logProfileUpdate(int $userId, array $updatedFields, Request $request): void
    {
        self::log('profile_updated', $userId, 'App\Models\User', $userId, [
            'updated_fields' => $updatedFields,
        ], $request);
    }

    /**
     * Notify about price drops.
     */
    public static function notifyPriceDrop(int $userId, int $bookId, float $oldPrice, float $newPrice): void
    {
        $discount = round((($oldPrice - $newPrice) / $oldPrice) * 100);
        
        self::notify(
            $userId,
            'price_drop',
            'Price Drop Alert! 💰',
            "A book in your wishlist is now {$discount}% off!",
            [
                'book_id' => $bookId,
                'old_price' => $oldPrice,
                'new_price' => $newPrice,
                'discount_percentage' => $discount,
            ]
        );
    }

    /**
     * Notify about new releases.
     */
    public static function notifyNewRelease(int $userId, int $bookId, string $bookTitle): void
    {
        self::notify(
            $userId,
            'new_release',
            'New Release Available! 🆕',
            "A new book you might like is now available: {$bookTitle}",
            ['book_id' => $bookId, 'book_title' => $bookTitle]
        );
    }

    /**
     * Notify about reading recommendations.
     */
    public static function notifyRecommendation(int $userId, array $recommendedBooks): void
    {
        $bookCount = count($recommendedBooks);
        
        self::notify(
            $userId,
            'recommendation',
            'New Recommendations for You! 💡',
            "We found {$bookCount} new books you might enjoy based on your reading history.",
            ['recommended_books' => $recommendedBooks]
        );
    }

    /**
     * Get user activity summary.
     */
    public static function getUserActivitySummary(int $userId, int $days = 30): array
    {
        $activities = ActivityLog::where('user_id', $userId)
            ->recent($days)
            ->selectRaw('action, COUNT(*) as count')
            ->groupBy('action')
            ->orderBy('count', 'desc')
            ->get();

        $totalActivities = $activities->sum('count');
        $uniqueActions = $activities->count();

        return [
            'total_activities' => $totalActivities,
            'unique_actions' => $uniqueActions,
            'activity_distribution' => $activities,
            'most_active_day' => self::getMostActiveDay($userId, $days),
            'favorite_books' => self::getFavoriteBooks($userId, $days),
        ];
    }

    /**
     * Get most active day for user.
     */
    private static function getMostActiveDay(int $userId, int $days): ?array
    {
        $mostActiveDay = ActivityLog::where('user_id', $userId)
            ->recent($days)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('count', 'desc')
            ->first();

        return $mostActiveDay ? [
            'date' => $mostActiveDay->date,
            'activity_count' => $mostActiveDay->count,
        ] : null;
    }

    /**
     * Get user's favorite books based on activity.
     */
    public static function getFavoriteBooks(int $userId, int $limit = 10): array
    {
        try {
            $favoriteBooks = ActivityLog::where('user_id', $userId)
                ->where('action', 'book_viewed') // Changed from activity_type to action
                ->selectRaw('model_id as subject_id, COUNT(*) as view_count') // Changed from subject_id to model_id
                ->groupBy('subject_id')
                ->orderByDesc('view_count')
                ->limit($limit)
                ->get();

            $bookIds = $favoriteBooks->pluck('subject_id')->toArray();
            
            $books = Book::whereIn('id', $bookIds)
                ->select('id', 'title', 'author', 'cover_image', 'rating')
                ->get()
                ->keyBy('id');

            return $favoriteBooks->map(function ($favorite) use ($books) {
                $book = $books->get($favorite->subject_id);
                return $book ? [
                    'id' => $book->id,
                    'title' => $book->title,
                    'author' => $book->author,
                    'cover_image' => $book->cover_image,
                    'rating' => $book->rating,
                    'view_count' => $favorite->view_count,
                ] : null;
            })->filter()->values()->toArray();

        } catch (\Exception $e) {
            Log::error('Error getting favorite books: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Send delivery ready notification.
     */
    public static function notifyDeliveryReady(int $userId, int $orderId, string $orderNumber, int $itemCount): void
    {
        try {
            self::notify(
                $userId,
                'delivery_ready',
                'Books Ready for Download',
                "Your purchased books are ready for download! Click here to access your digital library.",
                [
                    'order_id' => $orderId,
                    'order_number' => $orderNumber,
                    'item_count' => $itemCount,
                    'action_url' => route('api.v1.orders.show', $orderId),
                ]
            );
        } catch (\Exception $e) {
            Log::error('Error sending delivery ready notification: ' . $e->getMessage());
        }
    }

    /**
     * Send download reminder notification.
     */
    public static function notifyDownloadReminder(int $userId, int $orderId, string $orderNumber, int $itemCount): void
    {
        try {
            self::notify(
                $userId,
                'download_reminder',
                'Download Reminder',
                "Don't forget to download your purchased books! Your download links are still active.",
                [
                    'order_id' => $orderId,
                    'order_number' => $orderNumber,
                    'item_count' => $itemCount,
                    'action_url' => route('api.v1.orders.show', $orderId),
                ]
            );
        } catch (\Exception $e) {
            Log::error('Error sending download reminder notification: ' . $e->getMessage());
        }
    }

    /**
     * Send order status update notification.
     */
    public static function notifyOrderStatusUpdate(int $userId, int $orderId, string $orderNumber, string $oldStatus, string $newStatus, string $reason = null): void
    {
        try {
            $statusMessages = [
                'processing' => 'Your order is being processed',
                'completed' => 'Your order has been completed successfully',
                'cancelled' => 'Your order has been cancelled',
                'refunded' => 'Your order has been refunded',
            ];

            $message = $statusMessages[$newStatus] ?? "Your order status has changed from {$oldStatus} to {$newStatus}";
            if ($reason) {
                $message .= ". Reason: {$reason}";
            }

            self::notify(
                $userId,
                'order_status_update',
                'Order Status Updated',
                $message,
                [
                    'order_id' => $orderId,
                    'order_number' => $orderNumber,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'reason' => $reason,
                    'action_url' => route('api.v1.orders.show', $orderId),
                ]
            );
        } catch (\Exception $e) {
            Log::error('Error sending order status update notification: ' . $e->getMessage());
        }
    }

    /**
     * Send payment success notification.
     */
    public static function notifyPaymentSuccess(int $userId, int $orderId, string $orderNumber, float $amount, string $currency): void
    {
        try {
            self::notify(
                $userId,
                'payment_success',
                'Payment Successful',
                "Your payment of {$currency} " . number_format($amount, 2) . " for order #{$orderNumber} was successful!",
                [
                    'order_id' => $orderId,
                    'order_number' => $orderNumber,
                    'amount' => $amount,
                    'currency' => $currency,
                    'action_url' => route('api.v1.orders.show', $orderId),
                ]
            );
        } catch (\Exception $e) {
            Log::error('Error sending payment success notification: ' . $e->getMessage());
        }
    }

    /**
     * Send payment failure notification.
     */
    public static function notifyPaymentFailure(int $userId, int $orderId, string $orderNumber, string $errorMessage): void
    {
        try {
            self::notify(
                $userId,
                'payment_failure',
                'Payment Failed',
                "Your payment for order #{$orderNumber} failed. Please try again or contact support.",
                [
                    'order_id' => $orderId,
                    'order_number' => $orderNumber,
                    'error_message' => $errorMessage,
                    'action_url' => route('api.v1.orders.show', $orderId),
                ]
            );
        } catch (\Exception $e) {
            Log::error('Error sending payment failure notification: ' . $e->getMessage());
        }
    }

    /**
     * Send refund processed notification.
     */
    public static function notifyRefundProcessed(int $userId, int $orderId, string $orderNumber, float $refundAmount, string $currency, string $refundReference): void
    {
        try {
            self::notify(
                $userId,
                'refund_processed',
                'Refund Processed',
                "Your refund of {$currency} " . number_format($refundAmount, 2) . " for order #{$orderNumber} has been processed.",
                [
                    'order_id' => $orderId,
                    'order_number' => $orderNumber,
                    'refund_amount' => $refundAmount,
                    'currency' => $currency,
                    'refund_reference' => $refundReference,
                    'action_url' => route('api.v1.orders.show', $orderId),
                ]
            );
        } catch (\Exception $e) {
            Log::error('Error sending refund processed notification: ' . $e->getMessage());
        }
    }

    /**
     * Send coupon applied notification.
     */
    public static function notifyCouponApplied(int $userId, int $orderId, string $orderNumber, string $couponCode, float $discountAmount, string $currency): void
    {
        try {
            self::notify(
                $userId,
                'coupon_applied',
                'Coupon Applied',
                "Coupon '{$couponCode}' applied successfully! You saved {$currency} " . number_format($discountAmount, 2) . " on order #{$orderNumber}.",
                [
                    'order_id' => $orderId,
                    'order_number' => $orderNumber,
                    'coupon_code' => $couponCode,
                    'discount_amount' => $discountAmount,
                    'currency' => $currency,
                    'action_url' => route('api.v1.orders.show', $orderId),
                ]
            );
        } catch (\Exception $e) {
            Log::error('Error sending coupon applied notification: ' . $e->getMessage());
        }
    }

    /**
     * Send new book release notification.
     */
    public static function notifyNewBookRelease(int $userId, int $bookId, string $bookTitle, string $author): void
    {
        try {
            self::notify(
                $userId,
                'new_book_release',
                'New Book Available',
                "A new book by {$author} is now available: '{$bookTitle}'",
                [
                    'book_id' => $bookId,
                    'book_title' => $bookTitle,
                    'author' => $author,
                    'action_url' => route('api.v1.books.show', $bookId),
                ]
            );
        } catch (\Exception $e) {
            Log::error('Error sending new book release notification: ' . $e->getMessage());
        }
    }

    /**
     * Send price drop notification.
     */
    public static function notifyPriceDrop(int $userId, int $bookId, string $bookTitle, float $oldPrice, float $newPrice, string $currency): void
    {
        try {
            $discount = $oldPrice - $newPrice;
            $discountPercentage = round(($discount / $oldPrice) * 100, 1);

            self::notify(
                $userId,
                'price_drop',
                'Price Drop Alert',
                "The price of '{$bookTitle}' has dropped by {$discountPercentage}%! New price: {$currency} " . number_format($newPrice, 2),
                [
                    'book_id' => $bookId,
                    'book_title' => $bookTitle,
                    'old_price' => $oldPrice,
                    'new_price' => $newPrice,
                    'currency' => $currency,
                    'discount_amount' => $discount,
                    'discount_percentage' => $discountPercentage,
                    'action_url' => route('api.v1.books.show', $bookId),
                ]
            );
        } catch (\Exception $e) {
            Log::error('Error sending price drop notification: ' . $e->getMessage());
        }
    }
} 
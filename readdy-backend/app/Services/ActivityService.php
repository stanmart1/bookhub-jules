<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
    private static function getFavoriteBooks(int $userId, int $days): array
    {
        return ActivityLog::where('user_id', $userId)
            ->recent($days)
            ->whereNotNull('model_id')
            ->where('model_type', 'App\Models\Book')
            ->selectRaw('model_id, COUNT(*) as interaction_count')
            ->groupBy('model_id')
            ->orderBy('interaction_count', 'desc')
            ->limit(5)
            ->get()
            ->toArray();
    }
} 
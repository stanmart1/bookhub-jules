<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Book;
use App\Models\User;
use App\Models\DownloadLog;
use App\Models\DeliveryLog;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;

class AnalyticsService
{
    /**
     * Get comprehensive purchase analytics.
     */
    public function getPurchaseAnalytics(Request $request): array
    {
        try {
            $dateRange = $request->input('date_range', '30');
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

            // Basic purchase metrics
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

            // Financial metrics
            $averageOrderValue = Order::whereBetween('created_at', [$start, $end])
                ->where('status', 'completed')
                ->avg('total_amount') ?? 0;

            $totalDiscounts = Order::whereBetween('created_at', [$start, $end])
                ->where('status', 'completed')
                ->sum('discount_amount');

            $totalTaxes = Order::whereBetween('created_at', [$start, $end])
                ->where('status', 'completed')
                ->sum('tax_amount');

            // Conversion metrics
            $conversionRate = $totalOrders > 0 ? ($completedOrders / $totalOrders) * 100 : 0;
            $cancellationRate = $totalOrders > 0 ? ($cancelledOrders / $totalOrders) * 100 : 0;
            $refundRate = $totalOrders > 0 ? ($refundedOrders / $totalOrders) * 100 : 0;

            // Payment method analysis
            $paymentMethods = $this->getPaymentMethodAnalysis($start, $end);

            // Top performing books
            $topBooks = $this->getTopPerformingBooks($start, $end, 10);

            // Customer analytics
            $customerAnalytics = $this->getCustomerAnalytics($start, $end);

            return [
                'summary' => [
                    'total_orders' => $totalOrders,
                    'total_revenue' => $totalRevenue,
                    'pending_orders' => $pendingOrders,
                    'completed_orders' => $completedOrders,
                    'cancelled_orders' => $cancelledOrders,
                    'refunded_orders' => $refundedOrders,
                    'average_order_value' => round($averageOrderValue, 2),
                    'total_discounts' => $totalDiscounts,
                    'total_taxes' => $totalTaxes,
                    'conversion_rate' => round($conversionRate, 2),
                    'cancellation_rate' => round($cancellationRate, 2),
                    'refund_rate' => round($refundRate, 2),
                ],
                'payment_methods' => $paymentMethods,
                'top_books' => $topBooks,
                'customer_analytics' => $customerAnalytics,
                'date_range' => [
                    'start_date' => $start->format('Y-m-d'),
                    'end_date' => $end->format('Y-m-d'),
                ],
            ];

        } catch (\Exception $e) {
            Log::error('Error getting purchase analytics: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get delivery analytics.
     */
    public function getDeliveryAnalytics(Request $request): array
    {
        try {
            $dateRange = $request->input('date_range', '30');
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

            // Delivery metrics
            $totalDeliveries = DeliveryLog::whereBetween('created_at', [$start, $end])->count();
            $successfulDeliveries = DeliveryLog::whereBetween('created_at', [$start, $end])
                ->where('status', 'success')
                ->count();
            
            $failedDeliveries = DeliveryLog::whereBetween('created_at', [$start, $end])
                ->where('status', 'failed')
                ->count();

            $pendingDeliveries = DeliveryLog::whereBetween('created_at', [$start, $end])
                ->where('status', 'pending')
                ->count();

            // Download metrics
            $totalDownloads = DownloadLog::whereBetween('created_at', [$start, $end])->count();
            $uniqueDownloads = DownloadLog::whereBetween('created_at', [$start, $end])
                ->distinct('user_id', 'book_id')
                ->count();

            $averageDownloadTime = DownloadLog::whereBetween('created_at', [$start, $end])
                ->whereNotNull('download_duration')
                ->avg('download_duration');

            $totalDownloadSize = DownloadLog::whereBetween('created_at', [$start, $end])
                ->sum('file_size');

            // Delivery success rate
            $deliverySuccessRate = $totalDeliveries > 0 ? ($successfulDeliveries / $totalDeliveries) * 100 : 0;

            // Average delivery time
            $averageDeliveryTime = DeliveryLog::whereBetween('created_at', [$start, $end])
                ->where('status', 'success')
                ->whereNotNull('delivery_duration')
                ->avg('delivery_duration');

            // Top downloaded books
            $topDownloadedBooks = $this->getTopDownloadedBooks($start, $end, 10);

            // Delivery performance by time
            $deliveryPerformance = $this->getDeliveryPerformanceByTime($start, $end);

            return [
                'summary' => [
                    'total_deliveries' => $totalDeliveries,
                    'successful_deliveries' => $successfulDeliveries,
                    'failed_deliveries' => $failedDeliveries,
                    'pending_deliveries' => $pendingDeliveries,
                    'delivery_success_rate' => round($deliverySuccessRate, 2),
                    'average_delivery_time' => round($averageDeliveryTime, 2),
                    'total_downloads' => $totalDownloads,
                    'unique_downloads' => $uniqueDownloads,
                    'average_download_time' => round($averageDownloadTime, 2),
                    'total_download_size' => $totalDownloadSize,
                ],
                'top_downloaded_books' => $topDownloadedBooks,
                'delivery_performance' => $deliveryPerformance,
                'date_range' => [
                    'start_date' => $start->format('Y-m-d'),
                    'end_date' => $end->format('Y-m-d'),
                ],
            ];

        } catch (\Exception $e) {
            Log::error('Error getting delivery analytics: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get revenue trends by period.
     */
    public function getRevenueTrends(string $startDate, string $endDate, string $groupBy = 'day'): Collection
    {
        try {
            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->endOfDay();

            return Order::whereBetween('created_at', [$start, $end])
                ->where('status', 'completed')
                ->selectRaw($this->getGroupByClause($groupBy) . ' as period, SUM(total_amount) as revenue, COUNT(*) as orders')
                ->groupBy('period')
                ->orderBy('period')
                ->get();

        } catch (\Exception $e) {
            Log::error('Error getting revenue trends: ' . $e->getMessage());
            return collect([]);
        }
    }

    /**
     * Get order status trends.
     */
    public function getOrderStatusTrends(string $startDate, string $endDate, string $groupBy = 'day'): Collection
    {
        try {
            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->endOfDay();

            return Order::whereBetween('created_at', [$start, $end])
                ->selectRaw($this->getGroupByClause($groupBy) . ' as period, status, COUNT(*) as count')
                ->groupBy('period', 'status')
                ->orderBy('period')
                ->get()
                ->groupBy('period');

        } catch (\Exception $e) {
            Log::error('Error getting order status trends: ' . $e->getMessage());
            return collect([]);
        }
    }

    /**
     * Get customer analytics.
     */
    public function getCustomerAnalytics(string $startDate, string $endDate, int $limit = 20): Collection
    {
        try {
            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->endOfDay();

            return Order::whereBetween('created_at', [$start, $end])
                ->selectRaw('user_id, COUNT(*) as order_count, SUM(total_amount) as total_spent, AVG(total_amount) as avg_order_value')
                ->groupBy('user_id')
                ->orderByDesc('total_spent')
                ->limit($limit)
                ->with('user:id,name,email')
                ->get();

        } catch (\Exception $e) {
            Log::error('Error getting customer analytics: ' . $e->getMessage());
            return collect([]);
        }
    }

    /**
     * Get top performing books.
     */
    public function getTopPerformingBooks(string $startDate, string $endDate, int $limit = 10): Collection
    {
        try {
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

        } catch (\Exception $e) {
            Log::error('Error getting top performing books: ' . $e->getMessage());
            return collect([]);
        }
    }

    /**
     * Get payment method analysis.
     */
    public function getPaymentMethodAnalysis(string $startDate, string $endDate): Collection
    {
        try {
            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->endOfDay();

            return Order::join('payments', 'orders.payment_id', '=', 'payments.id')
                ->whereBetween('orders.created_at', [$start, $end])
                ->selectRaw('payments.gateway_name, payments.payment_method, COUNT(*) as usage_count, SUM(orders.total_amount) as total_amount, AVG(orders.total_amount) as avg_amount')
                ->groupBy('payments.gateway_name', 'payments.payment_method')
                ->orderByDesc('total_amount')
                ->get();

        } catch (\Exception $e) {
            Log::error('Error getting payment method analysis: ' . $e->getMessage());
            return collect([]);
        }
    }

    /**
     * Get top downloaded books.
     */
    public function getTopDownloadedBooks(string $startDate, string $endDate, int $limit = 10): Collection
    {
        try {
            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->endOfDay();

            return DownloadLog::join('books', 'download_logs.book_id', '=', 'books.id')
                ->whereBetween('download_logs.created_at', [$start, $end])
                ->selectRaw('books.title, books.author, COUNT(*) as download_count, SUM(download_logs.file_size) as total_size')
                ->groupBy('books.title', 'books.author')
                ->orderByDesc('download_count')
                ->limit($limit)
                ->get();

        } catch (\Exception $e) {
            Log::error('Error getting top downloaded books: ' . $e->getMessage());
            return collect([]);
        }
    }

    /**
     * Get delivery performance by time.
     */
    public function getDeliveryPerformanceByTime(string $startDate, string $endDate): array
    {
        try {
            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->endOfDay();

            $performance = DeliveryLog::whereBetween('created_at', [$start, $end])
                ->selectRaw('HOUR(created_at) as hour, status, COUNT(*) as count')
                ->groupBy('hour', 'status')
                ->get()
                ->groupBy('hour');

            $hourlyPerformance = [];
            for ($hour = 0; $hour < 24; $hour++) {
                $hourData = $performance->get($hour, collect([]));
                $total = $hourData->sum('count');
                $successful = $hourData->where('status', 'success')->sum('count');
                
                $hourlyPerformance[$hour] = [
                    'hour' => $hour,
                    'total_deliveries' => $total,
                    'successful_deliveries' => $successful,
                    'success_rate' => $total > 0 ? round(($successful / $total) * 100, 2) : 0,
                ];
            }

            return $hourlyPerformance;

        } catch (\Exception $e) {
            Log::error('Error getting delivery performance by time: ' . $e->getMessage());
            return [];
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
     * Generate comprehensive analytics report.
     */
    public function generateAnalyticsReport(Request $request): array
    {
        try {
            $purchaseAnalytics = $this->getPurchaseAnalytics($request);
            $deliveryAnalytics = $this->getDeliveryAnalytics($request);
            
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            
            if ($startDate && $endDate) {
                $revenueTrends = $this->getRevenueTrends($startDate, $endDate);
                $orderStatusTrends = $this->getOrderStatusTrends($startDate, $endDate);
            } else {
                $revenueTrends = collect([]);
                $orderStatusTrends = collect([]);
            }

            return [
                'purchase_analytics' => $purchaseAnalytics,
                'delivery_analytics' => $deliveryAnalytics,
                'revenue_trends' => $revenueTrends,
                'order_status_trends' => $orderStatusTrends,
                'generated_at' => now()->format('Y-m-d H:i:s'),
                'report_period' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ],
            ];

        } catch (\Exception $e) {
            Log::error('Error generating analytics report: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Track comprehensive user analytics
     */
    public function trackUserAnalytics(int $userId, string $action, array $data = []): void
    {
        try {
            $sessionId = session()->getId();
            
            ActivityLog::create([
                'user_id' => $userId,
                'action' => $action,
                'properties' => array_merge($data, [
                    'session_id' => $sessionId,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'timestamp' => now()->toISOString(),
                ]),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            // Cache user activity for real-time analytics
            $cacheKey = "user_activity:{$userId}";
            $userActivity = Cache::get($cacheKey, []);
            $userActivity[] = [
                'action' => $action,
                'data' => $data,
                'timestamp' => now()->toISOString(),
            ];
            
            // Keep only last 100 activities
            if (count($userActivity) > 100) {
                $userActivity = array_slice($userActivity, -100);
            }
            
            Cache::put($cacheKey, $userActivity, 3600); // Cache for 1 hour

        } catch (\Exception $e) {
            Log::error('Error tracking user analytics: ' . $e->getMessage());
        }
    }

    /**
     * Get user behavior insights
     */
    public function getUserBehaviorInsights(int $userId): array
    {
        try {
            $user = User::find($userId);
            if (!$user) {
                return [];
            }

            // Get user's reading patterns
            $readingPatterns = $this->getUserReadingPatterns($userId);
            
            // Get purchase behavior
            $purchaseBehavior = $this->getUserPurchaseBehavior($userId);
            
            // Calculate engagement level
            $engagementLevel = $this->calculateUserEngagementLevel($userId);
            
            // Calculate user lifetime value
            $lifetimeValue = $this->calculateUserLifetimeValue($userId);

            return [
                'user_id' => $userId,
                'reading_patterns' => $readingPatterns,
                'purchase_behavior' => $purchaseBehavior,
                'engagement_level' => $engagementLevel,
                'lifetime_value' => $lifetimeValue,
                'last_activity' => $user->last_login_at,
                'total_books_read' => $user->readingProgress()->where('is_finished', true)->count(),
                'total_purchases' => $user->orders()->where('status', 'completed')->count(),
                'total_spent' => $user->orders()->where('status', 'completed')->sum('total_amount'),
            ];

        } catch (\Exception $e) {
            Log::error('Error getting user behavior insights: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Generate user segments
     */
    public function generateUserSegments(): array
    {
        try {
            $segments = [
                'active_users' => $this->getActiveUsers(),
                'high_value_customers' => $this->getHighValueCustomers(),
                'churn_risk' => $this->getChurnRiskUsers(),
                'engagement_levels' => $this->getEngagementLevelSegments(),
            ];

            return $segments;

        } catch (\Exception $e) {
            Log::error('Error generating user segments: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get advanced reading analytics
     */
    public function getAdvancedReadingAnalytics(Request $request): array
    {
        try {
            $dateRange = $request->input('date_range', '30');
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');

            if ($startDate && $endDate) {
                $start = Carbon::parse($startDate)->startOfDay();
                $end = Carbon::parse($endDate)->endOfDay();
            } else {
                $end = Carbon::now()->endOfDay();
                $start = Carbon::now()->subDays($dateRange)->startOfDay();
            }

            return [
                'reading_speed_analysis' => $this->getReadingSpeedAnalysis($start, $end),
                'comprehension_metrics' => $this->getComprehensionMetrics($start, $end),
                'reading_patterns' => $this->getReadingPatterns($start, $end),
                'genre_preferences' => $this->getGenrePreferences($start, $end),
                'reading_time_optimization' => $this->getReadingTimeOptimization($start, $end),
                'progress_tracking_insights' => $this->getProgressTrackingInsights($start, $end),
            ];

        } catch (\Exception $e) {
            Log::error('Error getting advanced reading analytics: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get enhanced sales analytics
     */
    public function getEnhancedSalesAnalytics(Request $request): array
    {
        try {
            $dateRange = $request->input('date_range', '30');
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');

            if ($startDate && $endDate) {
                $start = Carbon::parse($startDate)->startOfDay();
                $end = Carbon::parse($endDate)->endOfDay();
            } else {
                $end = Carbon::now()->endOfDay();
                $start = Carbon::now()->subDays($dateRange)->startOfDay();
            }

            return [
                'revenue_forecasting' => $this->getRevenueForecasting($start, $end),
                'sales_trend_analysis' => $this->getSalesTrendAnalysis($start, $end),
                'customer_lifetime_value' => $this->getCustomerLifetimeValue($start, $end),
                'product_performance_analysis' => $this->getProductPerformanceAnalysis($start, $end),
                'market_segmentation' => $this->getMarketSegmentation($start, $end),
                'competitive_analysis' => $this->getCompetitiveAnalysis($start, $end),
            ];

        } catch (\Exception $e) {
            Log::error('Error getting enhanced sales analytics: ' . $e->getMessage());
            return [];
        }
    }

    // Private helper methods for analytics calculations
    private function getUserReadingPatterns(int $userId): array
    {
        $readingSessions = ReadingSession::where('user_id', $userId)
            ->where('created_at', '>=', now()->subDays(30))
            ->get();

        return [
            'average_session_duration' => $readingSessions->avg('duration_minutes'),
            'preferred_reading_times' => $this->getPreferredReadingTimes($readingSessions),
            'reading_frequency' => $readingSessions->count() / 30, // sessions per day
            'completion_rate' => $this->getCompletionRate($userId),
        ];
    }

    private function getUserPurchaseBehavior(int $userId): array
    {
        $orders = Order::where('user_id', $userId)
            ->where('status', 'completed')
            ->get();

        return [
            'average_order_value' => $orders->avg('total_amount'),
            'purchase_frequency' => $orders->count() / max(1, $orders->max('created_at')->diffInDays($orders->min('created_at'))),
            'preferred_categories' => $this->getPreferredCategories($userId),
            'seasonal_patterns' => $this->getSeasonalPatterns($orders),
        ];
    }

    private function calculateUserEngagementLevel(int $userId): string
    {
        $recentActivity = ActivityLog::where('user_id', $userId)
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        if ($recentActivity >= 20) return 'high';
        if ($recentActivity >= 10) return 'medium';
        return 'low';
    }

    private function calculateUserLifetimeValue(int $userId): float
    {
        return Order::where('user_id', $userId)
            ->where('status', 'completed')
            ->sum('total_amount');
    }

    private function getActiveUsers(): Collection
    {
        return User::where('last_login_at', '>=', now()->subDays(7))
            ->withCount(['orders', 'readingProgress'])
            ->orderByDesc('last_login_at')
            ->limit(100)
            ->get();
    }

    private function getHighValueCustomers(): Collection
    {
        return User::withSum('orders as total_spent', 'total_amount')
            ->whereHas('orders', function ($query) {
                $query->where('status', 'completed');
            })
            ->orderByDesc('total_spent')
            ->limit(50)
            ->get();
    }

    private function getChurnRiskUsers(): Collection
    {
        return User::where('last_login_at', '<=', now()->subDays(30))
            ->where('created_at', '<=', now()->subDays(60))
            ->withCount(['orders', 'readingProgress'])
            ->get();
    }

    private function getEngagementLevelSegments(): array
    {
        $users = User::withCount(['orders', 'readingProgress'])
            ->get();

        return [
            'high_engagement' => $users->where('orders_count', '>=', 5)->where('reading_progress_count', '>=', 10),
            'medium_engagement' => $users->where('orders_count', '>=', 2)->where('orders_count', '<', 5),
            'low_engagement' => $users->where('orders_count', '<', 2),
        ];
    }

    private function getReadingSpeedAnalysis(Carbon $start, Carbon $end): array
    {
        $sessions = ReadingSession::whereBetween('created_at', [$start, $end])
            ->where('duration_minutes', '>', 0)
            ->where('pages_read', '>', 0)
            ->get();

        return [
            'average_pages_per_minute' => $sessions->avg(function ($session) {
                return $session->pages_read / $session->duration_minutes;
            }),
            'speed_distribution' => $this->getSpeedDistribution($sessions),
            'speed_trends' => $this->getSpeedTrends($sessions),
        ];
    }

    private function getComprehensionMetrics(Carbon $start, Carbon $end): array
    {
        // Implementation for comprehension metrics
        return [
            'completion_rates' => $this->getCompletionRates($start, $end),
            'review_sentiment' => $this->getReviewSentiment($start, $end),
            'bookmark_frequency' => $this->getBookmarkFrequency($start, $end),
        ];
    }

    private function getReadingPatterns(Carbon $start, Carbon $end): array
    {
        $sessions = ReadingSession::whereBetween('created_at', [$start, $end])->get();
        
        return [
            'time_of_day_preferences' => $this->getTimeOfDayPreferences($sessions),
            'session_duration_patterns' => $this->getSessionDurationPatterns($sessions),
            'reading_frequency_patterns' => $this->getReadingFrequencyPatterns($sessions),
        ];
    }

    private function getGenrePreferences(Carbon $start, Carbon $end): array
    {
        $books = Book::join('reading_progress', 'books.id', '=', 'reading_progress.book_id')
            ->whereBetween('reading_progress.created_at', [$start, $end])
            ->join('book_category', 'books.id', '=', 'book_category.book_id')
            ->join('categories', 'book_category.category_id', '=', 'categories.id')
            ->selectRaw('categories.name, COUNT(*) as read_count')
            ->groupBy('categories.name')
            ->orderByDesc('read_count')
            ->get();

        return $books->toArray();
    }

    private function getReadingTimeOptimization(Carbon $start, Carbon $end): array
    {
        // Implementation for reading time optimization
        return [
            'optimal_reading_times' => $this->getOptimalReadingTimes($start, $end),
            'session_length_optimization' => $this->getSessionLengthOptimization($start, $end),
            'productivity_metrics' => $this->getProductivityMetrics($start, $end),
        ];
    }

    private function getProgressTrackingInsights(Carbon $start, Carbon $end): array
    {
        $progress = ReadingProgress::whereBetween('created_at', [$start, $end])->get();
        
        return [
            'completion_rates' => $this->getCompletionRates($start, $end),
            'abandonment_patterns' => $this->getAbandonmentPatterns($progress),
            'progress_acceleration' => $this->getProgressAcceleration($progress),
        ];
    }

    private function getRevenueForecasting(Carbon $start, Carbon $end): array
    {
        // Implementation for revenue forecasting
        return [
            'forecast_next_month' => $this->calculateRevenueForecast($start, $end),
            'seasonal_patterns' => $this->getSeasonalRevenuePatterns($start, $end),
            'growth_projections' => $this->getGrowthProjections($start, $end),
        ];
    }

    private function getSalesTrendAnalysis(Carbon $start, Carbon $end): array
    {
        $orders = Order::whereBetween('created_at', [$start, $end])
            ->where('status', 'completed')
            ->get();

        return [
            'daily_sales_trends' => $this->getDailySalesTrends($orders),
            'weekly_patterns' => $this->getWeeklySalesPatterns($orders),
            'monthly_growth' => $this->getMonthlyGrowth($orders),
        ];
    }

    private function getCustomerLifetimeValue(Carbon $start, Carbon $end): array
    {
        $customers = User::withSum('orders as total_spent', 'total_amount')
            ->whereHas('orders', function ($query) use ($start, $end) {
                $query->whereBetween('created_at', [$start, $end]);
            })
            ->get();

        return [
            'average_lifetime_value' => $customers->avg('total_spent'),
            'lifetime_value_distribution' => $this->getLifetimeValueDistribution($customers),
            'high_value_customers' => $customers->where('total_spent', '>=', 100)->count(),
        ];
    }

    private function getProductPerformanceAnalysis(Carbon $start, Carbon $end): array
    {
        $books = Book::join('order_items', 'books.id', '=', 'order_items.book_id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$start, $end])
            ->where('orders.status', 'completed')
            ->selectRaw('books.title, books.author, COUNT(*) as sales_count, SUM(order_items.total_price) as revenue')
            ->groupBy('books.id', 'books.title', 'books.author')
            ->orderByDesc('revenue')
            ->get();

        return [
            'top_performing_books' => $books->take(10),
            'revenue_by_book' => $books->toArray(),
            'sales_velocity' => $this->getSalesVelocity($books),
        ];
    }

    private function getMarketSegmentation(Carbon $start, Carbon $end): array
    {
        // Implementation for market segmentation
        return [
            'customer_segments' => $this->getCustomerSegments($start, $end),
            'geographic_distribution' => $this->getGeographicDistribution($start, $end),
            'demographic_analysis' => $this->getDemographicAnalysis($start, $end),
        ];
    }

    private function getCompetitiveAnalysis(Carbon $start, Carbon $end): array
    {
        // Implementation for competitive analysis
        return [
            'market_position' => $this->getMarketPosition($start, $end),
            'competitive_benchmarks' => $this->getCompetitiveBenchmarks($start, $end),
            'opportunity_analysis' => $this->getOpportunityAnalysis($start, $end),
        ];
    }

    // Additional helper methods for specific calculations
    private function getPreferredReadingTimes($sessions): array
    {
        return $sessions->groupBy(function ($session) {
            return $session->created_at->format('H');
        })->map->count()->toArray();
    }

    private function getCompletionRate(int $userId): float
    {
        $totalBooks = ReadingProgress::where('user_id', $userId)->count();
        $completedBooks = ReadingProgress::where('user_id', $userId)->where('is_finished', true)->count();
        
        return $totalBooks > 0 ? ($completedBooks / $totalBooks) * 100 : 0;
    }

    private function getPreferredCategories(int $userId): array
    {
        return OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('books', 'order_items.book_id', '=', 'books.id')
            ->join('book_category', 'books.id', '=', 'book_category.book_id')
            ->join('categories', 'book_category.category_id', '=', 'categories.id')
            ->where('orders.user_id', $userId)
            ->where('orders.status', 'completed')
            ->selectRaw('categories.name, COUNT(*) as purchase_count')
            ->groupBy('categories.name')
            ->orderByDesc('purchase_count')
            ->get()
            ->toArray();
    }

    private function getSeasonalPatterns($orders): array
    {
        return $orders->groupBy(function ($order) {
            return $order->created_at->format('M');
        })->map->count()->toArray();
    }

    private function getSpeedDistribution($sessions): array
    {
        $speeds = $sessions->map(function ($session) {
            return $session->pages_read / $session->duration_minutes;
        });

        return [
            'min' => $speeds->min(),
            'max' => $speeds->max(),
            'average' => $speeds->avg(),
            'median' => $speeds->median(),
        ];
    }

    private function getSpeedTrends($sessions): array
    {
        return $sessions->groupBy(function ($session) {
            return $session->created_at->format('Y-m-d');
        })->map(function ($daySessions) {
            return $daySessions->avg(function ($session) {
                return $session->pages_read / $session->duration_minutes;
            });
        })->toArray();
    }

    private function getCompletionRates(Carbon $start, Carbon $end): array
    {
        $progress = ReadingProgress::whereBetween('created_at', [$start, $end])->get();
        
        $total = $progress->count();
        $completed = $progress->where('is_finished', true)->count();
        
        return [
            'total_books' => $total,
            'completed_books' => $completed,
            'completion_rate' => $total > 0 ? ($completed / $total) * 100 : 0,
        ];
    }

    private function getReviewSentiment(Carbon $start, Carbon $end): array
    {
        $reviews = Review::whereBetween('created_at', [$start, $end])->get();
        
        return [
            'average_rating' => $reviews->avg('rating'),
            'rating_distribution' => $reviews->groupBy('rating')->map->count()->toArray(),
            'positive_reviews' => $reviews->where('rating', '>=', 4)->count(),
            'negative_reviews' => $reviews->where('rating', '<=', 2)->count(),
        ];
    }

    private function getBookmarkFrequency(Carbon $start, Carbon $end): array
    {
        $bookmarks = Bookmark::whereBetween('created_at', [$start, $end])->get();
        
        return [
            'total_bookmarks' => $bookmarks->count(),
            'average_bookmarks_per_user' => $bookmarks->count() / max(1, $bookmarks->unique('user_id')->count()),
            'bookmark_patterns' => $bookmarks->groupBy(function ($bookmark) {
                return $bookmark->created_at->format('H');
            })->map->count()->toArray(),
        ];
    }

    private function getTimeOfDayPreferences($sessions): array
    {
        return $sessions->groupBy(function ($session) {
            return $session->created_at->format('H');
        })->map->count()->toArray();
    }

    private function getSessionDurationPatterns($sessions): array
    {
        return [
            'average_duration' => $sessions->avg('duration_minutes'),
            'duration_distribution' => $sessions->groupBy(function ($session) {
                if ($session->duration_minutes < 15) return 'short';
                if ($session->duration_minutes < 60) return 'medium';
                return 'long';
            })->map->count()->toArray(),
        ];
    }

    private function getReadingFrequencyPatterns($sessions): array
    {
        return $sessions->groupBy(function ($session) {
            return $session->created_at->format('Y-m-d');
        })->map->count()->toArray();
    }

    private function getOptimalReadingTimes(Carbon $start, Carbon $end): array
    {
        $sessions = ReadingSession::whereBetween('created_at', [$start, $end])->get();
        
        $hourlyProductivity = [];
        for ($hour = 0; $hour < 24; $hour++) {
            $hourSessions = $sessions->filter(function ($session) use ($hour) {
                return $session->created_at->format('H') == $hour;
            });
            
            $hourlyProductivity[$hour] = [
                'hour' => $hour,
                'sessions' => $hourSessions->count(),
                'average_pages_read' => $hourSessions->avg('pages_read'),
                'average_duration' => $hourSessions->avg('duration_minutes'),
            ];
        }
        
        return $hourlyProductivity;
    }

    private function getSessionLengthOptimization(Carbon $start, Carbon $end): array
    {
        $sessions = ReadingSession::whereBetween('created_at', [$start, $end])->get();
        
        return [
            'optimal_session_length' => $sessions->avg('duration_minutes'),
            'productivity_by_duration' => $sessions->groupBy(function ($session) {
                if ($session->duration_minutes < 15) return 'short';
                if ($session->duration_minutes < 60) return 'medium';
                return 'long';
            })->map(function ($durationSessions) {
                return [
                    'average_pages_read' => $durationSessions->avg('pages_read'),
                    'completion_rate' => $durationSessions->where('pages_read', '>', 0)->count() / $durationSessions->count(),
                ];
            })->toArray(),
        ];
    }

    private function getProductivityMetrics(Carbon $start, Carbon $end): array
    {
        $sessions = ReadingSession::whereBetween('created_at', [$start, $end])->get();
        
        return [
            'total_reading_time' => $sessions->sum('duration_minutes'),
            'total_pages_read' => $sessions->sum('pages_read'),
            'pages_per_minute' => $sessions->sum('pages_read') / max(1, $sessions->sum('duration_minutes')),
            'sessions_per_day' => $sessions->count() / max(1, $start->diffInDays($end)),
        ];
    }

    private function getAbandonmentPatterns($progress): array
    {
        $abandoned = $progress->where('is_finished', false);
        
        return [
            'abandonment_rate' => $progress->count() > 0 ? ($abandoned->count() / $progress->count()) * 100 : 0,
            'abandonment_by_progress' => $abandoned->groupBy(function ($p) {
                if ($p->progress_percentage < 25) return 'early';
                if ($p->progress_percentage < 50) return 'quarter';
                if ($p->progress_percentage < 75) return 'half';
                return 'late';
            })->map->count()->toArray(),
        ];
    }

    private function getProgressAcceleration($progress): array
    {
        return [
            'average_progress_rate' => $progress->avg('progress_percentage'),
            'progress_acceleration' => $progress->groupBy('user_id')->map(function ($userProgress) {
                return $userProgress->sortBy('created_at')->map(function ($p, $index) {
                    if ($index === 0) return 0;
                    $previous = $userProgress->sortBy('created_at')->get($index - 1);
                    return $p->progress_percentage - $previous->progress_percentage;
                })->avg();
            })->avg(),
        ];
    }

    private function calculateRevenueForecast(Carbon $start, Carbon $end): float
    {
        $orders = Order::whereBetween('created_at', [$start, $end])
            ->where('status', 'completed')
            ->get();
        
        $dailyRevenue = $orders->groupBy(function ($order) {
            return $order->created_at->format('Y-m-d');
        })->map->sum('total_amount');
        
        $averageDailyRevenue = $dailyRevenue->avg();
        $daysInNextMonth = Carbon::now()->addMonth()->daysInMonth;
        
        return $averageDailyRevenue * $daysInNextMonth;
    }

    private function getSeasonalRevenuePatterns(Carbon $start, Carbon $end): array
    {
        $orders = Order::whereBetween('created_at', [$start, $end])
            ->where('status', 'completed')
            ->get();
        
        return $orders->groupBy(function ($order) {
            return $order->created_at->format('M');
        })->map->sum('total_amount')->toArray();
    }

    private function getGrowthProjections(Carbon $start, Carbon $end): array
    {
        $orders = Order::whereBetween('created_at', [$start, $end])
            ->where('status', 'completed')
            ->get();
        
        $monthlyRevenue = $orders->groupBy(function ($order) {
            return $order->created_at->format('Y-m');
        })->map->sum('total_amount');
        
        $growthRates = [];
        $months = $monthlyRevenue->keys()->sort();
        
        for ($i = 1; $i < $months->count(); $i++) {
            $currentMonth = $months[$i];
            $previousMonth = $months[$i - 1];
            
            $currentRevenue = $monthlyRevenue[$currentMonth];
            $previousRevenue = $monthlyRevenue[$previousMonth];
            
            $growthRates[$currentMonth] = $previousRevenue > 0 ? 
                (($currentRevenue - $previousRevenue) / $previousRevenue) * 100 : 0;
        }
        
        return [
            'monthly_growth_rates' => $growthRates,
            'average_growth_rate' => collect($growthRates)->avg(),
            'projected_next_month' => $this->calculateRevenueForecast($start, $end),
        ];
    }

    private function getDailySalesTrends($orders): array
    {
        return $orders->groupBy(function ($order) {
            return $order->created_at->format('Y-m-d');
        })->map->sum('total_amount')->toArray();
    }

    private function getWeeklySalesPatterns($orders): array
    {
        return $orders->groupBy(function ($order) {
            return $order->created_at->format('W');
        })->map->sum('total_amount')->toArray();
    }

    private function getMonthlyGrowth($orders): array
    {
        $monthlyRevenue = $orders->groupBy(function ($order) {
            return $order->created_at->format('Y-m');
        })->map->sum('total_amount');
        
        $growthRates = [];
        $months = $monthlyRevenue->keys()->sort();
        
        for ($i = 1; $i < $months->count(); $i++) {
            $currentMonth = $months[$i];
            $previousMonth = $months[$i - 1];
            
            $currentRevenue = $monthlyRevenue[$currentMonth];
            $previousRevenue = $monthlyRevenue[$previousMonth];
            
            $growthRates[$currentMonth] = $previousRevenue > 0 ? 
                (($currentRevenue - $previousRevenue) / $previousRevenue) * 100 : 0;
        }
        
        return $growthRates;
    }

    private function getLifetimeValueDistribution($customers): array
    {
        $values = $customers->pluck('total_spent');
        
        return [
            'min' => $values->min(),
            'max' => $values->max(),
            'average' => $values->avg(),
            'median' => $values->median(),
            'distribution' => [
                'low' => $values->where('total_spent', '<', 50)->count(),
                'medium' => $values->where('total_spent', '>=', 50)->where('total_spent', '<', 200)->count(),
                'high' => $values->where('total_spent', '>=', 200)->count(),
            ],
        ];
    }

    private function getSalesVelocity($books): array
    {
        return $books->map(function ($book) {
            return [
                'title' => $book->title,
                'sales_velocity' => $book->sales_count / max(1, $book->created_at->diffInDays(now())),
                'revenue_velocity' => $book->revenue / max(1, $book->created_at->diffInDays(now())),
            ];
        })->toArray();
    }

    private function getCustomerSegments(Carbon $start, Carbon $end): array
    {
        $customers = User::withSum('orders as total_spent', 'total_amount')
            ->whereHas('orders', function ($query) use ($start, $end) {
                $query->whereBetween('created_at', [$start, $end]);
            })
            ->get();
        
        return [
            'new_customers' => $customers->where('created_at', '>=', $start)->count(),
            'returning_customers' => $customers->where('created_at', '<', $start)->count(),
            'high_value_customers' => $customers->where('total_spent', '>=', 100)->count(),
            'average_customer_value' => $customers->avg('total_spent'),
        ];
    }

    private function getGeographicDistribution(Carbon $start, Carbon $end): array
    {
        // Implementation would depend on having geographic data
        return [
            'top_regions' => [],
            'regional_performance' => [],
        ];
    }

    private function getDemographicAnalysis(Carbon $start, Carbon $end): array
    {
        // Implementation would depend on having demographic data
        return [
            'age_distribution' => [],
            'gender_distribution' => [],
            'preferences_by_demographic' => [],
        ];
    }

    private function getMarketPosition(Carbon $start, Carbon $end): array
    {
        // Implementation for market position analysis
        return [
            'market_share' => 0,
            'competitive_advantage' => [],
            'growth_rate' => 0,
        ];
    }

    private function getCompetitiveBenchmarks(Carbon $start, Carbon $end): array
    {
        // Implementation for competitive benchmarks
        return [
            'industry_averages' => [],
            'competitor_analysis' => [],
            'performance_comparison' => [],
        ];
    }

    private function getOpportunityAnalysis(Carbon $start, Carbon $end): array
    {
        // Implementation for opportunity analysis
        return [
            'untapped_markets' => [],
            'growth_opportunities' => [],
            'recommendations' => [],
        ];
    }
} 
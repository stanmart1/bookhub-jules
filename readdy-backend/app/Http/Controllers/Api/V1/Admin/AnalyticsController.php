<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use App\Services\ReportingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class AnalyticsController extends Controller
{
    protected AnalyticsService $analyticsService;
    protected ReportingService $reportingService;

    public function __construct(AnalyticsService $analyticsService, ReportingService $reportingService)
    {
        $this->analyticsService = $analyticsService;
        $this->reportingService = $reportingService;
    }

    /**
     * Get comprehensive dashboard analytics.
     */
    public function getDashboardAnalytics(Request $request): JsonResponse
    {
        try {
            $analytics = [
                'purchase_analytics' => $this->analyticsService->getPurchaseAnalytics($request),
                'delivery_analytics' => $this->analyticsService->getDeliveryAnalytics($request),
                'user_analytics' => $this->getUserAnalytics($request),
                'reading_analytics' => $this->getReadingAnalytics($request),
                'content_analytics' => $this->getContentAnalytics($request),
                'sales_analytics' => $this->getSalesAnalytics($request),
                'revenue_analytics' => $this->getRevenueAnalytics($request),
            ];

            return response()->json([
                'success' => true,
                'data' => $analytics,
                'message' => 'Dashboard analytics retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting dashboard analytics: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving dashboard analytics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get real-time analytics.
     */
    public function getRealTimeAnalytics(Request $request): JsonResponse
    {
        try {
            $realTimeData = [
                'live_user_activity' => $this->getLiveUserActivity(),
                'real_time_sales' => $this->getRealTimeSales(),
                'system_performance' => $this->getSystemPerformance(),
                'active_sessions' => $this->getActiveSessions(),
            ];

            return response()->json([
                'success' => true,
                'data' => $realTimeData,
                'message' => 'Real-time analytics retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting real-time analytics: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving real-time analytics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user analytics.
     */
    public function getUserAnalytics(Request $request): JsonResponse
    {
        try {
            $userAnalytics = [
                'user_segments' => $this->analyticsService->generateUserSegments(),
                'user_behavior_insights' => $this->getUserBehaviorInsights($request),
                'user_engagement_metrics' => $this->getUserEngagementMetrics($request),
                'user_retention_analysis' => $this->getUserRetentionAnalysis($request),
            ];

            return response()->json([
                'success' => true,
                'data' => $userAnalytics,
                'message' => 'User analytics retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting user analytics: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving user analytics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get reading analytics.
     */
    public function getReadingAnalytics(Request $request): JsonResponse
    {
        try {
            $readingAnalytics = $this->analyticsService->getAdvancedReadingAnalytics($request);

            return response()->json([
                'success' => true,
                'data' => $readingAnalytics,
                'message' => 'Reading analytics retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting reading analytics: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving reading analytics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get content analytics.
     */
    public function getContentAnalytics(Request $request): JsonResponse
    {
        try {
            $contentAnalytics = [
                'content_performance' => $this->getContentPerformance($request),
                'content_engagement' => $this->getContentEngagement($request),
                'content_recommendations' => $this->getContentRecommendations($request),
                'content_optimization' => $this->getContentOptimization($request),
            ];

            return response()->json([
                'success' => true,
                'data' => $contentAnalytics,
                'message' => 'Content analytics retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting content analytics: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving content analytics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get sales analytics.
     */
    public function getSalesAnalytics(Request $request): JsonResponse
    {
        try {
            $salesAnalytics = $this->analyticsService->getEnhancedSalesAnalytics($request);

            return response()->json([
                'success' => true,
                'data' => $salesAnalytics,
                'message' => 'Sales analytics retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting sales analytics: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving sales analytics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get revenue analytics.
     */
    public function getRevenueAnalytics(Request $request): JsonResponse
    {
        try {
            $revenueAnalytics = [
                'revenue_trends' => $this->analyticsService->getRevenueTrends(
                    $request->input('start_date', now()->subDays(30)->format('Y-m-d')),
                    $request->input('end_date', now()->format('Y-m-d'))
                ),
                'revenue_forecasting' => $this->getRevenueForecasting($request),
                'revenue_optimization' => $this->getRevenueOptimization($request),
            ];

            return response()->json([
                'success' => true,
                'data' => $revenueAnalytics,
                'message' => 'Revenue analytics retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting revenue analytics: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving revenue analytics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get comprehensive analytics report.
     */
    public function comprehensiveReport(Request $request): JsonResponse
    {
        try {
            $report = $this->analyticsService->generateAnalyticsReport($request);

            return response()->json([
                'success' => true,
                'data' => $report,
                'message' => 'Comprehensive analytics report generated successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error generating comprehensive report: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error generating comprehensive report',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get dashboard summary.
     */
    public function dashboardSummary(Request $request): JsonResponse
    {
        try {
            $summary = [
                'total_users' => \App\Models\User::count(),
                'total_books' => \App\Models\Book::count(),
                'total_orders' => \App\Models\Order::count(),
                'total_revenue' => \App\Models\Order::where('status', 'completed')->sum('total_amount'),
                'active_users_today' => \App\Models\User::where('last_login_at', '>=', now()->startOfDay())->count(),
                'orders_today' => \App\Models\Order::where('created_at', '>=', now()->startOfDay())->count(),
                'revenue_today' => \App\Models\Order::where('created_at', '>=', now()->startOfDay())
                    ->where('status', 'completed')
                    ->sum('total_amount'),
            ];

            return response()->json([
                'success' => true,
                'data' => $summary,
                'message' => 'Dashboard summary retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting dashboard summary: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving dashboard summary',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Private helper methods for specific analytics calculations
    private function getUserBehaviorInsights(Request $request): array
    {
        $dateRange = $request->input('date_range', '30');
        $startDate = now()->subDays($dateRange)->startOfDay();
        $endDate = now()->endOfDay();

        return [
            'user_activity_patterns' => $this->getUserActivityPatterns($startDate, $endDate),
            'user_preferences' => $this->getUserPreferences($startDate, $endDate),
            'user_conversion_funnel' => $this->getUserConversionFunnel($startDate, $endDate),
        ];
    }

    private function getUserEngagementMetrics(Request $request): array
    {
        $dateRange = $request->input('date_range', '30');
        $startDate = now()->subDays($dateRange)->startOfDay();
        $endDate = now()->endOfDay();

        return [
            'daily_active_users' => $this->getDailyActiveUsers($startDate, $endDate),
            'session_duration' => $this->getSessionDuration($startDate, $endDate),
            'engagement_score' => $this->getEngagementScore($startDate, $endDate),
        ];
    }

    private function getUserRetentionAnalysis(Request $request): array
    {
        $dateRange = $request->input('date_range', '30');
        $startDate = now()->subDays($dateRange)->startOfDay();
        $endDate = now()->endOfDay();

        return [
            'retention_rates' => $this->getRetentionRates($startDate, $endDate),
            'churn_analysis' => $this->getChurnAnalysis($startDate, $endDate),
            'user_lifetime_value' => $this->getUserLifetimeValue($startDate, $endDate),
        ];
    }

    private function getContentPerformance(Request $request): array
    {
        $dateRange = $request->input('date_range', '30');
        $startDate = now()->subDays($dateRange)->startOfDay();
        $endDate = now()->endOfDay();

        return [
            'top_performing_content' => \App\Models\ContentPerformance::getTopPerforming(10),
            'content_engagement_metrics' => $this->getContentEngagementMetrics($startDate, $endDate),
            'content_completion_rates' => $this->getContentCompletionRates($startDate, $endDate),
        ];
    }

    private function getContentEngagement(Request $request): array
    {
        $dateRange = $request->input('date_range', '30');
        $startDate = now()->subDays($dateRange)->startOfDay();
        $endDate = now()->endOfDay();

        return [
            'engagement_by_category' => $this->getEngagementByCategory($startDate, $endDate),
            'engagement_trends' => $this->getEngagementTrends($startDate, $endDate),
            'user_engagement_levels' => $this->getUserEngagementLevels($startDate, $endDate),
        ];
    }

    private function getContentRecommendations(Request $request): array
    {
        return [
            'recommendation_performance' => $this->getRecommendationPerformance(),
            'recommendation_accuracy' => $this->getRecommendationAccuracy(),
            'recommendation_optimization' => $this->getRecommendationOptimization(),
        ];
    }

    private function getContentOptimization(Request $request): array
    {
        return [
            'content_gaps' => $this->getContentGaps(),
            'optimization_opportunities' => $this->getOptimizationOpportunities(),
            'content_strategy_recommendations' => $this->getContentStrategyRecommendations(),
        ];
    }

    private function getRevenueForecasting(Request $request): array
    {
        $dateRange = $request->input('date_range', '30');
        $startDate = now()->subDays($dateRange)->startOfDay();
        $endDate = now()->endOfDay();

        return [
            'forecast_next_month' => $this->calculateRevenueForecast($startDate, $endDate),
            'forecast_accuracy' => $this->getForecastAccuracy($startDate, $endDate),
            'forecast_confidence' => $this->getForecastConfidence($startDate, $endDate),
        ];
    }

    private function getRevenueOptimization(Request $request): array
    {
        return [
            'pricing_optimization' => $this->getPricingOptimization(),
            'discount_effectiveness' => $this->getDiscountEffectiveness(),
            'revenue_leakage_analysis' => $this->getRevenueLeakageAnalysis(),
        ];
    }

    private function getLiveUserActivity(): array
    {
        // Get real-time user activity from cache or database
        return [
            'active_users' => \App\Models\User::where('last_login_at', '>=', now()->subMinutes(5))->count(),
            'recent_actions' => \App\Models\ActivityLog::latest()->limit(10)->get(),
            'current_sessions' => session()->all(),
        ];
    }

    private function getRealTimeSales(): array
    {
        return [
            'sales_today' => \App\Models\Order::where('created_at', '>=', now()->startOfDay())
                ->where('status', 'completed')
                ->sum('total_amount'),
            'orders_today' => \App\Models\Order::where('created_at', '>=', now()->startOfDay())->count(),
            'pending_orders' => \App\Models\Order::where('status', 'pending')->count(),
        ];
    }

    private function getSystemPerformance(): array
    {
        return [
            'memory_usage' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true),
            'execution_time' => microtime(true) - LARAVEL_START,
            'database_connections' => \DB::connection()->getPdo()->getAttribute(\PDO::ATTR_CONNECTION_STATUS),
        ];
    }

    private function getActiveSessions(): array
    {
        return [
            'total_sessions' => session()->all(),
            'session_count' => count(session()->all()),
        ];
    }

    // Additional helper methods for specific calculations
    private function getUserActivityPatterns($startDate, $endDate): array
    {
        $activities = \App\Models\ActivityLog::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        return $activities->toArray();
    }

    private function getUserPreferences($startDate, $endDate): array
    {
        // Implementation for user preferences analysis
        return [
            'preferred_categories' => [],
            'preferred_authors' => [],
            'reading_times' => [],
        ];
    }

    private function getUserConversionFunnel($startDate, $endDate): array
    {
        // Implementation for conversion funnel analysis
        return [
            'visitors' => 0,
            'registered' => 0,
            'purchased' => 0,
            'conversion_rate' => 0,
        ];
    }

    private function getDailyActiveUsers($startDate, $endDate): array
    {
        $dailyUsers = \App\Models\User::whereBetween('last_login_at', [$startDate, $endDate])
            ->selectRaw('DATE(last_login_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return $dailyUsers->toArray();
    }

    private function getSessionDuration($startDate, $endDate): array
    {
        $sessions = \App\Models\ReadingSession::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('AVG(duration_minutes) as avg_duration, COUNT(*) as session_count')
            ->first();

        return [
            'average_duration' => $sessions->avg_duration ?? 0,
            'session_count' => $sessions->session_count ?? 0,
        ];
    }

    private function getEngagementScore($startDate, $endDate): float
    {
        // Calculate engagement score based on various metrics
        $userCount = \App\Models\User::whereBetween('last_login_at', [$startDate, $endDate])->count();
        $activityCount = \App\Models\ActivityLog::whereBetween('created_at', [$startDate, $endDate])->count();
        
        return $userCount > 0 ? $activityCount / $userCount : 0;
    }

    private function getRetentionRates($startDate, $endDate): array
    {
        // Implementation for retention rate calculation
        return [
            'day_1_retention' => 0,
            'day_7_retention' => 0,
            'day_30_retention' => 0,
        ];
    }

    private function getChurnAnalysis($startDate, $endDate): array
    {
        // Implementation for churn analysis
        return [
            'churn_rate' => 0,
            'churned_users' => 0,
            'at_risk_users' => 0,
        ];
    }

    private function getUserLifetimeValue($startDate, $endDate): array
    {
        $users = \App\Models\User::withSum('orders as total_spent', 'total_amount')
            ->whereHas('orders', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->get();

        return [
            'average_lifetime_value' => $users->avg('total_spent') ?? 0,
            'total_lifetime_value' => $users->sum('total_spent') ?? 0,
            'high_value_customers' => $users->where('total_spent', '>=', 100)->count(),
        ];
    }

    private function getContentEngagementMetrics($startDate, $endDate): array
    {
        $content = \App\Models\ContentPerformance::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('AVG(engagement_score) as avg_engagement, AVG(completion_rate) as avg_completion')
            ->first();

        return [
            'average_engagement' => $content->avg_engagement ?? 0,
            'average_completion' => $content->avg_completion ?? 0,
        ];
    }

    private function getContentCompletionRates($startDate, $endDate): array
    {
        $completionRates = \App\Models\ContentPerformance::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('completion_rate, COUNT(*) as count')
            ->groupBy('completion_rate')
            ->orderBy('completion_rate')
            ->get();

        return $completionRates->toArray();
    }

    private function getEngagementByCategory($startDate, $endDate): array
    {
        // Implementation for engagement by category
        return [
            'fiction' => 0,
            'non_fiction' => 0,
            'mystery' => 0,
            'romance' => 0,
        ];
    }

    private function getEngagementTrends($startDate, $endDate): array
    {
        // Implementation for engagement trends
        return [
            'daily_engagement' => [],
            'weekly_trends' => [],
        ];
    }

    private function getUserEngagementLevels($startDate, $endDate): array
    {
        // Implementation for user engagement levels
        return [
            'high_engagement' => 0,
            'medium_engagement' => 0,
            'low_engagement' => 0,
        ];
    }

    private function getRecommendationPerformance(): array
    {
        // Implementation for recommendation performance
        return [
            'click_through_rate' => 0,
            'conversion_rate' => 0,
            'accuracy_score' => 0,
        ];
    }

    private function getRecommendationAccuracy(): array
    {
        // Implementation for recommendation accuracy
        return [
            'precision' => 0,
            'recall' => 0,
            'f1_score' => 0,
        ];
    }

    private function getRecommendationOptimization(): array
    {
        // Implementation for recommendation optimization
        return [
            'optimization_suggestions' => [],
            'performance_improvements' => [],
        ];
    }

    private function getContentGaps(): array
    {
        // Implementation for content gaps analysis
        return [
            'missing_categories' => [],
            'underserved_audiences' => [],
            'content_opportunities' => [],
        ];
    }

    private function getOptimizationOpportunities(): array
    {
        // Implementation for optimization opportunities
        return [
            'performance_improvements' => [],
            'content_updates' => [],
            'feature_additions' => [],
        ];
    }

    private function getContentStrategyRecommendations(): array
    {
        // Implementation for content strategy recommendations
        return [
            'content_priorities' => [],
            'publishing_schedule' => [],
            'audience_targeting' => [],
        ];
    }

    private function calculateRevenueForecast($startDate, $endDate): float
    {
        $orders = \App\Models\Order::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'completed')
            ->get();
        
        $dailyRevenue = $orders->groupBy(function ($order) {
            return $order->created_at->format('Y-m-d');
        })->map->sum('total_amount');
        
        $averageDailyRevenue = $dailyRevenue->avg();
        $daysInNextMonth = now()->addMonth()->daysInMonth;
        
        return $averageDailyRevenue * $daysInNextMonth;
    }

    private function getForecastAccuracy($startDate, $endDate): array
    {
        // Implementation for forecast accuracy
        return [
            'accuracy_percentage' => 0,
            'confidence_interval' => 0,
        ];
    }

    private function getForecastConfidence($startDate, $endDate): array
    {
        // Implementation for forecast confidence
        return [
            'confidence_level' => 0,
            'uncertainty_factors' => [],
        ];
    }

    private function getPricingOptimization(): array
    {
        // Implementation for pricing optimization
        return [
            'optimal_prices' => [],
            'price_elasticity' => [],
            'revenue_impact' => [],
        ];
    }

    private function getDiscountEffectiveness(): array
    {
        // Implementation for discount effectiveness
        return [
            'discount_performance' => [],
            'conversion_impact' => [],
            'revenue_impact' => [],
        ];
    }

    private function getRevenueLeakageAnalysis(): array
    {
        // Implementation for revenue leakage analysis
        return [
            'leakage_sources' => [],
            'potential_revenue' => [],
            'optimization_opportunities' => [],
        ];
    }
} 
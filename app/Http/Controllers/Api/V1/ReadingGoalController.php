<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ReadingProgress;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReadingGoalController extends Controller
{
    /**
     * Get user's reading goals.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            // Get goals from user preferences
            $goals = $user->reading_goals ?? [];
            
            // Calculate progress for each goal
            $goalsWithProgress = $this->calculateGoalProgress($user->id, $goals);

            return response()->json([
                'success' => true,
                'data' => $goalsWithProgress,
                'message' => 'Reading goals retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving reading goals: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving reading goals',
                'errors' => ['database' => ['An error occurred while retrieving reading goals.']]
            ], 500);
        }
    }

    /**
     * Create or update reading goals.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'books_per_month' => 'nullable|integer|min:1|max:100',
                'pages_per_day' => 'nullable|integer|min:1|max:1000',
                'reading_time_per_day' => 'nullable|integer|min:1|max:1440', // minutes
                'books_per_year' => 'nullable|integer|min:1|max:1000',
                'streak_days' => 'nullable|integer|min:1|max:365',
                'categories_to_explore' => 'nullable|array',
                'authors_to_read' => 'nullable|array',
            ]);

            $user = $request->user();
            $currentGoals = $user->reading_goals ?? [];
            
            // Merge new goals with existing ones
            $updatedGoals = array_merge($currentGoals, $request->only([
                'books_per_month',
                'pages_per_day',
                'reading_time_per_day',
                'books_per_year',
                'streak_days',
                'categories_to_explore',
                'authors_to_read',
            ]));

            // Update user preferences
            $user->update(['reading_goals' => $updatedGoals]);

            // Calculate progress for updated goals
            $goalsWithProgress = $this->calculateGoalProgress($user->id, $updatedGoals);

            return response()->json([
                'success' => true,
                'data' => $goalsWithProgress,
                'message' => 'Reading goals updated successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating reading goals: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating reading goals',
                'errors' => ['database' => ['An error occurred while updating reading goals.']]
            ], 500);
        }
    }

    /**
     * Get reading achievements.
     */
    public function achievements(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            $achievements = [
                'total_books_read' => ReadingProgress::where('user_id', $user->id)
                    ->where('is_finished', true)
                    ->count(),
                'current_streak' => $this->calculateReadingStreak($user->id),
                'longest_streak' => $this->getLongestStreak($user->id),
                'total_reading_time' => ReadingProgress::where('user_id', $user->id)
                    ->sum('reading_time_minutes'),
                'average_books_per_month' => $this->calculateAverageBooksPerMonth($user->id),
                'favorite_categories' => $this->getFavoriteCategories($user->id),
                'reading_milestones' => $this->getReadingMilestones($user->id),
                'recent_achievements' => $this->getRecentAchievements($user->id),
            ];

            return response()->json([
                'success' => true,
                'data' => $achievements,
                'message' => 'Reading achievements retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving reading achievements: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving reading achievements',
                'errors' => ['database' => ['An error occurred while retrieving reading achievements.']]
            ], 500);
        }
    }

    /**
     * Get reading statistics and insights.
     */
    public function insights(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            $insights = [
                'reading_patterns' => $this->getReadingPatterns($user->id),
                'preferred_genres' => $this->getPreferredGenres($user->id),
                'reading_speed' => $this->calculateReadingSpeed($user->id),
                'peak_reading_times' => $this->getPeakReadingTimes($user->id),
                'completion_rate' => $this->calculateCompletionRate($user->id),
                'reading_goals_progress' => $this->getGoalsProgress($user->id),
            ];

            return response()->json([
                'success' => true,
                'data' => $insights,
                'message' => 'Reading insights retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving reading insights: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving reading insights',
                'errors' => ['database' => ['An error occurred while retrieving reading insights.']]
            ], 500);
        }
    }

    /**
     * Calculate progress for reading goals.
     */
    private function calculateGoalProgress($userId, $goals): array
    {
        $progress = [];
        
        // Books per month goal
        if (isset($goals['books_per_month'])) {
            $currentMonthBooks = ReadingProgress::where('user_id', $userId)
                ->where('is_finished', true)
                ->whereMonth('finished_at', now()->month)
                ->whereYear('finished_at', now()->year)
                ->count();
            
            $progress['books_per_month'] = [
                'target' => $goals['books_per_month'],
                'current' => $currentMonthBooks,
                'percentage' => min(100, ($currentMonthBooks / $goals['books_per_month']) * 100),
            ];
        }

        // Pages per day goal
        if (isset($goals['pages_per_day'])) {
            $todayPages = ReadingProgress::where('user_id', $userId)
                ->whereDate('last_read_at', today())
                ->sum(DB::raw('current_page - COALESCE(previous_page, 0)'));
            
            $progress['pages_per_day'] = [
                'target' => $goals['pages_per_day'],
                'current' => $todayPages,
                'percentage' => min(100, ($todayPages / $goals['pages_per_day']) * 100),
            ];
        }

        // Reading time per day goal
        if (isset($goals['reading_time_per_day'])) {
            $todayReadingTime = ReadingProgress::where('user_id', $userId)
                ->whereDate('last_read_at', today())
                ->sum('reading_time_minutes');
            
            $progress['reading_time_per_day'] = [
                'target' => $goals['reading_time_per_day'],
                'current' => $todayReadingTime,
                'percentage' => min(100, ($todayReadingTime / $goals['reading_time_per_day']) * 100),
            ];
        }

        // Books per year goal
        if (isset($goals['books_per_year'])) {
            $currentYearBooks = ReadingProgress::where('user_id', $userId)
                ->where('is_finished', true)
                ->whereYear('finished_at', now()->year)
                ->count();
            
            $progress['books_per_year'] = [
                'target' => $goals['books_per_year'],
                'current' => $currentYearBooks,
                'percentage' => min(100, ($currentYearBooks / $goals['books_per_year']) * 100),
            ];
        }

        // Streak goal
        if (isset($goals['streak_days'])) {
            $currentStreak = $this->calculateReadingStreak($userId);
            
            $progress['streak_days'] = [
                'target' => $goals['streak_days'],
                'current' => $currentStreak,
                'percentage' => min(100, ($currentStreak / $goals['streak_days']) * 100),
            ];
        }

        return $progress;
    }

    /**
     * Calculate reading streak.
     */
    private function calculateReadingStreak($userId): int
    {
        $streak = 0;
        $currentDate = now()->startOfDay();

        while (true) {
            $hasRead = ReadingProgress::where('user_id', $userId)
                ->whereDate('last_read_at', $currentDate)
                ->exists();

            if ($hasRead) {
                $streak++;
                $currentDate->subDay();
            } else {
                break;
            }
        }

        return $streak;
    }

    /**
     * Get longest reading streak.
     */
    private function getLongestStreak($userId): int
    {
        // This would require tracking streak history
        // For now, return current streak as longest
        return $this->calculateReadingStreak($userId);
    }

    /**
     * Calculate average books per month.
     */
    private function calculateAverageBooksPerMonth($userId): float
    {
        $totalBooks = ReadingProgress::where('user_id', $userId)
            ->where('is_finished', true)
            ->count();

        $firstBook = ReadingProgress::where('user_id', $userId)
            ->where('is_finished', true)
            ->orderBy('finished_at', 'asc')
            ->first();

        if (!$firstBook) {
            return 0;
        }

        $monthsSinceFirst = now()->diffInMonths($firstBook->finished_at) + 1;
        return round($totalBooks / $monthsSinceFirst, 1);
    }

    /**
     * Get favorite categories.
     */
    private function getFavoriteCategories($userId): array
    {
        return ReadingProgress::where('reading_progress.user_id', $userId)
            ->where('reading_progress.is_finished', true)
            ->join('books', 'reading_progress.book_id', '=', 'books.id')
            ->join('book_category', 'books.id', '=', 'book_category.book_id')
            ->join('categories', 'book_category.category_id', '=', 'categories.id')
            ->select('categories.name', DB::raw('count(*) as count'))
            ->groupBy('categories.id', 'categories.name')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get()
            ->toArray();
    }

    /**
     * Get reading milestones.
     */
    private function getReadingMilestones($userId): array
    {
        $totalBooks = ReadingProgress::where('user_id', $userId)
            ->where('is_finished', true)
            ->count();

        $milestones = [
            ['name' => 'First Book', 'achieved' => $totalBooks >= 1, 'count' => 1],
            ['name' => '10 Books', 'achieved' => $totalBooks >= 10, 'count' => 10],
            ['name' => '25 Books', 'achieved' => $totalBooks >= 25, 'count' => 25],
            ['name' => '50 Books', 'achieved' => $totalBooks >= 50, 'count' => 50],
            ['name' => '100 Books', 'achieved' => $totalBooks >= 100, 'count' => 100],
        ];

        return $milestones;
    }

    /**
     * Get recent achievements.
     */
    private function getRecentAchievements($userId): array
    {
        $recentBooks = ReadingProgress::where('user_id', $userId)
            ->where('is_finished', true)
            ->with('book')
            ->orderBy('finished_at', 'desc')
            ->limit(5)
            ->get();

        $achievements = [];
        foreach ($recentBooks as $progress) {
            $achievements[] = [
                'type' => 'book_completed',
                'title' => "Completed '{$progress->book->title}'",
                'date' => $progress->finished_at,
                'description' => "You finished reading {$progress->book->title}",
            ];
        }

        return $achievements;
    }

    /**
     * Get reading patterns.
     */
    private function getReadingPatterns($userId): array
    {
        // This would analyze reading patterns over time
        // For now, return basic stats
        return [
            'average_reading_sessions_per_day' => 2.5,
            'preferred_reading_days' => ['Monday', 'Wednesday', 'Friday'],
            'average_session_duration' => 45, // minutes
        ];
    }

    /**
     * Get preferred genres.
     */
    private function getPreferredGenres($userId): array
    {
        return $this->getFavoriteCategories($userId);
    }

    /**
     * Calculate reading speed.
     */
    private function calculateReadingSpeed($userId): float
    {
        $totalPages = ReadingProgress::where('user_id', $userId)
            ->where('is_finished', true)
            ->sum('total_pages');

        $totalTime = ReadingProgress::where('user_id', $userId)
            ->where('is_finished', true)
            ->sum('reading_time_minutes');

        if ($totalTime == 0) {
            return 0;
        }

        return round($totalPages / $totalTime, 2); // pages per minute
    }

    /**
     * Get peak reading times.
     */
    private function getPeakReadingTimes($userId): array
    {
        // This would analyze when user reads most
        // For now, return default pattern
        return [
            'morning' => 20, // percentage
            'afternoon' => 30,
            'evening' => 40,
            'night' => 10,
        ];
    }

    /**
     * Calculate completion rate.
     */
    private function calculateCompletionRate($userId): float
    {
        $totalStarted = ReadingProgress::where('user_id', $userId)->count();
        $totalFinished = ReadingProgress::where('user_id', $userId)
            ->where('is_finished', true)
            ->count();

        if ($totalStarted == 0) {
            return 0;
        }

        return round(($totalFinished / $totalStarted) * 100, 1);
    }

    /**
     * Get goals progress.
     */
    private function getGoalsProgress($userId): array
    {
        $user = \App\Models\User::find($userId);
        return $this->calculateGoalProgress($userId, $user->reading_goals ?? []);
    }
} 
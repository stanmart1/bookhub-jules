<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\ReadingProgress;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReadingProgressController extends Controller
{
    /**
     * Get reading progress for a book.
     */
    public function show(Request $request, $bookId): JsonResponse
    {
        try {
            $user = $request->user();
            
            $progress = ReadingProgress::where('user_id', $user->id)
                ->where('book_id', $bookId)
                ->first();

            if (!$progress) {
                return response()->json([
                    'success' => false,
                    'message' => 'No reading progress found',
                    'errors' => ['progress' => ['No reading progress found for this book.']]
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $progress,
                'message' => 'Reading progress retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving reading progress: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving reading progress',
                'errors' => ['database' => ['An error occurred while retrieving reading progress.']]
            ], 500);
        }
    }

    /**
     * Update reading progress.
     */
    public function update(Request $request, $bookId): JsonResponse
    {
        try {
            $request->validate([
                'current_page' => 'required|integer|min:1',
                'total_pages' => 'nullable|integer|min:1',
                'reading_time_minutes' => 'nullable|integer|min:0',
                'is_finished' => 'boolean',
            ]);

            $user = $request->user();
            
            $progress = ReadingProgress::where('user_id', $user->id)
                ->where('book_id', $bookId)
                ->first();

            if (!$progress) {
                // Create new progress entry
                $progress = ReadingProgress::create([
                    'user_id' => $user->id,
                    'book_id' => $bookId,
                    'current_page' => $request->current_page,
                    'total_pages' => $request->total_pages,
                    'reading_time_minutes' => $request->reading_time_minutes ?? 0,
                    'is_finished' => $request->boolean('is_finished', false),
                    'last_read_at' => now(),
                ]);
            } else {
                // Update existing progress
                $progress->update([
                    'current_page' => $request->current_page,
                    'total_pages' => $request->total_pages ?? $progress->total_pages,
                    'reading_time_minutes' => $progress->reading_time_minutes + ($request->reading_time_minutes ?? 0),
                    'is_finished' => $request->boolean('is_finished', $progress->is_finished),
                    'last_read_at' => now(),
                ]);
            }

            // Calculate progress percentage
            if ($progress->total_pages > 0) {
                $progress->progress_percentage = round(($progress->current_page / $progress->total_pages) * 100, 2);
                $progress->save();
            }

            // Set finished date if book is completed
            if ($progress->is_finished && !$progress->finished_at) {
                $progress->update(['finished_at' => now()]);
            }

            return response()->json([
                'success' => true,
                'data' => $progress,
                'message' => 'Reading progress updated successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating reading progress: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating reading progress',
                'errors' => ['database' => ['An error occurred while updating reading progress.']]
            ], 500);
        }
    }

    /**
     * Start a reading session.
     */
    public function session(Request $request, $bookId): JsonResponse
    {
        try {
            $request->validate([
                'action' => 'required|in:start,end',
                'session_duration' => 'nullable|integer|min:1', // in minutes
            ]);

            $user = $request->user();
            $action = $request->action;

            if ($action === 'start') {
                // Start reading session
                $session = [
                    'user_id' => $user->id,
                    'book_id' => $bookId,
                    'started_at' => now(),
                    'status' => 'active',
                ];

                // Store session in cache or database
                cache()->put("reading_session_{$user->id}_{$bookId}", $session, now()->addHours(24));

                return response()->json([
                    'success' => true,
                    'data' => $session,
                    'message' => 'Reading session started'
                ]);

            } else {
                // End reading session
                $sessionKey = "reading_session_{$user->id}_{$bookId}";
                $session = cache()->get($sessionKey);

                if (!$session) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No active reading session found',
                        'errors' => ['session' => ['No active reading session found for this book.']]
                    ], 404);
                }

                $sessionDuration = $request->session_duration ?? 0;
                $session['ended_at'] = now();
                $session['duration_minutes'] = $sessionDuration;
                $session['status'] = 'completed';

                // Update reading progress with session data
                $progress = ReadingProgress::where('user_id', $user->id)
                    ->where('book_id', $bookId)
                    ->first();

                if ($progress) {
                    $progress->update([
                        'reading_time_minutes' => $progress->reading_time_minutes + $sessionDuration,
                        'last_read_at' => now(),
                    ]);
                }

                // Remove session from cache
                cache()->forget($sessionKey);

                return response()->json([
                    'success' => true,
                    'data' => $session,
                    'message' => 'Reading session ended'
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error managing reading session: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error managing reading session',
                'errors' => ['database' => ['An error occurred while managing the reading session.']]
            ], 500);
        }
    }

    /**
     * Get reading analytics.
     */
    public function analytics(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            $analytics = [
                'total_books_read' => ReadingProgress::where('user_id', $user->id)
                    ->where('is_finished', true)
                    ->count(),
                'total_reading_time' => ReadingProgress::where('user_id', $user->id)
                    ->sum('reading_time_minutes'),
                'average_reading_time_per_book' => ReadingProgress::where('user_id', $user->id)
                    ->where('is_finished', true)
                    ->avg('reading_time_minutes'),
                'currently_reading' => ReadingProgress::where('user_id', $user->id)
                    ->where('is_finished', false)
                    ->with('book')
                    ->get(),
                'recently_finished' => ReadingProgress::where('user_id', $user->id)
                    ->where('is_finished', true)
                    ->orderBy('finished_at', 'desc')
                    ->limit(5)
                    ->with('book')
                    ->get(),
                'reading_streak' => $this->calculateReadingStreak($user->id),
                'monthly_stats' => $this->getMonthlyStats($user->id),
            ];

            return response()->json([
                'success' => true,
                'data' => $analytics,
                'message' => 'Reading analytics retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving reading analytics: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving reading analytics',
                'errors' => ['database' => ['An error occurred while retrieving reading analytics.']]
            ], 500);
        }
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
     * Get monthly reading statistics.
     */
    private function getMonthlyStats($userId): array
    {
        $currentMonth = now()->startOfMonth();
        $lastMonth = now()->subMonth()->startOfMonth();

        return [
            'current_month' => [
                'books_finished' => ReadingProgress::where('user_id', $userId)
                    ->where('is_finished', true)
                    ->whereMonth('finished_at', $currentMonth->month)
                    ->whereYear('finished_at', $currentMonth->year)
                    ->count(),
                'reading_time' => ReadingProgress::where('user_id', $userId)
                    ->whereMonth('last_read_at', $currentMonth->month)
                    ->whereYear('last_read_at', $currentMonth->year)
                    ->sum('reading_time_minutes'),
            ],
            'last_month' => [
                'books_finished' => ReadingProgress::where('user_id', $userId)
                    ->where('is_finished', true)
                    ->whereMonth('finished_at', $lastMonth->month)
                    ->whereYear('finished_at', $lastMonth->year)
                    ->count(),
                'reading_time' => ReadingProgress::where('user_id', $userId)
                    ->whereMonth('last_read_at', $lastMonth->month)
                    ->whereYear('last_read_at', $lastMonth->year)
                    ->sum('reading_time_minutes'),
            ],
        ];
    }
}

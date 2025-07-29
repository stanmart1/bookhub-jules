<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\UserPreference;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    /**
     * Get user profile.
     */
    public function show(Request $request): JsonResponse
    {
        try {
            $user = $request->user()->load(['profile', 'preferences']);

            return response()->json([
                'success' => true,
                'data' => $user,
                'message' => 'Profile retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving profile: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving profile',
                'errors' => ['database' => ['An error occurred while retrieving the profile.']]
            ], 500);
        }
    }

    /**
     * Update user profile.
     */
    public function update(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'name' => 'sometimes|string|max:255',
                'date_of_birth' => 'sometimes|date|before:today',
                'phone' => 'sometimes|string|max:20',
                'bio' => 'sometimes|string|max:1000',
                'location' => 'sometimes|string|max:255',
                'website' => 'sometimes|url|max:255',
                'social_links' => 'sometimes|array',
                'reading_preferences' => 'sometimes|array',
            ]);

            $user = $request->user();

            // Update user basic info
            $user->update($request->only(['name', 'date_of_birth', 'phone']));

            // Update or create user profile
            $profileData = $request->only(['bio', 'location', 'website', 'social_links']);
            if (!empty($profileData)) {
                $user->profile()->updateOrCreate(
                    ['user_id' => $user->id],
                    $profileData
                );
            }

            // Update reading preferences
            if ($request->has('reading_preferences')) {
                $user->preferences()->updateOrCreate(
                    ['user_id' => $user->id],
                    ['reading_preferences' => $request->reading_preferences]
                );
            }

            $user->load(['profile', 'preferences']);

            return response()->json([
                'success' => true,
                'data' => $user,
                'message' => 'Profile updated successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating profile: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating profile',
                'errors' => ['database' => ['An error occurred while updating the profile.']]
            ], 500);
        }
    }

    /**
     * Update user password.
     */
    public function updatePassword(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:8|confirmed',
            ]);

            $user = $request->user();

            // Check current password
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Current password is incorrect',
                    'errors' => ['current_password' => ['The current password is incorrect.']]
                ], 400);
            }

            // Update password
            $user->update([
                'password' => Hash::make($request->new_password)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Password updated successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating password: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating password',
                'errors' => ['database' => ['An error occurred while updating the password.']]
            ], 500);
        }
    }

    /**
     * Update user avatar.
     */
    public function updateAvatar(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            $user = $request->user();

            // Delete old avatar if exists
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            // Store new avatar
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            
            $user->update(['avatar' => $avatarPath]);

            return response()->json([
                'success' => true,
                'data' => [
                    'avatar_url' => Storage::disk('public')->url($avatarPath)
                ],
                'message' => 'Avatar updated successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating avatar: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating avatar',
                'errors' => ['avatar' => ['An error occurred while updating the avatar.']]
            ], 500);
        }
    }

    /**
     * Delete user avatar.
     */
    public function deleteAvatar(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            $user->update(['avatar' => null]);

            return response()->json([
                'success' => true,
                'message' => 'Avatar deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting avatar: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error deleting avatar',
                'errors' => ['avatar' => ['An error occurred while deleting the avatar.']]
            ], 500);
        }
    }

    /**
     * Get user preferences.
     */
    public function getPreferences(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $preferences = $user->preferences;

            if (!$preferences) {
                // Create default preferences
                $preferences = $user->preferences()->create([
                    'reading_preferences' => UserPreference::getDefaultReadingPreferences(),
                    'notification_preferences' => UserPreference::getDefaultNotificationPreferences(),
                    'display_preferences' => UserPreference::getDefaultDisplayPreferences(),
                    'privacy_preferences' => UserPreference::getDefaultPrivacyPreferences(),
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => $preferences,
                'message' => 'Preferences retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving preferences: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving preferences',
                'errors' => ['database' => ['An error occurred while retrieving preferences.']]
            ], 500);
        }
    }

    /**
     * Update user preferences.
     */
    public function updatePreferences(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'reading_preferences' => 'sometimes|array',
                'notification_preferences' => 'sometimes|array',
                'display_preferences' => 'sometimes|array',
                'privacy_preferences' => 'sometimes|array',
                'language' => 'sometimes|string|max:10',
                'timezone' => 'sometimes|string|max:50',
                'email_notifications' => 'sometimes|boolean',
                'push_notifications' => 'sometimes|boolean',
            ]);

            $user = $request->user();
            $preferences = $user->preferences;

            if (!$preferences) {
                $preferences = $user->preferences()->create([
                    'reading_preferences' => UserPreference::getDefaultReadingPreferences(),
                    'notification_preferences' => UserPreference::getDefaultNotificationPreferences(),
                    'display_preferences' => UserPreference::getDefaultDisplayPreferences(),
                    'privacy_preferences' => UserPreference::getDefaultPrivacyPreferences(),
                ]);
            }

            $preferences->update($request->all());

            return response()->json([
                'success' => true,
                'data' => $preferences,
                'message' => 'Preferences updated successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating preferences: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating preferences',
                'errors' => ['database' => ['An error occurred while updating preferences.']]
            ], 500);
        }
    }

    /**
     * Get user statistics.
     */
    public function statistics(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            $stats = [
                'total_books_read' => $user->readingProgress()->where('is_finished', true)->count(),
                'total_reading_time' => $user->readingProgress()->sum('reading_time_minutes'),
                'current_streak' => $this->calculateReadingStreak($user->id),
                'total_bookmarks' => $user->bookmarks()->count(),
                'total_reviews' => $user->reviews()->count(),
                'wishlist_items' => $user->wishlist()->count(),
                'library_books' => $user->library()->count(),
                'favorite_categories' => $this->getFavoriteCategories($user->id),
                'reading_level' => $this->calculateReadingLevel($user->id),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
                'message' => 'Profile statistics retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving profile statistics: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving profile statistics',
                'errors' => ['database' => ['An error occurred while retrieving profile statistics.']]
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
            $hasRead = \App\Models\ReadingProgress::where('user_id', $userId)
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
     * Get favorite categories.
     */
    private function getFavoriteCategories($userId): array
    {
        return \App\Models\ReadingProgress::where('reading_progress.user_id', $userId)
            ->where('reading_progress.is_finished', true)
            ->join('books', 'reading_progress.book_id', '=', 'books.id')
            ->join('book_category', 'books.id', '=', 'book_category.book_id')
            ->join('categories', 'book_category.category_id', '=', 'categories.id')
            ->select('categories.name', \DB::raw('count(*) as count'))
            ->groupBy('categories.id', 'categories.name')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get()
            ->toArray();
    }

    /**
     * Calculate reading level based on books read.
     */
    private function calculateReadingLevel($userId): string
    {
        $totalBooks = \App\Models\ReadingProgress::where('user_id', $userId)
            ->where('is_finished', true)
            ->count();

        return match(true) {
            $totalBooks >= 100 => 'Expert Reader',
            $totalBooks >= 50 => 'Advanced Reader',
            $totalBooks >= 25 => 'Intermediate Reader',
            $totalBooks >= 10 => 'Developing Reader',
            $totalBooks >= 1 => 'Beginner Reader',
            default => 'New Reader',
        };
    }
}

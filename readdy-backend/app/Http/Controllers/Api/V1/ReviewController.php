<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Review;
use App\Models\UserLibrary;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReviewController extends Controller
{
    /**
     * Get reviews for a book.
     */
    public function index(Request $request, $bookId): JsonResponse
    {
        try {
            $request->validate([
                'rating' => 'nullable|integer|min:1|max:5',
                'verified_only' => 'boolean',
                'helpful_only' => 'boolean',
                'sort_by' => 'nullable|in:created_at,rating,helpful_votes',
                'sort_order' => 'nullable|in:asc,desc',
                'per_page' => 'nullable|integer|min:1|max:50',
            ]);

            $query = Review::with(['user:id,name,avatar'])
                ->where('book_id', $bookId)
                ->approved();

            // Apply filters
            if ($request->has('rating')) {
                $query->where('rating', $request->rating);
            }

            if ($request->boolean('verified_only')) {
                $query->verified();
            }

            if ($request->boolean('helpful_only')) {
                $query->helpful();
            }

            // Apply sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $reviews = $query->paginate($request->get('per_page', 10));

            // Get review statistics
            $stats = $this->getReviewStats($bookId);

            return response()->json([
                'success' => true,
                'data' => $reviews,
                'stats' => $stats,
                'message' => 'Reviews retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving reviews: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving reviews',
                'errors' => ['database' => ['An error occurred while retrieving reviews.']]
            ], 500);
        }
    }

    /**
     * Create a new review.
     */
    public function store(Request $request, $bookId): JsonResponse
    {
        try {
            $request->validate([
                'rating' => 'required|integer|min:1|max:5',
                'title' => 'nullable|string|max:255',
                'content' => 'required|string|min:10|max:2000',
            ]);

            $user = $request->user();

            // Check if user already reviewed this book
            $existingReview = Review::where('user_id', $user->id)
                ->where('book_id', $bookId)
                ->first();

            if ($existingReview) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have already reviewed this book',
                    'errors' => ['review' => ['You can only review a book once.']]
                ], 400);
            }

            // Check if user owns the book (for verified purchase)
            $isVerifiedPurchase = UserLibrary::where('user_id', $user->id)
                ->where('book_id', $bookId)
                ->exists();

            DB::beginTransaction();

            $review = Review::create([
                'user_id' => $user->id,
                'book_id' => $bookId,
                'rating' => $request->rating,
                'title' => $request->title,
                'content' => $request->content,
                'is_verified_purchase' => $isVerifiedPurchase,
                'is_approved' => true, // Auto-approve for now
            ]);

            // Log review creation activity
            \App\Services\ActivityService::logReviewActivity(
                'review_created',
                $user->id,
                $bookId,
                $review->id,
                $request
            );

            // Update book rating statistics
            $this->updateBookRatingStats($bookId);

            DB::commit();

            $review->load('user:id,name,avatar');

            return response()->json([
                'success' => true,
                'data' => $review,
                'message' => 'Review created successfully'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating review: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error creating review',
                'errors' => ['database' => ['An error occurred while creating the review.']]
            ], 500);
        }
    }

    /**
     * Update a review.
     */
    public function update(Request $request, $reviewId): JsonResponse
    {
        try {
            $request->validate([
                'rating' => 'nullable|integer|min:1|max:5',
                'title' => 'nullable|string|max:255',
                'content' => 'nullable|string|min:10|max:2000',
            ]);

            $user = $request->user();
            
            $review = Review::where('id', $reviewId)
                ->where('user_id', $user->id)
                ->first();

            if (!$review) {
                return response()->json([
                    'success' => false,
                    'message' => 'Review not found',
                    'errors' => ['review' => ['Review not found or you do not have permission to edit it.']]
                ], 404);
            }

            DB::beginTransaction();

            $review->update($request->only(['rating', 'title', 'content']));

            // Log review update activity
            \App\Services\ActivityService::logReviewActivity(
                'review_updated',
                $user->id,
                $review->book_id,
                $review->id,
                $request
            );

            // Update book rating statistics
            $this->updateBookRatingStats($review->book_id);

            DB::commit();

            $review->load('user:id,name,avatar');

            return response()->json([
                'success' => true,
                'data' => $review,
                'message' => 'Review updated successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating review: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating review',
                'errors' => ['database' => ['An error occurred while updating the review.']]
            ], 500);
        }
    }

    /**
     * Delete a review.
     */
    public function destroy(Request $request, $reviewId): JsonResponse
    {
        try {
            $user = $request->user();
            
            $review = Review::where('id', $reviewId)
                ->where('user_id', $user->id)
                ->first();

            if (!$review) {
                return response()->json([
                    'success' => false,
                    'message' => 'Review not found',
                    'errors' => ['review' => ['Review not found or you do not have permission to delete it.']]
                ], 404);
            }

            DB::beginTransaction();

            $bookId = $review->book_id;
            $review->delete();

            // Update book rating statistics
            $this->updateBookRatingStats($bookId);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Review deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting review: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error deleting review',
                'errors' => ['database' => ['An error occurred while deleting the review.']]
            ], 500);
        }
    }

    /**
     * Mark a review as helpful.
     */
    public function markHelpful($reviewId): JsonResponse
    {
        try {
            $review = Review::findOrFail($reviewId);
            $review->markAsHelpful();

            return response()->json([
                'success' => true,
                'data' => ['helpful_votes' => $review->helpful_votes],
                'message' => 'Review marked as helpful'
            ]);

        } catch (\Exception $e) {
            Log::error('Error marking review as helpful: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error marking review as helpful',
                'errors' => ['database' => ['An error occurred while marking the review as helpful.']]
            ], 500);
        }
    }

    /**
     * Get user's reviews.
     */
    public function userReviews(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            $reviews = Review::with(['book:id,title,author,cover_image'])
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->paginate($request->get('per_page', 10));

            return response()->json([
                'success' => true,
                'data' => $reviews,
                'message' => 'User reviews retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving user reviews: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving user reviews',
                'errors' => ['database' => ['An error occurred while retrieving user reviews.']]
            ], 500);
        }
    }

    /**
     * Get review statistics for a book.
     */
    private function getReviewStats($bookId): array
    {
        $stats = Review::where('book_id', $bookId)
            ->approved()
            ->selectRaw('
                COUNT(*) as total_reviews,
                AVG(rating) as average_rating,
                COUNT(CASE WHEN rating = 5 THEN 1 END) as five_star,
                COUNT(CASE WHEN rating = 4 THEN 1 END) as four_star,
                COUNT(CASE WHEN rating = 3 THEN 1 END) as three_star,
                COUNT(CASE WHEN rating = 2 THEN 1 END) as two_star,
                COUNT(CASE WHEN rating = 1 THEN 1 END) as one_star,
                COUNT(CASE WHEN is_verified_purchase = 1 THEN 1 END) as verified_reviews
            ')
            ->first();

        return [
            'total_reviews' => (int) $stats->total_reviews,
            'average_rating' => round($stats->average_rating, 1),
            'rating_distribution' => [
                'five_star' => (int) $stats->five_star,
                'four_star' => (int) $stats->four_star,
                'three_star' => (int) $stats->three_star,
                'two_star' => (int) $stats->two_star,
                'one_star' => (int) $stats->one_star,
            ],
            'verified_reviews' => (int) $stats->verified_reviews,
        ];
    }

    /**
     * Update book rating statistics.
     */
    private function updateBookRatingStats($bookId): void
    {
        $stats = Review::where('book_id', $bookId)
            ->approved()
            ->selectRaw('AVG(rating) as avg_rating, COUNT(*) as count')
            ->first();

        Book::where('id', $bookId)->update([
            'rating_average' => round($stats->avg_rating, 2),
            'rating_count' => $stats->count,
        ]);
    }
} 
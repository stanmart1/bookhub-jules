<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SearchController extends Controller
{
    /**
     * Basic search functionality.
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'q' => 'required|string|min:2|max:255',
                'category_id' => 'nullable|integer|exists:categories,id',
                'price_min' => 'nullable|numeric|min:0',
                'price_max' => 'nullable|numeric|min:0',
                'rating' => 'nullable|numeric|min:1|max:5',
                'sort_by' => 'nullable|in:title,author,price,rating,publication_date',
                'sort_order' => 'nullable|in:asc,desc',
                'per_page' => 'nullable|integer|min:1|max:100',
            ]);

            $query = $request->input('q');
            $categoryId = $request->input('category_id');
            $priceMin = $request->input('price_min');
            $priceMax = $request->input('price_max');
            $rating = $request->input('rating');
            $sortBy = $request->get('sort_by', 'relevance');
            $sortOrder = $request->get('sort_order', 'desc');
            $perPage = $request->get('per_page', 15);

            $booksQuery = Book::with(['categories', 'reviews'])
                ->where('status', 'published')
                ->where(function ($q) use ($query) {
                    $q->where('title', 'like', "%{$query}%")
                      ->orWhere('author', 'like', "%{$query}%")
                      ->orWhere('description', 'like', "%{$query}%")
                      ->orWhere('isbn', 'like', "%{$query}%");
                });

            // Apply filters
            if ($categoryId) {
                $booksQuery->whereHas('categories', function ($q) use ($categoryId) {
                    $q->where('categories.id', $categoryId);
                });
            }

            if ($priceMin !== null) {
                $booksQuery->where('price', '>=', $priceMin);
            }

            if ($priceMax !== null) {
                $booksQuery->where('price', '<=', $priceMax);
            }

            if ($rating) {
                $booksQuery->where('rating_average', '>=', $rating);
            }

            // Apply sorting
            switch ($sortBy) {
                case 'title':
                    $booksQuery->orderBy('title', $sortOrder);
                    break;
                case 'author':
                    $booksQuery->orderBy('author', $sortOrder);
                    break;
                case 'price':
                    $booksQuery->orderBy('price', $sortOrder);
                    break;
                case 'rating':
                    $booksQuery->orderBy('rating_average', $sortOrder);
                    break;
                case 'publication_date':
                    $booksQuery->orderBy('publication_date', $sortOrder);
                    break;
                default:
                    // Default relevance sorting (by rating and popularity)
                    $booksQuery->orderBy('rating_average', 'desc')
                               ->orderBy('view_count', 'desc');
                    break;
            }

            $books = $booksQuery->paginate($perPage);

            // Log search activity if user is authenticated
            if ($request->user()) {
                \App\Services\ActivityService::logSearch(
                    $request->user()->id,
                    $query,
                    $request->only(['category_id', 'price_min', 'price_max', 'rating']),
                    $request
                );
            }

            return response()->json([
                'success' => true,
                'data' => $books,
                'message' => 'Search completed successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error performing search: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error performing search',
                'errors' => ['search' => ['An error occurred while performing the search.']]
            ], 500);
        }
    }

    /**
     * Advanced search with multiple criteria.
     */
    public function advanced(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'title' => 'nullable|string|max:255',
                'author' => 'nullable|string|max:255',
                'category_ids' => 'nullable|array',
                'category_ids.*' => 'integer|exists:categories,id',
                'price_range' => 'nullable|array',
                'price_range.min' => 'nullable|numeric|min:0',
                'price_range.max' => 'nullable|numeric|min:0',
                'rating_range' => 'nullable|array',
                'rating_range.min' => 'nullable|numeric|min:1|max:5',
                'rating_range.max' => 'nullable|numeric|min:1|max:5',
                'publication_year' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
                'language' => 'nullable|string|max:10',
                'is_free' => 'nullable|boolean',
                'is_featured' => 'nullable|boolean',
                'is_bestseller' => 'nullable|boolean',
                'is_new_release' => 'nullable|boolean',
                'sort_by' => 'nullable|in:title,author,price,rating,publication_date,view_count',
                'sort_order' => 'nullable|in:asc,desc',
                'per_page' => 'nullable|integer|min:1|max:100',
            ]);

            $booksQuery = Book::with(['categories', 'reviews'])
                ->where('status', 'published');

            // Title filter
            if ($request->filled('title')) {
                $booksQuery->where('title', 'like', "%{$request->title}%");
            }

            // Author filter
            if ($request->filled('author')) {
                $booksQuery->where('author', 'like', "%{$request->author}%");
            }

            // Category filter
            if ($request->filled('category_ids')) {
                $booksQuery->whereHas('categories', function ($q) use ($request) {
                    $q->whereIn('categories.id', $request->category_ids);
                });
            }

            // Price range filter
            if ($request->filled('price_range.min')) {
                $booksQuery->where('price', '>=', $request->price_range['min']);
            }
            if ($request->filled('price_range.max')) {
                $booksQuery->where('price', '<=', $request->price_range['max']);
            }

            // Rating range filter
            if ($request->filled('rating_range.min')) {
                $booksQuery->where('rating_average', '>=', $request->rating_range['min']);
            }
            if ($request->filled('rating_range.max')) {
                $booksQuery->where('rating_average', '<=', $request->rating_range['max']);
            }

            // Publication year filter
            if ($request->filled('publication_year')) {
                $booksQuery->whereYear('publication_date', $request->publication_year);
            }

            // Language filter
            if ($request->filled('language')) {
                $booksQuery->where('language', $request->language);
            }

            // Boolean filters
            if ($request->has('is_free')) {
                $booksQuery->where('is_free', $request->boolean('is_free'));
            }

            if ($request->has('is_featured')) {
                $booksQuery->where('is_featured', $request->boolean('is_featured'));
            }

            if ($request->has('is_bestseller')) {
                $booksQuery->where('is_bestseller', $request->boolean('is_bestseller'));
            }

            if ($request->has('is_new_release')) {
                $booksQuery->where('is_new_release', $request->boolean('is_new_release'));
            }

            // Apply sorting
            $sortBy = $request->get('sort_by', 'relevance');
            $sortOrder = $request->get('sort_order', 'desc');

            switch ($sortBy) {
                case 'title':
                    $booksQuery->orderBy('title', $sortOrder);
                    break;
                case 'author':
                    $booksQuery->orderBy('author', $sortOrder);
                    break;
                case 'price':
                    $booksQuery->orderBy('price', $sortOrder);
                    break;
                case 'rating':
                    $booksQuery->orderBy('rating_average', $sortOrder);
                    break;
                case 'publication_date':
                    $booksQuery->orderBy('publication_date', $sortOrder);
                    break;
                case 'view_count':
                    $booksQuery->orderBy('view_count', $sortOrder);
                    break;
                default:
                    $booksQuery->orderBy('rating_average', 'desc')
                               ->orderBy('view_count', 'desc');
                    break;
            }

            $perPage = $request->get('per_page', 15);
            $books = $booksQuery->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $books,
                'message' => 'Advanced search completed successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error performing advanced search: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error performing advanced search',
                'errors' => ['search' => ['An error occurred while performing the advanced search.']]
            ], 500);
        }
    }

    /**
     * Get search suggestions.
     */
    public function suggestions(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'q' => 'required|string|min:1|max:255',
                'limit' => 'nullable|integer|min:1|max:20',
            ]);

            $query = $request->input('q');
            $limit = $request->get('limit', 10);

            $suggestions = [];

            // Book title suggestions
            $titleSuggestions = Book::where('title', 'like', "%{$query}%")
                ->where('status', 'published')
                ->select('title')
                ->distinct()
                ->limit($limit)
                ->pluck('title')
                ->toArray();

            $suggestions['titles'] = $titleSuggestions;

            // Author suggestions
            $authorSuggestions = Book::where('author', 'like', "%{$query}%")
                ->where('status', 'published')
                ->select('author')
                ->distinct()
                ->limit($limit)
                ->pluck('author')
                ->toArray();

            $suggestions['authors'] = $authorSuggestions;

            // Category suggestions
            $categorySuggestions = Category::where('name', 'like', "%{$query}%")
                ->where('is_active', true)
                ->select('name')
                ->distinct()
                ->limit($limit)
                ->pluck('name')
                ->toArray();

            $suggestions['categories'] = $categorySuggestions;

            return response()->json([
                'success' => true,
                'data' => $suggestions,
                'message' => 'Search suggestions retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving search suggestions: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving search suggestions',
                'errors' => ['suggestions' => ['An error occurred while retrieving search suggestions.']]
            ], 500);
        }
    }

    /**
     * Get book recommendations.
     */
    public function recommendations(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $limit = $request->get('limit', 10);

            $recommendations = [];

            if ($user) {
                // Get user's reading history and preferences
                $userCategories = DB::table('reading_progress')
                    ->join('books', 'reading_progress.book_id', '=', 'books.id')
                    ->join('book_category', 'books.id', '=', 'book_category.book_id')
                    ->join('categories', 'book_category.category_id', '=', 'categories.id')
                    ->where('reading_progress.user_id', $user->id)
                    ->select('categories.id', DB::raw('count(*) as count'))
                    ->groupBy('categories.id')
                    ->orderBy('count', 'desc')
                    ->limit(5)
                    ->pluck('categories.id')
                    ->toArray();

                if (!empty($userCategories)) {
                    // Recommend books from user's preferred categories
                    $recommendations['based_on_history'] = Book::with(['categories'])
                        ->whereHas('categories', function ($q) use ($userCategories) {
                            $q->whereIn('categories.id', $userCategories);
                        })
                        ->where('status', 'published')
                        ->whereNotIn('id', function ($q) use ($user) {
                            $q->select('book_id')
                              ->from('reading_progress')
                              ->where('user_id', $user->id);
                        })
                        ->orderBy('rating_average', 'desc')
                        ->limit($limit)
                        ->get();
                }
            }

            // Popular books
            $recommendations['popular'] = Book::with(['categories'])
                ->where('status', 'published')
                ->orderBy('view_count', 'desc')
                ->orderBy('rating_average', 'desc')
                ->limit($limit)
                ->get();

            // New releases
            $recommendations['new_releases'] = Book::with(['categories'])
                ->where('status', 'published')
                ->where('is_new_release', true)
                ->orderBy('publication_date', 'desc')
                ->limit($limit)
                ->get();

            // Bestsellers
            $recommendations['bestsellers'] = Book::with(['categories'])
                ->where('status', 'published')
                ->where('is_bestseller', true)
                ->orderBy('rating_average', 'desc')
                ->limit($limit)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $recommendations,
                'message' => 'Book recommendations retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving book recommendations: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving book recommendations',
                'errors' => ['recommendations' => ['An error occurred while retrieving book recommendations.']]
            ], 500);
        }
    }

    /**
     * Get trending books.
     */
    public function trending(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'period' => 'nullable|in:week,month,year',
                'limit' => 'nullable|integer|min:1|max:50',
            ]);

            $period = $request->get('period', 'week');
            $limit = $request->get('limit', 10);

            $dateFilter = match($period) {
                'week' => now()->subWeek(),
                'month' => now()->subMonth(),
                'year' => now()->subYear(),
                default => now()->subWeek(),
            };

            $trendingBooks = Book::with(['categories'])
                ->where('status', 'published')
                ->where('created_at', '>=', $dateFilter)
                ->orderBy('view_count', 'desc')
                ->orderBy('rating_average', 'desc')
                ->limit($limit)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $trendingBooks,
                'message' => 'Trending books retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving trending books: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving trending books',
                'errors' => ['trending' => ['An error occurred while retrieving trending books.']]
            ], 500);
        }
    }

    /**
     * Get search analytics for admin.
     */
    public function analytics(Request $request): JsonResponse
    {
        try {
            // Get search statistics from activity logs
            $searchStats = \App\Models\ActivityLog::where('action', 'search_performed')
                ->selectRaw('
                    COUNT(*) as total_searches,
                    COUNT(DISTINCT user_id) as unique_users,
                    COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as searches_this_week,
                    COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as searches_this_month
                ')
                ->first();

            // Get popular search terms
            $popularTerms = \App\Models\ActivityLog::where('action', 'search_performed')
                ->whereNotNull('properties->query')
                ->selectRaw('properties->>"$.query" as search_term, COUNT(*) as count')
                ->groupBy('search_term')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->get();

            // Get search trends over time
            $searchTrends = \App\Models\ActivityLog::where('action', 'search_performed')
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->orderBy('date', 'desc')
                ->limit(30)
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'total_searches' => (int) $searchStats->total_searches,
                    'unique_users' => (int) $searchStats->unique_users,
                    'searches_this_week' => (int) $searchStats->searches_this_week,
                    'searches_this_month' => (int) $searchStats->searches_this_month,
                    'popular_terms' => $popularTerms,
                    'search_trends' => $searchTrends,
                ],
                'message' => 'Search analytics retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving search analytics: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving search analytics',
                'errors' => ['database' => ['An error occurred while retrieving search analytics.']]
            ], 500);
        }
    }
}

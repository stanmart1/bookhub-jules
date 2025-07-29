<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\WishlistItem;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class WishlistController extends Controller
{
    /**
     * Get user's wishlist.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            $query = WishlistItem::with(['book.categories', 'book.reviews'])
                ->where('user_id', $user->id);

            // Apply filters
            if ($request->has('search')) {
                $search = $request->search;
                $query->whereHas('book', function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('author', 'like', "%{$search}%");
                });
            }

            if ($request->has('category_id')) {
                $query->whereHas('book.categories', function ($q) use ($request) {
                    $q->where('categories.id', $request->category_id);
                });
            }

            if ($request->boolean('with_notes_only')) {
                $query->withNotes();
            }

            // Apply sorting
            $sortBy = $request->get('sort_by', 'added_at');
            $sortOrder = $request->get('sort_order', 'desc');
            
            if ($sortBy === 'title') {
                $query->join('books', 'wishlist_items.book_id', '=', 'books.id')
                      ->orderBy('books.title', $sortOrder)
                      ->select('wishlist_items.*');
            } else {
                $query->orderBy($sortBy, $sortOrder);
            }

            $wishlist = $query->paginate($request->get('per_page', 15));

            return response()->json([
                'success' => true,
                'data' => $wishlist,
                'message' => 'Wishlist retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving wishlist: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving wishlist',
                'errors' => ['database' => ['An error occurred while retrieving the wishlist.']]
            ], 500);
        }
    }

    /**
     * Add a book to wishlist.
     */
    public function store(Request $request, $bookId): JsonResponse
    {
        try {
            $request->validate([
                'notes' => 'nullable|string|max:500',
            ]);

            $user = $request->user();

            // Check if book exists
            $book = Book::find($bookId);
            if (!$book) {
                return response()->json([
                    'success' => false,
                    'message' => 'Book not found',
                    'errors' => ['book' => ['The specified book does not exist.']]
                ], 404);
            }

            // Check if already in wishlist
            $existing = WishlistItem::where('user_id', $user->id)
                ->where('book_id', $bookId)
                ->first();

            if ($existing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Book already in wishlist',
                    'errors' => ['book' => ['This book is already in your wishlist.']]
                ], 400);
            }

            $wishlistItem = WishlistItem::create([
                'user_id' => $user->id,
                'book_id' => $bookId,
                'notes' => $request->notes,
                'added_at' => now(),
            ]);

            // Log wishlist activity
            \App\Services\ActivityService::logWishlistActivity(
                'book_added_to_wishlist',
                $user->id,
                $bookId,
                $request
            );

            $wishlistItem->load(['book:id,title,author,cover_image,price']);

            return response()->json([
                'success' => true,
                'data' => $wishlistItem,
                'message' => 'Book added to wishlist successfully'
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error adding book to wishlist: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error adding book to wishlist',
                'errors' => ['database' => ['An error occurred while adding the book to wishlist.']]
            ], 500);
        }
    }

    /**
     * Update wishlist item notes.
     */
    public function update(Request $request, $wishlistItemId): JsonResponse
    {
        try {
            $request->validate([
                'notes' => 'nullable|string|max:500',
            ]);

            $user = $request->user();
            
            $wishlistItem = WishlistItem::where('id', $wishlistItemId)
                ->where('user_id', $user->id)
                ->first();

            if (!$wishlistItem) {
                return response()->json([
                    'success' => false,
                    'message' => 'Wishlist item not found',
                    'errors' => ['wishlist' => ['Wishlist item not found or you do not have permission to edit it.']]
                ], 404);
            }

            $wishlistItem->update([
                'notes' => $request->notes,
            ]);

            $wishlistItem->load(['book:id,title,author,cover_image,price']);

            return response()->json([
                'success' => true,
                'data' => $wishlistItem,
                'message' => 'Wishlist item updated successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating wishlist item: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating wishlist item',
                'errors' => ['database' => ['An error occurred while updating the wishlist item.']]
            ], 500);
        }
    }

    /**
     * Remove a book from wishlist.
     */
    public function destroy(Request $request, $wishlistItemId): JsonResponse
    {
        try {
            $user = $request->user();
            
            $wishlistItem = WishlistItem::where('id', $wishlistItemId)
                ->where('user_id', $user->id)
                ->first();

            if (!$wishlistItem) {
                return response()->json([
                    'success' => false,
                    'message' => 'Wishlist item not found',
                    'errors' => ['wishlist' => ['Wishlist item not found or you do not have permission to delete it.']]
                ], 404);
            }

            // Log wishlist removal activity
            \App\Services\ActivityService::logWishlistActivity(
                'book_removed_from_wishlist',
                $user->id,
                $wishlistItem->book_id,
                $request
            );

            $wishlistItem->delete();

            return response()->json([
                'success' => true,
                'message' => 'Book removed from wishlist successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error removing book from wishlist: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error removing book from wishlist',
                'errors' => ['database' => ['An error occurred while removing the book from wishlist.']]
            ], 500);
        }
    }

    /**
     * Check if a book is in user's wishlist.
     */
    public function check(Request $request, $bookId): JsonResponse
    {
        try {
            $user = $request->user();
            
            $wishlistItem = WishlistItem::where('user_id', $user->id)
                ->where('book_id', $bookId)
                ->first();

            return response()->json([
                'success' => true,
                'data' => [
                    'in_wishlist' => $wishlistItem ? true : false,
                    'wishlist_item' => $wishlistItem,
                ],
                'message' => 'Wishlist status checked successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error checking wishlist status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error checking wishlist status',
                'errors' => ['database' => ['An error occurred while checking wishlist status.']]
            ], 500);
        }
    }

    /**
     * Get wishlist statistics.
     */
    public function stats(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            $stats = WishlistItem::where('user_id', $user->id)
                ->selectRaw('
                    COUNT(*) as total_items,
                    COUNT(CASE WHEN notes IS NOT NULL AND notes != "" THEN 1 END) as items_with_notes,
                    COUNT(CASE WHEN added_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as added_this_month
                ')
                ->first();

            // Get category distribution
            $categoryStats = WishlistItem::where('wishlist_items.user_id', $user->id)
                ->join('books', 'wishlist_items.book_id', '=', 'books.id')
                ->join('book_category', 'books.id', '=', 'book_category.book_id')
                ->join('categories', 'book_category.category_id', '=', 'categories.id')
                ->selectRaw('categories.name, COUNT(*) as count')
                ->groupBy('categories.id', 'categories.name')
                ->orderBy('count', 'desc')
                ->limit(5)
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'total_items' => (int) $stats->total_items,
                    'items_with_notes' => (int) $stats->items_with_notes,
                    'added_this_month' => (int) $stats->added_this_month,
                    'category_distribution' => $categoryStats,
                ],
                'message' => 'Wishlist statistics retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving wishlist statistics: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving wishlist statistics',
                'errors' => ['database' => ['An error occurred while retrieving wishlist statistics.']]
            ], 500);
        }
    }

    /**
     * Move wishlist item to library (purchase).
     */
    public function moveToLibrary(Request $request, $wishlistItemId): JsonResponse
    {
        try {
            $user = $request->user();
            
            $wishlistItem = WishlistItem::where('id', $wishlistItemId)
                ->where('user_id', $user->id)
                ->with('book')
                ->first();

            if (!$wishlistItem) {
                return response()->json([
                    'success' => false,
                    'message' => 'Wishlist item not found',
                    'errors' => ['wishlist' => ['Wishlist item not found or you do not have permission to access it.']]
                ], 404);
            }

            // Check if already in library
            $inLibrary = \App\Models\UserLibrary::where('user_id', $user->id)
                ->where('book_id', $wishlistItem->book_id)
                ->exists();

            if ($inLibrary) {
                return response()->json([
                    'success' => false,
                    'message' => 'Book already in library',
                    'errors' => ['library' => ['This book is already in your library.']]
                ], 400);
            }

            // This would typically involve payment processing
            // For now, we'll just add to library and remove from wishlist
            \App\Models\UserLibrary::create([
                'user_id' => $user->id,
                'book_id' => $wishlistItem->book_id,
                'purchase_date' => now(),
                'purchase_price' => $wishlistItem->book->price,
            ]);

            $wishlistItem->delete();

            return response()->json([
                'success' => true,
                'message' => 'Book moved to library successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error moving book to library: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error moving book to library',
                'errors' => ['database' => ['An error occurred while moving the book to library.']]
            ], 500);
        }
    }
} 
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Bookmark;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class BookmarkController extends Controller
{
    /**
     * Get bookmarks for a specific book.
     */
    public function index(Request $request, $bookId): JsonResponse
    {
        try {
            $user = $request->user();
            
            $bookmarks = Bookmark::where('user_id', $user->id)
                ->where('book_id', $bookId)
                ->orderBy('page_number', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $bookmarks,
                'message' => 'Bookmarks retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving bookmarks: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving bookmarks',
                'errors' => ['database' => ['An error occurred while retrieving bookmarks.']]
            ], 500);
        }
    }

    /**
     * Create a new bookmark.
     */
    public function store(Request $request, $bookId): JsonResponse
    {
        try {
            $request->validate([
                'page_number' => 'required|integer|min:1',
                'chapter' => 'nullable|string|max:255',
                'note' => 'nullable|string|max:1000',
            ]);

            $user = $request->user();
            
            // Check if bookmark already exists for this page
            $existing = Bookmark::where('user_id', $user->id)
                ->where('book_id', $bookId)
                ->where('page_number', $request->page_number)
                ->first();

            if ($existing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bookmark already exists for this page',
                    'errors' => ['page' => ['A bookmark already exists for page ' . $request->page_number . '.']]
                ], 400);
            }

            $bookmark = Bookmark::create([
                'user_id' => $user->id,
                'book_id' => $bookId,
                'page_number' => $request->page_number,
                'chapter' => $request->chapter,
                'note' => $request->note,
            ]);

            return response()->json([
                'success' => true,
                'data' => $bookmark,
                'message' => 'Bookmark created successfully'
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error creating bookmark: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error creating bookmark',
                'errors' => ['database' => ['An error occurred while creating the bookmark.']]
            ], 500);
        }
    }

    /**
     * Update a bookmark.
     */
    public function update(Request $request, $bookmarkId): JsonResponse
    {
        try {
            $request->validate([
                'page_number' => 'sometimes|integer|min:1',
                'chapter' => 'nullable|string|max:255',
                'note' => 'nullable|string|max:1000',
            ]);

            $user = $request->user();
            
            $bookmark = Bookmark::where('user_id', $user->id)
                ->where('id', $bookmarkId)
                ->first();

            if (!$bookmark) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bookmark not found',
                    'errors' => ['bookmark' => ['Bookmark not found.']]
                ], 404);
            }

            // Check if page number is being changed and if it conflicts
            if ($request->has('page_number') && $request->page_number !== $bookmark->page_number) {
                $existing = Bookmark::where('user_id', $user->id)
                    ->where('book_id', $bookmark->book_id)
                    ->where('page_number', $request->page_number)
                    ->where('id', '!=', $bookmarkId)
                    ->first();

                if ($existing) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Bookmark already exists for this page',
                        'errors' => ['page' => ['A bookmark already exists for page ' . $request->page_number . '.']]
                    ], 400);
                }
            }

            $bookmark->update($request->only(['page_number', 'chapter', 'note']));

            return response()->json([
                'success' => true,
                'data' => $bookmark,
                'message' => 'Bookmark updated successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating bookmark: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating bookmark',
                'errors' => ['database' => ['An error occurred while updating the bookmark.']]
            ], 500);
        }
    }

    /**
     * Delete a bookmark.
     */
    public function destroy($bookmarkId): JsonResponse
    {
        try {
            $user = request()->user();
            
            $bookmark = Bookmark::where('user_id', $user->id)
                ->where('id', $bookmarkId)
                ->first();

            if (!$bookmark) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bookmark not found',
                    'errors' => ['bookmark' => ['Bookmark not found.']]
                ], 404);
            }

            $bookmark->delete();

            return response()->json([
                'success' => true,
                'message' => 'Bookmark deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting bookmark: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error deleting bookmark',
                'errors' => ['database' => ['An error occurred while deleting the bookmark.']]
            ], 500);
        }
    }

    /**
     * Get all bookmarks for a user across all books.
     */
    public function userBookmarks(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            $bookmarks = Bookmark::with(['book'])
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->paginate(15);

            return response()->json([
                'success' => true,
                'data' => $bookmarks,
                'message' => 'User bookmarks retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving user bookmarks: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving user bookmarks',
                'errors' => ['database' => ['An error occurred while retrieving user bookmarks.']]
            ], 500);
        }
    }

    /**
     * Search bookmarks by note content.
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'q' => 'required|string|min:2|max:255',
                'book_id' => 'nullable|integer|exists:books,id',
            ]);

            $user = $request->user();
            $query = $request->input('q');
            $bookId = $request->input('book_id');

            $bookmarksQuery = Bookmark::with(['book'])
                ->where('user_id', $user->id)
                ->where(function ($q) use ($query) {
                    $q->where('note', 'like', "%{$query}%")
                      ->orWhere('chapter', 'like', "%{$query}%");
                });

            if ($bookId) {
                $bookmarksQuery->where('book_id', $bookId);
            }

            $bookmarks = $bookmarksQuery->orderBy('created_at', 'desc')->paginate(15);

            return response()->json([
                'success' => true,
                'data' => $bookmarks,
                'message' => 'Bookmark search completed successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error searching bookmarks: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error searching bookmarks',
                'errors' => ['search' => ['An error occurred while searching bookmarks.']]
            ], 500);
        }
    }

    /**
     * Get bookmark statistics for a user.
     */
    public function stats(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            $stats = [
                'total_bookmarks' => Bookmark::where('user_id', $user->id)->count(),
                'books_with_bookmarks' => Bookmark::where('user_id', $user->id)
                    ->distinct('book_id')
                    ->count('book_id'),
                'recent_bookmarks' => Bookmark::where('user_id', $user->id)
                    ->with('book')
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get(),
                'most_bookmarked_books' => Bookmark::where('user_library.user_id', $user->id)
                    ->join('books', 'bookmarks.book_id', '=', 'books.id')
                    ->select('books.title', 'books.id', \DB::raw('count(*) as bookmark_count'))
                    ->groupBy('books.id', 'books.title')
                    ->orderBy('bookmark_count', 'desc')
                    ->limit(5)
                    ->get(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
                'message' => 'Bookmark statistics retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving bookmark statistics: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving bookmark statistics',
                'errors' => ['database' => ['An error occurred while retrieving bookmark statistics.']]
            ], 500);
        }
    }
}

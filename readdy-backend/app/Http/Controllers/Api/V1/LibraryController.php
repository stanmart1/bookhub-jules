<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\UserLibrary;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LibraryController extends Controller
{
    /**
     * Display the user's library.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            $query = UserLibrary::with(['book.categories', 'book.files'])
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

            // Apply sorting
            $sortBy = $request->get('sort_by', 'purchase_date');
            $sortOrder = $request->get('sort_order', 'desc');
            
            if ($sortBy === 'title') {
                $query->join('books', 'user_library.book_id', '=', 'books.id')
                      ->orderBy('books.title', $sortOrder)
                      ->select('user_library.*');
            } else {
                $query->orderBy($sortBy, $sortOrder);
            }

            // Pagination
            $perPage = $request->get('per_page', 15);
            $library = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $library,
                'message' => 'Library retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving library: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving library',
                'errors' => ['database' => ['An error occurred while retrieving the library.']]
            ], 500);
        }
    }

    /**
     * Add a book to the user's library.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'book_id' => 'required|integer|exists:books,id',
                'purchase_price' => 'nullable|numeric|min:0',
                'payment_method' => 'nullable|string|max:100',
                'transaction_id' => 'nullable|string|max:255',
                'is_gift' => 'boolean',
                'gift_from' => 'nullable|string|max:255',
            ]);

            $user = $request->user();
            $bookId = $request->book_id;

            // Check if book is already in library
            $existing = UserLibrary::where('user_id', $user->id)
                ->where('book_id', $bookId)
                ->first();

            if ($existing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Book already in library',
                    'errors' => ['book' => ['This book is already in your library.']]
                ], 400);
            }

            // Create library entry
            $libraryEntry = UserLibrary::create([
                'user_id' => $user->id,
                'book_id' => $bookId,
                'purchase_date' => now(),
                'purchase_price' => $request->purchase_price ?? 0,
                'payment_method' => $request->payment_method,
                'transaction_id' => $request->transaction_id,
                'is_gift' => $request->boolean('is_gift', false),
                'gift_from' => $request->gift_from,
            ]);

            // Log book purchase activity
            \App\Services\ActivityService::logBookPurchase(
                $user->id, 
                $bookId, 
                $libraryEntry->purchase_price, 
                $request
            );

            $libraryEntry->load(['book.categories']);

            return response()->json([
                'success' => true,
                'data' => $libraryEntry,
                'message' => 'Book added to library successfully'
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error adding book to library: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error adding book to library',
                'errors' => ['database' => ['An error occurred while adding the book to library.']]
            ], 500);
        }
    }

    /**
     * Display a specific book in the user's library.
     */
    public function show(Request $request, $bookId): JsonResponse
    {
        try {
            $user = $request->user();
            
            $libraryEntry = UserLibrary::with(['book.categories', 'book.files', 'book.reviews'])
                ->where('user_id', $user->id)
                ->where('book_id', $bookId)
                ->first();

            if (!$libraryEntry) {
                return response()->json([
                    'success' => false,
                    'message' => 'Book not found in library',
                    'errors' => ['book' => ['This book is not in your library.']]
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $libraryEntry,
                'message' => 'Library book retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving library book: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving library book',
                'errors' => ['database' => ['An error occurred while retrieving the library book.']]
            ], 500);
        }
    }

    /**
     * Remove a book from the user's library.
     */
    public function destroy(Request $request, $bookId): JsonResponse
    {
        try {
            $user = $request->user();
            
            $libraryEntry = UserLibrary::where('user_id', $user->id)
                ->where('book_id', $bookId)
                ->first();

            if (!$libraryEntry) {
                return response()->json([
                    'success' => false,
                    'message' => 'Book not found in library',
                    'errors' => ['book' => ['This book is not in your library.']]
                ], 404);
            }

            $libraryEntry->delete();

            return response()->json([
                'success' => true,
                'message' => 'Book removed from library successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error removing book from library: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error removing book from library',
                'errors' => ['database' => ['An error occurred while removing the book from library.']]
            ], 500);
        }
    }

    /**
     * Organize the user's library.
     */
    public function organize(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'organization_type' => 'required|in:title,author,category,purchase_date,rating',
                'sort_order' => 'required|in:asc,desc',
            ]);

            $user = $request->user();
            
            // This method could be used to save user preferences
            // For now, we'll just return the organized library
            $query = UserLibrary::with(['book.categories'])
                ->where('user_id', $user->id);

            $organizationType = $request->organization_type;
            $sortOrder = $request->sort_order;

            switch ($organizationType) {
                case 'title':
                    $query->join('books', 'user_library.book_id', '=', 'books.id')
                          ->orderBy('books.title', $sortOrder)
                          ->select('user_library.*');
                    break;
                case 'author':
                    $query->join('books', 'user_library.book_id', '=', 'books.id')
                          ->orderBy('books.author', $sortOrder)
                          ->select('user_library.*');
                    break;
                case 'category':
                    $query->join('books', 'user_library.book_id', '=', 'books.id')
                          ->join('book_category', 'books.id', '=', 'book_category.book_id')
                          ->join('categories', 'book_category.category_id', '=', 'categories.id')
                          ->orderBy('categories.name', $sortOrder)
                          ->select('user_library.*');
                    break;
                case 'purchase_date':
                    $query->orderBy('purchase_date', $sortOrder);
                    break;
                case 'rating':
                    $query->join('books', 'user_library.book_id', '=', 'books.id')
                          ->orderBy('books.rating_average', $sortOrder)
                          ->select('user_library.*');
                    break;
            }

            $library = $query->paginate(15);

            return response()->json([
                'success' => true,
                'data' => $library,
                'message' => 'Library organized successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error organizing library: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error organizing library',
                'errors' => ['database' => ['An error occurred while organizing the library.']]
            ], 500);
        }
    }

    /**
     * Get library statistics.
     */
    public function stats(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            $stats = [
                'total_books' => UserLibrary::where('user_id', $user->id)->count(),
                'total_spent' => UserLibrary::where('user_id', $user->id)->sum('purchase_price'),
                'gift_books' => UserLibrary::where('user_id', $user->id)->where('is_gift', true)->count(),
                'recent_purchases' => UserLibrary::where('user_id', $user->id)
                    ->orderBy('purchase_date', 'desc')
                    ->limit(5)
                    ->with('book')
                    ->get(),
                'categories' => UserLibrary::where('user_library.user_id', $user->id)
                    ->join('books', 'user_library.book_id', '=', 'books.id')
                    ->join('book_category', 'books.id', '=', 'book_category.book_id')
                    ->join('categories', 'book_category.category_id', '=', 'categories.id')
                    ->select('categories.name', DB::raw('count(*) as count'))
                    ->groupBy('categories.id', 'categories.name')
                    ->orderBy('count', 'desc')
                    ->limit(10)
                    ->get(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
                'message' => 'Library statistics retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving library statistics: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving library statistics',
                'errors' => ['database' => ['An error occurred while retrieving library statistics.']]
            ], 500);
        }
    }
}

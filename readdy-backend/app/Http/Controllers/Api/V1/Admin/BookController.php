<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\BookStoreRequest;
use App\Http\Requests\BookUpdateRequest;
use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BookController extends Controller
{
    /**
     * Display a listing of books with admin features.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Book::with(['categories', 'reviews']);

            // Apply filters
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('author', 'like', "%{$search}%")
                      ->orWhere('isbn', 'like', "%{$search}%");
                });
            }

            if ($request->has('category_id')) {
                $query->whereHas('categories', function ($q) use ($request) {
                    $q->where('categories.id', $request->category_id);
                });
            }

            // Apply sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->get('per_page', 15);
            $books = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $books,
                'message' => 'Books retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving books: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving books',
                'errors' => ['database' => ['An error occurred while retrieving books.']]
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created book.
     */
    public function store(BookStoreRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $bookData = $request->validated();
            $categories = $bookData['categories'] ?? [];
            unset($bookData['categories']);

            // Create the book
            $book = Book::create($bookData);

            // Attach categories
            if (!empty($categories)) {
                $book->categories()->attach($categories);
            }

            // Load relationships for response
            $book->load(['categories']);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $book,
                'message' => 'Book created successfully'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating book: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error creating book',
                'errors' => ['database' => ['An error occurred while creating the book.']]
            ], 500);
        }
    }

    /**
     * Display the specified book.
     */
    public function show(Book $book): JsonResponse
    {
        try {
            $book->load(['categories', 'reviews.user', 'files']);

            return response()->json([
                'success' => true,
                'data' => $book,
                'message' => 'Book retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving book: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving book',
                'errors' => ['database' => ['An error occurred while retrieving the book.']]
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified book.
     */
    public function update(BookUpdateRequest $request, Book $book): JsonResponse
    {
        try {
            DB::beginTransaction();

            $bookData = $request->validated();
            $categories = $bookData['categories'] ?? null;
            unset($bookData['categories']);

            // Update the book
            $book->update($bookData);

            // Update categories if provided
            if ($categories !== null) {
                $book->categories()->sync($categories);
            }

            // Load relationships for response
            $book->load(['categories']);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $book,
                'message' => 'Book updated successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating book: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error updating book',
                'errors' => ['database' => ['An error occurred while updating the book.']]
            ], 500);
        }
    }

    /**
     * Remove the specified book.
     */
    public function destroy(Book $book): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Check if book has any purchases
            $hasPurchases = $book->owners()->exists();
            if ($hasPurchases) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete book with existing purchases',
                    'errors' => ['purchases' => ['This book has been purchased by users and cannot be deleted.']]
                ], 400);
            }

            // Delete the book (cascade will handle related records)
            $book->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Book deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting book: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error deleting book',
                'errors' => ['database' => ['An error occurred while deleting the book.']]
            ], 500);
        }
    }

    /**
     * Get book statistics for admin dashboard.
     */
    public function stats(): JsonResponse
    {
        try {
            $stats = [
                'total_books' => Book::count(),
                'published_books' => Book::where('status', 'published')->count(),
                'draft_books' => Book::where('status', 'draft')->count(),
                'featured_books' => Book::where('is_featured', true)->count(),
                'bestsellers' => Book::where('is_bestseller', true)->count(),
                'new_releases' => Book::where('is_new_release', true)->count(),
                'free_books' => Book::where('is_free', true)->count(),
                'total_views' => Book::sum('view_count'),
                'total_downloads' => Book::sum('download_count'),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
                'message' => 'Book statistics retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving book statistics: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving book statistics',
                'errors' => ['database' => ['An error occurred while retrieving statistics.']]
            ], 500);
        }
    }
}

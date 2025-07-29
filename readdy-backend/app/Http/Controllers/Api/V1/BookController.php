<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\Request;

class BookController extends Controller
{
    /**
     * Get all books with pagination and filtering.
     */
    public function index(Request $request)
    {
        $query = Book::with(['categories', 'reviews'])
                    ->published();

        // Apply filters
        if ($request->has('category')) {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('author', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->has('price_min')) {
            $query->where('price', '>=', $request->price_min);
        }

        if ($request->has('price_max')) {
            $query->where('price', '<=', $request->price_max);
        }

        if ($request->has('rating')) {
            $query->where('rating_average', '>=', $request->rating);
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $books = $query->paginate($request->get('per_page', 12));

        return response()->json([
            'success' => true,
            'data' => $books,
            'meta' => [
                'filters' => $request->only(['category', 'search', 'price_min', 'price_max', 'rating']),
                'sorting' => [
                    'sort_by' => $sortBy,
                    'sort_order' => $sortOrder,
                ],
            ],
        ]);
    }

    /**
     * Get a specific book.
     */
    public function show(Request $request, $id)
    {
        $book = Book::with(['categories', 'reviews.user', 'files'])
                    ->published()
                    ->findOrFail($id);

        // Increment view count
        $book->increment('view_count');

        // Log book view activity if user is authenticated
        if ($request->user()) {
            \App\Services\ActivityService::logBookView($request->user()->id, $book->id, $request);
        }

        return response()->json([
            'success' => true,
            'data' => $book,
        ]);
    }

    /**
     * Get featured books.
     */
    public function featured()
    {
        $books = Book::with(['categories'])
                    ->featured()
                    ->published()
                    ->take(8)
                    ->get();

        return response()->json([
            'success' => true,
            'data' => $books,
        ]);
    }

    /**
     * Get bestsellers.
     */
    public function bestsellers()
    {
        $books = Book::with(['categories'])
                    ->bestsellers()
                    ->published()
                    ->take(8)
                    ->get();

        return response()->json([
            'success' => true,
            'data' => $books,
        ]);
    }

    /**
     * Get new releases.
     */
    public function newReleases()
    {
        $books = Book::with(['categories'])
                    ->newReleases()
                    ->published()
                    ->take(8)
                    ->get();

        return response()->json([
            'success' => true,
            'data' => $books,
        ]);
    }
}

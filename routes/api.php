<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BookController;
use App\Http\Controllers\Api\V1\SearchController;
use App\Http\Controllers\Api\V1\LibraryController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\BookFileController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::prefix('v1')->group(function () {
    // Authentication routes
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);
    
    // Public book routes
    Route::get('/books', [BookController::class, 'index']);
    Route::get('/books/featured', [BookController::class, 'featured']);
    Route::get('/books/bestsellers', [BookController::class, 'bestsellers']);
    Route::get('/books/new-releases', [BookController::class, 'newReleases']);
    Route::get('/books/{id}', [BookController::class, 'show']);

    // Public search routes
    Route::get('/search', [SearchController::class, 'search']);
    Route::get('/search/advanced', [SearchController::class, 'advanced']);
    Route::get('/search/suggestions', [SearchController::class, 'suggestions']);
    Route::get('/recommendations', [SearchController::class, 'recommendations']);
    Route::get('/trending', [SearchController::class, 'trending']);
});

// Protected routes
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    // Authentication
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/user', [AuthController::class, 'user']);
    
    // Profile routes
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::put('/profile/password', [ProfileController::class, 'updatePassword']);
    Route::put('/profile/avatar', [ProfileController::class, 'updateAvatar']);
    Route::delete('/profile/avatar', [ProfileController::class, 'deleteAvatar']);
    Route::get('/profile/preferences', [ProfileController::class, 'getPreferences']);
    Route::put('/profile/preferences', [ProfileController::class, 'updatePreferences']);

    // Book file routes (with access control)
    Route::get('/books/{book}/files', [BookFileController::class, 'list']);
    Route::post('/books/{book}/files/{file}/download', [BookFileController::class, 'download']);

    // User library routes
    Route::get('/library', [LibraryController::class, 'index']);
    Route::post('/library', [LibraryController::class, 'store']);
    Route::get('/library/{bookId}', [LibraryController::class, 'show']);
    Route::delete('/library/{bookId}', [LibraryController::class, 'destroy']);
    Route::put('/library/organize', [LibraryController::class, 'organize']);
    Route::get('/library/stats', [LibraryController::class, 'stats']);
}); 
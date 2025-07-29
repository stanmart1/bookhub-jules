<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BookController;
use App\Http\Controllers\Api\V1\SearchController;
use App\Http\Controllers\Api\V1\LibraryController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\BookFileController;
use App\Http\Controllers\Api\V1\ReadingProgressController;
use App\Http\Controllers\Api\V1\BookmarkController;
use App\Http\Controllers\Api\V1\ReadingGoalController;
use App\Http\Controllers\Api\V1\ReviewController;
use App\Http\Controllers\Api\V1\WishlistController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\Admin\BookController as AdminBookController;
use App\Http\Controllers\Api\V1\Admin\OrderController as AdminOrderController;

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
    Route::get('/search/analytics', [SearchController::class, 'analytics']);

    // Public review routes
    Route::get('/books/{bookId}/reviews', [ReviewController::class, 'index']);

    // Public payment gateway routes
    Route::get('/payments/gateways', [PaymentController::class, 'gateways']);
    
    // Public webhook routes (called by payment gateways)
    Route::post('/payments/webhook/{gatewayName}', [PaymentController::class, 'webhook']);
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

    // Reading progress routes
    Route::get('/books/{bookId}/progress', [ReadingProgressController::class, 'show']);
    Route::put('/books/{bookId}/progress', [ReadingProgressController::class, 'update']);
    Route::post('/books/{bookId}/sessions', [ReadingProgressController::class, 'session']);
    Route::get('/reading/analytics', [ReadingProgressController::class, 'analytics']);

    // Bookmark routes
    Route::get('/books/{bookId}/bookmarks', [BookmarkController::class, 'index']);
    Route::post('/books/{bookId}/bookmarks', [BookmarkController::class, 'store']);
    Route::put('/bookmarks/{bookmarkId}', [BookmarkController::class, 'update']);
    Route::delete('/bookmarks/{bookmarkId}', [BookmarkController::class, 'destroy']);
    Route::get('/bookmarks', [BookmarkController::class, 'userBookmarks']);
    Route::get('/bookmarks/search', [BookmarkController::class, 'search']);
    Route::get('/bookmarks/stats', [BookmarkController::class, 'stats']);

    // Reading goals routes
    Route::get('/reading-goals', [ReadingGoalController::class, 'index']);
    Route::post('/reading-goals', [ReadingGoalController::class, 'store']);
    Route::get('/reading-goals/achievements', [ReadingGoalController::class, 'achievements']);
    Route::get('/reading-goals/insights', [ReadingGoalController::class, 'insights']);

    // Review routes
    Route::post('/books/{bookId}/reviews', [ReviewController::class, 'store']);
    Route::put('/reviews/{reviewId}', [ReviewController::class, 'update']);
    Route::delete('/reviews/{reviewId}', [ReviewController::class, 'destroy']);
    Route::post('/reviews/{reviewId}/helpful', [ReviewController::class, 'markHelpful']);
    Route::get('/reviews', [ReviewController::class, 'userReviews']);

    // Wishlist routes
    Route::get('/wishlist', [WishlistController::class, 'index']);
    Route::post('/wishlist/{bookId}', [WishlistController::class, 'store']);
    Route::put('/wishlist/{wishlistItemId}', [WishlistController::class, 'update']);
    Route::delete('/wishlist/{wishlistItemId}', [WishlistController::class, 'destroy']);
    Route::get('/wishlist/{bookId}/check', [WishlistController::class, 'check']);
    Route::get('/wishlist/stats', [WishlistController::class, 'stats']);
    Route::post('/wishlist/{wishlistItemId}/move-to-library', [WishlistController::class, 'moveToLibrary']);

    // Notification routes
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{notificationId}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/{notificationId}', [NotificationController::class, 'destroy']);
    Route::get('/notifications/stats', [NotificationController::class, 'stats']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);

    // Payment routes
    Route::post('/payments/initialize', [PaymentController::class, 'initialize']);
    Route::post('/payments/verify/{gatewayName}', [PaymentController::class, 'verify']);
    Route::get('/payments/history', [PaymentController::class, 'history']);
    Route::get('/payments/{paymentId}', [PaymentController::class, 'show']);
    Route::post('/payments/{paymentId}/retry', [PaymentController::class, 'retry']);

    // Order routes
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{orderId}', [OrderController::class, 'show']);
    Route::post('/orders/{orderId}/cancel', [OrderController::class, 'cancel']);
    Route::get('/orders/{orderId}/status-history', [OrderController::class, 'statusHistory']);
    Route::get('/orders/{orderId}/receipt', [OrderController::class, 'receipt']);
    Route::get('/orders/{orderId}/receipt/download', [OrderController::class, 'downloadReceipt']);
    
    // Order notification routes
    Route::get('/orders/notifications', [OrderController::class, 'notifications']);
    Route::post('/orders/notifications/{notificationId}/read', [OrderController::class, 'markNotificationAsRead']);
    Route::get('/orders/notifications/unread-count', [OrderController::class, 'unreadNotificationsCount']);
});

// Admin routes
Route::prefix('v1')->middleware(['auth:sanctum', 'admin'])->group(function () {
    // Admin book management
    Route::apiResource('admin/books', AdminBookController::class);
    Route::get('admin/books/stats', [AdminBookController::class, 'stats']);
    
    // Admin book file management
    Route::post('admin/books/{book}/files', [BookFileController::class, 'upload']);
    Route::delete('admin/books/{book}/files/{file}', [BookFileController::class, 'delete']);
    Route::put('admin/books/{book}/files/{file}/primary', [BookFileController::class, 'setPrimary']);

    // Admin payment routes
    Route::get('admin/payments/statistics', [PaymentController::class, 'statistics']);

    // Admin order routes
    Route::get('admin/orders', [AdminOrderController::class, 'index']);
    Route::get('admin/orders/{id}', [AdminOrderController::class, 'show']);
    Route::put('admin/orders/{id}', [AdminOrderController::class, 'update']);
    Route::post('admin/orders/{id}/cancel', [AdminOrderController::class, 'cancel']);
    Route::post('admin/orders/{id}/refund', [AdminOrderController::class, 'processRefund']);
    Route::get('admin/orders/{id}/refund-info', [AdminOrderController::class, 'getRefundInfo']);
    Route::get('admin/orders/statistics', [AdminOrderController::class, 'statistics']);
    Route::get('admin/orders/status/{status}', [AdminOrderController::class, 'byStatus']);
    Route::get('admin/orders/analytics/report', [AdminOrderController::class, 'analyticsReport']);
    Route::get('admin/orders/export', [AdminOrderController::class, 'exportReport']);
}); 
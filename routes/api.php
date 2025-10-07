<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\SellerController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\NotificationController;

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

// Test route
Route::get('/test', function () {
    return response()->json(['message' => 'API is working']);
});

// Test API route
Route::get('/test-api', function () {
    return response()->json(['message' => 'API is working correctly']);
});

// Test endpoint to verify backend is working
Route::get('/test-update', function () {
    return response()->json(['message' => 'Backend is working', 'timestamp' => now()]);
});

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/verify-email', [AuthController::class, 'verifyEmail']);
Route::post('/resend-verification-code', [AuthController::class, 'resendVerificationCode']);
Route::post('/send-password-reset-code', [AuthController::class, 'sendPasswordResetCode']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// Public product routes
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{id}', [CategoryController::class, 'show']);

// Public post routes
Route::get('/posts/sponsored', [PostController::class, 'getSponsoredPosts']);

// Public user profile route
Route::get('/users/{id}', [AuthController::class, 'show']);

// Public reviews routes
Route::get('/reviews/{product_id}', [ReviewController::class, 'index']);

// Public like count route
Route::get('/products/{id}/like-count', [LikeController::class, 'getLikeCount']);

// Development only route for getting verification codes
Route::post('/get-verification-code', [AuthController::class, 'getVerificationCode']);

// Newsletter routes (public)
Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe']);
Route::delete('/newsletter/unsubscribe', [NewsletterController::class, 'unsubscribe']);
Route::get('/newsletter/check-subscription', [NewsletterController::class, 'checkSubscription']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::put('/user/profile', [AuthController::class, 'updateProfile']);
    Route::post('/user/profile', [AuthController::class, 'updateProfile']); // For multipart file uploads
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    Route::post('/verify-password', [AuthController::class, 'verifyPassword']);

    // Security routes
    Route::get('/user/sessions', [AuthController::class, 'getSessions']);
    Route::delete('/user/sessions/{id}', [AuthController::class, 'terminateSession']);
    Route::get('/user/activity', [AuthController::class, 'getActivity']);
    Route::get('/user/export', [AuthController::class, 'exportUserData']);

    // Seller routes
    Route::prefix('seller')->group(function () {
        Route::get('/products', [SellerController::class, 'getProducts']);
        Route::get('/products/{id}', [SellerController::class, 'getProduct']);
        Route::post('/products', [SellerController::class, 'createProduct']);
        Route::put('/products/{id}', [SellerController::class, 'updateProduct']);
        Route::delete('/products/{id}', [SellerController::class, 'deleteProduct']);
    });

    // Cart routes
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart', [CartController::class, 'store']);
    Route::put('/cart/{id}', [CartController::class, 'update']);
    Route::delete('/cart/{id}', [CartController::class, 'destroy']);

    // Order routes
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::post('/orders', [OrderController::class, 'store']);

    // Wishlist routes
    Route::get('/wishlist', [WishlistController::class, 'index']);
    Route::post('/wishlist', [WishlistController::class, 'store']);
    Route::delete('/wishlist/{id}', [WishlistController::class, 'destroy']);

    // Review routes
    Route::post('/reviews/{product_id}', [ReviewController::class, 'store']);
    Route::post('/reviews/{product_id}/reply/{review_id}', [ReviewController::class, 'storeReply']);
    Route::put('/reviews/{id}', [ReviewController::class, 'update']);
    Route::delete('/reviews/{id}', [ReviewController::class, 'destroy']);

    // Like routes
    Route::post('/products/{id}/like', [LikeController::class, 'like']);
    Route::delete('/products/{id}/like', [LikeController::class, 'unlike']);
    Route::get('/products/{id}/like-status', [LikeController::class, 'getLikeStatus']);

    // Payment routes
    Route::post('/payment/process', [PaymentController::class, 'process']);
    Route::get('/payment/status/{id}', [PaymentController::class, 'status']);

    // Admin routes
    Route::middleware('admin')->group(function () {
        Route::get('/admin/dashboard', [AdminController::class, 'dashboard']);
        Route::get('/admin/users', [AdminController::class, 'users']);
        Route::get('/admin/products', [AdminController::class, 'products']);
        Route::post('/admin/products', [AdminController::class, 'createProduct']);
        Route::put('/admin/products/{id}', [AdminController::class, 'updateProduct']);
        Route::delete('/admin/products/{id}', [AdminController::class, 'deleteProduct']);
        Route::post('/admin/products/{id}/sponsor', [AdminController::class, 'toggleSponsor']);
        Route::post('/admin/expire-sponsorships', [AdminController::class, 'expireSponsorships']);
    });

    // Follow routes
    Route::post('/follow/{id}', [FollowController::class, 'follow']);
    Route::post('/follow/{id}/notify', [FollowController::class, 'toggleNotification']);
    Route::get('/follow/{id}/status', [FollowController::class, 'getStatus']);
    Route::get('/follow/{id}/followers', [FollowController::class, 'getFollowers']);
    Route::get('/follow/{id}/following', [FollowController::class, 'getFollowing']);

    // Post routes
    Route::get('/posts', [PostController::class, 'index']);
    Route::post('/posts', [PostController::class, 'store']);
    Route::get('/posts/{id}', [PostController::class, 'show']);
    Route::put('/posts/{id}', [PostController::class, 'update']);
    Route::delete('/posts/{id}', [PostController::class, 'destroy']);
    Route::post('/posts/{id}/like', [PostController::class, 'like']);
    Route::post('/posts/{id}/favorite', [PostController::class, 'favorite']);
    Route::get('/posts/{id}/comments', [PostController::class, 'getComments']);
    Route::post('/posts/{id}/comments', [PostController::class, 'addComment']);

    // Notification routes
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'getUnreadCount']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);
    Route::delete('/notifications', [NotificationController::class, 'destroyAll']);
});

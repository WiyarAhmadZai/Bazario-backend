<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\AdminController;

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
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::put('/user/profile', [AuthController::class, 'updateProfile']);
    Route::post('/user/profile', [AuthController::class, 'updateProfile']); // For multipart file uploads

    // Product routes
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{id}', [ProductController::class, 'show']);

    // Category routes
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{id}', [CategoryController::class, 'show']);

    // Cart routes
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart', [CartController::class, 'store']);
    Route::put('/cart/{id}', [CartController::class, 'update']);
    Route::delete('/cart/{id}', [CartController::class, 'destroy']);
    Route::delete('/cart', [CartController::class, 'clear']);

    // Order routes
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::post('/orders', [OrderController::class, 'store']);

    // Wishlist routes
    Route::get('/wishlist', [WishlistController::class, 'index']);
    Route::post('/wishlist', [WishlistController::class, 'store']);
    Route::delete('/wishlist/{id}', [WishlistController::class, 'destroy']);

    // Review routes
    Route::get('/products/{productId}/reviews', [ReviewController::class, 'index']);
    Route::post('/products/{productId}/reviews', [ReviewController::class, 'store']);

    // Payment routes
    Route::post('/checkout', [OrderController::class, 'checkout']);
    Route::post('/payments/process/{orderId}', [PaymentController::class, 'processPayment']);
    Route::post('/payments/webhook/{gateway}', [PaymentController::class, 'handleWebhook']);
    Route::post('/payments/confirm-bank-transfer/{orderId}', [PaymentController::class, 'confirmBankTransfer']);

    // Seller routes
    Route::get('/seller/products', [ProductController::class, 'sellerProducts']);
    Route::post('/seller/products', [ProductController::class, 'store']);
    Route::put('/seller/products/{id}', [ProductController::class, 'update']);
    Route::delete('/seller/products/{id}', [ProductController::class, 'destroy']);

    // Admin routes
    Route::middleware('admin')->group(function () {
        // Product admin routes
        Route::get('/admin/products/pending', [AdminController::class, 'getPendingProducts']);
        Route::put('/admin/products/{id}/approve', [AdminController::class, 'approveProduct']);
        Route::put('/admin/products/{id}/reject', [AdminController::class, 'rejectProduct']);

        // Category admin routes
        Route::post('/categories', [CategoryController::class, 'store']);
        Route::put('/categories/{id}', [CategoryController::class, 'update']);
        Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

        // Order admin routes
        Route::put('/orders/{id}/status', [OrderController::class, 'updateStatus']);

        // Review admin routes
        Route::put('/reviews/{id}', [ReviewController::class, 'update']);
        Route::delete('/reviews/{id}', [ReviewController::class, 'destroy']);

        // Payment admin routes
        Route::get('/admin/bank-transfers/pending', [AdminController::class, 'getPendingBankTransfers']);
        Route::put('/admin/bank-transfers/{id}/approve', [AdminController::class, 'approveBankTransfer']);
        Route::put('/admin/bank-transfers/{id}/reject', [AdminController::class, 'rejectBankTransfer']);

        // Commission admin routes
        Route::get('/admin/commission', [AdminController::class, 'getCommissionSettings']);
        Route::put('/admin/commission', [AdminController::class, 'updateCommissionSettings']);

        // User admin routes
        Route::get('/admin/users', [AdminController::class, 'getUsers']);
        Route::put('/admin/users/{id}', [AdminController::class, 'manageUser']);

        // Report routes
        Route::get('/admin/reports/sales', [AdminController::class, 'getSalesReport']);
        Route::get('/admin/reports/top-sellers', [AdminController::class, 'getTopSellers']);
    });
});

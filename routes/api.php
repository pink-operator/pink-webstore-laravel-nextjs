<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\VerificationController;
use App\Http\Controllers\Api\PasswordResetController;
use Illuminate\Support\Facades\Route;

// Note: for debugging API routes
Route::get('/test', function() {
    return response()->json(['message' => 'API routes are working!']);
});

// Public routes (removed throttling for testing)
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

// Email verification routes - make verify endpoint accessible without auth
Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])
    ->name('verification.verify');

// Password reset routes - simplified to ensure they get registered
Route::post('/auth/forgot-password', function (\Illuminate\Http\Request $request) {
    return app(PasswordResetController::class)->forgotPassword($request);
});

Route::post('/auth/reset-password', function (\Illuminate\Http\Request $request) {
    return app(PasswordResetController::class)->resetPassword($request);
})->name('password.reset');

// Product routes (some public, some protected)
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/search', [ProductController::class, 'search']);
Route::get('/products/{product}', [ProductController::class, 'show']);

// Categories
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{category}', [CategoryController::class, 'show']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/user', [AuthController::class, 'user']);
    
    // Email verification resend route - make sure this is defined correctly
    Route::post('/auth/email/verification-notification', [VerificationController::class, 'resend'])
        ->middleware('throttle:6,1');
    
    // Profile
    Route::put('/auth/profile', [ProfileController::class, 'update']);
    Route::delete('/auth/profile', [ProfileController::class, 'destroy']);

    // Products (admin only)
    Route::middleware(['admin'])->group(function () {
        Route::post('/products', [ProductController::class, 'store']);
        Route::put('/products/{product}', [ProductController::class, 'update']);
        Route::delete('/products/{product}', [ProductController::class, 'destroy']);
    });

    // Orders
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);

    // Order status (admin only)
    Route::middleware(['admin'])->group(function () {
        Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus']);
    });

    // Categories (admin only)
    Route::middleware(['admin'])->group(function () {
        Route::post('/categories', [CategoryController::class, 'store']);
        Route::put('/categories/{category}', [CategoryController::class, 'update']);
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);
    });
});

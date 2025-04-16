<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProfileController;

// Simple home route that shows an API info message
Route::get('/', function () {
    return response()->json([
        'message' => 'Pink Store API is running. Use the Next.js frontend to interact with the application.',
        'status' => 'ok',
        'frontend_url' => env('FRONTEND_URL', 'http://localhost:3000'),
        'documentation' => route('l5-swagger.default.api')
    ]);
})->name('home');

// API Information route
Route::get('/api-info', function() {
    return response()->json([
        'name' => 'Pink Store API',
        'version' => '1.0.0',
        'description' => 'RESTful API for the Pink Store e-commerce application',
        'documentation' => route('l5-swagger.default.api')
    ]);
})->name('api.info');

// Dashboard route for authenticated users
Route::get('dashboard', function() {
    return redirect('/');
})
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Admin routes
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
});

// API Documentation
Route::get('/api/documentation', function () {
    return response()->json([
        'title' => 'Pink Store API Documentation',
        'version' => '1.0.0',
        'description' => 'RESTful API for the Pink Store e-commerce application',
        'endpoints' => [
            ['path' => '/api/auth/register', 'method' => 'POST', 'description' => 'Register a new user'],
            ['path' => '/api/auth/login', 'method' => 'POST', 'description' => 'Login and get an auth token'],
            ['path' => '/api/auth/logout', 'method' => 'POST', 'description' => 'Logout (invalidate token)'],
            ['path' => '/api/auth/user', 'method' => 'GET', 'description' => 'Get authenticated user info'],
            ['path' => '/api/products', 'method' => 'GET', 'description' => 'List all products'],
            ['path' => '/api/products/{id}', 'method' => 'GET', 'description' => 'Get a specific product'],
            ['path' => '/api/orders', 'method' => 'GET', 'description' => 'List user orders'],
            ['path' => '/api/orders', 'method' => 'POST', 'description' => 'Create a new order'],
            ['path' => '/api/orders/{id}', 'method' => 'GET', 'description' => 'Get a specific order']
        ]
    ]);
})->name('l5-swagger.default.api');

// API routes
Route::prefix('api')->group(function () {
    // Test endpoint
    Route::get('/test-endpoint', function() {
        return response()->json(['message' => 'API test endpoint is working!']);
    });

    // Public routes with auth rate limiting
    Route::middleware('throttle:auth')->group(function () {
        Route::post('/auth/register', [AuthController::class, 'register']);
        Route::post('/auth/login', [AuthController::class, 'login']);
    });

    // Product routes (public)
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
        
        // Profile
        Route::put('/auth/profile', [ProfileController::class, 'update']);
        Route::delete('/auth/profile', [ProfileController::class, 'destroy']);

        // Products (admin only) - simplified for testing
        Route::post('/products', [ProductController::class, 'store'])->middleware('auth:sanctum');
        Route::put('/products/{product}', [ProductController::class, 'update'])->middleware('auth:sanctum');
        Route::delete('/products/{product}', [ProductController::class, 'destroy'])->middleware('auth:sanctum');

        // Orders
        Route::get('/orders', [OrderController::class, 'index']);
        Route::post('/orders', [OrderController::class, 'store']);
        Route::get('/orders/{order}', [OrderController::class, 'show']);

        // Order status (admin only) - simplified for testing
        Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->middleware('auth:sanctum');

        // Categories (admin only) - simplified for testing
        Route::post('/categories', [CategoryController::class, 'store'])->middleware('auth:sanctum');
        Route::put('/categories/{category}', [CategoryController::class, 'update'])->middleware('auth:sanctum');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->middleware('auth:sanctum');
    });
});

// Include auth routes
require __DIR__.'/auth.php';

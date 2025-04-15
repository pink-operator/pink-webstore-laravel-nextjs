<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;

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

// Include auth routes
require __DIR__.'/auth.php';

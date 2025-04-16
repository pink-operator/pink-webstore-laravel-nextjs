<?php

namespace Tests\Helpers;

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\App;

class TestHelper
{
    /**
     * Register auth routes without rate limiting for testing
     */
    public static function registerAuthRoutesWithoutRateLimit(): void
    {
        // Clear any existing routes to prevent duplicates
        Route::getRoutes()->refreshNameLookups();
        
        // Define auth routes without throttling middleware
        Route::post('/api/auth/register', [AuthController::class, 'register']);
        Route::post('/api/auth/login', [AuthController::class, 'login']);
        
        // Protected routes (these still need auth middleware)
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/api/auth/logout', [AuthController::class, 'logout']);
            Route::get('/api/auth/user', [AuthController::class, 'user']);
        });
        
        // Disable rate limiting for tests
        self::disableAllRateLimiting();
    }
    
    /**
     * Disable rate limiting for all tests by removing throttle middleware
     */
    public static function disableAllRateLimiting(): void
    {
        // This creates a dummy rate limiter that always returns null (no limit)
        RateLimiter::for('auth', function () {
            return null;
        });
        
        RateLimiter::for('api', function () {
            return null;
        });
        
        RateLimiter::for('web', function () {
            return null;
        });
        
        // For Laravel tests, we can also disable the throttle middleware
        if (App::environment('testing')) {
            // Disable the throttle middleware by aliasing it to a pass-through middleware
            app()->bind(\Illuminate\Routing\Middleware\ThrottleRequests::class, function () {
                return new class {
                    public function handle($request, $next, ...$params)
                    {
                        return $next($request);
                    }
                };
            });
        }
    }
}
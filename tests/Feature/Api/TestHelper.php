<?php

namespace Tests\Feature\Api;

use Illuminate\Support\Facades\Route;

class TestHelper
{
    /**
     * Configure test routes without rate limiting for authentication tests
     */
    public static function registerAuthRoutesWithoutRateLimit(): void
    {
        // Re-register the authentication routes without rate limiting
        // This allows tests to run without being affected by rate limiting
        Route::post('/api/auth/register', [\App\Http\Controllers\Api\AuthController::class, 'register']);
        Route::post('/api/auth/login', [\App\Http\Controllers\Api\AuthController::class, 'login']);
    }
}
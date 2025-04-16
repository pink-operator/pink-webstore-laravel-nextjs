<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;

class ApiTestCase extends TestCase
{
    use RefreshDatabase;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Define auth routes without rate limiting middleware
        $this->defineRoutesWithoutRateLimiting();
    }
    
    protected function defineRoutesWithoutRateLimiting(): void
    {
        // Clear existing routes to prevent conflicts
        Route::getRoutes()->refreshNameLookups();
        
        // Define auth routes manually without the rate limiting middleware
        Route::post('/api/auth/register', [AuthController::class, 'register'])
            ->name('auth.register');
            
        Route::post('/api/auth/login', [AuthController::class, 'login'])
            ->name('auth.login');
            
        Route::post('/api/auth/logout', [AuthController::class, 'logout'])
            ->middleware('auth:sanctum')
            ->name('auth.logout');
            
        Route::get('/api/auth/user', [AuthController::class, 'user'])
            ->middleware('auth:sanctum')
            ->name('auth.user');
    }
    
    protected function actingAsCustomer($user = null)
    {
        $user = $user ?: User::factory()->create(['role' => 'customer']);
        Sanctum::actingAs($user);
        return $user;
    }
    
    protected function actingAsAdmin($user = null)
    {
        $user = $user ?: User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($user);
        return $user;
    }
}
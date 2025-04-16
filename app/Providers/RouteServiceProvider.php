<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/dashboard';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            // API routes must be registered first to ensure proper precedence
            Route::middleware(['api'])
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            // Sanctum routes for token handling
            Route::middleware(['web'])
                ->group(base_path('routes/sanctum.php'));

            // Auth routes (non-API)
            Route::middleware(['web'])
                ->group(base_path('routes/auth.php'));

            // Web routes last
            Route::middleware(['web'])
                ->group(base_path('routes/web.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return $request->user()
                ? Limit::perMinute(60)->by($request->user()->id)
                : Limit::perMinute(30)->by($request->ip());
        });

        // Fixed auth rate limiter to use proper Limit object instead of integer
        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('admin', function (Request $request) {
            return $request->user() && $request->user()->isAdmin()
                ? Limit::perMinute(120)->by($request->user()->id)
                : Limit::perMinute(5)->by($request->ip());
        });
    }
}

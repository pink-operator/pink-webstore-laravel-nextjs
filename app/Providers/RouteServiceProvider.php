<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    public const HOME = '/dashboard';

    public function boot(): void
    {
        // Configure rate limiters
        $this->configureRateLimiting();

        $this->routes(function () {
            // API routes must be registered first to ensure proper precedence
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            // Sanctum routes for CSRF cookie
            Route::middleware('web')
                ->group(base_path('routes/sanctum.php'));

            // Web routes last
            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }

    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return $request->user()
                ? Limit::perMinute(60)->by($request->user()->id)
                : Limit::perMinute(30)->by($request->ip());
        });

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

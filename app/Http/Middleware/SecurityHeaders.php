<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Security Headers
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'accelerometer=(), camera=(), geolocation=(), gyroscope=(), magnetometer=(), microphone=(), payment=(), usb=()');

        // Rate Limit Headers
        if ($request->is('api/*')) {
            $key = 'api:'.$request->ip();
            if ($request->user()) {
                $key = 'api:'.$request->user()->id;
            }

            $limiter = RateLimiter::for('api', function () use ($request) {
                return $request->user()
                    ? \Illuminate\Cache\RateLimiting\Limit::perMinute(60)->by($request->user()->id)
                    : \Illuminate\Cache\RateLimiting\Limit::perMinute(30)->by($request->ip());
            });

            $response->headers->set('X-RateLimit-Limit', $limiter->maxAttempts);
            $response->headers->set('X-RateLimit-Remaining', $limiter->remaining($key));
            $response->headers->set('X-RateLimit-Reset', now()->addMinutes(1)->getTimestamp());
        }

        if (!app()->environment('local')) {
            $response->headers->set('Content-Security-Policy', "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self' data:;");
        }

        return $response;
    }
}

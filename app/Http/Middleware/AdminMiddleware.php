<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            return redirect()->route('home')->with('error', 'Unauthorized access.');
        }

        return $next($request);
    }
}

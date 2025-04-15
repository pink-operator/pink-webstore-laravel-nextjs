<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    protected $except = [
        // Allow Swagger UI to make API calls without CSRF token
        'api/documentation',
        'api/documentation/*',
    ];
}

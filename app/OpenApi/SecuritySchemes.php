<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT',
    description: 'Enter your bearer token in the format: Bearer <token>'
)]
#[OA\SecurityScheme(
    securityScheme: 'csrf',
    type: 'apiKey',
    name: 'X-XSRF-TOKEN',
    in: 'header',
    description: 'CSRF token required for mutation operations'
)]
class SecuritySchemes {}

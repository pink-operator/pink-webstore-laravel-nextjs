<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: "1.0.0",
    title: "Pink Store E-Commerce API",
    description: "API documentation for Pink Store e-commerce platform",
    contact: new OA\Contact(
        name: "Pink Store Support",
        email: "support@pinkstore.com"
    )
)]
#[OA\Server(
    url: "http://localhost:8000",
    description: "Local development server"
)]
#[OA\Server(
    url: "https://api.pinkstore.com",
    description: "Production server"
)]
#[OA\ExternalDocumentation(
    description: "Find out more about Pink Store",
    url: "https://pinkstore.com/docs"
)]
class Info {}

<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'CreateProductRequest',
    required: ['name', 'description', 'price', 'stock_quantity'],
    properties: [
        new OA\Property(property: 'name', type: 'string', maxLength: 255),
        new OA\Property(property: 'description', type: 'string'),
        new OA\Property(property: 'price', type: 'number', format: 'float', minimum: 0),
        new OA\Property(property: 'stock_quantity', type: 'integer', minimum: 0),
        new OA\Property(property: 'featured', type: 'boolean'),
    ]
)]
#[OA\Schema(
    schema: 'UpdateProductRequest',
    properties: [
        new OA\Property(property: 'name', type: 'string', maxLength: 255),
        new OA\Property(property: 'description', type: 'string'),
        new OA\Property(property: 'price', type: 'number', format: 'float', minimum: 0),
        new OA\Property(property: 'stock_quantity', type: 'integer', minimum: 0),
        new OA\Property(property: 'featured', type: 'boolean'),
    ]
)]
#[OA\Schema(
    schema: 'CreateOrderRequest',
    required: ['items'],
    properties: [
        new OA\Property(
            property: 'items',
            type: 'array',
            items: new OA\Items(
                required: ['product_id', 'quantity'],
                properties: [
                    new OA\Property(property: 'product_id', type: 'integer'),
                    new OA\Property(property: 'quantity', type: 'integer', minimum: 1),
                ]
            )
        ),
    ]
)]
#[OA\Schema(
    schema: 'UpdateOrderStatusRequest',
    required: ['status'],
    properties: [
        new OA\Property(
            property: 'status',
            type: 'string',
            enum: ['pending', 'processing', 'completed', 'cancelled']
        ),
    ]
)]
#[OA\Schema(
    schema: 'LoginRequest',
    required: ['email', 'password'],
    properties: [
        new OA\Property(property: 'email', type: 'string', format: 'email'),
        new OA\Property(property: 'password', type: 'string', format: 'password'),
    ]
)]
#[OA\Schema(
    schema: 'RegisterRequest',
    required: ['name', 'email', 'password', 'password_confirmation'],
    properties: [
        new OA\Property(property: 'name', type: 'string', maxLength: 255),
        new OA\Property(property: 'email', type: 'string', format: 'email'),
        new OA\Property(property: 'password', type: 'string', format: 'password', minLength: 8),
        new OA\Property(property: 'password_confirmation', type: 'string', format: 'password'),
    ]
)]
class RequestBodies {}

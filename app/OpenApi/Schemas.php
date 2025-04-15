<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Product',
    required: ['name', 'description', 'price', 'stock_quantity'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', format: 'int64'),
        new OA\Property(property: 'name', type: 'string', maxLength: 255),
        new OA\Property(property: 'description', type: 'string'),
        new OA\Property(property: 'price', type: 'number', format: 'float'),
        new OA\Property(property: 'stock_quantity', type: 'integer', format: 'int32'),
        new OA\Property(property: 'featured', type: 'boolean'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ]
)]
#[OA\Schema(
    schema: 'Order',
    required: ['user_id', 'total', 'status'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', format: 'int64'),
        new OA\Property(property: 'user_id', type: 'integer', format: 'int64'),
        new OA\Property(property: 'total', type: 'number', format: 'float'),
        new OA\Property(property: 'status', type: 'string', enum: ['pending', 'processing', 'completed', 'cancelled']),
        new OA\Property(property: 'items', type: 'array', items: new OA\Items(ref: '#/components/schemas/OrderItem')),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ]
)]
#[OA\Schema(
    schema: 'OrderItem',
    required: ['order_id', 'product_id', 'quantity', 'price'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', format: 'int64'),
        new OA\Property(property: 'order_id', type: 'integer', format: 'int64'),
        new OA\Property(property: 'product_id', type: 'integer', format: 'int64'),
        new OA\Property(property: 'quantity', type: 'integer', format: 'int32'),
        new OA\Property(property: 'price', type: 'number', format: 'float'),
        new OA\Property(property: 'product', ref: '#/components/schemas/Product'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ]
)]
class Schemas {}

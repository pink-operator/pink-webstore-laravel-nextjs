<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ValidationError',
    required: ['message', 'errors'],
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'The given data was invalid.'),
        new OA\Property(
            property: 'errors',
            type: 'object',
            additionalProperties: new OA\AdditionalProperties(
                type: 'array',
                items: new OA\Items(type: 'string')
            )
        ),
    ]
)]
#[OA\Schema(
    schema: 'UnauthorizedError',
    required: ['message'],
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.'),
    ]
)]
#[OA\Schema(
    schema: 'ForbiddenError',
    required: ['message'],
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'You are not authorized to perform this action.'),
    ]
)]
#[OA\Schema(
    schema: 'NotFoundError',
    required: ['message'],
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'Resource not found.'),
    ]
)]
#[OA\Schema(
    schema: 'ServerError',
    required: ['message'],
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'Internal server error.'),
    ]
)]
class ErrorResponses {}

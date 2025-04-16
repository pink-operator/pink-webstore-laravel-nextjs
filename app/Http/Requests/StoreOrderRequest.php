<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Any authenticated user can create orders
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'An order must contain at least one item',
            'items.array' => 'Order items must be provided as an array',
            'items.min' => 'An order must contain at least one item',
            'items.*.product_id.required' => 'Each order item must specify a product',
            'items.*.product_id.exists' => 'One or more selected products do not exist',
            'items.*.quantity.required' => 'Each order item must specify a quantity',
            'items.*.quantity.min' => 'Product quantities must be at least 1',
        ];
    }
}

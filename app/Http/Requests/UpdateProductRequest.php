<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'UpdateProductRequest',
    properties: [
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'description', type: 'string'),
        new OA\Property(property: 'price', type: 'number', format: 'float'),
        new OA\Property(property: 'stock_quantity', type: 'integer'),
        new OA\Property(property: 'category_ids', type: 'array', items: new OA\Items(type: 'integer')),
    ]
)]
class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'name' => ['string', 'max:255', Rule::unique('products')->ignore($this->product)],
            'description' => ['string'],
            'price' => ['numeric', 'min:0'],
            'stock_quantity' => ['integer', 'min:0'],
            'featured' => ['boolean'],
            'original_price' => ['nullable', 'numeric', 'min:0'],
            'image_url' => ['nullable', 'url'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['exists:categories,id']
        ];
    }
}

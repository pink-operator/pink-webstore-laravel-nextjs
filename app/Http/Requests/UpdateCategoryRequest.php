<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    protected function prepareForValidation()
    {
        if ($this->has('name')) {
            $this->merge([
                'slug' => Str::slug($this->name),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => [
                'sometimes', 
                'required', 
                'string', 
                'max:255',
                Rule::unique('categories')->ignore($this->category)
            ],
            'description' => ['nullable', 'string'],
            'image_url' => ['nullable', 'url'],
            'is_active' => ['boolean'],
            'sort_order' => ['integer'],
        ];
    }
}

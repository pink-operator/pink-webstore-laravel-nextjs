<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\ProductResource;

class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];

        // Include products if requested via ?include=products
        if ($request->has('include') && $request->include === 'products') {
            $data['products'] = ProductResource::collection($this->whenLoaded('products'));
            $this->load('products');
        }

        return $data;
    }
}

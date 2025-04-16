<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'description' => fake()->paragraph(),
            'price' => fake()->randomFloat(2, 10, 1000),
            'original_price' => fn(array $attrs) => fake()->optional()->randomFloat(2, $attrs['price'], $attrs['price'] * 1.5),
            'stock_quantity' => fake()->numberBetween(0, 100),
            'featured' => fake()->boolean(20),
            'image_url' => fake()->imageUrl(400, 400),
            'rating' => fake()->randomFloat(1, 0, 5),
            'rating_count' => fake()->numberBetween(0, 1000),
        ];
    }
}

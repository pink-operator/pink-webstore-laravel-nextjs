<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        $price = $this->faker->randomFloat(2, 10, 1000);
        $hasDiscount = $this->faker->boolean(70); // 70% chance of having a discount
        $originalPrice = $hasDiscount ? $price * (1 + $this->faker->randomFloat(2, 0.1, 0.5)) : null;

        return [
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->paragraph(),
            'price' => $price,
            'original_price' => $originalPrice,
            'stock_quantity' => $this->faker->numberBetween(0, 100),
            'featured' => $this->faker->boolean(20), // 20% chance of being featured
            'image_url' => $this->faker->randomElement([
                'https://placehold.co/400x400/f3f4f6/2563eb.png',
                'https://placehold.co/400x400/f3f4f6/dc2626.png',
                'https://placehold.co/400x400/f3f4f6/16a34a.png',
                'https://placehold.co/400x400/f3f4f6/7c3aed.png',
            ]),
            'rating' => $this->faker->randomFloat(1, 3.5, 5.0),
            'rating_count' => $this->faker->numberBetween(0, 500),
        ];
    }
}

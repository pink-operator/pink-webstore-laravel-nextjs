<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->words(2, true);
        return [
            'name' => ucwords($name),
            'slug' => Str::slug($name),
            'description' => $this->faker->paragraph(),
            'image_url' => $this->faker->randomElement([
                'https://placehold.co/400x400/f3f4f6/000000.png?text=Electronics',
                'https://placehold.co/400x400/f3f4f6/000000.png?text=Fashion',
                'https://placehold.co/400x400/f3f4f6/000000.png?text=Home',
                'https://placehold.co/400x400/f3f4f6/000000.png?text=Beauty',
            ]),
            'is_active' => $this->faker->boolean(90), // 90% chance of being active
            'sort_order' => $this->faker->numberBetween(0, 10),
        ];
    }
}

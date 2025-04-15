<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('products')->delete();

        // Insert featured products
        DB::table('products')->insert([
            [
                'name' => 'MacBook Pro M3 Max',
                'description' => 'Apple\'s most powerful laptop featuring the M3 Max chip, 32GB RAM, and 1TB SSD storage.',
                'price' => 2999.99,
                'original_price' => 3499.99,
                'stock_quantity' => 15,
                'featured' => true,
                'image_url' => 'https://placehold.co/400x400/f3f4f6/000000.png?text=MacBook+Pro',
                'rating' => 4.8,
                'rating_count' => 324,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Samsung QLED 4K Smart TV',
                'description' => '65" QLED display with quantum processing and AI upscaling technology.',
                'price' => 1299.99,
                'original_price' => 1799.99,
                'stock_quantity' => 20,
                'featured' => true,
                'image_url' => 'https://placehold.co/400x400/f3f4f6/000000.png?text=Samsung+TV',
                'rating' => 4.7,
                'rating_count' => 256,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Sony WH-1000XM5',
                'description' => 'Industry-leading noise canceling headphones with exceptional sound quality.',
                'price' => 349.99,
                'original_price' => 399.99,
                'stock_quantity' => 50,
                'featured' => true,
                'image_url' => 'https://placehold.co/400x400/f3f4f6/000000.png?text=Sony+WH1000XM5',
                'rating' => 4.9,
                'rating_count' => 428,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'iPhone 15 Pro Max',
                'description' => 'Apple\'s flagship smartphone with A17 Pro chip and advanced camera system.',
                'price' => 1199.99,
                'original_price' => 1299.99,
                'stock_quantity' => 30,
                'featured' => true,
                'image_url' => 'https://placehold.co/400x400/f3f4f6/000000.png?text=iPhone+15+Pro',
                'rating' => 4.8,
                'rating_count' => 512,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Create additional random products using the factory
        \App\Models\Product::factory(16)->create();
    }
}

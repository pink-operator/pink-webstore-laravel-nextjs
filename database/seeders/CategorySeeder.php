<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('categories')->delete();
        DB::table('category_product')->delete();

        // Create main categories
        $categories = [
            [
                'name' => 'Electronics',
                'slug' => 'electronics',
                'description' => 'Latest gadgets and electronic devices',
                'image_url' => 'https://placehold.co/400x400/f3f4f6/000000.png?text=Electronics',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Fashion',
                'slug' => 'fashion',
                'description' => 'Trendy clothing and accessories',
                'image_url' => 'https://placehold.co/400x400/f3f4f6/000000.png?text=Fashion',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Home & Living',
                'slug' => 'home-living',
                'description' => 'Everything for your home',
                'image_url' => 'https://placehold.co/400x400/f3f4f6/000000.png?text=Home',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Beauty & Personal Care',
                'slug' => 'beauty-personal-care',
                'description' => 'Beauty and personal care products',
                'image_url' => 'https://placehold.co/400x400/f3f4f6/000000.png?text=Beauty',
                'is_active' => true,
                'sort_order' => 4,
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }

        // Create additional random categories
        Category::factory()->count(3)->create();

        // Assign random categories to products
        $products = Product::all();
        $categories = Category::all();

        foreach ($products as $product) {
            // Assign 1-3 random categories to each product
            $product->categories()->attach(
                $categories->random(rand(1, 3))->pluck('id')->toArray()
            );
        }
    }
}

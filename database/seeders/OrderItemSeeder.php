<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('order_items')->delete();

        // Get our first two orders
        $orders = DB::table('orders')->take(2)->get();
        $products = DB::table('products')->take(2)->get();

        if ($orders->count() >= 2 && $products->count() >= 2) {
            DB::table('order_items')->insert([
                [
                    'order_id' => $orders[0]->id,
                    'product_id' => $products[0]->id, // Laptop
                    'quantity' => 1,
                    'price' => 1200.00,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'order_id' => $orders[0]->id,
                    'product_id' => $products[1]->id, // Smartphone
                    'quantity' => 1,
                    'price' => 800.00,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'order_id' => $orders[1]->id,
                    'product_id' => $products[1]->id, // Smartphone
                    'quantity' => 1,
                    'price' => 800.00,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }
    }
}

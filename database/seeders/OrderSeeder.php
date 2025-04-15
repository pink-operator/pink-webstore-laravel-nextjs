<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('orders')->delete();

        // Get our customer user IDs (excluding admin)
        $customerIds = DB::table('users')
            ->where('role', 'customer')
            ->pluck('id')
            ->toArray();

        DB::table('orders')->insert([
            [
                'user_id' => $customerIds[0], // John Doe
                'total_price' => 2000.00,
                'status' => 'completed',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $customerIds[1], // Jane Smith
                'total_price' => 800.00,
                'status' => 'processing',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}

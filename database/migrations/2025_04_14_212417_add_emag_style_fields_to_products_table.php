<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('original_price', 8, 2)->after('price')->nullable();
            $table->string('image_url')->after('featured')->nullable();
            $table->decimal('rating', 2, 1)->after('image_url')->default(0);
            $table->integer('rating_count')->after('rating')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'original_price',
                'image_url',
                'rating',
                'rating_count'
            ]);
        });
    }
};

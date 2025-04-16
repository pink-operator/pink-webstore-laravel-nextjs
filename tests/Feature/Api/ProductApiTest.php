<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->customer = User::factory()->create(['role' => 'customer']);
    }

    public function test_can_list_products()
    {
        $products = Product::factory(3)->create();

        $response = $this->getJson('/api/products');

        $response->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'description', 'price', 'stock_quantity', 'featured', 'image_url', 'rating', 'rating_count']
                ]
            ]);
    }
    
    public function test_can_filter_featured_products()
    {
        Product::factory(2)->create(['featured' => false]);
        Product::factory(3)->create(['featured' => true]);
        
        $response = $this->getJson('/api/products?featured=true');
        
        $response->assertOk()
            ->assertJsonCount(3, 'data');
            
        $response->json('data')[0]['featured'] = true;
    }

    public function test_can_search_products()
    {
        $laptop = Product::factory()->create(['name' => 'Gaming Laptop']);
        $phone = Product::factory()->create(['name' => 'Smartphone']);

        $response = $this->getJson('/api/products/search?search=laptop');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Gaming Laptop');
    }

    public function test_admin_can_create_product()
    {
        $productData = [
            'name' => 'New Product',
            'description' => 'Description',
            'price' => 99.99,
            'stock_quantity' => 10
        ];

        $response = $this->actingAs($this->admin)
            ->postJson('/api/products', $productData);

        $response->assertCreated()
            ->assertJsonPath('data.name', $productData['name']);
    }

    public function test_customer_cannot_create_product()
    {
        $productData = [
            'name' => 'New Product',
            'description' => 'Description',
            'price' => 99.99,
            'stock_quantity' => 10
        ];

        $response = $this->actingAs($this->customer)
            ->postJson('/api/products', $productData);

        $response->assertForbidden();
    }

    public function test_admin_can_update_product()
    {
        $product = Product::factory()->create();
        $updateData = ['name' => 'Updated Name'];

        $response = $this->actingAs($this->admin)
            ->putJson("/api/products/{$product->id}", $updateData);

        $response->assertOk()
            ->assertJsonPath('data.name', 'Updated Name');
    }

    public function test_admin_can_delete_product()
    {
        $product = Product::factory()->create();

        $response = $this->actingAs($this->admin)
            ->deleteJson("/api/products/{$product->id}");

        $response->assertNoContent();
        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }

    public function test_products_are_paginated()
    {
        Product::factory(15)->create();

        $response = $this->getJson('/api/products?page=1&per_page=10');

        $response->assertOk()
            ->assertJsonCount(10, 'data')
            ->assertJsonStructure([
                'data',
                'meta' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total'
                ]
            ]);

        $this->assertEquals(15, $response->json('meta.total'));
    }

    public function test_products_can_be_sorted()
    {
        Product::factory()->create(['price' => 100]);
        Product::factory()->create(['price' => 50]);
        Product::factory()->create(['price' => 75]);

        $response = $this->getJson('/api/products?sort=price&direction=asc');

        $response->assertOk();
        $this->assertEquals(50, $response->json('data.0.price'));
        $this->assertEquals(100, $response->json('data.2.price'));
    }

    public function test_product_creation_validates_required_fields()
    {
        $response = $this->actingAs($this->admin)
            ->postJson('/api/products', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'description', 'price', 'stock_quantity']);
    }

    public function test_product_creation_validates_price_format()
    {
        $response = $this->actingAs($this->admin)
            ->postJson('/api/products', [
                'name' => 'Test Product',
                'description' => 'Description',
                'price' => 'invalid',
                'stock_quantity' => 10
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['price']);
    }

    public function test_can_filter_products_by_price_range()
    {
        Product::factory()->create(['price' => 25]);
        Product::factory()->create(['price' => 50]);
        Product::factory()->create(['price' => 75]);
        Product::factory()->create(['price' => 100]);

        $response = $this->getJson('/api/products?min_price=40&max_price=80');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_can_filter_products_by_stock_availability()
    {
        Product::factory()->create(['stock_quantity' => 0]);
        Product::factory()->create(['stock_quantity' => 5]);

        $response = $this->getJson('/api/products?in_stock=true');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_cannot_update_nonexistent_product()
    {
        $response = $this->actingAs($this->admin)
            ->putJson("/api/products/99999", ['name' => 'Updated Name']);

        $response->assertNotFound();
    }

    public function test_cannot_create_product_with_duplicate_name()
    {
        Product::factory()->create(['name' => 'Existing Product']);

        $response = $this->actingAs($this->admin)
            ->postJson('/api/products', [
                'name' => 'Existing Product',
                'description' => 'Description',
                'price' => 99.99,
                'stock_quantity' => 10
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_can_filter_products_by_category()
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create();
        $product->categories()->attach($category);
        
        Product::factory()->create(); // Product without category

        $response = $this->getJson("/api/products?category={$category->id}");

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_product_soft_delete()
    {
        $product = Product::factory()->create();

        $this->actingAs($this->admin)
            ->deleteJson("/api/products/{$product->id}");

        $this->assertSoftDeleted('products', ['id' => $product->id]);
        
        // Verify it doesn't show up in the listing
        $response = $this->getJson('/api/products');
        $response->assertOk()
            ->assertJsonCount(0, 'data');
    }
}

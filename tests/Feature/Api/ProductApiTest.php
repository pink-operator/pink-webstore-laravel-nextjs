<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Product;
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
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }
}

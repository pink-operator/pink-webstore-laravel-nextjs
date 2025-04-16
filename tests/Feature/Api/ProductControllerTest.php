<?php

namespace Tests\Feature\Api;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ProductControllerTest extends ApiTestCase
{
    protected $admin;
    
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->admin = $this->actingAsAdmin();
    }

    public function test_can_list_products()
    {
        $products = Product::factory()->count(5)->create();

        $response = $this->getJson('/api/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'description',
                        'price',
                        'stock_quantity',
                        'featured',
                        'image_url',
                        'rating',
                        'rating_count',
                        'created_at',
                        'updated_at'
                    ]
                ],
                'links',
                'meta'
            ])
            ->assertJsonCount(5, 'data');
    }

    public function test_can_filter_products_by_category()
    {
        $category = Category::factory()->create();
        $productsInCategory = Product::factory()->count(3)->create();
        foreach ($productsInCategory as $product) {
            $product->categories()->attach($category->id);
        }
        Product::factory()->count(2)->create();

        $response = $this->getJson("/api/products?category={$category->slug}");

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_can_search_products()
    {
        Product::factory()->create(['name' => 'Test Product ABC']);
        Product::factory()->create(['name' => 'Another Product XYZ']);

        $response = $this->getJson('/api/products?search=ABC');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Test Product ABC');
    }

    public function test_can_sort_products_by_price()
    {
        Product::factory()->create(['price' => 100]);
        Product::factory()->create(['price' => 50]);
        Product::factory()->create(['price' => 75]);

        $response = $this->getJson('/api/products?sort=price&direction=asc');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');

        $prices = collect($response->json('data'))->pluck('price')->toArray();
        $this->assertEquals([50, 75, 100], $prices);
    }

    public function test_can_get_single_product()
    {
        $product = Product::factory()->create();

        $response = $this->getJson("/api/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'description',
                    'price',
                    'stock_quantity',
                    'featured',
                    'image_url',
                    'rating',
                    'rating_count',
                    'created_at',
                    'updated_at',
                    'categories'
                ]
            ])
            ->assertJsonPath('data.id', $product->id);
    }

    public function test_returns_404_for_non_existent_product()
    {
        $response = $this->getJson('/api/products/999');

        $response->assertStatus(404);
    }

    public function test_admin_can_create_product()
    {
        $this->actingAsAdmin();
        
        $category = Category::factory()->create();
        
        $response = $this->postJson('/api/products', [
            'name' => 'New Product',
            'description' => 'Product description',
            'price' => 99.99,
            'stock_quantity' => 100,
            'category_ids' => [$category->id],
            'featured' => true,
            'image_url' => 'https://example.com/image.jpg'
        ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'description',
                    'price',
                    'stock_quantity',
                    'featured',
                    'image_url',
                    'rating',
                    'rating_count',
                    'categories',
                    'created_at',
                    'updated_at'
                ]
            ]);

        $this->assertDatabaseHas('products', [
            'name' => 'New Product',
            'price' => 99.99,
            'stock_quantity' => 100,
        ]);

        $this->assertDatabaseHas('category_product', [
            'category_id' => $category->id,
            'product_id' => $response->json('data.id')
        ]);
    }

    public function test_customer_cannot_create_product()
    {
        $this->actingAsCustomer();

        $response = $this->postJson('/api/products', [
            'name' => 'New Product',
            'description' => 'Product description',
            'price' => 99.99,
            'stock_quantity' => 100
        ]);

        $response->assertForbidden();
    }

    public function test_admin_can_update_product()
    {
        $this->actingAsAdmin();
        
        $product = Product::factory()->create();
        $newCategory = Category::factory()->create();

        $response = $this->putJson("/api/products/{$product->id}", [
            'name' => 'Updated Product',
            'description' => 'Updated description',
            'price' => 149.99,
            'stock_quantity' => 200,
            'category_ids' => [$newCategory->id]
        ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'Updated Product')
            ->assertJsonPath('data.price', 149.99);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Product',
            'price' => 149.99,
            'stock_quantity' => 200,
        ]);

        $this->assertDatabaseHas('category_product', [
            'category_id' => $newCategory->id,
            'product_id' => $product->id
        ]);
    }

    public function test_admin_can_update_product_image()
    {
        $this->actingAsAdmin();
        
        $product = Product::factory()->create();

        $response = $this->putJson("/api/products/{$product->id}", [
            'name' => $product->name,
            'description' => $product->description,
            'price' => $product->price,
            'stock_quantity' => $product->stock_quantity,
            'image_url' => 'https://example.com/new-image.jpg'
        ]);

        $response->assertOk()
            ->assertJsonPath('data.image_url', 'https://example.com/new-image.jpg');
    }

    public function test_customer_cannot_update_product()
    {
        $this->actingAsCustomer();
        
        $product = Product::factory()->create();

        $response = $this->putJson("/api/products/{$product->id}", [
            'name' => 'Updated Product',
            'description' => 'Updated description',
            'price' => 149.99,
            'stock_quantity' => 200
        ]);

        $response->assertForbidden();
    }

    public function test_admin_can_delete_product()
    {
        $this->actingAs($this->admin);
        
        $product = Product::factory()->create();

        $response = $this->deleteJson("/api/products/{$product->id}");

        $response->assertNoContent();
        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }

    public function test_customer_cannot_delete_product()
    {
        $this->actingAsCustomer();
        
        $product = Product::factory()->create();

        $response = $this->deleteJson("/api/products/{$product->id}");

        $response->assertForbidden();
        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }

    public function test_validates_required_fields_for_create()
    {
        $this->actingAsAdmin();

        $response = $this->postJson('/api/products', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'description', 'price', 'stock_quantity']);
    }

    public function test_validates_price_is_numeric_and_positive()
    {
        $this->actingAsAdmin();

        $response = $this->postJson('/api/products', [
            'name' => 'Test Product',
            'description' => 'Description',
            'price' => 'invalid',
            'stock_quantity' => 100
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['price']);

        $response = $this->postJson('/api/products', [
            'name' => 'Test Product',
            'description' => 'Description',
            'price' => -10,
            'stock_quantity' => 100
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['price']);
    }

    public function test_validates_stock_quantity_is_integer_and_non_negative()
    {
        $this->actingAsAdmin();

        $response = $this->postJson('/api/products', [
            'name' => 'Test Product',
            'description' => 'Description',
            'price' => 99.99,
            'stock_quantity' => 'invalid'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['stock_quantity']);

        $response = $this->postJson('/api/products', [
            'name' => 'Test Product',
            'description' => 'Description',
            'price' => 99.99,
            'stock_quantity' => -1
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['stock_quantity']);
    }
}
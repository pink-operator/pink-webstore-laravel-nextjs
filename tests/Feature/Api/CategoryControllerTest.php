<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Str;

class CategoryControllerTest extends ApiTestCase
{
    public function test_can_list_categories()
    {
        $categories = Category::factory()->count(5)->create();

        $response = $this->getJson('/api/categories');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'slug',
                        'description',
                        'created_at',
                        'updated_at'
                    ]
                ],
                'links',
                'meta'
            ])
            ->assertJsonCount(5, 'data');
    }

    public function test_can_get_single_category()
    {
        $category = Category::factory()->create();

        $response = $this->getJson("/api/categories/{$category->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'slug',
                    'description',
                    'created_at',
                    'updated_at'
                ]
            ])
            ->assertJsonPath('data.id', $category->id);
    }

    public function test_can_get_category_with_products()
    {
        $category = Category::factory()
            ->has(Product::factory()->count(3))
            ->create();

        $response = $this->getJson("/api/categories/{$category->id}?include=products");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'slug',
                    'description',
                    'products' => [
                        '*' => [
                            'id',
                            'name',
                            'price',
                            'stock_quantity'
                        ]
                    ]
                ]
            ])
            ->assertJsonCount(3, 'data.products');
    }

    public function test_returns_404_for_non_existent_category()
    {
        $response = $this->getJson('/api/categories/999');

        $response->assertStatus(404);
    }

    public function test_admin_can_create_category()
    {
        $this->actingAsAdmin();

        $categoryData = [
            'name' => 'Test Category',
            'description' => 'Test category description'
        ];

        $response = $this->postJson('/api/categories', $categoryData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'slug',
                    'description'
                ]
            ])
            ->assertJsonPath('data.name', $categoryData['name'])
            ->assertJsonPath('data.slug', Str::slug($categoryData['name']));

        $this->assertDatabaseHas('categories', [
            'name' => $categoryData['name'],
            'description' => $categoryData['description']
        ]);
    }

    public function test_customer_cannot_create_category()
    {
        $this->actingAsCustomer();

        $response = $this->postJson('/api/categories', [
            'name' => 'Test Category',
            'description' => 'Test description'
        ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_update_category()
    {
        $this->actingAsAdmin();
        
        $category = Category::factory()->create();

        $response = $this->putJson("/api/categories/{$category->id}", [
            'name' => 'Updated Category',
            'description' => 'Updated description'
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Updated Category')
            ->assertJsonPath('data.slug', Str::slug('Updated Category'));

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Updated Category',
            'description' => 'Updated description'
        ]);
    }

    public function test_customer_cannot_update_category()
    {
        $this->actingAsCustomer();
        
        $category = Category::factory()->create();

        $response = $this->putJson("/api/categories/{$category->id}", [
            'name' => 'Updated Category',
            'description' => 'Updated description'
        ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_delete_category()
    {
        $this->actingAsAdmin();
        
        $category = Category::factory()->create();

        $response = $this->deleteJson("/api/categories/{$category->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_cannot_delete_category_with_products()
    {
        $this->actingAsAdmin();
        
        $category = Category::factory()
            ->has(Product::factory())
            ->create();

        $response = $this->deleteJson("/api/categories/{$category->id}");

        $response->assertStatus(422)
            ->assertJsonStructure(['message']);

        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_customer_cannot_delete_category()
    {
        $this->actingAsCustomer();
        
        $category = Category::factory()->create();

        $response = $this->deleteJson("/api/categories/{$category->id}");

        $response->assertStatus(403);
    }

    public function test_validates_required_fields_for_create()
    {
        $this->actingAsAdmin();

        $response = $this->postJson('/api/categories', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_validates_unique_category_name()
    {
        $this->actingAsAdmin();
        
        $existingCategory = Category::factory()->create(['name' => 'Test Category']);

        $response = $this->postJson('/api/categories', [
            'name' => 'Test Category',
            'description' => 'Another description'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_can_update_category_with_same_name()
    {
        $this->actingAsAdmin();
        
        $category = Category::factory()->create(['name' => 'Test Category']);

        $response = $this->putJson("/api/categories/{$category->id}", [
            'name' => 'Test Category',
            'description' => 'Updated description'
        ]);

        $response->assertStatus(200);
    }

    public function test_cannot_update_category_with_existing_name()
    {
        $this->actingAsAdmin();
        
        $category1 = Category::factory()->create(['name' => 'Category One']);
        $category2 = Category::factory()->create(['name' => 'Category Two']);

        $response = $this->putJson("/api/categories/{$category2->id}", [
            'name' => 'Category One',
            'description' => 'Updated description'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }
}
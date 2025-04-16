<?php

namespace Tests\Feature\Api;

use App\Models\Order;
use App\Models\Product;
use App\Models\OrderItem;
use App\Models\User;

class OrderControllerTest extends ApiTestCase
{
    protected $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->customer = $this->actingAsCustomer();
    }

    public function test_customer_can_list_their_orders()
    {
        // Create orders for the current customer
        $customerOrders = Order::factory()
            ->count(3)
            ->create(['user_id' => $this->customer->id]);
            
        // Create orders for another customer
        $otherOrders = Order::factory()
            ->count(2)
            ->create();

        $response = $this->getJson('/api/orders');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'user_id',
                        'status',
                        'total',
                        'created_at',
                        'updated_at',
                        'items' => [
                            '*' => [
                                'id',
                                'product_id',
                                'quantity',
                                'price',
                                'product' => [
                                    'id',
                                    'name'
                                ]
                            ]
                        ]
                    ]
                ],
                'links',
                'meta'
            ])
            ->assertJsonCount(3, 'data');
    }

    public function test_admin_can_list_all_orders()
    {
        $this->actingAsAdmin();
        
        Order::factory()->count(5)->create();

        $response = $this->getJson('/api/orders');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data');
    }

    public function test_customer_can_view_their_order()
    {
        // Create a customer user and authenticate
        $customer = $this->actingAsCustomer();
        
        // Create an order for this customer
        $order = Order::factory()->create([
            'user_id' => $customer->id
        ]);
        
        // Create order items
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();
        
        $order->items()->create([
            'product_id' => $product1->id,
            'quantity' => 2,
            'price' => $product1->price
        ]);
        
        $order->items()->create([
            'product_id' => $product2->id,
            'quantity' => 1,
            'price' => $product2->price
        ]);

        // Make the request
        $response = $this->getJson("/api/orders/{$order->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'user_id',
                    'status',
                    'total',
                    'items' => [
                        '*' => [
                            'id',
                            'product_id',
                            'quantity',
                            'price'
                        ]
                    ],
                    'created_at',
                    'updated_at'
                ]
            ]);
    }

    public function test_customer_cannot_view_other_customer_order()
    {
        $this->actingAsCustomer();
        
        $otherOrder = Order::factory()->create();

        $response = $this->getJson("/api/orders/{$otherOrder->id}");

        $response->assertStatus(403);
    }

    public function test_admin_can_view_any_order()
    {
        $this->actingAsAdmin();
        
        $order = Order::factory()->create();

        $response = $this->getJson("/api/orders/{$order->id}");

        $response->assertStatus(200);
    }

    public function test_customer_can_create_order()
    {
        $this->actingAsCustomer();
        
        $products = Product::factory()->count(2)->create([
            'stock_quantity' => 10
        ]);

        $orderData = [
            'items' => [
                [
                    'product_id' => $products[0]->id,
                    'quantity' => 2
                ],
                [
                    'product_id' => $products[1]->id,
                    'quantity' => 3
                ]
            ]
        ];

        $response = $this->postJson('/api/orders', $orderData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'user_id',
                    'status',
                    'total',
                    'items'
                ]
            ]);

        // Check that products stock was reduced
        $this->assertDatabaseHas('products', [
            'id' => $products[0]->id,
            'stock_quantity' => 8
        ]);
        
        $this->assertDatabaseHas('products', [
            'id' => $products[1]->id,
            'stock_quantity' => 7
        ]);
    }

    public function test_cannot_create_order_with_insufficient_stock()
    {
        $this->actingAsCustomer();
        
        $product = Product::factory()->create([
            'stock_quantity' => 5
        ]);

        $response = $this->postJson('/api/orders', [
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 10
                ]
            ]
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items.0.quantity']);

        // Check that stock wasn't modified
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock_quantity' => 5
        ]);
    }

    public function test_cannot_create_order_with_invalid_product()
    {
        $this->actingAsCustomer();

        $response = $this->postJson('/api/orders', [
            'items' => [
                [
                    'product_id' => 999,
                    'quantity' => 1
                ]
            ]
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items.0.product_id']);
    }

    public function test_admin_can_update_order_status()
    {
        $this->actingAsAdmin();
        
        $order = Order::factory()->create(['status' => 'pending']);

        $response = $this->patchJson("/api/orders/{$order->id}/status", [
            'status' => 'processing'
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'processing');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'processing'
        ]);
    }

    public function test_customer_cannot_update_order_status()
    {
        $this->actingAsCustomer();
        
        $order = Order::factory()->create(['status' => 'pending']);

        $response = $this->patchJson("/api/orders/{$order->id}/status", [
            'status' => 'processing'
        ]);

        $response->assertStatus(403);
    }

    public function test_validates_order_status_update()
    {
        $this->actingAsAdmin();
        
        $order = Order::factory()->create(['status' => 'pending']);

        $response = $this->patchJson("/api/orders/{$order->id}/status", [
            'status' => 'invalid_status'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    public function test_validates_required_fields_for_order_creation()
    {
        $this->actingAsCustomer();

        $response = $this->postJson('/api/orders', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items']);
    }

    public function test_validates_items_array_for_order_creation()
    {
        $this->actingAsCustomer();

        $response = $this->postJson('/api/orders', [
            'items' => 'not_an_array'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items']);
    }

    public function test_validates_item_quantity_is_positive()
    {
        $this->actingAsCustomer();
        
        $product = Product::factory()->create();

        $response = $this->postJson('/api/orders', [
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 0
                ]
            ]
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items.0.quantity']);

        $response = $this->postJson('/api/orders', [
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => -1
                ]
            ]
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items.0.quantity']);
    }

    public function test_order_total_is_calculated_correctly()
    {
        $this->actingAsCustomer();
        
        $product1 = Product::factory()->create([
            'price' => 100,
            'stock_quantity' => 10
        ]);
        
        $product2 = Product::factory()->create([
            'price' => 200,
            'stock_quantity' => 10
        ]);

        $response = $this->postJson('/api/orders', [
            'items' => [
                [
                    'product_id' => $product1->id,
                    'quantity' => 2
                ],
                [
                    'product_id' => $product2->id,
                    'quantity' => 1
                ]
            ]
        ]);

        $response->assertStatus(201);
        
        // Get the actual total from the response and compare it as a float
        $total = $response->json('data.total');
        $this->assertEquals(400.00, (float)$total, '', 0.01);
    }
}
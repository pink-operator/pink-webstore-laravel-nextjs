<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $customer;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->customer = User::factory()->create(['role' => 'customer']);
        $this->product = Product::factory()->create([
            'price' => 100,
            'stock_quantity' => 5
        ]);
    }

    public function test_customer_can_create_order()
    {
        $orderData = [
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 2
                ]
            ]
        ];

        $response = $this->actingAs($this->customer)
            ->postJson('/api/orders', $orderData);

        $response->assertCreated()
            ->assertJson([
                'data' => [
                    'total_price' => 200,
                    'status' => 'pending'
                ]
            ]);

        $this->assertEquals(3, $this->product->fresh()->stock_quantity);
    }

    public function test_cannot_order_out_of_stock_product()
    {
        $orderData = [
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 10
                ]
            ]
        ];

        $response = $this->actingAs($this->customer)
            ->postJson('/api/orders', $orderData);

        $response->assertStatus(422)
            ->assertJsonFragment([
                'message' => "Insufficient stock for product: {$this->product->name}"
            ]);

        $this->assertEquals(5, $this->product->fresh()->stock_quantity);
    }

    public function test_customer_can_view_own_orders()
    {
        $order = Order::factory()
            ->for($this->customer)
            ->create(['status' => 'pending']);

        $response = $this->actingAs($this->customer)
            ->getJson('/api/orders');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $order->id);
    }

    public function test_customer_cannot_view_others_orders()
    {
        $otherCustomer = User::factory()->create(['role' => 'customer']);
        $order = Order::factory()
            ->for($otherCustomer)
            ->create();

        $response = $this->actingAs($this->customer)
            ->getJson("/api/orders/{$order->id}");

        $response->assertForbidden();
    }

    public function test_admin_can_view_all_orders()
    {
        Order::factory()->for($this->customer)->create();
        Order::factory()->for(User::factory())->create();

        $response = $this->actingAs($this->admin)
            ->getJson('/api/orders');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_admin_can_update_order_status()
    {
        $order = Order::factory()
            ->for($this->customer)
            ->create(['status' => 'pending']);

        $response = $this->actingAs($this->admin)
            ->patchJson("/api/orders/{$order->id}/status", [
                'status' => 'completed'
            ]);

        $response->assertOk()
            ->assertJsonPath('data.status', 'completed');
    }

    public function test_cancelling_order_restores_stock()
    {
        $order = Order::factory()
            ->for($this->customer)
            ->create(['status' => 'pending']);

        $order->items()->create([
            'product_id' => $this->product->id,
            'quantity' => 2,
            'price' => $this->product->price
        ]);

        $initialStock = $this->product->stock_quantity;

        $response = $this->actingAs($this->admin)
            ->patchJson("/api/orders/{$order->id}/status", [
                'status' => 'cancelled'
            ]);

        $response->assertOk();
        $this->assertEquals($initialStock + 2, $this->product->fresh()->stock_quantity);
    }
}

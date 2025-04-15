<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @OA\Tag(
 *     name="Orders",
 *     description="API Endpoints for order management"
 * )
 */
class OrderController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/orders",
     *     summary="List orders (all for admin, own for customers)",
     *     tags={"Orders"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of orders",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/OrderResource"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $orders = $request->user()->isAdmin()
            ? Order::with(['items.product', 'user'])->latest()->paginate(10)
            : $request->user()->orders()->with('items.product')->latest()->paginate(10);

        return OrderResource::collection($orders);
    }

    /**
     * @OA\Post(
     *     path="/api/orders",
     *     summary="Create a new order",
     *     tags={"Orders"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"items"},
     *             @OA\Property(
     *                 property="items",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     required={"product_id", "quantity"},
     *                     @OA\Property(property="product_id", type="integer"),
     *                     @OA\Property(property="quantity", type="integer", minimum=1)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Order created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/OrderResource")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error or insufficient stock"
     *     )
     * )
     */
    public function store(StoreOrderRequest $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $total_price = 0;
                $items = collect($request->validated('items'))->map(function ($item) use (&$total_price) {
                    $product = Product::findOrFail($item['product_id']);

                    if (!$product->hasEnoughStock($item['quantity'])) {
                        throw new \Exception("Insufficient stock for product: {$product->name}");
                    }

                    $item_price = $product->price * $item['quantity'];
                    $total_price += $item_price;

                    return [
                        'product_id' => $product->id,
                        'quantity' => $item['quantity'],
                        'price' => $product->price,
                    ];
                });

                $order = Order::create([
                    'user_id' => $request->user()->id,
                    'total_price' => $total_price,
                    'status' => 'pending',
                ]);

                $order->items()->createMany($items);

                // Update stock quantities
                foreach ($items as $item) {
                    Product::where('id', $item['product_id'])
                        ->decrement('stock_quantity', $item['quantity']);
                }

                return new OrderResource($order->load('items.product'));
            });
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors' => ['general' => [$e->getMessage()]]
            ], 422);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/orders/{order}",
     *     summary="Get a specific order",
     *     tags={"Orders"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="order",
     *         in="path",
     *         required=true,
     *         description="Order ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order details",
     *         @OA\JsonContent(ref="#/components/schemas/OrderResource")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized access to order"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order not found"
     *     )
     * )
     */
    public function show(Order $order)
    {
        $this->authorize('view', $order);
        return new OrderResource($order->load('items.product', 'user'));
    }

    /**
     * @OA\Patch(
     *     path="/api/orders/{order}/status",
     *     summary="Update order status (admin only)",
     *     tags={"Orders"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="order",
     *         in="path",
     *         required=true,
     *         description="Order ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 enum={"pending", "processing", "completed", "cancelled"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order status updated",
     *         @OA\JsonContent(ref="#/components/schemas/OrderResource")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized - Admin only"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order not found"
     *     )
     * )
     */
    public function updateStatus(Request $request, Order $order)
    {
        $this->authorize('update', $order);

        $validated = $request->validate([
            'status' => ['required', 'in:pending,processing,completed,cancelled'],
        ]);

        $order->update($validated);

        // If order is cancelled, restore product quantities
        if ($validated['status'] === 'cancelled') {
            foreach ($order->items as $item) {
                $item->product->increment('stock_quantity', $item->quantity);
            }
        }

        return new OrderResource($order->load('items.product'));
    }
}

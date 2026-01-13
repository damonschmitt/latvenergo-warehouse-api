<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;

class OrderController extends Controller
{
    public function store(StoreOrderRequest $request)
    {
        $items = $request->input('items');
        $productIds = array_unique(array_column($items, 'product_id'));

        try {
            $result = DB::transaction(function () use ($items, $productIds) {
                // Check availability (with lock to prevent race conditions)
                $products = Product::whereIn('id', $productIds)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                // Check if all products have sufficient stock
                foreach ($items as $item) {
                    $productId = $item['product_id'];

                    if ($products[$productId]->quantity < $item['quantity']) {
                        throw new \RuntimeException("Insufficient stock for product ID {$productId}");
                    }
                }

                // Create order
                $order = Order::create([]);

                $orderItems = [];
                $total = 0;

                // Create order items and subtract quantities
                foreach ($items as $item) {
                    $product = $products[$item['product_id']];
                    $quantity = $item['quantity'];
                    $price = $product->price;

                    // Create order item
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'price' => $price,
                    ]);

                    // Subtract quantity from product
                    $product->decrement('quantity', $quantity);

                    $orderItems[] = [
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'price' => $price,
                    ];

                    $total += $price * $quantity;
                }

                return [
                    'order_id' => $order->id,
                    'items' => $orderItems,
                    'total' => $total,
                ];
            });

            return response()->json($result, 201);

        } catch (\RuntimeException $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 409);

        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Failed to create order: ' . $e->getMessage()
            ], 500);
        }
    }
}

<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Product;

class OrderControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Assert the order response JSON structure for a given number of items
     */
    private function assertOrderJsonStructure($response, int $itemCount): void
    {
        $itemsStructure = array_fill(0, $itemCount, [
            'product_id',
            'quantity',
            'price',
        ]);

        $response->assertJsonStructure([
            'order_id',
            'items' => $itemsStructure,
            'total',
        ]);
    }

    // Test that creating an order with empty items array fails validation
    public function test_order_with_empty_items_fails_validation(): void
    {
        $response = $this->postJson('/api/orders', [
            'items' => []
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('items');
    }

    // Test that creating an order with non-existent product ID fails validation
    public function test_order_with_nonexistent_product_fails_validation(): void
    {
        Product::factory()->create();

        $response = $this->postJson('/api/orders', [
            'items' => [
                ['product_id' => 999, 'quantity' => 1]
            ]
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('items.0.product_id');
    }

    // Test that creating an order with invalid quantity fails validation
    public function test_order_with_invalid_quantity_fails_validation(): void
    {
        $product = Product::factory()->create();

        $response = $this->postJson('/api/orders', [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 0]
            ]
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('items.0.quantity');
    }

    // Test that creating an order with one item succeeds and reduces stock
    public function test_order_with_one_item_succeeds_and_reduces_stock(): void
    {
        $product = Product::factory()->create([
            'price' => 100.00,
            'quantity' => 10,
        ]);

        $response = $this->postJson('/api/orders', [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 3]
            ]
        ]);

        $response->assertStatus(201);
        
        $this->assertOrderJsonStructure($response, 1);
        
        $response->assertJson([
            'total' => 300.00,
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $response->json('order_id'),
        ]);

        $this->assertDatabaseHas('order_items', [
            'order_id' => $response->json('order_id'),
            'product_id' => $product->id,
            'quantity' => 3,
            'price' => 100.00,
        ]);

        $product->refresh();
        $this->assertEquals(7, $product->quantity);
    }

    // Test that creating an order with two items succeeds and reduces stock
    public function test_order_with_two_items_succeeds_and_reduces_stock(): void
    {
        $product1 = Product::factory()->create([
            'price' => 50.00,
            'quantity' => 20,
        ]);
        
        $product2 = Product::factory()->create([
            'price' => 75.00,
            'quantity' => 15,
        ]);

        $response = $this->postJson('/api/orders', [
            'items' => [
                ['product_id' => $product1->id, 'quantity' => 2],
                ['product_id' => $product2->id, 'quantity' => 3],
            ]
        ]);

        $response->assertStatus(201);
        
        $this->assertOrderJsonStructure($response, 2);
        
        $response->assertJson([
            'total' => 325.00,
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $response->json('order_id'),
        ]);

        $this->assertDatabaseHas('order_items', [
            'order_id' => $response->json('order_id'),
            'product_id' => $product1->id,
            'quantity' => 2,
            'price' => 50.00,
        ]);

        $this->assertDatabaseHas('order_items', [
            'order_id' => $response->json('order_id'),
            'product_id' => $product2->id,
            'quantity' => 3,
            'price' => 75.00,
        ]);

        $product1->refresh();
        $product2->refresh();
        $this->assertEquals(18, $product1->quantity);
        $this->assertEquals(12, $product2->quantity);
    }

    // Test that creating an order with insufficient stock fails
    public function test_order_with_insufficient_stock_fails(): void
    {
        $product = Product::factory()->create([
            'quantity' => 5,
        ]);

        $response = $this->postJson('/api/orders', [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 10]
            ]
        ]);

        $response->assertStatus(409);
        $response->assertJsonStructure(['error']);
        $response->assertJson([
            'error' => "Insufficient stock for product ID {$product->id}"
        ]);
    }
}

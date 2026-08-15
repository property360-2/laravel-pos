<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosSaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashier_can_process_a_sale_and_stock_is_deducted(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);

        $category = Category::create(['name' => 'Test Category']);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Test Product',
            'sku' => 'TST-001',
            'price' => 10.00,
            'stock_quantity' => 10,
            'low_stock_threshold' => 2,
        ]);

        $response = $this->actingAs($cashier)->postJson('/api/transactions', [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 3],
            ],
            'discount' => 5,
            'payment_method' => 'cash',
            'amount_paid' => 100,
        ]);

        $response->assertStatus(201);
        $this->assertEquals(25, (int) $response->json('transaction.total'));
        $this->assertEquals(75, (int) $response->json('transaction.change_amount'));
        $this->assertSame(7, $product->fresh()->stock_quantity);
        $this->assertDatabaseCount('stock_movements', 1);
    }

    public function test_overselling_returns_422(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::create(['name' => 'Test Category']);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Test Product',
            'sku' => 'TST-002',
            'price' => 10.00,
            'stock_quantity' => 2,
            'low_stock_threshold' => 2,
        ]);

        $response = $this->actingAs($admin)->postJson('/api/transactions', [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 5],
            ],
            'discount' => 0,
            'payment_method' => 'cash',
            'amount_paid' => 1000,
        ]);

        $response->assertStatus(422);
        $this->assertSame(2, $product->fresh()->stock_quantity);
    }

    public function test_cashier_cannot_create_products(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);
        $category = Category::create(['name' => 'Test Category']);

        $response = $this->actingAs($cashier)->postJson('/api/products', [
            'category_id' => $category->id,
            'name' => 'Forbidden',
            'sku' => 'FORB-001',
            'price' => 5,
            'stock_quantity' => 1,
            'low_stock_threshold' => 1,
        ]);

        $response->assertStatus(403);
    }
}

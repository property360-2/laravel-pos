<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportChartTest extends TestCase
{
    use RefreshDatabase;

    private function seedStore(): array
    {
        $cashier = User::factory()->create(['role' => 'cashier']);

        $drinks = Category::create(['name' => 'Drinks']);
        $food = Category::create(['name' => 'Food']);

        $soda = Product::create([
            'category_id' => $drinks->id,
            'name' => 'Soda Can',
            'sku' => 'CHT-SODA',
            'price' => 20.00,
            'stock_quantity' => 50,
            'low_stock_threshold' => 5,
        ]);
        $bread = Product::create([
            'category_id' => $food->id,
            'name' => 'Bread Loaf',
            'sku' => 'CHT-BREAD',
            'price' => 15.00,
            'stock_quantity' => 50,
            'low_stock_threshold' => 5,
        ]);

        $this->actingAs($cashier)->postJson('/api/transactions', [
            'items' => [['product_id' => $soda->id, 'quantity' => 2]],
            'discount' => 0,
            'payment_method' => 'cash',
            'amount_paid' => 100,
        ])->assertStatus(201);

        $this->actingAs($cashier)->postJson('/api/transactions', [
            'items' => [['product_id' => $bread->id, 'quantity' => 1]],
            'discount' => 0,
            'payment_method' => 'cash',
            'amount_paid' => 100,
        ])->assertStatus(201);

        return [$soda, $bread];
    }

    public function test_sales_trend_returns_daily_revenue_and_transactions(): void
    {
        $this->seedStore();

        $response = $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->getJson('/api/reports/sales-trend?start_date='.now()->toDateString().'&end_date='.now()->toDateString());

        $response->assertStatus(200)
            ->assertJsonCount(1);

        $response->assertJsonFragment([
            'date' => now()->toDateString(),
            'revenue' => 55.0,
            'transactions' => 2,
        ]);
    }

    public function test_sales_by_category_returns_revenue_per_category(): void
    {
        $this->seedStore();

        $response = $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->getJson('/api/reports/sales-by-category');

        $response->assertStatus(200)
            ->assertJsonCount(2);

        $response->assertJsonFragment(['category' => 'Drinks', 'revenue' => 40.0]);
        $response->assertJsonFragment(['category' => 'Food', 'revenue' => 15.0]);
    }

    public function test_sales_trend_rejects_excessive_date_range(): void
    {
        $this->seedStore();

        $response = $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->getJson('/api/reports/sales-trend?start_date=2024-01-01&end_date=2026-01-01');

        $response->assertStatus(422);
    }
}

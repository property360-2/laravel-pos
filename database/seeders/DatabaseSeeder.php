<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@stockflow.test',
            'role' => 'admin',
            'password' => 'password',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'John Doe',
            'email' => 'cashier@stockflow.test',
            'role' => 'cashier',
            'password' => 'password',
            'email_verified_at' => now(),
        ]);

        $categories = collect([
            'Beverages',
            'Bakery',
            'Dairy',
            'Snacks',
            'Personal Care',
            'Household',
        ])->map(fn (string $name) => Category::create(['name' => $name]))
            ->mapWithKeys(fn (Category $category) => [$category->name => $category->id]);

        $products = [
            // [category, name, sku, price, stock, threshold]
            ['Beverages', 'Coca-Cola 330ml', 'BEV-001', 25.00, 48, 10],
            ['Beverages', 'Sprite 330ml', 'BEV-002', 25.00, 36, 10],
            ['Beverages', 'Royal Tru-Orange 320ml', 'BEV-003', 25.00, 4, 5],
            ['Beverages', 'C2 Apple 230ml', 'BEV-004', 22.00, 60, 12],
            ['Beverages', 'Wilkins 500ml', 'BEV-005', 20.00, 24, 10],
            ['Bakery', 'Fresh Bread 500g', 'BAK-001', 40.00, 12, 5],
            ['Bakery', 'Butter Croissant', 'BAK-002', 35.00, 3, 5],
            ['Bakery', 'Pandesal 6pcs', 'BAK-003', 30.00, 0, 5],
            ['Dairy', 'Full Cream Milk 1L', 'DAI-001', 78.00, 18, 6],
            ['Dairy', 'Fruit Yogurt 110g', 'DAI-002', 32.00, 2, 5],
            ['Dairy', 'Cream Cheese 165g', 'DAI-003', 45.00, 9, 4],
            ['Snacks', 'Piattos 40g', 'SNK-001', 20.00, 55, 10],
            ['Snacks', 'Nova Multigrain 30g', 'SNK-002', 19.00, 40, 10],
            ['Snacks', 'Cream-O 20g', 'SNK-003', 15.00, 0, 5],
            ['Snacks', 'Chippy 60g', 'SNK-004', 24.00, 8, 6],
            ['Personal Care', 'Antibacterial Soap 60g', 'PRC-001', 32.00, 22, 6],
            ['Personal Care', 'Shampoo Sachet 10ml', 'PRC-002', 8.00, 100, 20],
            ['Personal Care', 'Toothpaste 20g', 'PRC-003', 28.00, 5, 5],
            ['Household', 'Laundry Bar 60g', 'HOU-001', 18.00, 30, 8],
            ['Household', 'Dishwashing Liquid 200ml', 'HOU-002', 35.00, 14, 6],
        ];

        foreach ($products as [$categoryName, $name, $sku, $price, $stock, $threshold]) {
            $product = Product::create([
                'category_id' => $categories[$categoryName],
                'name' => $name,
                'sku' => $sku,
                'price' => $price,
                'stock_quantity' => $stock,
                'low_stock_threshold' => $threshold,
            ]);

            if ($stock > 0) {
                StockMovement::create([
                    'product_id' => $product->id,
                    'user_id' => $admin->id,
                    'type' => 'stock_in',
                    'quantity' => $stock,
                    'reason' => 'Initial inventory',
                ]);
            }
        }

        $settings = [
            'store_name' => 'StockFlow Store',
            'store_address' => '123 Business Street',
            'store_phone' => '(02) 8123-4567',
            'currency_symbol' => '₱',
            'receipt_footer' => 'Thank you for your purchase!',
        ];

        foreach ($settings as $key => $value) {
            Setting::set($key, $value);
        }
    }
}

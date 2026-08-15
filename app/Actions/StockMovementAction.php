<?php

namespace App\Actions;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockMovementAction
{
    /**
     * Record a stock-in delivery and increase product stock.
     */
    public function stockIn(Product $product, int $quantity, ?string $reason, ?User $user): StockMovement
    {
        if ($quantity < 1) {
            throw ValidationException::withMessages([
                'quantity' => 'Stock-in quantity must be at least 1.',
            ]);
        }

        return DB::transaction(function () use ($product, $quantity, $reason, $user) {
            Product::query()->whereKey($product->id)->lockForUpdate()->first();

            $product->increment('stock_quantity', $quantity);

            return StockMovement::create([
                'product_id' => $product->id,
                'user_id' => $user?->id,
                'type' => 'stock_in',
                'quantity' => $quantity,
                'reason' => $reason,
            ]);
        });
    }

    /**
     * Apply a signed manual adjustment (+/-) to product stock.
     */
    public function adjust(Product $product, int $quantity, ?string $reason, ?User $user): StockMovement
    {
        if ($quantity === 0) {
            throw ValidationException::withMessages([
                'quantity' => 'Adjustment quantity cannot be zero.',
            ]);
        }

        return DB::transaction(function () use ($product, $quantity, $reason, $user) {
            $locked = Product::query()->whereKey($product->id)->lockForUpdate()->firstOrFail();

            $resulting = $locked->stock_quantity + $quantity;

            if ($resulting < 0) {
                throw ValidationException::withMessages([
                    'quantity' => "Adjustment would result in negative stock (current: {$product->stock_quantity}).",
                ]);
            }

            $product->increment('stock_quantity', $quantity);

            return StockMovement::create([
                'product_id' => $product->id,
                'user_id' => $user?->id,
                'type' => 'adjustment',
                'quantity' => $quantity,
                'reason' => $reason,
            ]);
        });
    }
}

<?php

namespace App\Actions;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProcessSaleAction
{
    /**
     * Atomically process a POS sale: validate stock, deduct inventory,
     * record stock movements, and persist the transaction with item snapshots.
     *
     * @param  array<int, array{product_id: int, quantity: int}>  $items
     *
     * @throws ValidationException
     */
    public function execute(User $cashier, array $items, float $discount, string $paymentMethod, float $amountPaid): Transaction
    {
        return DB::transaction(function () use ($cashier, $items, $discount, $paymentMethod, $amountPaid) {
            $productIds = collect($items)->pluck('product_id')->unique()->values();

            $products = Product::query()
                ->whereIn('id', $productIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $lines = [];
            $subtotal = 0.0;

            foreach ($items as $item) {
                $product = $products->get($item['product_id']);

                if ($product === null) {
                    throw ValidationException::withMessages([
                        'items' => "Product #{$item['product_id']} could not be found or is no longer available.",
                    ]);
                }

                $quantity = (int) $item['quantity'];

                if ($quantity < 1) {
                    throw ValidationException::withMessages([
                        'items' => "Invalid quantity for {$product->name}.",
                    ]);
                }

                if ($product->stock_quantity < $quantity) {
                    throw ValidationException::withMessages([
                        'items' => "Insufficient stock for {$product->name}: only {$product->stock_quantity} left.",
                    ]);
                }

                $lineSubtotal = round((float) $product->price * $quantity, 2);
                $subtotal += $lineSubtotal;

                $lines[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'unit_price' => $product->price,
                    'subtotal' => $lineSubtotal,
                ];
            }

            $subtotal = round($subtotal, 2);
            $discount = round($discount, 2);

            if ($discount < 0 || $discount > $subtotal) {
                throw ValidationException::withMessages([
                    'discount' => 'Discount cannot be negative or greater than the subtotal.',
                ]);
            }

            $total = round($subtotal - $discount, 2);

            if ($amountPaid < $total) {
                throw ValidationException::withMessages([
                    'amount_paid' => 'Amount paid is less than the total amount due.',
                ]);
            }

            $transaction = Transaction::create([
                'transaction_number' => $this->generateTransactionNumber(),
                'user_id' => $cashier->id,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => $total,
                'payment_method' => $paymentMethod,
                'amount_paid' => round($amountPaid, 2),
                'change_amount' => round($amountPaid - $total, 2),
            ]);

            foreach ($lines as $line) {
                /** @var Product $product */
                $product = $line['product'];

                $transaction->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'subtotal' => $line['subtotal'],
                ]);

                $product->decrement('stock_quantity', $line['quantity']);

                StockMovement::create([
                    'product_id' => $product->id,
                    'user_id' => $cashier->id,
                    'type' => 'sale',
                    'quantity' => -$line['quantity'],
                    'reason' => "Sale {$transaction->transaction_number}",
                ]);
            }

            return $transaction->load(['items', 'user']);
        }, 3);
    }

    private function generateTransactionNumber(): string
    {
        $sequence = Transaction::query()
            ->whereDate('created_at', now()->toDateString())
            ->count() + 1;

        return 'TXN-'.now()->format('Ymd').'-'.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }
}

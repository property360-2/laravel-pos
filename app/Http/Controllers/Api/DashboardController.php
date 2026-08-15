<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function metrics(Request $request): JsonResponse
    {
        $period = $request->query('period', 'today');

        $since = match ($period) {
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            default => now()->startOfDay(),
        };

        $salesBase = Transaction::where('created_at', '>=', $since);

        $bestSelling = TransactionItem::query()
            ->selectRaw('product_name, SUM(quantity) as total_quantity, SUM(subtotal) as total_revenue')
            ->whereHas('transaction', fn ($q) => $q->where('created_at', '>=', $since))
            ->groupBy('product_name')
            ->orderByDesc('total_quantity')
            ->limit(5)
            ->get();

        $criticalStock = Product::query()
            ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
            ->orderBy('stock_quantity')
            ->limit(5)
            ->get(['id', 'name', 'sku', 'stock_quantity', 'low_stock_threshold']);

        return response()->json([
            'period' => $period,
            'total_products' => Product::count(),
            'low_stock_count' => Product::lowStock()->count(),
            'inventory_value' => (float) Product::query()
                ->selectRaw('SUM(stock_quantity * price) as value')
                ->value('value'),
            'sales_total' => (float) (clone $salesBase)->sum('total'),
            'transactions_count' => (clone $salesBase)->count(),
            'best_selling' => $bestSelling,
            'critical_stock' => $criticalStock,
        ]);
    }
}

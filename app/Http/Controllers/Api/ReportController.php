<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function salesSummary(Request $request): JsonResponse
    {
        [$start, $end] = $this->dateRange($request);

        $base = Transaction::query()
            ->when($start, fn ($q) => $q->whereDate('created_at', '>=', $start))
            ->when($end, fn ($q) => $q->whereDate('created_at', '<=', $end));

        $revenue = (float) (clone $base)->sum('total');
        $count = (clone $base)->count();

        return response()->json([
            'start_date' => $start,
            'end_date' => $end,
            'total_revenue' => $revenue,
            'total_transactions' => $count,
            'average_basket' => $count > 0 ? round($revenue / $count, 2) : 0,
        ]);
    }

    public function bestSelling(Request $request): JsonResponse
    {
        [$start, $end] = $this->dateRange($request);

        $bestSelling = TransactionItem::query()
            ->selectRaw('product_name, SUM(quantity) as total_quantity, SUM(subtotal) as total_revenue')
            ->whereHas('transaction', function ($q) use ($start, $end) {
                $q->when($start, fn ($qq) => $qq->whereDate('created_at', '>=', $start))
                    ->when($end, fn ($qq) => $qq->whereDate('created_at', '<=', $end));
            })
            ->groupBy('product_name')
            ->orderByDesc('total_quantity')
            ->limit($request->integer('limit', 10))
            ->get();

        return response()->json($bestSelling);
    }

    public function lowStock(): JsonResponse
    {
        return response()->json(
            Product::query()
                ->with('category:id,name')
                ->lowStock()
                ->orderBy('stock_quantity')
                ->get()
        );
    }

    public function inventoryValue(): JsonResponse
    {
        $rows = Product::query()
            ->join('categories', 'categories.id', '=', 'products.category_id')
            ->selectRaw('categories.name as category, COUNT(products.id) as product_count, SUM(products.stock_quantity) as total_stock, SUM(products.stock_quantity * products.price) as total_value')
            ->groupBy('categories.name')
            ->orderByDesc('total_value')
            ->get();

        return response()->json($rows);
    }

    public function stockMovement(Request $request): JsonResponse
    {
        [$start, $end] = $this->dateRange($request);

        $rows = StockMovement::query()
            ->selectRaw('type, SUM(quantity) as total_quantity, COUNT(*) as movement_count')
            ->when($start, fn ($q) => $q->whereDate('created_at', '>=', $start))
            ->when($end, fn ($q) => $q->whereDate('created_at', '<=', $end))
            ->groupBy('type')
            ->get();

        return response()->json($rows);
    }

    private function dateRange(Request $request): array
    {
        return [
            $request->query('start_date'),
            $request->query('end_date'),
        ];
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Actions\StockMovementAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\AdjustStockRequest;
use App\Http\Requests\StockInRequest;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function list(Request $request): JsonResponse
    {
        $products = Product::query()
            ->with('category')
            ->when($request->filled('search'), function ($query) use ($request) {
                $term = '%'.$request->string('search').'%';
                $query->where(fn ($q) => $q->where('name', 'like', $term)->orWhere('sku', 'like', $term));
            })
            ->when($request->boolean('low_stock_only'), function ($query) {
                $query->lowStock();
            })
            ->orderBy('name')
            ->get();

        return response()->json($products);
    }

    public function lowStock(): JsonResponse
    {
        return response()->json(
            Product::query()->with('category')->lowStock()->orderBy('stock_quantity')->get()
        );
    }

    public function stockIn(StockInRequest $request, StockMovementAction $action): JsonResponse
    {
        $movement = $action->stockIn(
            Product::findOrFail($request->integer('product_id')),
            $request->integer('quantity'),
            $request->input('reason'),
            $request->user(),
        );

        return response()->json($movement->load('product'), 201);
    }

    public function adjust(AdjustStockRequest $request, StockMovementAction $action): JsonResponse
    {
        $movement = $action->adjust(
            Product::findOrFail($request->integer('product_id')),
            $request->integer('quantity'),
            $request->input('reason'),
            $request->user(),
        );

        return response()->json($movement->load('product'), 201);
    }

    public function movements(Request $request): JsonResponse
    {
        $movements = StockMovement::query()
            ->with(['product:id,name,sku', 'user:id,name'])
            ->when($request->query('product_id'), function ($query, $productId) {
                $query->where('product_id', $productId);
            })
            ->when($request->query('type'), function ($query, $type) {
                $query->where('type', $type);
            })
            ->when($request->query('start_date'), function ($query, $date) {
                $query->whereDate('created_at', '>=', $date);
            })
            ->when($request->query('end_date'), function ($query, $date) {
                $query->whereDate('created_at', '<=', $date);
            })
            ->latest()
            ->paginate($request->integer('per_page', 20));

        return response()->json($movements);
    }
}

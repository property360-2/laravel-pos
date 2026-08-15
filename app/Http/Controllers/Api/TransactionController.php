<?php

namespace App\Http\Controllers\Api;

use App\Actions\ProcessSaleAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTransactionRequest;
use App\Models\Setting;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $transactions = Transaction::query()
            ->with('user:id,name')
            ->withCount('items')
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where('transaction_number', 'like', '%'.$request->string('search').'%');
            })
            ->when($request->query('start_date'), function ($query, $date) {
                $query->whereDate('created_at', '>=', $date);
            })
            ->when($request->query('end_date'), function ($query, $date) {
                $query->whereDate('created_at', '<=', $date);
            })
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return response()->json($transactions);
    }

    public function store(StoreTransactionRequest $request, ProcessSaleAction $action): JsonResponse
    {
        $transaction = $action->execute(
            $request->user(),
            $request->array('items'),
            (float) $request->float('discount', 0),
            $request->string('payment_method')->toString(),
            (float) $request->float('amount_paid'),
        );

        return response()->json($this->receiptPayload($transaction), 201);
    }

    public function show(Transaction $transaction): JsonResponse
    {
        return response()->json($this->receiptPayload($transaction));
    }

    private function receiptPayload(Transaction $transaction): array
    {
        $settings = Setting::allAsArray();

        return [
            'transaction' => $transaction->load(['items', 'user:id,name']),
            'store' => [
                'name' => $settings['store_name'] ?? 'StockFlow Store',
                'address' => $settings['store_address'] ?? '',
                'phone' => $settings['store_phone'] ?? '',
                'currency_symbol' => $settings['currency_symbol'] ?? '₱',
                'receipt_footer' => $settings['receipt_footer'] ?? '',
            ],
        ];
    }
}

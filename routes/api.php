<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\TransactionController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/dashboard', [DashboardController::class, 'metrics']);

    Route::get('/products', [ProductController::class, 'index']);
    Route::post('/products', [ProductController::class, 'store']);
    Route::get('/products/{product}', [ProductController::class, 'show']);
    Route::match(['put', 'patch'], '/products/{product}', [ProductController::class, 'update']);
    Route::delete('/products/{product}', [ProductController::class, 'destroy']);

    Route::get('/categories', [CategoryController::class, 'index']);
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::get('/categories/{category}', [CategoryController::class, 'show']);
    Route::match(['put', 'patch'], '/categories/{category}', [CategoryController::class, 'update']);
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);

    Route::get('/transactions', [TransactionController::class, 'index']);
    Route::post('/transactions', [TransactionController::class, 'store']);
    Route::get('/transactions/{transaction}', [TransactionController::class, 'show']);

    Route::get('/inventory', [InventoryController::class, 'list']);
    Route::get('/inventory/low-stock', [InventoryController::class, 'lowStock']);
    Route::post('/inventory/stock-in', [InventoryController::class, 'stockIn']);
    Route::post('/inventory/adjust', [InventoryController::class, 'adjust']);
    Route::get('/inventory/movements', [InventoryController::class, 'movements']);

    Route::get('/reports/sales-summary', [ReportController::class, 'salesSummary']);
    Route::get('/reports/sales-trend', [ReportController::class, 'salesTrend']);
    Route::get('/reports/sales-by-category', [ReportController::class, 'salesByCategory']);
    Route::get('/reports/best-selling', [ReportController::class, 'bestSelling']);
    Route::get('/reports/low-stock', [ReportController::class, 'lowStock']);
    Route::get('/reports/inventory-value', [ReportController::class, 'inventoryValue']);
    Route::get('/reports/stock-movement', [ReportController::class, 'stockMovement']);

    Route::match(['put', 'patch'], '/settings', [SettingController::class, 'update']);
});

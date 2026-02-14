<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {
    Route::get('/health', fn () => response()->json(['status' => 'ok', 'app' => 'Gift Box API']));

    Route::get('/categories', [App\Http\Controllers\Api\V1\CategoryController::class, 'index']);
    Route::get('/categories/{category}', [App\Http\Controllers\Api\V1\CategoryController::class, 'show']);

    Route::get('/brands', [App\Http\Controllers\Api\V1\BrandController::class, 'index']);
    Route::get('/brands/{brand}', [App\Http\Controllers\Api\V1\BrandController::class, 'show']);

    Route::get('/vouchers', [App\Http\Controllers\Api\V1\VoucherController::class, 'index']);
    Route::get('/vouchers/{voucher}', [App\Http\Controllers\Api\V1\VoucherController::class, 'show']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::apiResource('orders', App\Http\Controllers\Api\V1\OrderController::class)->only(['index', 'show', 'store']);
    });
});

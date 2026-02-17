<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {
    Route::get('/health', fn () => response()->json(['status' => 'ok', 'app' => config('app.name').' API']));

    // Public APIs (no auth required)
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{slug}', [CategoryController::class, 'show']);
    Route::get('/products/hot-deals', [ProductController::class, 'hotDeals']);
    Route::get('/products/trending', [ProductController::class, 'trending']);
    Route::get('/products/best-sellers', [ProductController::class, 'bestSellers']);
    Route::get('/products/featured', [ProductController::class, 'featured']);
    Route::get('/products/new-arrivals', [ProductController::class, 'newArrivals']);
    Route::get('/products/{slug}', [ProductController::class, 'show']);

    // Auth APIs (public routes for login/register)
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/otp/send', [AuthController::class, 'sendOtp']);
        Route::post('/otp/verify', [AuthController::class, 'verifyOtp']);
        Route::post('/google', [AuthController::class, 'google']);
    });

    // Protected APIs (auth required - checkout, orders, payments)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);
        // Placeholder for future: checkout, orders, payments
    });
});

<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BalanceController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\LoyaltyController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\PayUController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\TagController;
use App\Http\Controllers\Api\V1\WebhookPaymentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {
    Route::get('/health', fn () => response()->json(['status' => 'ok', 'app' => config('app.name').' API']));

    // Gift card balance check — public, no auth required
    Route::post('/balance', [BalanceController::class, 'check']);

    // Public APIs (no auth required)
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{slug}', [CategoryController::class, 'show']);
    Route::get('/tags', [TagController::class, 'index']);
    Route::get('/tags/{slug}', [TagController::class, 'show']);
    Route::get('/products/search',    [ProductController::class, 'search']);
    Route::get('/products/hot-deals', [ProductController::class, 'hotDeals']);
    Route::get('/products/trending', [ProductController::class, 'trending']);
    Route::get('/products/best-sellers', [ProductController::class, 'bestSellers']);
    Route::get('/products/featured', [ProductController::class, 'featured']);
    Route::get('/products/new-arrivals', [ProductController::class, 'newArrivals']);
    Route::get('/products/{product}', [ProductController::class, 'show']);

    // Auth APIs (public routes for login/register)
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/otp/send', [AuthController::class, 'sendOtp']);
        Route::post('/otp/verify', [AuthController::class, 'verifyOtp']);
        Route::post('/google', [AuthController::class, 'google']);
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('/reset-password', [AuthController::class, 'resetPassword']);
        Route::post('/verify-email', [ProfileController::class, 'verifyEmailWithOtp']);
    });

    // Protected: Orders & payment (logged-in user only)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);

        // Order APIs (one product per order; requires login)
        Route::get('/orders', [OrderController::class, 'index']);
        Route::post('/order', [OrderController::class, 'store']);
        Route::get('/order', [OrderController::class, 'show']);
        Route::get('/order/{order}', [OrderController::class, 'showById']);
        Route::get('/order/{order}/cards',  [OrderController::class, 'fetchCards']);
        Route::post('/order/{order}/resend', [OrderController::class, 'resend']);
        Route::post('/order/item', [OrderController::class, 'setItem']);
        Route::put('/order/item', [OrderController::class, 'updateItem']);
        Route::delete('/order/item', [OrderController::class, 'clearItem']);

        // Profile management
        Route::prefix('profile')->group(function () {
            Route::put('/',                   [ProfileController::class, 'update']);
            Route::post('/avatar',            [ProfileController::class, 'uploadAvatar']);
            Route::delete('/avatar',          [ProfileController::class, 'removeAvatar']);
            Route::post('/email',             [ProfileController::class, 'requestEmailChange']);
            Route::post('/email/verify',      [ProfileController::class, 'verifyEmailChange']);
            Route::post('/email/resend',      [ProfileController::class, 'resendEmailVerification']);
            Route::post('/phone',             [ProfileController::class, 'requestPhoneChange']);
            Route::post('/phone/verify',      [ProfileController::class, 'verifyPhoneChange']);
            Route::post('/password',          [ProfileController::class, 'changePassword']);
        });

        // PayU payment initiation (authenticated)
        Route::post('/payment/initiate', [PayUController::class, 'initiate']);

        // Loyalty program (authenticated)
        Route::prefix('loyalty')->group(function () {
            Route::get('/balance',  [LoyaltyController::class, 'balance']);
            Route::get('/history',  [LoyaltyController::class, 'history']);
            Route::get('/estimate', [LoyaltyController::class, 'estimate']);
        });
    });

    // PayU callbacks — server-to-server POSTs from PayU (no auth middleware)
    Route::post('/payment/payu/success', [PayUController::class, 'payuSuccess']);
    Route::post('/payment/payu/failure', [PayUController::class, 'payuFailure']);

    // Legacy/manual webhook: server-to-server, secured by gateway webhook secret
    Route::post('/webhooks/payment-success', [WebhookPaymentController::class, 'paymentSuccess']);
});

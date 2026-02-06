<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GatewayController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentController;
use Illuminate\Support\Facades\Route;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('user', [AuthController::class, 'me']);

    // Orders
    Route::apiResource('orders', OrderController::class); // Full CRUD
    Route::post('orders/{order}/pay', [PaymentController::class, 'store']); // Process Payment

    // Payments
    Route::get('payments', [PaymentController::class, 'index']);
    Route::get('payments/{payment}', [PaymentController::class, 'show']);

    // Gateways
    Route::apiResource('gateways', GatewayController::class)->only(['index', 'store']);
});

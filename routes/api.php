<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ShippingWebhookController;
use App\Http\Controllers\Api\ProductController;

Route::post('/customers', [OrderController::class, 'storeCustomer']);
Route::post('/shipping/webhook', [ShippingWebhookController::class, 'handle']);
Route::post('/order', [OrderController::class, 'storeOrder']);
Route::post('/orders/{order}/place', [OrderController::class, 'placeOrder']);
Route::patch('/orders/{order}/status', [OrderController::class, 'changeStatus']);

Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
    Route::get('/products', [ProductController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
});
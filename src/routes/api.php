<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductReviewController;

Route::apiResource('products', ProductController::class)
    ->only(['index', 'show']);

Route::middleware('auth:api')->group(function () {
    Route::apiResource('products', ProductController::class)
        ->only(['store', 'update', 'destroy']);

    Route::post('/products/{product}/reviews', [ProductReviewController::class, 'store']);
    Route::delete('/products/{product}/reviews/{review}', [ProductReviewController::class, 'destroy']);
});

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::get('me', [AuthController::class, 'me']);
    Route::post('logout', [AuthController::class, 'logout']);
});

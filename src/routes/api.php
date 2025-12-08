<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductReviewController;
use Illuminate\Support\Facades\Route;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::get('products', [ProductController::class, 'index']);
Route::get('products/{product}', [ProductController::class, 'show'])
    ->middleware('product.published');

Route::middleware('auth:api')->group(function () {
    Route::get('me', [AuthController::class, 'me']);
    Route::post('logout', [AuthController::class, 'logout']);

    Route::middleware('admin')->group(function () {
        Route::post('products', [ProductController::class, 'store']);
        Route::patch('products/{product}', [ProductController::class, 'update']);
        Route::delete('products/{product}', [ProductController::class, 'destroy']);
    });

    Route::post('products/{product}/reviews', [ProductReviewController::class, 'store'])
        ->middleware('product.published');

    Route::patch('products/{product}/reviews/{review}', [ProductReviewController::class, 'update']);

    Route::delete('products/{product}/reviews/{review}', [ProductReviewController::class, 'destroy'])
        ->middleware('review.belongs-to-product');
});

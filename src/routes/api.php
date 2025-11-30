<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductReviewController;

Route::apiResource('products', ProductController::class);

Route::post('/products/{product}/reviews', [ProductReviewController::class, 'store']);
Route::delete('/products/{product}/reviews/{review}', [ProductReviewController::class, 'destroy']);

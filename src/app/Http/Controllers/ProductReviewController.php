<?php

namespace App\Http\Controllers;

use App\Enums\ProductStatus;
use App\Http\Requests\StoreProductReviewRequest;
use App\Http\Resources\ProductReviewResource;
use App\Models\Product;
use App\Models\User;

class ProductReviewController extends Controller
{

    public function store(StoreProductReviewRequest $request, Product $product)
    {
        // TODO: Перенести в middleware
        if ($product->status === ProductStatus::Draft) {
            return response()->json(['message' => 'Товар не найден'], 404);
        }

        // TODO: заменить на auth()->id() после реализации авторизации
        $user = User::inRandomOrder()->first();

        $review = $product->productReviews()->make();
        $review->user_id = $user->id;
        $review->text = $request->text;
        $review->rating = $request->rating;
        $review->save();

        return new ProductReviewResource($review);
    }
}

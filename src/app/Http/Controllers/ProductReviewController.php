<?php

namespace App\Http\Controllers;

use App\Enums\ProductStatus;
use App\Http\Requests\StoreProductReviewRequest;
use App\Http\Resources\ProductReviewResource;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class ProductReviewController extends Controller
{
    public function store(StoreProductReviewRequest $request, Product $product)
    {
        // TODO: Перенести в middleware
        if ($product->status === ProductStatus::Draft) {
            return response()->json(['message' => 'Товар не найден'], 404);
        }

        $review = $product->productReviews()->make();
        $review->user_id = auth()->id();
        $review->text = $request->text;
        $review->rating = $request->rating;
        $review->save();

        return new ProductReviewResource($review);
    }

    // TODO: Добавить политику: отзыв может удалить только автор отзыва и админ
    public function destroy(Product $product, ProductReview $review): JsonResponse
    {
        // TODO: Перенести в middleware
        if ($review->product_id !== $product->id) {
            return response()->json(['message' => 'Доступ запрещён'], 403);
        }

        $review->delete();

        return response()->json(null, 204);

    }
}

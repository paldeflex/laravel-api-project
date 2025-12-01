<?php

namespace App\Http\Controllers;

use App\Enums\ProductStatus;
use App\Http\Requests\StoreProductReviewRequest;
use App\Http\Resources\ProductReviewResource;
use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ProductReviewController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('product.published', only: ['store']),
        ];
    }

    public function store(StoreProductReviewRequest $request, Product $product)
    {
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

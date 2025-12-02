<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductReviewRequest;
use App\Http\Requests\UpdateProductReviewRequest;
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
            new Middleware('review.belongs-to-product', only: ['destroy']),
        ];
    }

    public function store(StoreProductReviewRequest $request, Product $product): ProductReviewResource
    {
        $review = $product->productReviews()->make();
        $review->user_id = auth()->id();
        $review->text = $request->text;
        $review->rating = $request->rating;
        $review->save();

        return new ProductReviewResource($review);
    }

    public function update(UpdateProductReviewRequest $request, Product $product, ProductReview $review): ProductReviewResource
    {
        $this->authorize('update', $review);

        $review->update($request->validated());

        return new ProductReviewResource($review);
    }

    public function destroy(Product $product, ProductReview $review): JsonResponse
    {
        $this->authorize('delete', $review);

        $review->delete();

        return response()->json(null, 204);
    }
}

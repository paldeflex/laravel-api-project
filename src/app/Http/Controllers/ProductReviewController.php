<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductReviewRequest;
use App\Http\Requests\UpdateProductReviewRequest;
use App\Http\Resources\ProductReviewResource;
use App\Models\Product;
use App\Models\ProductReview;
use App\Services\ProductReviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ProductReviewController extends Controller implements HasMiddleware
{
    public function __construct(
        protected ProductReviewService $productReviewService,
    ) {
    }

    public static function middleware(): array
    {
        return [
            new Middleware('product.published', only: ['store']),
            new Middleware('review.belongs-to-product', only: ['destroy']),
        ];
    }

    public function store(StoreProductReviewRequest $request, Product $product): ProductReviewResource
    {
        $review = $this->productReviewService->createReview(
            $product,
            auth()->id(),
            $request->validated()
        );

        return new ProductReviewResource($review);
    }

    public function update(UpdateProductReviewRequest $request, Product $product, ProductReview $review): ProductReviewResource
    {
        $this->authorize('update', $review);

        $review = $this->productReviewService->updateReview(
            $review,
            $request->validated()
        );

        return new ProductReviewResource($review);
    }

    public function destroy(Product $product, ProductReview $review): JsonResponse
    {
        $this->authorize('delete', $review);

        $this->productReviewService->deleteReview($review);

        return response()->json(null, 204);
    }
}

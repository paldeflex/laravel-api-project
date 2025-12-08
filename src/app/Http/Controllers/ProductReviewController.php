<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\DTO\ProductReviewCreateData;
use App\DTO\ProductReviewUpdateData;
use App\Http\Requests\StoreProductReviewRequest;
use App\Http\Requests\UpdateProductReviewRequest;
use App\Http\Resources\ProductReviewResource;
use App\Models\Product;
use App\Models\ProductReview;
use App\Services\ProductReviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Symfony\Component\HttpFoundation\Response;

final class ProductReviewController extends Controller implements HasMiddleware
{
    public function __construct(
        private readonly ProductReviewService $productReviewService,
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
        $validated = $request->validated();

        $dto = new ProductReviewCreateData(
            userId: auth()->id(),
            text: $validated['text'],
            rating: $validated['rating'] ?? null,
        );

        $review = $this->productReviewService->createReview(
            $product,
            $dto
        );

        return new ProductReviewResource($review);
    }


    public function update(UpdateProductReviewRequest $request, Product $product, ProductReview $review): ProductReviewResource
    {
        $this->authorize('update', $review);

        $validated = $request->validated();

        $dto = new ProductReviewUpdateData(
            text: $validated['text'] ?? null,
            rating: $validated['rating'] ?? null,
        );

        $review = $this->productReviewService->updateReview(
            $review,
            $dto
        );

        return new ProductReviewResource($review);
    }

    public function destroy(Product $product, ProductReview $review): JsonResponse
    {
        $this->authorize('delete', $review);

        $this->productReviewService->deleteReview($review);

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}

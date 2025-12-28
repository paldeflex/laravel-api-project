<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\DTO\Reviews\ProductReviewCreateData;
use App\DTO\Reviews\ProductReviewUpdateData;
use App\Http\Requests\Reviews\StoreProductReviewRequest;
use App\Http\Requests\Reviews\UpdateProductReviewRequest;
use App\Http\Resources\Reviews\ProductReviewResource;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;
use App\Services\Reviews\ProductReviewService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class ProductReviewController extends Controller
{
    public function __construct(
        private readonly ProductReviewService $productReviewService,
    ) {}

    public function store(StoreProductReviewRequest $request, Product $product): ProductReviewResource
    {
        /** @var User $user */
        $user = $request->user();

        /** @var array{text: string, rating?: int|string|null} $data */
        $data = $request->validated();

        $dto = ProductReviewCreateData::fromArray(
            data: $data,
            userId: $user->id,
        );

        $review = $this->productReviewService->createReview(
            product: $product,
            data: $dto,
        );

        return new ProductReviewResource($review);
    }

    public function update(UpdateProductReviewRequest $request, Product $product, ProductReview $review): ProductReviewResource
    {
        $this->authorize('update', $review);

        /** @var array{text?: string|null, rating?: int|string|null} $data */
        $data = $request->validated();

        $dto = ProductReviewUpdateData::fromArray($data);

        $review = $this->productReviewService->updateReview(
            review: $review,
            data: $dto,
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

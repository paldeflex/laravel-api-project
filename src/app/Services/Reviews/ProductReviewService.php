<?php

declare(strict_types=1);

namespace App\Services\Reviews;

use App\DTO\Reviews\ProductReviewCreateData;
use App\DTO\Reviews\ProductReviewUpdateData;
use App\Models\Product;
use App\Models\ProductReview;
use App\Repositories\Contracts\Reviews\ProductReviewRepositoryInterface;

final readonly class ProductReviewService
{
    public function __construct(
        private ProductReviewRepositoryInterface $reviewRepository,
    ) {}

    public function createReview(Product $product, ProductReviewCreateData $data): ProductReview
    {
        return $this->reviewRepository->create($product, $data);
    }

    public function updateReview(ProductReview $review, ProductReviewUpdateData $data): ProductReview
    {
        return $this->reviewRepository->update($review, $data);
    }

    public function deleteReview(ProductReview $review): void
    {
        $this->reviewRepository->delete($review);
    }
}

<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\ProductReviewCreateData;
use App\DTO\ProductReviewUpdateData;
use App\Models\Product;
use App\Models\ProductReview;

final class ProductReviewService
{
    public function createReview(Product $product, ProductReviewCreateData $data): ProductReview
    {
        /** @var ProductReview $review */
        $review = $product
            ->productReviews()
            ->create([
                'user_id' => $data->userId,
                'text' => $data->text,
                'rating' => $data->rating,
            ]);

        return $review;
    }

    public function updateReview(ProductReview $review, ProductReviewUpdateData $data): ProductReview
    {
        $review->update($data->toArray());

        return $review;
    }

    public function deleteReview(ProductReview $review): void
    {
        $review->delete();
    }
}

<?php

declare(strict_types=1);

namespace App\Repositories\Reviews;

use App\DTO\Reviews\ProductReviewCreateData;
use App\DTO\Reviews\ProductReviewUpdateData;
use App\Models\Product;
use App\Models\ProductReview;
use App\Repositories\Contracts\Reviews\ProductReviewRepositoryInterface;

final class ProductReviewRepository implements ProductReviewRepositoryInterface
{
    public function create(Product $product, ProductReviewCreateData $data): ProductReview
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

    public function update(ProductReview $review, ProductReviewUpdateData $data): ProductReview
    {
        $review->update($data->toArray());

        return $review;
    }

    public function delete(ProductReview $review): void
    {
        $review->delete();
    }
}

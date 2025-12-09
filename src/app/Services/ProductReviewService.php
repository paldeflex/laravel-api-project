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
        return $product
            ->productReviews()
            ->create([
                'user_id' => $data->userId,
                'text'    => $data->text,
                'rating'  => $data->rating,
            ]);
    }

    public function updateReview(ProductReview $review, ProductReviewUpdateData $data): ProductReview
    {
        $payload = [];

        if (! is_null($data->text)) {
            $payload['text'] = $data->text;
        }

        if (! is_null($data->rating)) {
            $payload['rating'] = $data->rating;
        }

        $review->update($payload);

        return $review;
    }

    public function deleteReview(ProductReview $review): void
    {
        $review->delete();
    }
}

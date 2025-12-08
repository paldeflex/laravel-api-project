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
        $review = $product->productReviews()->make();
        $review->user_id = $data->userId;
        $review->text = $data->text;
        $review->rating = $data->rating;
        $review->save();

        return $review;
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

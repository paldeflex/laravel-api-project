<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductReview;

class ProductReviewService
{
    public function createReview(Product $product, int $userId, array $data): ProductReview
    {
        $review = $product->productReviews()->make();
        $review->user_id = $userId;
        $review->text = $data['text'];
        $review->rating = $data['rating'];
        $review->save();

        return $review;
    }

    public function updateReview(ProductReview $review, array $data): ProductReview
    {
        $review->update($data);

        return $review;
    }

    public function deleteReview(ProductReview $review): void
    {
        $review->delete();
    }
}

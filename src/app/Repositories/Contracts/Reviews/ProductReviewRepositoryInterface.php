<?php

declare(strict_types=1);

namespace App\Repositories\Contracts\Reviews;

use App\DTO\Reviews\ProductReviewCreateData;
use App\DTO\Reviews\ProductReviewUpdateData;
use App\Models\Product;
use App\Models\ProductReview;

interface ProductReviewRepositoryInterface
{
    public function create(Product $product, ProductReviewCreateData $data): ProductReview;

    public function update(ProductReview $review, ProductReviewUpdateData $data): ProductReview;

    public function delete(ProductReview $review): void;
}

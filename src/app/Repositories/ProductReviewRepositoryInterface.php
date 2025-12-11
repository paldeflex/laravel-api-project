<?php

declare(strict_types=1);

namespace App\Repositories;

use App\DTO\ProductReviewCreateData;
use App\DTO\ProductReviewUpdateData;
use App\Models\Product;
use App\Models\ProductReview;

interface ProductReviewRepositoryInterface
{
    public function create(Product $product, ProductReviewCreateData $data): ProductReview;

    public function update(ProductReview $review, ProductReviewUpdateData $data): ProductReview;

    public function delete(ProductReview $review): void;
}

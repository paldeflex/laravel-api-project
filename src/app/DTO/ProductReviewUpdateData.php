<?php

namespace App\DTO;

readonly class ProductReviewUpdateData
{
    public function __construct(
        public ?string $text = null,
        public ?int    $rating = null,
    ) {
    }
}

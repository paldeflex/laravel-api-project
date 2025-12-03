<?php

namespace App\DTO;

readonly class ProductReviewCreateData
{
    public function __construct(
        public int    $userId,
        public string $text,
        public ?int   $rating,
    ) {
    }
}

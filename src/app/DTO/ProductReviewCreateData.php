<?php

declare(strict_types=1);

namespace App\DTO;

final readonly class ProductReviewCreateData
{
    public function __construct(
        public int    $userId,
        public string $text,
        public ?int   $rating,
    ) {
    }
}

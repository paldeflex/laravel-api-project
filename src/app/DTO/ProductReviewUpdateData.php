<?php

declare(strict_types=1);

namespace App\DTO;

final readonly class ProductReviewUpdateData
{
    public function __construct(
        public ?string $text = null,
        public ?int    $rating = null,
    ) {
    }
}

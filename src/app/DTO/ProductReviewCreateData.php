<?php

declare(strict_types=1);

namespace App\DTO;

final readonly class ProductReviewCreateData
{
    public function __construct(
        public int $userId,
        public string $text,
        public ?int $rating,
    ) {}

    public static function fromArray(array $data, int $userId): self
    {
        return new self(
            userId: $userId,
            text: $data['text'],
            rating: $data['rating'] ?? null,
        );
    }
}

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

    /**
     * @param  array{text: string, rating?: int|string|null}  $data
     */
    public static function fromArray(array $data, int $userId): self
    {
        return new self(
            userId: $userId,
            text: $data['text'],
            rating: array_key_exists('rating', $data) && $data['rating'] !== null
                ? (int) $data['rating']
                : null,
        );
    }
}

<?php

declare(strict_types=1);

namespace App\DTO\Reviews;

final readonly class ProductReviewUpdateData
{
    public function __construct(
        public ?string $text = null,
        public ?int $rating = null,
    ) {}

    /**
     * @param  array{text?: string|null, rating?: int|string|null}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            text: $data['text'] ?? null,
            rating: array_key_exists('rating', $data) && $data['rating'] !== null
                ? (int) $data['rating']
                : null,
        );
    }

    /**
     * @return array<string, int|string>
     */
    public function toArray(): array
    {
        return array_filter(
            [
                'text' => $this->text,
                'rating' => $this->rating,
            ],
            static fn (mixed $value): bool => $value !== null,
        );
    }
}

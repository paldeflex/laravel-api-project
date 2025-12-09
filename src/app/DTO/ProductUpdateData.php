<?php

declare(strict_types=1);

namespace App\DTO;

use App\Enums\ProductStatus;

final readonly class ProductUpdateData
{
    public function __construct(
        public ?string $name = null,
        public ?string $description = null,
        public ?int $quantity = null,
        public ?int $price = null,
        public ?ProductStatus $status = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? null,
            description: $data['description'] ?? null,
            quantity: isset($data['quantity']) ? (int) $data['quantity'] : null,
            price: isset($data['price']) ? (int) $data['price'] : null,
            status: isset($data['status'])
                ? ProductStatus::from($data['status'])
                : null,
        );
    }

    public function toArray(): array
    {
        return array_filter(
            [
                'name' => $this->name,
                'description' => $this->description,
                'quantity' => $this->quantity,
                'price' => $this->price,
                'status' => $this->status?->value,
            ],
            static fn (mixed $value): bool => $value !== null,
        );
    }
}

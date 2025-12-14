<?php

declare(strict_types=1);

namespace App\DTO\Products;

use App\Enums\Products\ProductStatus;

final readonly class ProductUpdateData
{
    public function __construct(
        public ?string $name = null,
        public ?string $description = null,
        public ?int $quantity = null,
        public ?int $price = null,
        public ?ProductStatus $status = null,
    ) {}

    /**
     * @param  array{name?: string|null, description?: string|null, quantity?: int|string|null, price?: int|string|null, status?: int|string|null}  $data
     */
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

    /**
     * @return array<string, int|string>
     */
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

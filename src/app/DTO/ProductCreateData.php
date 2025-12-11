<?php

declare(strict_types=1);

namespace App\DTO;

use App\Enums\ProductStatus;

final readonly class ProductCreateData
{
    public function __construct(
        public string $name,
        public ?string $description,
        public ?int $quantity,
        public ?int $price,
        public ?ProductStatus $status,
    ) {}

    /**
     * @param  array{name: string, description?: string|null, quantity?: int|string|null, price?: int|string|null, status?: int|string|null}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            description: $data['description'] ?? null,
            quantity: $data['quantity'] ?? null,
            price: $data['price'] ?? null,
            status: isset($data['status'])
                ? ProductStatus::from($data['status'])
                : null,
        );
    }
}

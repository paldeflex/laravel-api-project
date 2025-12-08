<?php

declare(strict_types=1);

namespace App\DTO;

use App\Enums\ProductStatus;

final readonly class ProductCreateData
{
    public function __construct(
        public string         $name,
        public ?string        $description,
        public ?int           $quantity,
        public ?int           $price,
        public ?ProductStatus $status,
    ) {
    }
}

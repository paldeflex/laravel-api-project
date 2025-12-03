<?php

namespace App\DTO;

use App\Enums\ProductStatus;

readonly class ProductCreateData
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

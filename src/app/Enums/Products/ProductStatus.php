<?php

declare(strict_types=1);

namespace App\Enums\Products;

enum ProductStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
}

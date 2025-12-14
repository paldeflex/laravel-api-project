<?php

declare(strict_types=1);

namespace App\Services\Contracts\Products;

use App\Models\Product;
use Illuminate\Http\UploadedFile;

interface ProductImageStorageInterface
{
    public function store(Product $product, UploadedFile $file): void;
}

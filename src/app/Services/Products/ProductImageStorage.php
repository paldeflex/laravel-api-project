<?php

declare(strict_types=1);

namespace App\Services\Products;

use App\Models\Product;
use App\Services\Contracts\Products\ProductImageStorageInterface;
use Illuminate\Http\UploadedFile;

final class ProductImageStorage implements ProductImageStorageInterface
{
    public function store(Product $product, UploadedFile $file): void
    {
        $directory = 'products/'.$product->id;

        $path = $file->store($directory, 'public');

        $product->productImages()->create([
            'path' => $path,
        ]);
    }
}

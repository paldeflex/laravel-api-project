<?php

declare(strict_types=1);

namespace App\Services\Products;

use App\Models\Product;
use App\Services\Contracts\Products\ProductImageStorageInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

final class ProductImageStorage implements ProductImageStorageInterface
{
    private const string DISK = 'public';

    public function store(Product $product, UploadedFile $file): void
    {
        $directory = 'products/'.$product->id;

        $path = Storage::disk(self::DISK)->putFile($directory, $file);

        $product->productImages()->create([
            'path' => $path,
        ]);
    }
}

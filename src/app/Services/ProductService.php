<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\ProductCreateData;
use App\DTO\ProductUpdateData;
use App\Enums\ProductStatus;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;

final class ProductService
{
    public function getPublishedProducts(): LengthAwarePaginator
    {
        return Product::query()
            ->where('status', ProductStatus::Published)
            ->withAvg('productReviews', 'rating')
            ->with('productImages')
            ->paginate(100);
    }

    public function createProduct(ProductCreateData $data, int $userId, ?array $images = null): Product
    {
        $product = Product::create([
            'user_id' => $userId,
            'name' => $data->name,
            'description' => $data->description,
            'quantity' => $data->quantity,
            'price' => $data->price,
            'status' => $data->status?->value,
        ]);

        if ($images) {
            $this->handleImages($product, $images);
        }

        $product->load('productImages');

        return $product;
    }

    public function updateProduct(Product $product, ProductUpdateData $data, ?array $images = null): Product
    {
        $product->update($data->toArray());

        if ($images) {
            $this->handleImages($product, $images);
        }

        $product->load('productImages');

        return $product;
    }

    public function getProductForShow(Product $product): Product
    {
        $product->loadAvg('productReviews', 'rating')
            ->load('productImages', 'productReviews');

        return $product;
    }

    public function deleteProduct(Product $product): void
    {
        $product->delete();
    }

    private function handleImages(Product $product, array $images): void
    {
        $imagesDirectory = $this->getProductImagesDirectory($product);

        foreach ($images as $image) {
            if (! $image instanceof UploadedFile) {
                continue;
            }

            $imagePath = $image->store($imagesDirectory, 'public');

            $product->productImages()->create([
                'path' => $imagePath,
            ]);
        }
    }

    private function getProductImagesDirectory(Product $product): string
    {
        return 'products/'.$product->id;
    }
}

<?php

namespace App\Services;

use App\Enums\ProductStatus;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;

class ProductService
{
    public function getPublishedProducts(): LengthAwarePaginator
    {
        return Product::query()
            ->where('status', ProductStatus::Published)
            ->withAvg('productReviews', 'rating')
            ->with('productImages')
            ->paginate(100);
    }

    public function createProduct(array $data, int $userId, ?array $images = null): Product
    {
        $data['user_id'] = $userId;

        $product = Product::create($data);

        if ($images) {
            $this->handleImages($product, $images);
        }

        $product->load('productImages');

        return $product;
    }

    public function updateProduct(Product $product, array $data, ?array $images = null): Product
    {
        $product->update($data);

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
        foreach ($images as $image) {
            if (! $image instanceof UploadedFile) {
                continue;
            }

            $path = $image->store('products/'.$product->id, 'public');

            $product->productImages()->create([
                'path' => $path,
            ]);
        }
    }
}

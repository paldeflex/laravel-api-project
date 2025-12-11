<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\ProductCreateData;
use App\DTO\ProductUpdateData;
use App\Models\Product;
use App\Repositories\ProductRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;

final readonly class ProductService
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
        private ProductImageStorageInterface $productImageStorage,
    ) {}

    public function getPublishedProducts(): LengthAwarePaginator
    {
        return $this->productRepository->getPublishedProducts();
    }

    public function createProduct(ProductCreateData $data, int $userId, ?array $images = null): Product
    {
        $product = $this->productRepository->create($data, $userId);

        if ($images) {
            $this->handleImages($product, $images);
        }

        return $this->productRepository->findForShow($product);
    }

    public function updateProduct(Product $product, ProductUpdateData $data, ?array $images = null): Product
    {
        $product = $this->productRepository->update($product, $data);

        if ($images) {
            $this->handleImages($product, $images);
        }

        return $this->productRepository->findForShow($product);
    }

    public function getProductForShow(Product $product): Product
    {
        return $this->productRepository->findForShow($product);
    }

    public function deleteProduct(Product $product): void
    {
        $this->productRepository->delete($product);
    }

    private function handleImages(Product $product, array $images): void
    {
        foreach ($images as $image) {
            if (! $image instanceof UploadedFile) {
                continue;
            }

            $this->productImageStorage->store($product, $image);
        }
    }
}

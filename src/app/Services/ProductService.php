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

    /**
     * @return LengthAwarePaginator<int, Product>
     */
    public function getPublishedProducts(): LengthAwarePaginator
    {
        return $this->productRepository->getPublishedProducts();
    }

    /**
     * @param  array<int, UploadedFile>|null  $images
     */
    public function createProduct(ProductCreateData $data, int $userId, ?array $images = null): Product
    {
        $product = $this->productRepository->create($data, $userId);

        return $this->prepareProductForShow($product, $images);
    }

    /**
     * @param  array<int, UploadedFile>|null  $images
     */
    public function updateProduct(Product $product, ProductUpdateData $data, ?array $images = null): Product
    {
        $product = $this->productRepository->update($product, $data);

        return $this->prepareProductForShow($product, $images);
    }

    public function getProductForShow(Product $product): Product
    {
        return $this->productRepository->findForShow($product);
    }

    public function deleteProduct(Product $product): void
    {
        $this->productRepository->delete($product);
    }

    /**
     * @param  array<int, UploadedFile>|null  $images
     */
    private function prepareProductForShow(Product $product, ?array $images = null): Product
    {
        if ($images) {
            $this->handleImages($product, $images);
        }

        return $this->productRepository->findForShow($product);
    }

    /**
     * @param  array<int, UploadedFile>  $images
     */
    private function handleImages(Product $product, array $images): void
    {
        foreach ($images as $image) {
            $this->productImageStorage->store($product, $image);
        }
    }
}

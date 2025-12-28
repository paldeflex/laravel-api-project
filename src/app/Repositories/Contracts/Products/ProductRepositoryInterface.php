<?php

declare(strict_types=1);

namespace App\Repositories\Contracts\Products;

use App\DTO\Products\ProductCreateData;
use App\DTO\Products\ProductUpdateData;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProductRepositoryInterface
{
    /**
     * @return LengthAwarePaginator<int, Product>
     */
    public function getPublishedProducts(): LengthAwarePaginator;

    public function create(ProductCreateData $data, int $userId): Product;

    public function update(Product $product, ProductUpdateData $data): Product;

    public function findForShow(Product $product): Product;

    public function delete(Product $product): void;
}

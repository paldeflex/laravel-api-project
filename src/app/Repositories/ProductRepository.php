<?php

declare(strict_types=1);

namespace App\Repositories;

use App\DTO\ProductCreateData;
use App\DTO\ProductUpdateData;
use App\Enums\ProductStatus;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ProductRepository implements ProductRepositoryInterface
{
    public function getPublishedProducts(): LengthAwarePaginator
    {
        return Product::query()
            ->where('status', ProductStatus::Published)
            ->withAvg('productReviews', 'rating')
            ->with('productImages')
            ->paginate(100);
    }

    public function create(ProductCreateData $data, int $userId): Product
    {
        return Product::create([
            'user_id' => $userId,
            'name' => $data->name,
            'description' => $data->description,
            'quantity' => $data->quantity,
            'price' => $data->price,
            'status' => $data->status?->value,
        ]);
    }

    public function update(Product $product, ProductUpdateData $data): Product
    {
        $product->update($data->toArray());

        return $product;
    }

    public function findForShow(Product $product): Product
    {
        $product
            ->loadAvg('productReviews', 'rating')
            ->load('productImages', 'productReviews');

        return $product;
    }

    public function delete(Product $product): void
    {
        $product->delete();
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\DTO\ProductCreateData;
use App\Enums\ProductStatus;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductDetailResource;
use App\Http\Resources\ProductListResource;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

final class ProductController extends Controller implements HasMiddleware
{

    public function __construct(
        private readonly ProductService $productService,
    ) {
    }

    public static function middleware(): array
    {
        return [
            new Middleware('product.published', only: ['show']),
        ];
    }

    public function index(): AnonymousResourceCollection
    {
        $products = $this->productService->getPublishedProducts();

        return ProductListResource::collection($products);
    }


    public function store(StoreProductRequest $request): ProductDetailResource
    {
        $images = $request->hasFile('images') ? $request->file('images') : null;

        $data = $request->validated();

        $dto = new ProductCreateData(
            $data['name'],
            $data['description'] ?? null,
            $data['quantity'] ?? null,
            $data['price'] ?? null,
            isset($data['status'])
                ? ProductStatus::from($data['status'])
                : null,
        );

        $product = $this->productService->createProduct(
            $dto,
            auth()->id(),
            $images
        );

        return new ProductDetailResource($product);
    }

    public function show(Product $product): ProductDetailResource
    {
        $product = $this->productService->getProductForShow($product);

        return new ProductDetailResource($product);
    }

    public function update(UpdateProductRequest $request, Product $product): ProductDetailResource
    {
        $images = $request->hasFile('images') ? $request->file('images') : null;

        $product = $this->productService->updateProduct(
            $product,
            $request->validated(),
            $images
        );

        return new ProductDetailResource($product);
    }

    public function destroy(Product $product): JsonResponse
    {
        $this->productService->deleteProduct($product);

        return response()->json(null, 204);
    }
}

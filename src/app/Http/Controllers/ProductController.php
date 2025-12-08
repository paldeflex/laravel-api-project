<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\DTO\ProductCreateData;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductDetailResource;
use App\Http\Resources\ProductListResource;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

final class ProductController extends Controller
{

    public function __construct(
        private readonly ProductService $productService,
    ) {
    }

    public function index(): AnonymousResourceCollection
    {
        $products = $this->productService->getPublishedProducts();

        return ProductListResource::collection($products);
    }



    public function store(StoreProductRequest $request): ProductDetailResource
    {
        $images = $request->hasFile('images') ? $request->file('images') : null;

        $dto = ProductCreateData::fromArray($request->validated());

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

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}

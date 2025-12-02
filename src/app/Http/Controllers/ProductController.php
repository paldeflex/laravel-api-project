<?php

namespace App\Http\Controllers;

use App\Enums\ProductStatus;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductDetailResource;
use App\Http\Resources\ProductListResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ProductController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('product.published', only: ['show']),
        ];
    }

    public function index(): AnonymousResourceCollection
    {
        $products = Product::query()
            ->where('status', ProductStatus::Published)
            ->withAvg('productReviews', 'rating')
            ->with('productImages')
            ->paginate(100);

        return ProductListResource::collection($products);
    }


    public function store(StoreProductRequest $request): ProductDetailResource
    {
        $productData = $request->validated();
        $productData['user_id'] = auth()->id();

        $product = Product::create($productData);
        $this->handleImages($product, $request);
        $product->load('productImages');

        return new ProductDetailResource($product);
    }

    public function show(Product $product): ProductDetailResource
    {
        $product->loadAvg('productReviews', 'rating')
            ->load('productImages', 'productReviews');

        return new ProductDetailResource($product);
    }

    public function update(UpdateProductRequest $request, Product $product): ProductDetailResource
    {
        $product->update($request->validated());
        $this->handleImages($product, $request);
        $product->load('productImages');

        return new ProductDetailResource($product);
    }

    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        return response()->json(null, 204);
    }

    private function handleImages(Product $product, Request $request): void
    {
        if (! $request->hasFile('images')) {
            return;
        }

        foreach ($request->file('images') as $image) {
            $path = $image->store('products/'.$product->id, 'public');

            $product->productImages()->create([
                'path' => $path,
            ]);
        }
    }
}

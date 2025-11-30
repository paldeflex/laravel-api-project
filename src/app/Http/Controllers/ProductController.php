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

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::query()
            ->where('status', ProductStatus::Published)
            ->withAvg('productReviews', 'rating')
            ->with('productImages')
            ->paginate(100);

        return ProductListResource::collection($products);
    }

    // TODO: когда сделаю авторизацию, добавить user_id
    public function store(StoreProductRequest $request)
    {
        $product = Product::create($request->validated());
        $this->handleImages($product, $request);
        $product->load('productImages');

        return new ProductDetailResource($product);
    }

    public function show(Product $product): ProductDetailResource|JsonResponse
    {
        // TODO: Перенести в middleware
        if ($product->status === ProductStatus::Draft) {
            return response()->json(['message' => 'Товар не найден'], 404);
        }

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
        if (!$request->hasFile('images')) {
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

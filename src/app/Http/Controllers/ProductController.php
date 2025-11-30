<?php

namespace App\Http\Controllers;

use App\Enums\ProductStatus;
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
    public function store(Request $request)
    {
        $product = Product::create($request->all());

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

    public function update(Request $request, string $id)
    {
        //
    }

    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        return response()->json(null, 204);
    }
}

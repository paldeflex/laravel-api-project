<?php

namespace App\Http\Controllers;

use App\Enums\ProductStatus;
use App\Http\Resources\ProductResource;
use App\Models\Product;
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

        return ProductResource::collection($products);
    }

    public function store(Request $request)
    {
        //
    }

    public function show(Product $product): ProductResource
    {
        if ($product->status === ProductStatus::Draft) {
            abort(404);
        }

        $product->loadAvg('productReviews', 'rating')
            ->load('productImages');


        return new ProductResource($product);
    }

    public function update(Request $request, string $id)
    {
        //
    }

    public function destroy(string $id)
    {
        //
    }
}

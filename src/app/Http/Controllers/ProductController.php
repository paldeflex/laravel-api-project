<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{

    public function index()
    {
        $products = Product::query()
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

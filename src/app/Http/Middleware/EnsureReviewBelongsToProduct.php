<?php

namespace App\Http\Middleware;

use App\Models\Product;
use App\Models\ProductReview;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureReviewBelongsToProduct
{
    public function handle(Request $request, Closure $next): Response
    {
        $product = $request->route('product');
        $review = $request->route('review');

        if ($product instanceof Product && $review instanceof ProductReview) {
            if ($review->product_id !== $product->id) {
                return response()->json(['message' => 'Доступ запрещён'], 403);
            }
        }

        return $next($request);
    }
}

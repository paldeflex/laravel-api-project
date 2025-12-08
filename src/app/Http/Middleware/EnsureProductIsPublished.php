<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\ProductStatus;
use App\Models\Product;
use Closure;
use Illuminate\Http\Request;

final class EnsureProductIsPublished
{
    public function handle(Request $request, Closure $next)
    {
        $product = $request->route('product');

        if ($product instanceof Product && $product->status === ProductStatus::Draft) {
            return response()->json(['message' => 'Товар не найден'], 404);
        }

        return $next($request);
    }
}

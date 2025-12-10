<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\ProductStatus;
use App\Models\Product;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureProductIsPublished
{
    public function handle(Request $request, Closure $next): Response
    {
        $product = $request->route('product');

        abort_if(
            $product instanceof Product && $product->status === ProductStatus::Draft,
            Response::HTTP_NOT_FOUND,
        );

        return $next($request);
    }
}

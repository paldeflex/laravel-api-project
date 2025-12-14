<?php

declare(strict_types=1);

namespace App\Http\Resources\Products;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * @mixin Product
 */
final class ProductListResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'quantity' => $this->quantity,
            'price' => $this->price,
            'status' => $this->status,
            'rating' => $this->product_reviews_avg_rating !== null
                ? round((float) $this->product_reviews_avg_rating, 1)
                : null,
            'images' => $this->whenLoaded(
                'productImages',
                fn () => $this->productImages
                    ->map(
                        fn (ProductImage $image): string => Storage::disk('public')->url($image->path)
                    )
            ),
        ];
    }
}

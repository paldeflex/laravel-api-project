<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductListResource extends JsonResource
{
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
            'images' => $this->whenLoaded('productImages', fn () => $this->productImages->pluck('path')),
        ];
    }
}

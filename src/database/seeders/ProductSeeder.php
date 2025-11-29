<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductReview;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        Product::factory()
            ->count(10)
            ->has(ProductReview::factory(5))
            ->has(ProductImage::factory(5))
            ->create();
    }
}

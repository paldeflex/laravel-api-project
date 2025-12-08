<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductReview;
use Illuminate\Database\Seeder;

final class ProductSeeder extends Seeder
{
    public function run(): void
    {
        Product::factory(10)
            ->has(ProductReview::factory(5))
            ->has(ProductImage::factory(5))
            ->create();
    }
}

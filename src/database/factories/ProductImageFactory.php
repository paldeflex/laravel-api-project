<?php

namespace Database\Factories;

use App\Models\ProductImage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductImage>
 */
class ProductImageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'path' => 'products/'
                .$this->faker->numberBetween(1, 50)
                .'/'.$this->faker->numerify('image_####.jpg'),
        ];
    }
}

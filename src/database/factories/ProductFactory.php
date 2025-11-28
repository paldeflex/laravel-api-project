<?php

namespace Database\Factories;

use App\Enums\ProductStatus;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => $this->faker->name(),
            'description' => $this->faker->text(),
            'quantity' => $this->faker->randomDigit(),
            'price' => $this->faker->randomNumber(),
            'status' => $this->faker->randomElement(ProductStatus::cases()),

        ];
    }
}

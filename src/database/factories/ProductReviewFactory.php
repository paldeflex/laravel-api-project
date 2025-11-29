<?php

namespace Database\Factories;

use App\Models\ProductReview;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductReview>
 */
class ProductReviewFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'text' => $this->faker->realText(),
            'rating' => $this->faker->numberBetween(1, 5),
        ];
    }
}

<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\DTO\ProductReviewCreateData;
use App\DTO\ProductReviewUpdateData;
use App\Enums\ProductStatus;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;
use App\Services\ProductReviewService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ProductReviewServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_review_sets_user_and_product_and_data(): void
    {
        $user = User::factory()->create();
        $productOwner = User::factory()->create();

        $product = Product::create([
            'user_id' => $productOwner->id,
            'name' => 'Product',
            'description' => 'Desc',
            'quantity' => 10,
            'price' => 1000,
            'status' => ProductStatus::Published,
        ]);

        $service = new ProductReviewService();

        $review = $service->createReview(
            $product,
            new ProductReviewCreateData(
                $user->id,
                'Nice',
                5
            )
        );

        $this->assertDatabaseHas('product_reviews', [
            'id' => $review->id,
            'product_id' => $product->id,
            'user_id' => $user->id,
            'text' => 'Nice',
            'rating' => 5,
        ]);
    }

    public function test_update_review_updates_fields(): void
    {
        $user = User::factory()->create();
        $productOwner = User::factory()->create();

        $product = Product::create([
            'user_id' => $productOwner->id,
            'name' => 'Product',
            'description' => 'Desc',
            'quantity' => 10,
            'price' => 1000,
            'status' => ProductStatus::Published,
        ]);

        $review = new ProductReview([
            'text' => 'Old',
            'rating' => 3,
        ]);

        $review->user()->associate($user);
        $product->productReviews()->save($review);

        $service = new ProductReviewService();

        $updated = $service->updateReview(
            $review,
            new ProductReviewUpdateData(
                'Updated',
                4
            )
        );

        $this->assertEquals('Updated', $updated->text);
        $this->assertEquals(4, $updated->rating);

        $this->assertDatabaseHas('product_reviews', [
            'id' => $review->id,
            'text' => 'Updated',
            'rating' => 4,
        ]);
    }

    public function test_delete_review_soft_deletes_record(): void
    {
        $user = User::factory()->create();
        $productOwner = User::factory()->create();

        $product = Product::create([
            'user_id' => $productOwner->id,
            'name' => 'Product',
            'description' => 'Desc',
            'quantity' => 10,
            'price' => 1000,
            'status' => ProductStatus::Published,
        ]);

        $review = new ProductReview([
            'text' => 'Text',
            'rating' => 3,
        ]);

        $review->user()->associate($user);
        $product->productReviews()->save($review);

        $service = new ProductReviewService();

        $service->deleteReview($review);

        $this->assertSoftDeleted('product_reviews', [
            'id' => $review->id,
        ]);
    }
}

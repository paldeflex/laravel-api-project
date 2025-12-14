<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\DTO\Reviews\ProductReviewCreateData;
use App\DTO\Reviews\ProductReviewUpdateData;
use App\Enums\Products\ProductStatus;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;
use App\Repositories\Contracts\Reviews\ProductReviewRepositoryInterface;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

final class ProductReviewRepositoryTest extends TestCase
{
    use DatabaseTransactions;

    private ProductReviewRepositoryInterface $repository;

    /**
     * @throws BindingResolutionException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->app->make(ProductReviewRepositoryInterface::class);
    }

    public function test_create_creates_review_with_user_and_product_and_fields(): void
    {
        $user = User::factory()->create();
        $productOwner = User::factory()->create();

        $product = Product::factory()->create([
            'user_id' => $productOwner->id,
            'status' => ProductStatus::Published,
        ]);

        $dto = new ProductReviewCreateData(
            userId: $user->id,
            text: 'Great!',
            rating: 5,
        );

        $review = $this->repository->create($product, $dto);

        $this->assertInstanceOf(ProductReview::class, $review);
        $this->assertNotNull($review->id);

        $this->assertDatabaseHas('product_reviews', [
            'id' => $review->id,
            'product_id' => $product->id,
            'user_id' => $user->id,
            'text' => 'Great!',
            'rating' => 5,
        ]);
    }

    public function test_update_updates_review_fields(): void
    {
        $user = User::factory()->create();
        $productOwner = User::factory()->create();

        $product = Product::factory()->create([
            'user_id' => $productOwner->id,
            'status' => ProductStatus::Published,
        ]);

        $review = new ProductReview([
            'text' => 'Old text',
            'rating' => 3,
        ]);

        $review->user()->associate($user);
        $product->productReviews()->save($review);

        $dto = ProductReviewUpdateData::fromArray([
            'text' => 'Updated text',
            'rating' => 4,
        ]);

        $updated = $this->repository->update($review, $dto);

        $this->assertEquals('Updated text', $updated->text);
        $this->assertEquals(4, $updated->rating);

        $this->assertDatabaseHas('product_reviews', [
            'id' => $review->id,
            'text' => 'Updated text',
            'rating' => 4,
        ]);
    }

    public function test_delete_soft_deletes_review(): void
    {
        $user = User::factory()->create();
        $productOwner = User::factory()->create();

        $product = Product::factory()->create([
            'user_id' => $productOwner->id,
            'status' => ProductStatus::Published,
        ]);

        $review = new ProductReview([
            'text' => 'Some text',
            'rating' => 4,
        ]);

        $review->user()->associate($user);
        $product->productReviews()->save($review);

        $this->repository->delete($review);

        $this->assertSoftDeleted('product_reviews', [
            'id' => $review->id,
        ]);
    }
}

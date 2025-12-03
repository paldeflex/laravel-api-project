<?php

namespace Tests\Feature;

use App\Enums\ProductStatus;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductReviewControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_review_for_published_product(): void
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

        $this->actingAs($user, 'api');

        $response = $this->postJson('/api/products/'.$product->id.'/reviews', [
            'text' => 'Nice product',
            'rating' => 5,
        ]);

        $response->assertSuccessful();
        $response->assertJsonPath('data.text', 'Nice product');

        $this->assertDatabaseHas('product_reviews', [
            'product_id' => $product->id,
            'user_id' => $user->id,
            'text' => 'Nice product',
            'rating' => 5,
        ]);
    }

    public function test_cannot_create_review_for_draft_product(): void
    {
        $user = User::factory()->create();
        $productOwner = User::factory()->create();

        $product = Product::create([
            'user_id' => $productOwner->id,
            'name' => 'Product',
            'description' => 'Desc',
            'quantity' => 10,
            'price' => 1000,
            'status' => ProductStatus::Draft,
        ]);

        $this->actingAs($user, 'api');

        $response = $this->postJson('/api/products/'.$product->id.'/reviews', [
            'text' => 'Nice product',
            'rating' => 5,
        ]);

        $response
            ->assertStatus(404)
            ->assertJson([
                'message' => 'Товар не найден',
            ]);
    }

    public function test_user_can_update_own_review(): void
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
            'text' => 'Old text',
            'rating' => 3,
        ]);

        $review->user()->associate($user);
        $product->productReviews()->save($review);

        $this->actingAs($user, 'api');

        $response = $this->patchJson('/api/products/'.$product->id.'/reviews/'.$review->id, [
            'text' => 'Updated text',
            'rating' => 4,
        ]);

        $response->assertSuccessful();
        $response->assertJsonPath('data.text', 'Updated text');

        $this->assertDatabaseHas('product_reviews', [
            'id' => $review->id,
            'text' => 'Updated text',
            'rating' => 4,
        ]);
    }

    public function test_user_cannot_update_foreign_review(): void
    {
        $user = User::factory()->create();
        $author = User::factory()->create();
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

        $review->user()->associate($author);
        $product->productReviews()->save($review);

        $this->actingAs($user, 'api');

        $response = $this->patchJson('/api/products/'.$product->id.'/reviews/'.$review->id, [
            'text' => 'Updated',
            'rating' => 5,
        ]);

        $response->assertStatus(403);
    }

    public function test_user_can_delete_own_review(): void
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

        $this->actingAs($user, 'api');

        $response = $this->deleteJson('/api/products/'.$product->id.'/reviews/'.$review->id);

        $response->assertNoContent();

        $this->assertSoftDeleted('product_reviews', [
            'id' => $review->id,
        ]);
    }

    public function test_cannot_delete_review_that_does_not_belong_to_product(): void
    {
        $user = User::factory()->create();
        $productOwner = User::factory()->create();

        $productOne = Product::create([
            'user_id' => $productOwner->id,
            'name' => 'Product one',
            'description' => 'Desc',
            'quantity' => 10,
            'price' => 1000,
            'status' => ProductStatus::Published,
        ]);

        $productTwo = Product::create([
            'user_id' => $productOwner->id,
            'name' => 'Product two',
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
        $productTwo->productReviews()->save($review);

        $this->actingAs($user, 'api');

        $response = $this->deleteJson('/api/products/'.$productOne->id.'/reviews/'.$review->id);

        $response
            ->assertStatus(403)
            ->assertJson([
                'message' => 'Доступ запрещён',
            ]);
    }
}

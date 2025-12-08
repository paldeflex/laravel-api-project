<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\DTO\ProductReviewCreateData;
use App\Enums\ProductStatus;
use App\Models\Product;
use App\Models\User;
use App\Services\ProductReviewService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

final class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_only_published_products(): void
    {
        $user = User::factory()->create();

        Product::create([
            'user_id' => $user->id,
            'name' => 'Published product',
            'description' => 'Desc',
            'quantity' => 10,
            'price' => 1000,
            'status' => ProductStatus::Published,
        ]);

        Product::create([
            'user_id' => $user->id,
            'name' => 'Draft product',
            'description' => 'Desc',
            'quantity' => 5,
            'price' => 500,
            'status' => ProductStatus::Draft,
        ]);

        $response = $this->getJson('/api/products');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.name', 'Published product');
    }

    public function test_index_returns_rating_for_published_product(): void
    {
        $user = User::factory()->create();

        $product = Product::create([
            'user_id' => $user->id,
            'name' => 'Product with rating',
            'description' => 'Desc',
            'quantity' => 10,
            'price' => 1000,
            'status' => ProductStatus::Published,
        ]);

        $reviewService = new ProductReviewService();

        $reviewService->createReview(
            $product,
            new ProductReviewCreateData(
                userId: $user->id,
                text: 'Good',
                rating: 4,
            )
        );

        $reviewService->createReview(
            $product,
            new ProductReviewCreateData(
                userId: $user->id,
                text: 'Great',
                rating: 5,
            )
        );

        $response = $this->getJson('/api/products');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.id', $product->id);
        $response->assertJsonPath('data.0.rating', 4.5);
    }

    public function test_show_returns_published_product_with_reviews_and_images(): void
    {
        $user = User::factory()->create();

        $product = Product::create([
            'user_id' => $user->id,
            'name' => 'Published product',
            'description' => 'Desc',
            'quantity' => 10,
            'price' => 1000,
            'status' => ProductStatus::Published,
        ]);

        $product->productImages()->create([
            'path' => 'products/'.$product->id.'/image.jpg',
        ]);

        $reviewService = new ProductReviewService();

        $review = $reviewService->createReview(
            $product,
            new ProductReviewCreateData(
                userId: $user->id,
                text: 'Nice',
                rating: 5,
            )
        );

        $response = $this->getJson('/api/products/'.$product->id);

        $response->assertOk();
        $response->assertJsonPath('data.id', $product->id);
        $response->assertJsonPath('data.reviews.0.id', $review->id);
        $response->assertJsonPath('data.rating', 5);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'description',
                'quantity',
                'price',
                'status',
                'rating',
                'images',
                'reviews',
            ],
        ]);
    }

    public function test_show_returns_404_for_draft_product(): void
    {
        $user = User::factory()->create();

        $product = Product::create([
            'user_id' => $user->id,
            'name' => 'Draft product',
            'description' => 'Desc',
            'quantity' => 5,
            'price' => 500,
            'status' => ProductStatus::Draft,
        ]);

        $response = $this->getJson('/api/products/'.$product->id);

        $response
            ->assertStatus(Response::HTTP_NOT_FOUND)
            ->assertJson([
                'message' => 'Не найдено',
            ]);
    }

    public function test_admin_can_store_product(): void
    {
        $admin = User::factory()->create();
        $admin->is_admin = true;
        $admin->save();

        $this->actingAs($admin, 'api');

        $response = $this->postJson('/api/products', [
            'name' => 'New product',
            'description' => 'Desc',
            'quantity' => 3,
            'price' => 300,
            'status' => ProductStatus::Published->value,
        ]);

        $response->assertSuccessful();
        $response->assertJsonPath('data.name', 'New product');

        $this->assertDatabaseHas('products', [
            'name' => 'New product',
            'user_id' => $admin->id,
        ]);
    }

    public function test_non_admin_cannot_store_product(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'api');

        $response = $this->postJson('/api/products', [
            'name' => 'New product',
            'description' => 'Desc',
            'quantity' => 3,
            'price' => 300,
            'status' => ProductStatus::Published->value,
        ]);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_admin_can_update_product(): void
    {
        $admin = User::factory()->create();
        $admin->is_admin = true;
        $admin->save();

        $product = Product::create([
            'user_id' => $admin->id,
            'name' => 'Old name',
            'description' => 'Desc',
            'quantity' => 3,
            'price' => 300,
            'status' => ProductStatus::Draft,
        ]);

        $this->actingAs($admin, 'api');

        $response = $this->patchJson('/api/products/'.$product->id, [
            'name' => 'Updated name',
            'price' => 500,
        ]);

        $response->assertSuccessful();
        $response->assertJsonPath('data.name', 'Updated name');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated name',
            'price' => 500,
        ]);
    }

    public function test_admin_can_destroy_product(): void
    {
        $admin = User::factory()->create();
        $admin->is_admin = true;
        $admin->save();

        $product = Product::create([
            'user_id' => $admin->id,
            'name' => 'To delete',
            'description' => 'Desc',
            'quantity' => 1,
            'price' => 100,
            'status' => ProductStatus::Draft,
        ]);

        $this->actingAs($admin, 'api');

        $response = $this->deleteJson('/api/products/'.$product->id);

        $response->assertNoContent();

        $this->assertSoftDeleted('products', [
            'id' => $product->id,
        ]);
    }
}

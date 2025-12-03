<?php

namespace Tests\Unit;

use App\Enums\ProductStatus;
use App\Models\Product;
use App\Models\User;
use App\Services\ProductReviewService;
use App\Services\ProductService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_published_products_returns_only_published_with_relations(): void
    {
        $user = User::factory()->create();

        $published = Product::create([
            'user_id' => $user->id,
            'name' => 'Published',
            'description' => 'Desc',
            'quantity' => 10,
            'price' => 1000,
            'status' => ProductStatus::Published,
        ]);

        Product::create([
            'user_id' => $user->id,
            'name' => 'Draft',
            'description' => 'Desc',
            'quantity' => 5,
            'price' => 500,
            'status' => ProductStatus::Draft,
        ]);

        $reviewService = new ProductReviewService();
        $reviewService->createReview($published, $user->id, [
            'text' => 'Ok',
            'rating' => 4,
        ]);

        $service = new ProductService();

        $paginator = $service->getPublishedProducts();

        $this->assertCount(1, $paginator->items());

        $product = $paginator->items()[0];

        $this->assertEquals('Published', $product->name);
        $this->assertTrue($product->relationLoaded('productImages'));
        $this->assertNotNull($product->product_reviews_avg_rating);
    }

    public function test_create_product_sets_user_and_saves_images(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $service = new ProductService();

        $data = [
            'name' => 'New product',
            'description' => 'Desc',
            'quantity' => 3,
            'price' => 300,
            'status' => ProductStatus::Published,
        ];

        $images = [
            UploadedFile::fake()->create('image1.jpg', 100, 'image/jpeg'),
            UploadedFile::fake()->create('image2.jpg', 100, 'image/jpeg'),
        ];

        $product = $service->createProduct($data, $user->id, $images);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'user_id' => $user->id,
            'name' => 'New product',
        ]);

        $this->assertCount(2, $product->productImages);

        foreach ($product->productImages as $image) {
            Storage::disk('public')->assertExists($image->path);
        }
    }

    public function test_update_product_updates_fields_and_appends_images(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $product = Product::create([
            'user_id' => $user->id,
            'name' => 'Old product',
            'description' => 'Old desc',
            'quantity' => 1,
            'price' => 100,
            'status' => ProductStatus::Draft,
        ]);

        $service = new ProductService();

        $images = [
            UploadedFile::fake()->create('image1.jpg', 100, 'image/jpeg'),
        ];

        $updated = $service->updateProduct($product, [
            'name' => 'Updated product',
            'price' => 200,
        ], $images);

        $this->assertEquals('Updated product', $updated->name);
        $this->assertEquals(200, $updated->price);
        $this->assertCount(1, $updated->productImages);

        foreach ($updated->productImages as $image) {
            Storage::disk('public')->assertExists($image->path);
        }
    }

    public function test_delete_product_soft_deletes_record(): void
    {
        $user = User::factory()->create();

        $product = Product::create([
            'user_id' => $user->id,
            'name' => 'To delete',
            'description' => 'Desc',
            'quantity' => 1,
            'price' => 100,
            'status' => ProductStatus::Draft,
        ]);

        $service = new ProductService();

        $service->deleteProduct($product);

        $this->assertSoftDeleted('products', [
            'id' => $product->id,
        ]);
    }
}

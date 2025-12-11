<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\DTO\ProductCreateData;
use App\DTO\ProductUpdateData;
use App\Enums\ProductStatus;
use App\Models\Product;
use App\Models\User;
use App\Repositories\ProductRepository;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ProductRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private ProductRepository $repository;

    /**
     * @throws BindingResolutionException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->app->make(ProductRepository::class);
    }

    public function test_get_published_products_returns_only_published_with_relations(): void
    {
        $user = User::factory()->create();

        Product::factory()->count(2)->create([
            'user_id' => $user->id,
            'status' => ProductStatus::Draft,
        ]);

        $publishedProducts = Product::factory()->count(3)->create([
            'user_id' => $user->id,
            'status' => ProductStatus::Published,
        ]);

        $productWithRelations = $publishedProducts->first();

        $productWithRelations->productImages()->create([
            'path' => 'products/'.$productWithRelations->id.'/image1.jpg',
        ]);

        $productWithRelations->productReviews()->createMany([
            [
                'user_id' => $user->id,
                'text' => 'Good',
                'rating' => 4,
            ],
            [
                'user_id' => $user->id,
                'text' => 'Great',
                'rating' => 5,
            ],
        ]);

        $paginator = $this->repository->getPublishedProducts();
        $items = $paginator->items();

        $this->assertCount(3, $items);

        foreach ($items as $product) {
            $this->assertInstanceOf(Product::class, $product);
            $this->assertTrue($product->status === ProductStatus::Published);

            $this->assertTrue($product->relationLoaded('productImages'));
        }

        /** @var Product $found */
        $found = collect($items)->firstWhere('id', $productWithRelations->id);

        $this->assertNotNull($found, 'Product with relations not found in paginator items');
        $this->assertNotNull($found->product_reviews_avg_rating);
        $this->assertEquals(4.5, (float) $found->product_reviews_avg_rating);
    }

    public function test_create_creates_product_with_correct_fields(): void
    {
        $user = User::factory()->create();

        $data = new ProductCreateData(
            name: 'Created product',
            description: 'Desc',
            quantity: 10,
            price: 1000,
            status: ProductStatus::Published,
        );

        $product = $this->repository->create($data, $user->id);

        $this->assertInstanceOf(Product::class, $product);
        $this->assertNotNull($product->id);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'user_id' => $user->id,
            'name' => 'Created product',
            'description' => 'Desc',
            'quantity' => 10,
            'price' => 1000,
            'status' => ProductStatus::Published->value,
        ]);
    }

    public function test_update_updates_fields_in_database(): void
    {
        $user = User::factory()->create();

        $product = Product::factory()->create([
            'user_id' => $user->id,
            'name' => 'Old name',
            'description' => 'Old desc',
            'quantity' => 1,
            'price' => 100,
            'status' => ProductStatus::Draft,
        ]);

        $data = ProductUpdateData::fromArray([
            'name' => 'Updated name',
            'description' => 'New desc',
            'price' => 500,
        ]);

        $updated = $this->repository->update($product, $data);

        $this->assertInstanceOf(Product::class, $updated);
        $this->assertEquals('Updated name', $updated->name);
        $this->assertEquals('New desc', $updated->description);
        $this->assertEquals(500, $updated->price);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated name',
            'description' => 'New desc',
            'price' => 500,
        ]);
    }

    public function test_find_for_show_loads_relations_and_rating(): void
    {
        $user = User::factory()->create();

        $product = Product::factory()->create([
            'user_id' => $user->id,
            'name' => 'Product with relations',
            'status' => ProductStatus::Published,
        ]);

        $product->productImages()->createMany([
            ['path' => 'products/'.$product->id.'/image1.jpg'],
            ['path' => 'products/'.$product->id.'/image2.jpg'],
        ]);

        $product->productReviews()->createMany([
            [
                'user_id' => $user->id,
                'text' => 'Ok',
                'rating' => 3,
            ],
            [
                'user_id' => $user->id,
                'text' => 'Nice',
                'rating' => 5,
            ],
        ]);

        $result = $this->repository->findForShow($product);

        $this->assertInstanceOf(Product::class, $result);
        $this->assertTrue($result->relationLoaded('productImages'));
        $this->assertTrue($result->relationLoaded('productReviews'));
        $this->assertNotNull($result->product_reviews_avg_rating);

        $this->assertEquals(4.0, (float) $result->product_reviews_avg_rating);
    }

    public function test_delete_soft_deletes_product(): void
    {
        $user = User::factory()->create();

        $product = Product::factory()->create([
            'user_id' => $user->id,
            'name' => 'To delete',
            'status' => ProductStatus::Draft,
        ]);

        $this->repository->delete($product);

        $this->assertSoftDeleted('products', [
            'id' => $product->id,
        ]);
    }
}

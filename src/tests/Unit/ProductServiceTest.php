<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\DTO\ProductCreateData;
use App\DTO\ProductUpdateData;
use App\Enums\ProductStatus;
use App\Models\Product;
use App\Repositories\ProductRepositoryInterface;
use App\Services\ProductImageStorageInterface;
use App\Services\ProductService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Mockery;
use Tests\TestCase;

final class ProductServiceTest extends TestCase
{
    use DatabaseTransactions;

    private ProductRepositoryInterface $productRepository;

    private ProductImageStorageInterface $imageStorage;

    private ProductService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->productRepository = Mockery::mock(ProductRepositoryInterface::class);
        $this->imageStorage = Mockery::mock(ProductImageStorageInterface::class);

        $this->service = new ProductService(
            $this->productRepository,
            $this->imageStorage,
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_create_product_calls_repository_and_image_storage(): void
    {
        $data = new ProductCreateData(
            name: 'Test product',
            description: 'Description',
            quantity: 10,
            price: 100_00,
            status: ProductStatus::Published,
        );

        $userId = 42;

        $product = new Product;
        $product->id = 123;
        $product->name = $data->name;

        $imageFile = UploadedFile::fake()->create('image.jpg', 100, 'image/jpeg');

        $this->productRepository
            ->shouldReceive('create')
            ->once()
            ->with($data, $userId)
            ->andReturn($product);

        $this->productRepository
            ->shouldReceive('findForShow')
            ->once()
            ->with($product)
            ->andReturn($product);

        $this->imageStorage
            ->shouldReceive('store')
            ->once()
            ->with($product, $imageFile);

        $result = $this->service->createProduct($data, $userId, [$imageFile]);

        $this->assertSame($product, $result);
    }

    public function test_update_product_calls_repository_and_image_storage(): void
    {
        $product = new Product;
        $product->id = 123;
        $product->name = 'Old name';

        $data = ProductUpdateData::fromArray([
            'name' => 'Updated name',
            'price' => 200,
        ]);

        $image = UploadedFile::fake()->create('image.jpg');

        $this->productRepository
            ->shouldReceive('update')
            ->once()
            ->with($product, $data)
            ->andReturn($product);

        $this->imageStorage
            ->shouldReceive('store')
            ->once()
            ->with($product, $image);

        $this->productRepository
            ->shouldReceive('findForShow')
            ->once()
            ->with($product)
            ->andReturn($product);

        $result = $this->service->updateProduct($product, $data, [$image]);

        $this->assertSame($product, $result);
    }

    public function test_get_product_for_show_calls_repository(): void
    {
        $product = new Product;
        $product->id = 55;

        $this->productRepository
            ->shouldReceive('findForShow')
            ->once()
            ->with($product)
            ->andReturn($product);

        $result = $this->service->getProductForShow($product);

        $this->assertSame($product, $result);
    }

    public function test_delete_product_calls_repository_delete(): void
    {
        $product = new Product;
        $product->id = 777;

        $this->productRepository
            ->shouldReceive('delete')
            ->once()
            ->with($product);

        $this->service->deleteProduct($product);

        $this->assertTrue(true);
    }
}

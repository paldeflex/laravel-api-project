<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\ProductRepository;
use App\Repositories\ProductRepositoryInterface;
use App\Services\ProductImageStorage;
use App\Services\ProductImageStorageInterface;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            ProductRepositoryInterface::class,
            ProductRepository::class,
        );

        $this->app->bind(
            ProductImageStorageInterface::class,
            ProductImageStorage::class,
        );
    }

    public function boot(): void
    {
        //
    }
}

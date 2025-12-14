<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\Contracts\Products\ProductRepositoryInterface;
use App\Repositories\Contracts\Reviews\ProductReviewRepositoryInterface;
use App\Repositories\Contracts\Users\UserRepositoryInterface;
use App\Repositories\Products\ProductRepository;
use App\Repositories\Reviews\ProductReviewRepository;
use App\Repositories\Users\UserRepository;
use App\Services\Auth\JwtConfigTokenTtlProvider;
use App\Services\Contracts\Auth\TokenTtlProviderInterface;
use App\Services\Contracts\Products\ProductImageStorageInterface;
use App\Services\Products\ProductImageStorage;
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

        $this->app->bind(
            ProductReviewRepositoryInterface::class,
            ProductReviewRepository::class,
        );

        $this->app->bind(
            UserRepositoryInterface::class,
            UserRepository::class,
        );

        $this->app->singleton(
            TokenTtlProviderInterface::class,
            JwtConfigTokenTtlProvider::class,
        );
    }

    public function boot(): void
    {
        //
    }
}

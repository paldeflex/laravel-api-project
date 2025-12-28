<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\ProductRepository;
use App\Repositories\ProductRepositoryInterface;
use App\Repositories\ProductReviewRepository;
use App\Repositories\ProductReviewRepositoryInterface;
use App\Repositories\UserRepository;
use App\Repositories\UserRepositoryInterface;
use App\Services\Contracts\Report\ReportLogServiceInterface;
use App\Services\ProductImageStorage;
use App\Services\ProductImageStorageInterface;
use App\Services\Report\ReportLogService;
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
            ReportLogServiceInterface::class,
            ReportLogService::class,
        );
    }

    public function boot(): void
    {
        //
    }
}

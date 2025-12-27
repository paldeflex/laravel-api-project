<?php

declare(strict_types=1);

namespace App\Providers;

use App\Adapters\TelegramAdapter;
use App\Contracts\MessengerInterface;
use App\Events\CriticalLogEvent;
use App\Listeners\TelegramLogListener;
use App\Repositories\ProductRepository;
use App\Repositories\ProductRepositoryInterface;
use App\Repositories\ProductReviewRepository;
use App\Repositories\ProductReviewRepositoryInterface;
use App\Repositories\UserRepository;
use App\Repositories\UserRepositoryInterface;
use App\Services\ProductImageStorage;
use App\Services\ProductImageStorageInterface;
use Illuminate\Support\Facades\Event;
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

        $this->app->singleton(MessengerInterface::class, function (): TelegramAdapter {
            $botToken = config('telegram.bot_token');
            $chatId = config('telegram.chat_id');

            return new TelegramAdapter(
                is_string($botToken) ? $botToken : '',
                is_string($chatId) ? $chatId : '',
            );
        });
    }

    public function boot(): void
    {
        Event::listen(CriticalLogEvent::class, TelegramLogListener::class);
    }
}

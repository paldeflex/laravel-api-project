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

        $this->app->bind(MessengerInterface::class, function () {
            /** @var string $botToken */
            $botToken = config('telegram.bot_token', '');
            /** @var string $chatId */
            $chatId = config('telegram.chat_id', '');

            return new TelegramAdapter($botToken, $chatId);
        });
    }

    public function boot(): void
    {
        Event::listen(CriticalLogEvent::class, TelegramLogListener::class);
    }
}

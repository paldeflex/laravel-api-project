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
use App\Adapters\TelegramAdapter;
use App\Contracts\MessengerInterface;
use App\Events\CriticalLogEvent;
use App\Listeners\TelegramLogListener;
use Illuminate\Support\Facades\Event;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(
            ProductRepositoryInterface::class,
            ProductRepository::class,
        );

        $this->app->singleton(
            ProductImageStorageInterface::class,
            ProductImageStorage::class,
        );

        $this->app->singleton(
            ProductReviewRepositoryInterface::class,
            ProductReviewRepository::class,
        );

        $this->app->singleton(
            UserRepositoryInterface::class,
            UserRepository::class,
        );

        $this->app->singleton(
            TokenTtlProviderInterface::class,
            JwtConfigTokenTtlProvider::class,
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

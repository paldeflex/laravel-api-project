<?php

declare(strict_types=1);

namespace App\Logging;

use App\Adapters\TelegramAdapter;
use Monolog\Level;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

/**
 * Laravel custom log channel factory for Telegram.
 */
final readonly class TelegramLogChannel
{
    /**
     * Create a custom Monolog instance.
     *
     * @param  array{level?: string}  $config
     */
    public function __invoke(array $config): LoggerInterface
    {
        /** @var string $botToken */
        $botToken = config('telegram.bot_token', '');
        /** @var string $chatId */
        $chatId = config('telegram.chat_id', '');
        $adapter = new TelegramAdapter($botToken, $chatId);
        $level = $this->parseLevel($config['level'] ?? 'critical');
        $handler = new TelegramLogHandler($adapter, $level);

        return new Logger('telegram', [$handler]);
    }

    private function parseLevel(string $level): Level
    {
        return match (strtolower($level)) {
            'debug' => Level::Debug,
            'info' => Level::Info,
            'notice' => Level::Notice,
            'warning' => Level::Warning,
            'error' => Level::Error,
            'alert' => Level::Alert,
            'emergency' => Level::Emergency,
            default => Level::Critical,
        };

    }
}

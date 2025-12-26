<?php

declare(strict_types=1);

namespace App\Adapters;

use App\Contracts\MessengerInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Telegram messenger adapter.
 * Sends messages via Telegram Bot API.
 */
final readonly class TelegramAdapter implements MessengerInterface
{
    private const string API_URL = 'https://api.telegram.org/bot';

    public function __construct(
        private string $botToken,
        private string $chatId,
    ) {}

    public function send(string $message): bool
    {
        return $this->sendTelegramMessage($message);
    }

    public function sendFormatted(string $title, string $body, ?string $level = null): bool
    {
        $emoji = $this->getLevelEmoji($level);
        $levelLabel = $level !== null ? strtoupper($level) : 'INFO';

        $formattedMessage = <<<MESSAGE
{$emoji} <b>[{$levelLabel}]</b> {$title}

<pre>{$body}</pre>

🕐 {$this->getTimestamp()}
🖥️ {$this->getEnvironment()}
MESSAGE;

        return $this->sendTelegramMessage($formattedMessage, 'HTML');
    }

    private function sendTelegramMessage(string $message, string $parseMode = 'HTML'): bool
    {
        if ($this->botToken === '' || $this->chatId === '') {
            Log::warning('Telegram logger not configured: missing bot token or chat ID');

            return false;
        }

        try {
            /** @var \Illuminate\Http\Client\Response $response */
            $response = Http::post(self::API_URL.$this->botToken.'/sendMessage', [
                'chat_id' => $this->chatId,
                'text' => $this->truncateMessage($message),
                'parse_mode' => $parseMode,
                'disable_web_page_preview' => true,
            ]);

            return $response->successful();
        } catch (\Throwable $e) {
            Log::warning('Failed to send Telegram message: '.$e->getMessage());

            return false;
        }
    }

    private function getLevelEmoji(?string $level): string
    {
        return match ($level) {
            'emergency' => '🚨',
            'alert' => '🔴',
            'critical' => '❌',
            'error' => '⚠️',
            'warning' => '⚡',
            'notice' => '📝',
            'info' => 'ℹ️',
            'debug' => '🔍',
            default => '📋',
        };
    }

    private function getTimestamp(): string
    {
        return now()->format('Y-m-d H:i:s T');
    }

    private function getEnvironment(): string
    {
        /** @var string $appName */
        $appName = config('app.name', 'Laravel');
        /** @var string $appEnv */
        $appEnv = config('app.env', 'production');

        return $appName.' ('.$appEnv.')';
    }

    /**
     * Telegram has a 4096 character limit for messages.
     */
    private function truncateMessage(string $message, int $maxLength = 4000): string
    {
        if (mb_strlen($message) <= $maxLength) {
            return $message;
        }

        return mb_substr($message, 0, $maxLength - 20).'... [truncated]';
    }
}

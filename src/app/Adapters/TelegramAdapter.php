<?php

declare(strict_types=1);

namespace App\Adapters;

use App\Contracts\MessengerInterface;
use App\Enums\LogLevelEmoji;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

final readonly class TelegramAdapter implements MessengerInterface
{
    private const string API_URL = 'https://api.telegram.org/bot';

    private const string PARSE_MODE = 'HTML';

    private string $appEnvironment;

    private int $maxMessageLength;

    public function __construct(
        private string $botToken,
        private string $chatId,
    ) {
        /** @var string $appName */
        $appName = config('app.name', 'Laravel');
        /** @var string $appEnv */
        $appEnv = config('app.env', 'production');
        $this->appEnvironment = $appName.' ('.$appEnv.')';

        /** @var int $maxLength */
        $maxLength = config('telegram.max_message_length', 4000);
        $this->maxMessageLength = $maxLength;
    }

    public function send(string $title, string $body, ?string $level = null): bool
    {
        if (! $this->isConfigured()) {
            Log::warning('Telegram logger not configured: missing bot token or chat ID');

            return false;
        }

        $emoji = LogLevelEmoji::fromLevel($level);
        $levelLabel = $level !== null ? strtoupper($level) : 'INFO';

        $formattedMessage = <<<MESSAGE
            $emoji <b>[$levelLabel]</b> {$title}
            <pre>$body</pre>
            🕐 {$this->getTimestamp()}
            🖥️ {$this->appEnvironment}
        MESSAGE;

        return $this->sendRequest($formattedMessage);
    }

    private function isConfigured(): bool
    {
        return $this->botToken !== '' && $this->chatId !== '';
    }

    private function sendRequest(string $message): bool
    {
        try {
            /** @var Response $response */
            $response = Http::post($this->buildApiUrl(), [
                'chat_id' => $this->chatId,
                'text' => $this->truncateMessage($message),
                'parse_mode' => self::PARSE_MODE,
                'disable_web_page_preview' => true,
            ]);

            return $response->successful();
        } catch (Throwable $e) {
            Log::warning('Failed to send Telegram message: '.$e->getMessage());

            return false;
        }
    }

    private function buildApiUrl(): string
    {
        return self::API_URL.$this->botToken.'/sendMessage';
    }

    private function getTimestamp(): string
    {
        return now()->format('Y-m-d H:i:s T');
    }

    private function truncateMessage(string $message): string
    {
        if (mb_strlen($message) <= $this->maxMessageLength) {
            return $message;
        }

        return mb_substr($message, 0, $this->maxMessageLength - 20).'... [truncated]';
    }
}

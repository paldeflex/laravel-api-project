<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Adapters\TelegramAdapter;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

final class TelegramAdapterTest extends TestCase
{
    public function test_send_returns_true_on_successful_request(): void
    {
        Http::fake([
            'api.telegram.org/*' => Http::response(['ok' => true]),
        ]);

        $adapter = new TelegramAdapter('test-bot-token', '123456');

        $result = $adapter->send('Test message');

        $this->assertTrue($result);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'api.telegram.org/bottest-bot-token/sendMessage')
                && $request['chat_id'] === '123456'
                && $request['text'] === 'Test message';
        });
    }

    public function test_send_returns_false_when_bot_token_is_empty(): void
    {
        $adapter = new TelegramAdapter('', '123456');

        $result = $adapter->send('Test message');

        $this->assertFalse($result);
    }

    public function test_send_returns_false_when_chat_id_is_empty(): void
    {
        $adapter = new TelegramAdapter('test-bot-token', '');

        $result = $adapter->send('Test message');

        $this->assertFalse($result);
    }

    public function test_send_returns_false_on_api_error(): void
    {
        Http::fake([
            'api.telegram.org/*' => Http::response(['ok' => false], 400),
        ]);

        $adapter = new TelegramAdapter('test-bot-token', '123456');

        $result = $adapter->send('Test message');

        $this->assertFalse($result);
    }

    public function test_send_formatted_includes_level_and_emoji(): void
    {
        Http::fake([
            'api.telegram.org/*' => Http::response(['ok' => true]),
        ]);

        $adapter = new TelegramAdapter('test-bot-token', '123456');

        $result = $adapter->sendFormatted('App Error', 'Something went wrong', 'critical');

        $this->assertTrue($result);

        Http::assertSent(function ($request) {
            $text = $request['text'];

            return str_contains($text, '❌')
                && str_contains($text, '[CRITICAL]')
                && str_contains($text, 'App Error')
                && str_contains($text, 'Something went wrong');
        });
    }

    public function test_send_formatted_uses_emergency_emoji(): void
    {
        Http::fake([
            'api.telegram.org/*' => Http::response(['ok' => true]),
        ]);

        $adapter = new TelegramAdapter('test-bot-token', '123456');

        $adapter->sendFormatted('System Down', 'Critical failure', 'emergency');

        Http::assertSent(function ($request) {
            return str_contains($request['text'], '🚨')
                && str_contains($request['text'], '[EMERGENCY]');
        });
    }

    public function test_message_is_truncated_when_too_long(): void
    {
        Http::fake([
            'api.telegram.org/*' => Http::response(['ok' => true]),
        ]);

        $adapter = new TelegramAdapter('test-bot-token', '123456');

        $longMessage = str_repeat('a', 5000);

        $adapter->send($longMessage);

        Http::assertSent(function ($request) {
            $text = $request['text'];

            return mb_strlen($text) <= 4000
                && str_contains($text, '[truncated]');
        });
    }

    public function test_send_catches_exceptions_and_returns_false(): void
    {
        Http::fake([
            'api.telegram.org/*' => function () {
                throw new RuntimeException('Network error');
            },
        ]);

        $adapter = new TelegramAdapter('test-bot-token', '123456');

        $result = $adapter->send('Test message');

        $this->assertFalse($result);
    }
}

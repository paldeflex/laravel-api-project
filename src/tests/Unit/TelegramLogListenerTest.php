<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Contracts\MessengerInterface;
use App\Events\CriticalLogEvent;
use App\Listeners\TelegramLogListener;
use Mockery;
use RuntimeException;
use Tests\TestCase;

final class TelegramLogListenerTest extends TestCase
{
    private MessengerInterface $messenger;

    private TelegramLogListener $listener;

    protected function setUp(): void
    {
        parent::setUp();

        $this->messenger = Mockery::mock(MessengerInterface::class);
        $this->listener = new TelegramLogListener($this->messenger);
    }

    public function test_handle_sends_formatted_message(): void
    {
        $event = new CriticalLogEvent(
            level: 'critical',
            message: 'Database connection failed',
            context: [],
        );

        $this->messenger
            ->shouldReceive('send')
            ->once()
            ->with('Application Log', 'Database connection failed', 'critical')
            ->andReturn(true);

        $this->listener->handle($event);

        $this->assertTrue(true);
    }

    public function test_handle_includes_context_in_message(): void
    {
        $event = new CriticalLogEvent(
            level: 'emergency',
            message: 'Payment service unavailable',
            context: ['service' => 'stripe', 'retry_count' => 3],
        );

        $this->messenger
            ->shouldReceive('send')
            ->once()
            ->withArgs(function (string $title, string $body, string $level) {
                return $title === 'Application Log'
                    && str_contains($body, 'Payment service unavailable')
                    && str_contains($body, 'Context')
                    && str_contains($body, 'stripe')
                    && str_contains($body, 'retry_count')
                    && $level === 'emergency';
            })
            ->andReturn(true);

        $this->listener->handle($event);

        $this->assertTrue(true);
    }

    public function test_handle_formats_exception_in_context(): void
    {
        $exception = new RuntimeException('Connection timeout', 504);

        $event = new CriticalLogEvent(
            level: 'critical',
            message: 'External API error',
            context: ['exception' => $exception],
        );

        $this->messenger
            ->shouldReceive('send')
            ->once()
            ->withArgs(function (string $title, string $body, string $level) {
                return str_contains($body, 'RuntimeException')
                    && str_contains($body, 'Connection timeout')
                    && str_contains($body, 'File:');
            })
            ->andReturn(true);

        $this->listener->handle($event);

        $this->assertTrue(true);
    }

    public function test_should_queue_returns_true_when_configured(): void
    {
        config(['telegram.bot_token' => 'test-token']);

        $event = new CriticalLogEvent(
            level: 'critical',
            message: 'Test',
            context: [],
        );

        $result = $this->listener->shouldQueue();

        $this->assertTrue($result);
    }

    public function test_should_queue_returns_false_when_not_configured(): void
    {
        config(['telegram.bot_token' => '']);

        $event = new CriticalLogEvent(
            level: 'critical',
            message: 'Test',
            context: [],
        );

        $result = $this->listener->shouldQueue();

        $this->assertFalse($result);
    }
}

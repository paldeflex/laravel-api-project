<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Contracts\MessengerInterface;
use App\Logging\TelegramLogHandler;
use DateTimeImmutable;
use Mockery;
use Monolog\Level;
use Monolog\LogRecord;
use RuntimeException;
use Tests\TestCase;

final class TelegramLogHandlerTest extends TestCase
{
    private MessengerInterface $messenger;

    private TelegramLogHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->messenger = Mockery::mock(MessengerInterface::class);
        $this->handler = new TelegramLogHandler($this->messenger, Level::Critical);
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_handles_critical_level_logs(): void
    {
        $this->messenger
            ->shouldReceive('sendFormatted')
            ->once()
            ->with('test-channel', 'Critical error occurred', 'Critical')
            ->andReturn(true);

        $record = new LogRecord(
            datetime: new DateTimeImmutable,
            channel: 'test-channel',
            level: Level::Critical,
            message: 'Critical error occurred',
            context: [],
            extra: [],
        );

        $this->handler->handle($record);

        // Mockery verifies sendFormatted was called
        $this->assertTrue(true);
    }

    public function test_handles_emergency_level_logs(): void
    {
        $this->messenger
            ->shouldReceive('sendFormatted')
            ->once()
            ->with('test-channel', 'System down!', 'Emergency')
            ->andReturn(true);

        $record = new LogRecord(
            datetime: new DateTimeImmutable,
            channel: 'test-channel',
            level: Level::Emergency,
            message: 'System down!',
            context: [],
            extra: [],
        );

        $this->handler->handle($record);

        // Mockery verifies sendFormatted was called
        $this->assertTrue(true);
    }

    public function test_ignores_lower_level_logs(): void
    {
        $this->messenger
            ->shouldNotReceive('sendFormatted');

        $record = new LogRecord(
            datetime: new DateTimeImmutable,
            channel: 'test-channel',
            level: Level::Warning,
            message: 'Just a warning',
            context: [],
            extra: [],
        );

        $result = $this->handler->handle($record);

        // Handler returns false for unhandled (below threshold) logs
        $this->assertFalse($result);
    }

    public function test_includes_context_in_message(): void
    {
        $this->messenger
            ->shouldReceive('sendFormatted')
            ->once()
            ->withArgs(function (string $title, string $body, string $level) {
                return $title === 'test-channel'
                    && str_contains($body, 'Error message')
                    && str_contains($body, 'Context')
                    && str_contains($body, 'user_id')
                    && str_contains($body, '123')
                    && $level === 'Critical';
            })
            ->andReturn(true);

        $record = new LogRecord(
            datetime: new DateTimeImmutable,
            channel: 'test-channel',
            level: Level::Critical,
            message: 'Error message',
            context: ['user_id' => 123],
            extra: [],
        );

        $this->handler->handle($record);

        // Mockery verifies sendFormatted was called with correct args
        $this->assertTrue(true);
    }

    public function test_includes_exception_details_in_message(): void
    {
        $exception = new RuntimeException('Something broke', 500);

        $this->messenger
            ->shouldReceive('sendFormatted')
            ->once()
            ->withArgs(function (string $title, string $body, string $level) {
                return str_contains($body, 'RuntimeException')
                    && str_contains($body, 'Something broke')
                    && str_contains($body, 'File:');
            })
            ->andReturn(true);

        $record = new LogRecord(
            datetime: new DateTimeImmutable,
            channel: 'test-channel',
            level: Level::Critical,
            message: 'Exception caught',
            context: ['exception' => $exception],
            extra: [],
        );

        $this->handler->handle($record);

        // Mockery verifies exception details were included
        $this->assertTrue(true);
    }

    public function test_includes_extra_data_in_message(): void
    {
        $this->messenger
            ->shouldReceive('sendFormatted')
            ->once()
            ->withArgs(function (string $title, string $body, string $level) {
                return str_contains($body, 'Extra')
                    && str_contains($body, 'request_id')
                    && str_contains($body, 'abc123');
            })
            ->andReturn(true);

        $record = new LogRecord(
            datetime: new DateTimeImmutable,
            channel: 'test-channel',
            level: Level::Critical,
            message: 'Error with extra',
            context: [],
            extra: ['request_id' => 'abc123'],
        );

        $this->handler->handle($record);

        // Mockery verifies extra data was included
        $this->assertTrue(true);
    }
}

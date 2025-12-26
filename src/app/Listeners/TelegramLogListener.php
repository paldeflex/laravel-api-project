<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Contracts\MessengerInterface;
use App\Events\CriticalLogEvent;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Listener that sends critical log events to Telegram.
 * Implements ShouldQueue to process asynchronously.
 */
final readonly class TelegramLogListener implements ShouldQueue
{
    /**
     * The name of the queue the job should be sent to.
     */
    public string $queue;

    public function __construct(
        private MessengerInterface $messenger,
    ) {
        $this->queue = 'logs';
    }

    /**
     * Handle the event.
     */
    public function handle(CriticalLogEvent $event): void
    {
        $body = $event->message;

        if ($event->context !== []) {
            $contextString = $this->formatContext($event->context);
            $body .= "\n\n📎 Context:\n".$contextString;
        }

        $this->messenger->sendFormatted(
            title: 'Application Log',
            body: $body,
            level: $event->level,
        );
    }

    /**
     * Determine whether the listener should be queued.
     */
    public function shouldQueue(CriticalLogEvent $event): bool
    {
        // Only queue if messenger is configured
        /** @var string $botToken */
        $botToken = config('telegram.bot_token', '');

        return $botToken !== '';
    }

    /**
     * @param array<string, mixed> $context
     */
    private function formatContext(array $context): string
    {
        // Handle exception specially
        if (isset($context['exception']) && $context['exception'] instanceof \Throwable) {
            $exception = $context['exception'];
            unset($context['exception']);

            $exceptionInfo = sprintf(
                "%s: %s\nFile: %s:%d",
                $exception::class,
                $exception->getMessage(),
                $exception->getFile(),
                $exception->getLine()
            );

            if ($context !== []) {
                return $exceptionInfo."\n\n".json_encode($context, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            }

            return $exceptionInfo;
        }

        $result = json_encode($context, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        return $result !== false ? $result : '';
    }
}

<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Contracts\MessengerInterface;
use App\Events\CriticalLogEvent;
use App\Support\JsonFlags;
use Illuminate\Contracts\Queue\ShouldQueue;
use Throwable;

final readonly class TelegramLogListener implements ShouldQueue
{
    public string $queue;

    public function __construct(
        private MessengerInterface $messenger,
    ) {
        /** @var string $queueName */
        $queueName = config('telegram.queue_name', 'logs');
        $this->queue = $queueName;
    }

    public function handle(CriticalLogEvent $event): void
    {
        /** @var string $title */
        $title = config('telegram.log_title', 'Application Log');

        $body = $event->message;
        if ($event->context !== []) {
            $contextString = $this->formatContext($event->context);
            $body .= "\n\n📎 Context:\n".$contextString;
        }

        $this->messenger->send(
            title: $title,
            body: $body,
            level: $event->level,
        );
    }

    public function shouldQueue(): bool
    {
        /** @var string $botToken */
        $botToken = config('telegram.bot_token', '');

        return $botToken !== '';
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function formatContext(array $context): string
    {
        if (isset($context['exception']) && $context['exception'] instanceof Throwable) {
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
                return $exceptionInfo."\n\n".json_encode($context, JsonFlags::READABLE);
            }

            return $exceptionInfo;
        }

        $result = json_encode($context, JsonFlags::READABLE);

        return $result !== false ? $result : '';
    }
}

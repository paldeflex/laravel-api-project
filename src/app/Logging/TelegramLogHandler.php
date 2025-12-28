<?php

declare(strict_types=1);

namespace App\Logging;

use App\Contracts\MessengerInterface;
use App\Support\JsonFlags;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\LogRecord;
use Throwable;

final class TelegramLogHandler extends AbstractProcessingHandler
{
    private readonly int $maxTraceLines;

    public function __construct(
        private readonly MessengerInterface $messenger,
        Level $level = Level::Critical,
        bool $bubble = true,
    ) {
        parent::__construct($level, $bubble);

        /** @var int $maxLines */
        $maxLines = config('telegram.max_trace_lines', 10);
        $this->maxTraceLines = $maxLines;
    }

    protected function write(LogRecord $record): void
    {
        /** @var string $title */
        $title = config('telegram.log_title', 'Application Log');

        /** @var array<string, mixed> $context */
        $context = $record->context;
        /** @var array<string, mixed> $extra */
        $extra = $record->extra;
        $contextString = $this->formatContext($context);
        $extraString = $this->formatExtra($extra);
        $body = $record->message;

        if ($contextString !== '') {
            $body .= "\n\n📎 Context:\n".$contextString;
        }

        if ($extraString !== '') {
            $body .= "\n\n📊 Extra:\n".$extraString;
        }

        $this->messenger->send(
            title: $title,
            body: $body,
            level: $record->level->name,
        );
    }

    /**
     * @param array<string, mixed> $context
     */
    private function formatContext(array $context): string
    {
        if ($context === []) {
            return '';
        }

        if (isset($context['exception']) && $context['exception'] instanceof Throwable) {
            $exception = $context['exception'];
            unset($context['exception']);
            $exceptionInfo = sprintf(
                "%s: %s\nFile: %s:%d\nTrace:\n%s",
                $exception::class,
                $exception->getMessage(),
                $exception->getFile(),
                $exception->getLine(),
                $this->formatTrace($exception->getTraceAsString())
            );

            if ($context !== []) {
                $encoded = json_encode($context, JsonFlags::READABLE);
                return $exceptionInfo."\n\n".($encoded !== false ? $encoded : '');
            }

            return $exceptionInfo;
        }

        $encoded = json_encode($context, JsonFlags::READABLE);

        return $encoded !== false ? $encoded : '';
    }

    /**
     * @param array<string, mixed> $extra
     */
    private function formatExtra(array $extra): string
    {
        if ($extra === []) {
            return '';
        }

        $encoded = json_encode($extra, JsonFlags::READABLE);

        return $encoded !== false ? $encoded : '';
    }

    private function formatTrace(string $trace): string
    {
        $lines = explode("\n", $trace);
        $limitedLines = array_slice($lines, 0, $this->maxTraceLines);

        if (count($lines) > $this->maxTraceLines) {
            $limitedLines[] = '... ('.(count($lines) - $this->maxTraceLines).' more lines)';
        }

        return implode("\n", $limitedLines);
    }
}

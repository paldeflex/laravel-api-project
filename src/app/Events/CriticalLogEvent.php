<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event dispatched when a critical log message is recorded.
 */
final readonly class CriticalLogEvent
{
    use Dispatchable;
    use SerializesModels;

    /**
     * @param  string  $level  Log level (critical, emergency, alert)
     * @param  string  $message  Log message
     * @param  array<string, mixed>  $context  Additional context data
     */
    public function __construct(

        public string $level,

        public string $message,

        public array $context = [],

    ) {}

}

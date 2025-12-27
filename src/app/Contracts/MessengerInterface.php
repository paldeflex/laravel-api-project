<?php

declare(strict_types=1);

namespace App\Contracts;

/**
 * Interface for messenger adapters.
 * Allows sending messages through different messaging platforms.
 */
interface MessengerInterface
{
    /**
     * Send a message to the configured recipient.
     *
     * @param  string  $message  The message content to send
     * @return bool True if message was sent successfully
     */
    public function send(string $message): bool;

    /**
     * Send a formatted message with title and body.
     *
     * @param  string  $title  Message title/subject
     * @param  string  $body  Message body content
     * @param  string|null  $level  Optional severity level (e.g., 'critical', 'emergency')
     * @return bool True if message was sent successfully
     */
    public function sendFormatted(string $title, string $body, ?string $level = null): bool;
}

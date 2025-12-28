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
     * @return bool True if message was sent successfully
     */
    public function send(string $title, string $body, ?string $level = null): bool;
}

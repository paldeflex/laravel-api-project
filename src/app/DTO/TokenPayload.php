<?php

declare(strict_types=1);

namespace App\DTO;

final class TokenPayload
{
    public function __construct(
        public readonly string $accessToken,
        public readonly string $tokenType,
        public readonly int $expiresIn,
    ) {}
}

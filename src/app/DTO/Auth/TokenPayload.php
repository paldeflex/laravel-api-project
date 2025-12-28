<?php

declare(strict_types=1);

namespace App\DTO\Auth;

final readonly class TokenPayload
{
    public function __construct(
        public string $accessToken,
        public string $tokenType,
        public int $expiresIn,
    ) {}
}

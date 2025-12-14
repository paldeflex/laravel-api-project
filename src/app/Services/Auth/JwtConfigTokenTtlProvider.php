<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Services\Contracts\Auth\TokenTtlProviderInterface;
use App\Exceptions\Auth\InvalidJwtConfigException;

final class JwtConfigTokenTtlProvider implements TokenTtlProviderInterface
{
    private const int SECONDS_PER_MINUTE = 60;

    public function accessTokenTtlSeconds(): int
    {
        $ttlMinutes = config('jwt.ttl');

        if (! is_int($ttlMinutes)) {
            throw new InvalidJwtConfigException('jwt.ttl must be an integer');
        }

        return $ttlMinutes * self::SECONDS_PER_MINUTE;
    }
}

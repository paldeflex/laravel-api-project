<?php

namespace App\Services\Contracts\Auth;

interface TokenTtlProviderInterface
{
    public function accessTokenTtlSeconds(): int;
}

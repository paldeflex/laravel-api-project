<?php

namespace App\DTO;

readonly class LoginData
{
    public function __construct(
        public string $email,
        public string $password,
    ) {
    }
}

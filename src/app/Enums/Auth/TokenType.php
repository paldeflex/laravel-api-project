<?php

declare(strict_types=1);

namespace App\Enums\Auth;

enum TokenType: string
{
    case Bearer = 'bearer';
}

<?php

declare(strict_types=1);

namespace App\Support;

final class JsonFlags
{
    public const int READABLE = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE;
}

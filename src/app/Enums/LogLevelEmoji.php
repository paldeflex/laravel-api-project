<?php

declare(strict_types=1);

namespace App\Enums;

enum LogLevelEmoji: string
{
    case Emergency = '🚨';
    case Alert = '🔴';
    case Critical = '❌';
    case Error = '⚠️';
    case Warning = '⚡';
    case Notice = '📝';
    case Info = 'ℹ️';
    case Debug = '🔍';
    case Default = '📋';

    public static function fromLevel(?string $level): string
    {
        if ($level === null) {
            return self::Default->value;
        }

        return match (strtolower($level)) {
            'emergency' => self::Emergency->value,
            'alert' => self::Alert->value,
            'critical' => self::Critical->value,
            'error' => self::Error->value,
            'warning' => self::Warning->value,
            'notice' => self::Notice->value,
            'info' => self::Info->value,
            'debug' => self::Debug->value,
            default => self::Default->value,
        };
    }
}

<?php

declare(strict_types=1);

namespace App\Exports\Concerns;

use Carbon\Carbon;

trait FormatsDateTime
{
    private function formatDateTime(?Carbon $dateTime): string
    {
        if ($dateTime === null) {
            return __('exports.common.empty');
        }

        /** @var string $format */
        $format = config('reports.date_format', 'Y-m-d_H-i-s');

        return $dateTime->format(str_replace('_', ' ', $format));
    }
}

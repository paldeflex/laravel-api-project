<?php

declare(strict_types=1);

return [
    'storage_disk' => env('REPORTS_STORAGE_DISK', 'local'),

    'storage_path' => env('REPORTS_STORAGE_PATH', 'reports'),

    'date_format' => 'Y-m-d_H-i-s',
];

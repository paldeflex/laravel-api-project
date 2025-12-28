<?php

declare(strict_types=1);

namespace App\Services\Contracts\Report;

use App\Models\ReportLog;

interface ReportLogServiceInterface
{
    public function markAsProcessing(ReportLog $reportLog): void;

    public function markAsCompleted(ReportLog $reportLog, string $fileName, string $filePath): void;

    public function markAsFailed(ReportLog $reportLog, string $errorMessage): void;
}

<?php

declare(strict_types=1);

namespace App\Services\Report;

use App\Enums\Report\ReportStatus;
use App\Models\ReportLog;
use App\Services\Contracts\Report\ReportLogServiceInterface;

final class ReportLogService implements ReportLogServiceInterface
{
    public function markAsProcessing(ReportLog $reportLog): void
    {
        $reportLog->update([
            'status' => ReportStatus::Processing,
            'started_at' => now(),
            'attempts' => $reportLog->attempts + 1,
        ]);
    }

    public function markAsCompleted(ReportLog $reportLog, string $fileName, string $filePath): void
    {
        $reportLog->update([
            'status' => ReportStatus::Completed,
            'file_name' => $fileName,
            'file_path' => $filePath,
            'completed_at' => now(),
        ]);
    }

    public function markAsFailed(ReportLog $reportLog, string $errorMessage): void
    {
        $reportLog->update([
            'status' => ReportStatus::Failed,
            'error_message' => $errorMessage,
            'completed_at' => now(),
        ]);
    }
}

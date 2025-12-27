<?php

declare(strict_types=1);

namespace App\Services\Report;

use App\DTO\Report\GenerateReportData;
use App\Enums\Report\ReportStatus;
use App\Jobs\GenerateExcelReportJob;
use App\Models\ReportLog;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

final readonly class ReportService
{
    /**
     * Queue a new report generation.
     */
    public function queueReport(GenerateReportData $data): ReportLog
    {
        $reportLog = ReportLog::create([
            'user_id' => $data->userId,
            'report_type' => $data->reportType,
            'status' => ReportStatus::Pending,
        ]);

        GenerateExcelReportJob::dispatch($reportLog);

        return $reportLog;
    }

    /**
     * Get user's report logs.
     *
     * @return LengthAwarePaginator<int, ReportLog>
     */
    public function getUserReports(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return ReportLog::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    /**
     * Get a specific report log.
     */
    public function getReport(int $reportId): ?ReportLog
    {
        return ReportLog::find($reportId);
    }

    /**
     * Download a completed report.
     */
    public function downloadReport(ReportLog $reportLog): ?StreamedResponse
    {
        if ($reportLog->status !== ReportStatus::Completed || $reportLog->file_path === null) {
            return null;
        }

        if (! Storage::disk('local')->exists($reportLog->file_path)) {
            return null;
        }

        return Storage::disk('local')->download(
            $reportLog->file_path,
            $reportLog->file_name
        );
    }

    /**
     * Check if user can access the report.
     */
    public function canUserAccessReport(User $user, ReportLog $reportLog): bool
    {
        return $reportLog->user_id === $user->id || $user->is_admin;
    }
}

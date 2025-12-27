<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\Report\ReportType;
use App\Exports\ProductReviewsExport;
use App\Exports\ProductsExport;
use App\Exports\UsersExport;
use App\Models\ReportLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

final class GenerateExcelReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Maximum number of attempts.
     */
    public int $tries = 5;

    /**
     * Maximum number of unhandled exceptions before failing.
     */
    public int $maxExceptions = 5;

    /**
     * Delete the job if its models no longer exist.
     */
    public bool $deleteWhenMissingModels = true;

    public function __construct(
        public readonly ReportLog $reportLog,
    ) {}

    /**
     * Calculate the number of seconds to wait before retrying the job.
     * Uses exponential backoff: 3s, 15s, 30s, 60s, 120s.
     *
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [3, 15, 30, 60, 120];
    }

    /**
     * @throws Throwable
     */
    public function handle(): void
    {
        $this->reportLog->markAsProcessing();

        Log::info('Starting report generation', [
            'report_log_id' => $this->reportLog->id,
            'report_type' => $this->reportLog->report_type->value,
            'user_id' => $this->reportLog->user_id,
            'attempt' => $this->reportLog->attempts,
        ]);

        try {
            $export = $this->getExportClass();
            $fileName = $this->generateFileName();
            $filePath = 'reports/'.$fileName;

            Excel::store($export, $filePath, 'local');

            $this->reportLog->markAsCompleted($fileName, $filePath);

            Log::info('Report generated successfully', [
                'report_log_id' => $this->reportLog->id,
                'report_type' => $this->reportLog->report_type->value,
                'file_path' => $filePath,
                'attempts' => $this->reportLog->attempts,
            ]);
        } catch (Throwable $e) {
            Log::error('Report generation failed', [
                'report_log_id' => $this->reportLog->id,
                'report_type' => $this->reportLog->report_type->value,
                'error' => $e->getMessage(),
                'attempt' => $this->reportLog->attempts,
            ]);

            throw $e;
        }
    }

    public function failed(?Throwable $exception): void
    {
        $errorMessage = $exception?->getMessage() ?? 'Unknown error';

        $this->reportLog->markAsFailed($errorMessage);

        Log::error('Report generation permanently failed', [
            'report_log_id' => $this->reportLog->id,
            'report_type' => $this->reportLog->report_type->value,
            'error' => $errorMessage,
            'total_attempts' => $this->reportLog->attempts,
        ]);
    }

    private function getExportClass(): ProductsExport|ProductReviewsExport|UsersExport
    {
        return match ($this->reportLog->report_type) {
            ReportType::Products => new ProductsExport,
            ReportType::ProductReviews => new ProductReviewsExport,
            ReportType::Users => new UsersExport,
        };
    }

    private function generateFileName(): string
    {
        $timestamp = now()->format('Y-m-d_H-i-s');
        $type = $this->reportLog->report_type->value;

        return "{$type}_report_{$timestamp}_{$this->reportLog->id}.xlsx";
    }
}

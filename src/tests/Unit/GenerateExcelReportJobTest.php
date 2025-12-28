<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Enums\Report\ReportStatus;
use App\Enums\Report\ReportType;
use App\Jobs\GenerateExcelReportJob;
use App\Models\ReportLog;
use App\Models\User;
use App\Services\Contracts\Report\ReportLogServiceInterface;
use App\Services\Report\ReportLogService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;
use Throwable;

final class GenerateExcelReportJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_is_dispatched_to_queue(): void
    {
        Queue::fake();

        $user = User::factory()->create();

        $reportLog = ReportLog::create([
            'user_id' => $user->id,
            'report_type' => ReportType::Products,
            'status' => ReportStatus::Pending,
        ]);

        GenerateExcelReportJob::dispatch($reportLog);

        Queue::assertPushed(GenerateExcelReportJob::class, function ($job) use ($reportLog) {
            return $job->reportLog->id === $reportLog->id;
        });
    }

    public function test_job_has_correct_backoff_values(): void
    {
        $user = User::factory()->create();

        $reportLog = ReportLog::create([
            'user_id' => $user->id,
            'report_type' => ReportType::Products,
            'status' => ReportStatus::Pending,
        ]);

        $job = new GenerateExcelReportJob($reportLog);

        $this->assertSame([3, 15, 30, 60, 120], $job->backoff());
    }

    public function test_job_has_correct_tries(): void
    {
        $user = User::factory()->create();

        $reportLog = ReportLog::create([
            'user_id' => $user->id,
            'report_type' => ReportType::Products,
            'status' => ReportStatus::Pending,
        ]);

        $job = new GenerateExcelReportJob($reportLog);

        $this->assertSame(5, $job->tries);
    }

    /**
     * @throws Throwable
     */
    public function test_job_calls_service_mark_as_processing(): void
    {
        $user = User::factory()->create();

        $reportLog = ReportLog::create([
            'user_id' => $user->id,
            'report_type' => ReportType::Products,
            'status' => ReportStatus::Pending,
        ]);

        /** @var ReportLogService&MockInterface $serviceMock */
        $serviceMock = Mockery::mock(ReportLogServiceInterface::class);
        $serviceMock->shouldReceive('markAsProcessing')
            ->once()
            ->with(Mockery::on(fn ($arg) => $arg->id === $reportLog->id));
        $serviceMock->shouldReceive('markAsCompleted')
            ->once();

        $job = new GenerateExcelReportJob($reportLog, $serviceMock);
        $job->handle();
    }

    /**
     * @throws Throwable
     */
    public function test_job_calls_service_mark_as_completed_on_success(): void
    {
        $user = User::factory()->create();

        $reportLog = ReportLog::create([
            'user_id' => $user->id,
            'report_type' => ReportType::Products,
            'status' => ReportStatus::Pending,
        ]);

        /** @var ReportLogService&MockInterface $serviceMock */
        $serviceMock = Mockery::mock(ReportLogServiceInterface::class);
        $serviceMock->shouldReceive('markAsProcessing')->once();
        $serviceMock->shouldReceive('markAsCompleted')
            ->once()
            ->withArgs(function ($log, $fileName, $filePath) use ($reportLog) {
                return $log->id === $reportLog->id
                    && str_contains($fileName, 'products_report_')
                    && str_contains($filePath, 'reports/');
            });

        $job = new GenerateExcelReportJob($reportLog, $serviceMock);
        $job->handle();
    }

    public function test_failed_method_calls_service_mark_as_failed(): void
    {
        $user = User::factory()->create();

        $reportLog = ReportLog::create([
            'user_id' => $user->id,
            'report_type' => ReportType::Products,
            'status' => ReportStatus::Processing,
        ]);

        /** @var ReportLogService&MockInterface $serviceMock */
        $serviceMock = Mockery::mock(ReportLogServiceInterface::class);
        $serviceMock->shouldReceive('markAsFailed')
            ->once()
            ->with(
                Mockery::on(fn ($arg) => $arg->id === $reportLog->id),
                'Test error message'
            );

        $job = new GenerateExcelReportJob($reportLog, $serviceMock);
        $job->failed(new Exception('Test error message'));
    }

    public function test_job_dispatched_with_correct_report_type(): void
    {
        Queue::fake();

        $user = User::factory()->create();

        $reportLog = ReportLog::create([
            'user_id' => $user->id,
            'report_type' => ReportType::ProductReviews,
            'status' => ReportStatus::Pending,
        ]);

        GenerateExcelReportJob::dispatch($reportLog);

        Queue::assertPushed(GenerateExcelReportJob::class, function ($job) {
            return $job->reportLog->report_type === ReportType::ProductReviews;
        });
    }
}

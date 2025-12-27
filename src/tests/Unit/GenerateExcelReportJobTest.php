<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Enums\Report\ReportStatus;
use App\Enums\Report\ReportType;
use App\Jobs\GenerateExcelReportJob;
use App\Models\ReportLog;
use App\Models\User;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Throwable;

final class GenerateExcelReportJobTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
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
    public function test_job_generates_products_report_successfully(): void
    {
        $user = User::factory()->create();

        $reportLog = ReportLog::create([
            'user_id' => $user->id,
            'report_type' => ReportType::Products,
            'status' => ReportStatus::Pending,
        ]);

        Log::shouldReceive('info')
            ->twice();

        $job = new GenerateExcelReportJob($reportLog);
        $job->handle();

        $reportLog->refresh();

        $this->assertSame(ReportStatus::Completed, $reportLog->status);
        $this->assertNotNull($reportLog->file_name);
        $this->assertNotNull($reportLog->file_path);
        $this->assertNotNull($reportLog->completed_at);
        $this->assertSame(1, $reportLog->attempts);
        $this->assertStringContainsString('products_report_', $reportLog->file_name);
        Storage::disk('local')->assertExists($reportLog->file_path);
    }

    /**
     * @throws Throwable
     */
    public function test_job_generates_product_reviews_report_successfully(): void
    {
        $user = User::factory()->create();

        $reportLog = ReportLog::create([
            'user_id' => $user->id,
            'report_type' => ReportType::ProductReviews,
            'status' => ReportStatus::Pending,
        ]);

        Log::shouldReceive('info')
            ->twice();

        $job = new GenerateExcelReportJob($reportLog);
        $job->handle();

        $reportLog->refresh();

        $this->assertSame(ReportStatus::Completed, $reportLog->status);
        $this->assertStringContainsString('product_reviews_report_', $reportLog->file_name);
    }

    /**
     * @throws Throwable
     */
    public function test_job_generates_users_report_successfully(): void
    {
        $user = User::factory()->create();

        $reportLog = ReportLog::create([
            'user_id' => $user->id,
            'report_type' => ReportType::Users,
            'status' => ReportStatus::Pending,
        ]);

        Log::shouldReceive('info')
            ->twice();

        $job = new GenerateExcelReportJob($reportLog);
        $job->handle();

        $reportLog->refresh();

        $this->assertSame(ReportStatus::Completed, $reportLog->status);
        $this->assertStringContainsString('users_report_', $reportLog->file_name);
    }

    /**
     * @throws Throwable
     */
    public function test_job_marks_as_processing_before_generation(): void
    {
        $user = User::factory()->create();

        $reportLog = ReportLog::create([
            'user_id' => $user->id,
            'report_type' => ReportType::Products,
            'status' => ReportStatus::Pending,
        ]);

        Log::shouldReceive('info')
            ->twice();

        $job = new GenerateExcelReportJob($reportLog);
        $job->handle();

        $reportLog->refresh();

        $this->assertNotNull($reportLog->started_at);
    }

    public function test_failed_method_marks_report_as_failed(): void
    {
        $user = User::factory()->create();

        $reportLog = ReportLog::create([
            'user_id' => $user->id,
            'report_type' => ReportType::Products,
            'status' => ReportStatus::Processing,
            'attempts' => 5,
        ]);

        Log::shouldReceive('error')
            ->once();

        $job = new GenerateExcelReportJob($reportLog);
        $exception = new Exception('Test error message');
        $job->failed($exception);

        $reportLog->refresh();

        $this->assertSame(ReportStatus::Failed, $reportLog->status);
        $this->assertSame('Test error message', $reportLog->error_message);
        $this->assertNotNull($reportLog->completed_at);
    }

    /**
     * @throws Throwable
     */
    public function test_job_increments_attempts_on_each_run(): void
    {
        $user = User::factory()->create();

        $reportLog = ReportLog::create([
            'user_id' => $user->id,
            'report_type' => ReportType::Products,
            'status' => ReportStatus::Pending,
            'attempts' => 2,
        ]);

        Log::shouldReceive('info')
            ->twice();

        $job = new GenerateExcelReportJob($reportLog);
        $job->handle();

        $reportLog->refresh();

        $this->assertSame(3, $reportLog->attempts);
    }
}

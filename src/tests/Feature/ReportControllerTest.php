<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\Report\ReportStatus;
use App\Enums\Report\ReportType;
use App\Jobs\GenerateExcelReportJob;
use App\Models\ReportLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

final class ReportControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_requires_authentication(): void
    {
        $response = $this->getJson('/api/reports');

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_index_returns_user_reports(): void
    {
        $user = User::factory()->create();

        ReportLog::create([
            'user_id' => $user->id,
            'report_type' => ReportType::Products,
            'status' => ReportStatus::Completed,
        ]);

        ReportLog::create([
            'user_id' => $user->id,
            'report_type' => ReportType::Users,
            'status' => ReportStatus::Pending,
        ]);

        $this->actingAs($user, 'api');

        $response = $this->getJson('/api/reports');

        $response->assertOk();
        $response->assertJsonCount(2, 'data');
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'report_type',
                    'report_type_label',
                    'status',
                    'file_name',
                    'error_message',
                    'attempts',
                    'started_at',
                    'completed_at',
                    'created_at',
                ],
            ],
        ]);
    }

    public function test_index_does_not_return_other_users_reports(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        ReportLog::create([
            'user_id' => $user1->id,
            'report_type' => ReportType::Products,
            'status' => ReportStatus::Completed,
        ]);

        ReportLog::create([
            'user_id' => $user2->id,
            'report_type' => ReportType::Products,
            'status' => ReportStatus::Completed,
        ]);

        $this->actingAs($user1, 'api');

        $response = $this->getJson('/api/reports');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
    }

    public function test_store_requires_authentication(): void
    {
        $response = $this->postJson('/api/reports', [
            'report_type' => ReportType::Products->value,
        ]);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_store_validates_report_type(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'api');

        $response = $this->postJson('/api/reports', [
            'report_type' => 'invalid_type',
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors(['report_type']);
    }

    public function test_store_requires_report_type(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'api');

        $response = $this->postJson('/api/reports');

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors(['report_type']);
    }

    public function test_store_creates_report_and_dispatches_job(): void
    {
        Queue::fake();

        $user = User::factory()->create();

        $this->actingAs($user, 'api');

        $response = $this->postJson('/api/reports', [
            'report_type' => ReportType::Products->value,
        ]);

        $response->assertStatus(Response::HTTP_ACCEPTED);
        $response->assertJsonPath('message', 'Report generation queued successfully');
        $response->assertJsonPath('data.report_type', ReportType::Products->value);
        $response->assertJsonPath('data.status', ReportStatus::Pending->value);

        $this->assertDatabaseHas('report_logs', [
            'user_id' => $user->id,
            'report_type' => ReportType::Products->value,
            'status' => ReportStatus::Pending->value,
        ]);

        Queue::assertPushed(GenerateExcelReportJob::class);
    }

    public function test_store_accepts_all_report_types(): void
    {
        Queue::fake();

        $user = User::factory()->create();

        $this->actingAs($user, 'api');

        foreach (ReportType::cases() as $reportType) {
            $response = $this->postJson('/api/reports', [
                'report_type' => $reportType->value,
            ]);

            $response->assertStatus(Response::HTTP_ACCEPTED);
            $response->assertJsonPath('data.report_type', $reportType->value);
        }
    }

    public function test_show_requires_authentication(): void
    {
        $user = User::factory()->create();

        $reportLog = ReportLog::create([
            'user_id' => $user->id,
            'report_type' => ReportType::Products,
            'status' => ReportStatus::Completed,
        ]);

        $response = $this->getJson("/api/reports/$reportLog->id");

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_show_returns_report_for_owner(): void
    {
        $user = User::factory()->create();

        $reportLog = ReportLog::create([
            'user_id' => $user->id,
            'report_type' => ReportType::Products,
            'status' => ReportStatus::Completed,
            'file_name' => 'test.xlsx',
        ]);

        $this->actingAs($user, 'api');

        $response = $this->getJson("/api/reports/$reportLog->id");

        $response->assertOk();
        $response->assertJsonPath('data.id', $reportLog->id);
        $response->assertJsonPath('data.report_type', ReportType::Products->value);
        $response->assertJsonPath('data.file_name', 'test.xlsx');
    }

    public function test_show_returns_forbidden_for_other_user(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $reportLog = ReportLog::create([
            'user_id' => $owner->id,
            'report_type' => ReportType::Products,
            'status' => ReportStatus::Completed,
        ]);

        $this->actingAs($other, 'api');

        $response = $this->getJson("/api/reports/$reportLog->id");

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_show_allows_admin_to_view_any_report(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->admin()->create();

        $reportLog = ReportLog::create([
            'user_id' => $owner->id,
            'report_type' => ReportType::Products,
            'status' => ReportStatus::Completed,
        ]);

        $this->actingAs($admin, 'api');

        $response = $this->getJson("/api/reports/$reportLog->id");

        $response->assertOk();
        $response->assertJsonPath('data.id', $reportLog->id);
    }

    public function test_download_requires_authentication(): void
    {
        $user = User::factory()->create();

        $reportLog = ReportLog::create([
            'user_id' => $user->id,
            'report_type' => ReportType::Products,
            'status' => ReportStatus::Completed,
        ]);

        $response = $this->getJson("/api/reports/$reportLog->id/download");

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_download_returns_forbidden_for_other_user(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $reportLog = ReportLog::create([
            'user_id' => $owner->id,
            'report_type' => ReportType::Products,
            'status' => ReportStatus::Completed,
        ]);

        $this->actingAs($other, 'api');

        $response = $this->getJson("/api/reports/$reportLog->id/download");

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_download_returns_not_found_when_file_not_available(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();

        $reportLog = ReportLog::create([
            'user_id' => $user->id,
            'report_type' => ReportType::Products,
            'status' => ReportStatus::Processing,
        ]);

        $this->actingAs($user, 'api');

        $response = $this->getJson("/api/reports/$reportLog->id/download");

        $response->assertStatus(Response::HTTP_NOT_FOUND);
        $response->assertJsonPath('message', 'Report file not available');
    }

    public function test_download_returns_file_when_completed(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();

        $reportLog = ReportLog::create([
            'user_id' => $user->id,
            'report_type' => ReportType::Products,
            'status' => ReportStatus::Completed,
            'file_name' => 'test_report.xlsx',
            'file_path' => 'reports/test_report.xlsx',
        ]);

        Storage::disk('local')->put('reports/test_report.xlsx', 'Excel content');

        $this->actingAs($user, 'api');

        $response = $this->get("/api/reports/$reportLog->id/download");

        $response->assertOk();
        $response->assertHeader('content-disposition', 'attachment; filename=test_report.xlsx');
    }

    public function test_download_allows_admin_to_download_any_report(): void
    {
        Storage::fake('local');

        $owner = User::factory()->create();
        $admin = User::factory()->admin()->create();

        $reportLog = ReportLog::create([
            'user_id' => $owner->id,
            'report_type' => ReportType::Products,
            'status' => ReportStatus::Completed,
            'file_name' => 'test_report.xlsx',
            'file_path' => 'reports/test_report.xlsx',
        ]);

        Storage::disk('local')->put('reports/test_report.xlsx', 'Excel content');

        $this->actingAs($admin, 'api');

        $response = $this->get("/api/reports/$reportLog->id/download");

        $response->assertOk();
    }
}

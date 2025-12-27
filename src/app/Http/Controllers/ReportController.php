<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Report\GenerateReportRequest;
use App\Http\Resources\Report\ReportLogResource;
use App\Models\ReportLog;
use App\Models\User;
use App\Services\Report\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ReportController extends Controller
{
    public function __construct(
        private readonly ReportService $reportService,
    ) {}

    /**
     * List user's reports.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        /** @var User $user */
        $user = $request->user();

        $reports = $this->reportService->getUserReports($user);

        return ReportLogResource::collection($reports);
    }

    /**
     * Queue a new report generation.
     */
    public function store(GenerateReportRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $reportLog = $this->reportService->queueReport(
            user: $user,
            reportType: $request->getReportType(),
        );

        return response()->json([
            'message' => 'Report generation queued successfully',
            'data' => new ReportLogResource($reportLog),
        ], Response::HTTP_ACCEPTED);
    }

    /**
     * Get report status.
     */
    public function show(Request $request, ReportLog $report): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if (! $this->reportService->canUserAccessReport($user, $report)) {
            return response()->json([
                'message' => 'Forbidden',
            ], Response::HTTP_FORBIDDEN);
        }

        return response()->json([
            'data' => new ReportLogResource($report),
        ]);
    }

    /**
     * Download completed report.
     */
    public function download(Request $request, ReportLog $report): StreamedResponse|JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if (! $this->reportService->canUserAccessReport($user, $report)) {
            return response()->json([
                'message' => 'Forbidden',
            ], Response::HTTP_FORBIDDEN);
        }

        $response = $this->reportService->downloadReport($report);

        if ($response === null) {
            return response()->json([
                'message' => 'Report file not available',
            ], Response::HTTP_NOT_FOUND);
        }

        return $response;
    }
}

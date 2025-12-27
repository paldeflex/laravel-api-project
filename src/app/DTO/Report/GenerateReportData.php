<?php

declare(strict_types=1);

namespace App\DTO\Report;

use App\Enums\Report\ReportType;

final readonly class GenerateReportData
{
    public function __construct(
        public int $userId,
        public ReportType $reportType,
    ) {}

    /**
     * @param  array{user_id: int, report_type: string}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            userId: $data['user_id'],
            reportType: ReportType::from($data['report_type']),
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Resources\Report;

use App\Models\ReportLog;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ReportLog
 */
final class ReportLogResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'report_type' => $this->report_type->value,
            'report_type_label' => $this->report_type->label(),
            'status' => $this->status->value,
            'file_name' => $this->file_name,
            'error_message' => $this->error_message,
            'attempts' => $this->attempts,
            'started_at' => $this->started_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}

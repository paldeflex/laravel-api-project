<?php

declare(strict_types=1);

namespace App\Http\Requests\Report;

use App\Enums\Report\ReportType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class GenerateReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'report_type' => ['required', 'string', Rule::enum(ReportType::class)],
        ];
    }

    public function getReportType(): ReportType
    {
        /** @var string $type */
        $type = $this->validated('report_type');

        return ReportType::from($type);
    }
}

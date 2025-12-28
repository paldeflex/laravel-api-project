<?php

declare(strict_types=1);

namespace App\Exports;

use App\Exports\Concerns\FormatsDateTime;
use App\Models\ProductReview;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * @implements WithMapping<ProductReview>
 */
final class ProductReviewsExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    use FormatsDateTime;

    /**
     * @return Collection<int, ProductReview>
     */
    public function collection(): Collection
    {
        return ProductReview::with(['user', 'product'])->get();
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return [
            __('exports.product_reviews.id'),
            __('exports.product_reviews.product'),
            __('exports.product_reviews.user'),
            __('exports.product_reviews.rating'),
            __('exports.product_reviews.comment'),
            __('exports.product_reviews.created_at'),
        ];
    }

    /**
     * @param  ProductReview  $row
     * @return array<int, mixed>
     */
    public function map(mixed $row): array
    {
        return [
            $row->id,
            $row->product->name ?? __('exports.common.empty'),
            $row->user->name ?? __('exports.common.empty'),
            $row->rating,
            $row->text ?? __('exports.common.empty'),
            $this->formatDateTime($row->created_at),
        ];
    }

    public function styles(Worksheet $sheet): void
    {
        $sheet->getStyle('A1:F1')->getFont()->setBold(true);
    }
}

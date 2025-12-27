<?php

declare(strict_types=1);

namespace App\Exports;

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
            'ID',
            'Product',
            'User',
            'Rating',
            'Review Text',
            'Created At',
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
            $row->product !== null ? $row->product->name : '-',
            $row->user !== null ? $row->user->name : '-',
            $row->rating,
            $row->text ?? '-',
            $row->created_at?->format('Y-m-d H:i:s'),
        ];
    }

    public function styles(Worksheet $sheet): void
    {
        $sheet->getStyle('A1:F1')->getFont()->setBold(true);
    }
}

<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Product;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * @implements WithMapping<Product>
 */
final class ProductsExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    /**
     * @return Collection<int, Product>
     */
    public function collection(): Collection
    {
        return Product::with('user')->get();
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Description',
            'Price',
            'Quantity',
            'Status',
            'Owner',
            'Created At',
        ];
    }

    /**
     * @param  Product  $row
     * @return array<int, mixed>
     */
    public function map(mixed $row): array
    {
        return [
            $row->id,
            $row->name,
            $row->description ?? '-',
            $row->price !== null ? number_format($row->price / 100, 2) : '-',
            $row->quantity ?? 0,
            $row->status->value,
            $row->user !== null ? $row->user->name : '-',
            $row->created_at?->format('Y-m-d H:i:s'),
        ];
    }

    public function styles(Worksheet $sheet): void
    {
        $sheet->getStyle('A1:H1')->getFont()->setBold(true);
    }
}

<?php

declare(strict_types=1);

namespace App\Exports;

use App\Exports\Concerns\FormatsDateTime;
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
    use FormatsDateTime;

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
            __('exports.products.id'),
            __('exports.products.name'),
            __('exports.products.description'),
            __('exports.products.price'),
            __('exports.products.quantity'),
            __('exports.products.status'),
            __('exports.products.owner'),
            __('exports.products.created_at'),
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
            $row->description ?? __('exports.common.empty'),
            $row->price !== null ? $this->convertCentsToDecimal($row->price) : __('exports.common.empty'),
            $row->quantity ?? 0,
            $row->status->value,
            $row->user->name ?? __('exports.common.empty'),
            $this->formatDateTime($row->created_at),
        ];
    }

    public function styles(Worksheet $sheet): void
    {
        $sheet->getStyle('A1:H1')->getFont()->setBold(true);
    }

    private function convertCentsToDecimal(int $cents): string
    {
        return number_format($cents / 100, 2);
    }
}

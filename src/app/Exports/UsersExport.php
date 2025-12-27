<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * @implements WithMapping<User>
 */
final class UsersExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    /**
     * @return Collection<int, User>
     */
    public function collection(): Collection
    {
        return User::withCount('productReviews')->get();
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Email',
            'Is Admin',
            'Reviews Count',
            'Created At',
        ];
    }

    /**
     * @param  User  $row
     * @return array<int, mixed>
     */
    public function map(mixed $row): array
    {
        /** @var int $reviewsCount */
        $reviewsCount = $row->getAttribute('product_reviews_count') ?? 0;

        return [
            $row->id,
            $row->name,
            $row->email,
            $row->is_admin ? 'Yes' : 'No',
            $reviewsCount,
            $row->created_at?->format('Y-m-d H:i:s'),
        ];
    }

    public function styles(Worksheet $sheet): void
    {
        $sheet->getStyle('A1:F1')->getFont()->setBold(true);
    }
}

<?php

declare(strict_types=1);

namespace App\Enums\Report;

enum ReportType: string
{
    case Products = 'products';
    case ProductReviews = 'product_reviews';
    case Users = 'users';

    public function label(): string
    {
        return match ($this) {
            self::Products => 'Отчёт по товарам',
            self::ProductReviews => 'Отчёт по отзывам о товарах',
            self::Users => 'Отчёт по пользователям',
        };
    }
}

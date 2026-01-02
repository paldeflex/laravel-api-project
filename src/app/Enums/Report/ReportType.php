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
        return __('enums.report_type.'.$this->value);
    }
}

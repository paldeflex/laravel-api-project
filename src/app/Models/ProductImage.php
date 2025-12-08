<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

final class ProductImage extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'path',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Report\ReportStatus;
use App\Enums\Report\ReportType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property ReportType $report_type
 * @property string|null $file_name
 * @property string|null $file_path
 * @property ReportStatus $status
 * @property string|null $error_message
 * @property int $attempts
 * @property \Illuminate\Support\Carbon|null $started_at
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read User $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReportLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReportLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReportLog query()
 *
 * @mixin \Eloquent
 */
final class ReportLog extends Model
{
    protected $fillable = [
        'user_id',
        'report_type',
        'file_name',
        'file_path',
        'status',
        'error_message',
        'attempts',
        'started_at',
        'completed_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'report_type' => ReportType::class,
        'status' => ReportStatus::class,
        'attempts' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

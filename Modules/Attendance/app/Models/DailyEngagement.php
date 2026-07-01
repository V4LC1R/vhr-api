<?php

namespace Modules\Attendance\Models;

use App\Supports\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Attributes\UseResource;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Attendance\Database\Factories\DailyEngagementFactory;
use Modules\Attendance\Enums\DailyEngagementStatusEnum;
use Modules\Attendance\Enums\DailyEngagementTypeEnum;
use Modules\Attendance\Http\Resources\DailyEngagementResource;
use Modules\Core\Models\UserCompany;

#[Fillable([
    'companyId',
    'employeeId',
    'workloadId',
    'date',
    'type',
    'status',
    'worked_minutes',
    'expected_minutes',
    'balance_minutes',
    'diaria_value',
    'note',
    'draftedBy',
    'approvedBy',
    'approvedAt',
])]
#[UseFactory(DailyEngagementFactory::class)]
#[UseResource(DailyEngagementResource::class)]
class DailyEngagement extends Model
{
    use HasUuids;
    use HasFactory;
    use BelongsToCompany;

    protected $table = 'attendance.daily_engagements';

    protected function casts(): array
    {
        return [
            'date'             => 'date',
            'type'             => DailyEngagementTypeEnum::class,
            'status'           => DailyEngagementStatusEnum::class,
            'worked_minutes'   => 'integer',
            'expected_minutes' => 'integer',
            'balance_minutes'  => 'integer',
            'diaria_value'     => 'double',
            'approvedAt'       => 'datetime',
        ];
    }

    public function timeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class, 'dailyEngagementId');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(employeeRepo()->getModelClass(), 'employeeId');
    }

    public function workload(): BelongsTo
    {
        return $this->belongsTo(workloadRepo()->getModelClass(), 'workloadId');
    }

    public function draftedByUserCompany(): BelongsTo
    {
        return $this->belongsTo(UserCompany::class, 'draftedBy');
    }

    public function approvedByUserCompany(): BelongsTo
    {
        return $this->belongsTo(UserCompany::class, 'approvedBy');
    }
}

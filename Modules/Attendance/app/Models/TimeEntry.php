<?php

namespace Modules\Attendance\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Attributes\UseResource;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Attendance\Database\Factories\TimeEntryFactory;
use Modules\Attendance\Enums\TimeEntrySourceEnum;
use Modules\Attendance\Enums\TimeEntryTypeEnum;
use Modules\Attendance\Http\Resources\TimeEntryResource;

#[Fillable([
    'companyId',
    'dailyEngagementId',
    'punched_at',
    'type',
    'source',
    'note',
])]
#[UseFactory(TimeEntryFactory::class)]
#[UseResource(TimeEntryResource::class)]
class TimeEntry extends Model
{
    use HasUuids;
    use HasFactory;

    protected $table = 'attendance.time_entries';

    protected function casts(): array
    {
        return [
            'punched_at' => 'datetime',
            'type'       => TimeEntryTypeEnum::class,
            'source'     => TimeEntrySourceEnum::class,
        ];
    }

    public function dailyEngagement(): BelongsTo
    {
        return $this->belongsTo(DailyEngagement::class, 'dailyEngagementId');
    }
}

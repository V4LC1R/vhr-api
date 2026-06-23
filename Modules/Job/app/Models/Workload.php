<?php

namespace Modules\Job\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Attributes\UseResource;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Job\Database\Factories\WorkloadFactory;
use Modules\Job\Http\Resources\WorkloadResource;

#[Fillable([
    'companyId',
    'description',
    'monthly_hours',
    'weekly_hours',
    'entry_time',
    'left_time',
    'interval_start_at',
    'interval_end_at',
])]
#[UseFactory(WorkloadFactory::class)]
#[UseResource(WorkloadResource::class)]
class Workload extends Model
{
    use HasUuids;
    use HasFactory;

    protected $table = 'job.workloads';

    public function employments(): HasMany
    {
        return $this->hasMany(Employment::class, 'workloadId');
    }
}

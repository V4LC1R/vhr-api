<?php

namespace Modules\Job\Models;

use App\Supports\Traits\BelongsToCompany;
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
    'monthlyHours',
    'weeklyHours',
    'entryTime',
    'leftTime',
    'intervalStartAt',
    'intervalEndAt',
])]
#[UseFactory(WorkloadFactory::class)]
#[UseResource(WorkloadResource::class)]
class Workload extends Model
{
    use HasUuids;
    use HasFactory;
    use BelongsToCompany;

    protected $table = 'job.workloads';

    protected function casts(): array
    {
        return [
            'monthlyHours' => 'integer',
            'weeklyHours'  => 'integer',
        ];
    }

    public function employments(): HasMany
    {
        return $this->hasMany(Employment::class, 'workloadId');
    }
}

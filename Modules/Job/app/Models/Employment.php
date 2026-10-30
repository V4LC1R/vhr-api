<?php

namespace Modules\Job\Models;

use App\Supports\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Job\Database\Factories\EmploymentFactory;
use Modules\Job\Enums\EmploymentStatusEnum;
use Modules\Job\Enums\EmploymentTypeEnum;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'employeeId',
    'workloadId',
    'companyId',
    'kind',
    'status',
    'registerAt',
    'leftAt',
])]
#[UseFactory(EmploymentFactory::class)]
class Employment extends Model
{
    use HasUuids;
    use HasFactory;
    use SoftDeletes;
    use BelongsToCompany;

    protected $table = 'job.employments';

    protected function casts(): array
    {
        return [
            'status'      => EmploymentStatusEnum::class,
            'kind'        => EmploymentTypeEnum::class,
            'registerAt' => 'datetime',
            'leftAt'     => 'datetime',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employeeId');
    }

    public function workload(): BelongsTo
    {
        return $this->belongsTo(Workload::class, 'workloadId');
    }
}

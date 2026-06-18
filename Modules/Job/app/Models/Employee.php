<?php

namespace Modules\Job\Models;
namespace Modules\Job\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
// use Illuminate\Database\Eloquent\Attributes\UseFactory;
// use Illuminate\Database\Eloquent\Attributes\UseResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Job\Database\Factories\EmployeeFactory;

//use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'personCompanyId',
    'workloadId',
    'registerNumber',
    'register_at',
    'out_at',
])]

#[UseFactory(EmployeeFactory::class)]
// #[UseResource(UserResource::class)]
class Employee extends Model
{
    use HasUuids;
    use HasFactory;

    protected $table = 'job.employees';

    protected function casts(): array
    {
        return [
            'registerNumber' => 'integer',
            'register_at' => 'datetime',
            'left_at' => 'datetime',
        ];
    }

    public function personCompany(): BelongsTo
    {
        return $this->belongsTo(PersonCompany::class, 'personCompanyId', 'id');
    }

    public function workload(): BelongsTo
    {
        return $this->belongsTo(Workload::class, 'workloadId', 'id');
    }
}

<?php

namespace Modules\Job\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Attributes\UseResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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

    // Define o schema.tabela explícito do PostgreSQL
    protected $table = 'job.workloads';

    /**
     * Relacionamento com os funcionários que possuem esta jornada.
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'workloadId');
    }
}

<?php

namespace Modules\Job\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Attributes\UseResource;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\Job\Database\Factories\EmployeeFactory;
use Modules\Job\Enums\EmploymentStatusEnum;
use Modules\Job\Http\Resources\EmployeeResource;

#[Fillable([
    'companyId',
    'personId',
    'registerNumber',
])]
#[UseFactory(EmployeeFactory::class)]
#[UseResource(EmployeeResource::class)]
class Employee extends Model
{
    use HasUuids;
    use HasFactory;

    protected $table = 'job.employees';

    protected function casts(): array
    {
        return [
            'registerNumber' => 'integer',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(
            companyRepo()->getModelClass(),
            'companyId',
            'id'
        );
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(
            personRepo()->getModelClass(),
            'personId',
            'id'
        );
    }

    public function employments(): HasMany
    {
        return $this->hasMany(Employment::class, 'employeeId');
    }

    public function activeEmployment(): HasOne
    {
        return $this->hasOne(Employment::class, 'employeeId')
            ->whereIn('status', [
                EmploymentStatusEnum::HIRED->value,
                EmploymentStatusEnum::EXPERIENCE->value,
            ])
            ->latest();
    }
}

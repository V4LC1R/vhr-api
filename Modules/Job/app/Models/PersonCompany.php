<?php

namespace Modules\Job\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Job\Database\Factories\PersonCompanyFactory;
use Modules\Job\Enums\PersonCompanyRoleEnum;
use Modules\Job\Enums\PersonCompanyStatusEnum;

#[Fillable([
    'companyId',
    'personId',
    'status',
    'role',
])]
#[UseFactory(PersonCompanyFactory::class)]
class PersonCompany extends Model
{
    use HasUuids;
    use HasFactory;

    protected $table = 'job.person_companies';

    protected function casts(): array
    {
        return [
            'status' => PersonCompanyStatusEnum::class,
            'role' => PersonCompanyRoleEnum::class,
        ];
    }

    public static function options(): array
    {
        return array_map(
            fn (self $item) => [
                'value' => $item->value,
                'label' => $item->label(),
            ],
            self::cases()
        );
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

    public function employee(): HasMany
    {
        return $this->hasMany(
            Employee::class,
            'personCompanyId',
            'id'
        );
    }
}

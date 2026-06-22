<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Attributes\UseResource;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Database\Factories\PersonFactory;
use Modules\Core\Http\Resources\PersonResource;

#[Fillable([
    'name',
    'email',
    'cellphone',
])]
#[UseFactory(PersonFactory::class)]
#[UseResource(PersonResource::class)]
class Person extends Model
{
    use HasUuids;
    use HasFactory;
    use SoftDeletes;

    protected $table = 'core.persons';

    public $incrementing = false;

    protected $keyType = 'string';

    public function employees(): HasMany
    {
        return $this->hasMany(
            employeeRepo()->getModelClass(),
            'personId'
        );
    }

    public function hasCompanyLink(): bool
    {
        return $this->employees()
            ->exists();
    }
}

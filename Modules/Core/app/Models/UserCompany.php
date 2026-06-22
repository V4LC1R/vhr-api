<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Attributes\UseResource;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Support\Collection;
use Modules\Core\Database\Factories\UserCompanyFactory;
use Modules\Core\Http\Resources\UserCompanyResource;
use Modules\Core\Models\Company;
use Spatie\Permission\Traits\HasRoles;

#[Fillable([
    'companyId',
    'userId',
    'personId',
])]
#[UseResource(UserCompanyResource::class)]
#[UseFactory(UserCompanyFactory::class)]
class UserCompany extends Model implements AuthorizableContract
{
    use HasRoles;
    use HasUuids;
    use HasFactory;

    protected $table = 'core.user_companies';

    public $incrementing = false;

    protected $keyType = 'string';


    public function can($abilities, $arguments = []): bool
    {
        return $this->hasPermissionTo($abilities, 'web');
    }

    protected function getDefaultGuardName(): string
    {
        return 'web';
    }

    public function getGuardNames(): Collection
    {
        return collect(['web']);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'userId'
        );
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(
            Company::class,
            'companyId'
        );
    }


    public function person(): BelongsTo
    {
        return $this->belongsTo(
            Person::class,
            'personId'
        );
    }
}

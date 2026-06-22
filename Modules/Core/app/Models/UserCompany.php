<?php

namespace Modules\Core\Models;

use App\Supports\Traits\AdapterToPermission;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Attributes\UseResource;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Modules\Core\Database\Factories\UserCompanyFactory;
use Modules\Core\Http\Resources\UserCompanyResource;
use Modules\Core\Models\Company;

#[Fillable([
    'companyId',
    'userId',
    'personId',
])]
#[UseResource(UserCompanyResource::class)]
#[UseFactory(UserCompanyFactory::class)]
class UserCompany extends Model implements AuthorizableContract
{
    use HasUuids;
    use HasFactory;
    use AdapterToPermission;

    protected $table = 'core.user_companies';

    public $incrementing = false;

    protected $keyType = 'string';

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

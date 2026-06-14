<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Attributes\UseResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;
use Modules\Core\Database\Factories\UserFactory;
use Modules\Core\Http\Resources\UserResource;
use Spatie\Permission\Traits\HasRoles;

#[Fillable([
    'personId',
    'email',
    'password',
    'status',
])]

#[Hidden(['password'])]
#[UseFactory(UserFactory::class)]
#[UseResource(UserResource::class)]
class User extends Authenticatable
{
    use HasUuids;
    use HasFactory;
    use HasApiTokens;
    use HasRoles;
    use SoftDeletes;

    protected $table = 'core.users';

    public $incrementing = false;

    protected $keyType = 'string';

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'personId');
    }
}

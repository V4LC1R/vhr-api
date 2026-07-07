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
use Modules\Core\Enums\TokenPasswordStatusEnum;
use Modules\Core\Http\Resources\PersonResource;

#[Fillable([
    'token',
    'status',
    'userId',
    'ipAddress',
    'userAgent',
    'expiresAt',
    'requestedAt',
    'usedAt'
])]
#[UseFactory(PersonFactory::class)]
#[UseResource(PersonResource::class)]
class PasswordResetToken extends Model
{
    use HasUuids;
    use HasFactory;
    use SoftDeletes;

    protected $table = 'core.password_reset_tokens';

    public $incrementing = false;

    protected $keyType = 'string';

    protected function casts(): array
    {
        return [
            'status'           => TokenPasswordStatusEnum::class,
            'requestedAt'       => 'datetime',
            'expiresAt'       => 'datetime',
            'usedAt' => 'datetime',
        ];
    }

    public function user(): HasMany
    {
        return $this->hasMany(
            User::class,
            'userId'
        );
    }
}

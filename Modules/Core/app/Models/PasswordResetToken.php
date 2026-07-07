<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Modules\Core\Database\Factories\PasswordResetTokenFactory;
use Modules\Core\Enums\TokenPasswordStatusEnum;

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
#[UseFactory(PasswordResetTokenFactory::class)]
class PasswordResetToken extends Model
{
    use HasUuids;
    use HasFactory;

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

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'userId'
        );
    }
}

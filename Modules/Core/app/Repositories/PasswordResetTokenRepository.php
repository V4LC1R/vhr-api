<?php

declare(strict_types=1);

namespace Modules\Core\Repositories;

use App\Contracts\PasswordResetTokenRepositoryInterface;
use App\Supports\Abstracts\BaseRepository;
use Modules\Core\Enums\TokenPasswordStatusEnum;
use Modules\Core\Models\PasswordResetToken;

/**
 * @extends BaseRepository<PasswordResetToken>
 */
class PasswordResetTokenRepository extends BaseRepository implements PasswordResetTokenRepositoryInterface
{
    public function __construct(
        PasswordResetToken $model
    ) {
        parent::__construct($model);
    }

    public function findValidByToken(string $token): ?PasswordResetToken
    {
        return $this->model
            ->newQuery()
            ->where('token', $token)
            ->where('status', TokenPasswordStatusEnum::PENDING)
            ->where('expiresAt', '>', now())
            ->first();
    }

    public function markUsed(PasswordResetToken $token): PasswordResetToken
    {
        $token->update([
            'status' => TokenPasswordStatusEnum::USED,
            'usedAt' => now(),
        ]);

        return $token->refresh();
    }

    public function invalidatePendingForUser(string $userId): void
    {
        $this->model
            ->newQuery()
            ->where('userId', $userId)
            ->where('status', TokenPasswordStatusEnum::PENDING)
            ->update([
                'status' => TokenPasswordStatusEnum::USED,
                'usedAt' => now(),
            ]);
    }
}

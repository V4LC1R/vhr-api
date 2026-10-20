<?php

declare(strict_types=1);

namespace Modules\Core\Contracts;

use App\Contracts\RepositoryInterface;
use Modules\Core\Models\PasswordResetToken;

/**
 * @extends RepositoryInterface<PasswordResetToken>
 */
interface PasswordResetTokenRepositoryInterface extends RepositoryInterface
{
    public function findValidByToken(string $token);

    public function markUsed(PasswordResetToken $token);

    public function invalidatePendingForUser(string $userId): void;
}

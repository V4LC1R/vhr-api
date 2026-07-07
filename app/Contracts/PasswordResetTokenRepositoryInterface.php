<?php

declare(strict_types=1);

namespace App\Contracts;

use Modules\Core\Models\PasswordResetToken;

interface PasswordResetTokenRepositoryInterface
{
    public function getModel();

    public function findById(string $id, array $relations = []);

    public function create(array $data);

    public function findValidByToken(string $token);

    public function markUsed(PasswordResetToken $token);

    public function invalidatePendingForUser(string $userId): void;

    public function getModelClass(): string;
}

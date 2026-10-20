<?php

declare(strict_types=1);

namespace Modules\Core\Contracts;

use App\Contracts\RepositoryInterface;

interface UserRepositoryInterface extends RepositoryInterface
{
    public function findByEmail(string $email);
}

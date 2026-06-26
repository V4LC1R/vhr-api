<?php

declare(strict_types=1);

namespace Modules\Core\Repositories;

use App\Contracts\UserRepositoryInterface;
use App\Supports\Abstracts\BaseRepository;
use Modules\Core\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends BaseRepository<User>
 */
class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function __construct(
        User $model
    ) {
        parent::__construct($model);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->model
            ->newQuery()
            ->where('email', $email)
            ->first();
    }
}

<?php

namespace Modules\Core\Repositories;

use App\Contracts\UserRepositoryInterface;
use Modules\Core\Models\User;
use Illuminate\Database\Eloquent\Model;

class UserRepository implements UserRepositoryInterface
{
    public function __construct(
        protected User $model
    ) {
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    public function findById(string $id, array $relations = []): ?Model
    {
        return $this->model->newQuery()->with($relations)->find($id);
    }

    public function findByEmail(string $email): ?Model
    {
        return $this->model->newQuery()->where('email', $email)->first();
    }

    public function create(array $data): Model
    {
        return $this->model->newQuery()->create($data);
    }

    public function update(string $id, array $data): ?Model
    {
        $user = $this->findById($id);

        if (!$user) {
            return null;
        }

        $user->update($data);

        return $user->fresh();
    }

    public function delete(string $id): bool
    {
        $user = $this->findById($id);

        if (!$user) {
            return false;
        }

        return $user->delete();
    }
}

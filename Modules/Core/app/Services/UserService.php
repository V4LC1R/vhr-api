<?php

namespace Modules\Core\Services;

use App\Contracts\UserRepositoryInterface;
use App\Exceptions\UniqueConstraintException;
use Modules\Core\Data\UserData;
use Modules\Core\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserService
{
    public function __construct(
        protected UserRepositoryInterface $userRepository
    ) {
    }

    public function create(UserData $userData)
    {
        $usuarioExistente = $this->userRepository->findByEmail($userData->email);

        if ($usuarioExistente) {
            throw new UniqueConstraintException('Este e-mail já está cadastrado no sistema.');
        }

        $data = [
            'personId' => $userData->personId,
            'email'    => $userData->email,
            'password' => $userData->password
        ];

        return $this
            ->userRepository
            ->create($data)
            ->toResource();
    }

    public function update(string $id, UserData $userData)
    {
        $data = $userData->except('id')->toArray();

        $user = $this->userRepository->update($id, $data);

        if (!$user) {
            throw (new ModelNotFoundException())->setModel(User::class, [$id]);
        }

        return $user->toResource();
        ;
    }

    public function delete(string $id): void
    {
        $deletado = $this->userRepository->delete($id);

        if (!$deletado) {
            throw (new ModelNotFoundException())->setModel(User::class, [$id]);
        }
    }
}

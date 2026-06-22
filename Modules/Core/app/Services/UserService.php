<?php

namespace Modules\Core\Services;

use App\Contracts\UserRepositoryInterface;
use App\Exceptions\UniqueConstraintException;
use Illuminate\Support\Facades\DB;
use Modules\Core\Data\UserData;
use Modules\Core\Models\User;
use Modules\Core\Models\UserCompany;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserService
{
    public function __construct(
        protected UserRepositoryInterface $userRepository
    ) {
    }

    public function create(UserData $userData, string $companyId, ?string $personId = null)
    {
        $usuarioExistente = $this->userRepository
            ->findByEmail($userData->email);

        if ($usuarioExistente) {
            throw new UniqueConstraintException(
                'Este e-mail já está cadastrado no sistema.'
            );
        }

        return DB::transaction(function () use (
            $userData,
            $companyId,
            $personId
        ) {

            $user = $this->userRepository->create([
                'email'    => $userData->email,
                'password' => $userData->password,
            ]);

            UserCompany::create([
                'userId'    => $user->id,
                'companyId' => $companyId,
                'personId'  => $personId,
            ]);

            return $user
                ->load('userCompanies.company', 'userCompanies.person')
                ->toResource();
        });
    }


    public function update(string $id, UserData $userData)
    {
        $data = $userData
            ->except('id')
            ->toArray();


        $user = $this->userRepository->update($id, $data);


        if (!$user) {
            throw (new ModelNotFoundException())
                ->setModel(User::class, [$id]);
        }


        return $user->toResource();
    }


    public function delete(string $id): void
    {
        $deletado = $this->userRepository->delete($id);


        if (!$deletado) {
            throw (new ModelNotFoundException())
                ->setModel(User::class, [$id]);
        }
    }
}

<?php

namespace Modules\Core\Services;

use App\Contracts\UserRepositoryInterface;
use App\Exceptions\UniqueConstraintException;
use Illuminate\Support\Facades\DB;
use Modules\Core\Data\UserData;
use Modules\Core\Models\User;
use Modules\Core\Models\UserCompany;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Spatie\Permission\PermissionRegistrar;

class UserService
{
    public function __construct(
        protected UserRepositoryInterface $userRepository
    ) {
    }

    public function list(string $companyId, array $filters = [], int $perPage = 15)
    {
        return User::query()
            ->whereHas(
                'userCompanies',
                fn ($q) => $q->where('companyId', $companyId)
            )
            ->with([
                'userCompanies' => fn ($q) => $q
                    ->where('companyId', $companyId)
                    ->with(['company', 'person']),
            ])
            ->when(
                !empty($filters['email']),
                fn ($q) => $q->where('email', 'ilike', "%{$filters['email']}%")
            )
            ->when(
                !empty($filters['status']),
                fn ($q) => $q->where('status', $filters['status'])
            )
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn ($user) => $user->toResource());
    }

    public function create(UserData $userData, string $companyId, string $role, ?string $personId = null)
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
            $role,
            $personId
        ) {

            $user = $this->userRepository->create([
                'email'    => $userData->email,
                'password' => $userData->password,
            ]);

            $userCompany = UserCompany::create([
                'userId'    => $user->id,
                'companyId' => $companyId,
                'personId'  => $personId,
            ]);

            app(PermissionRegistrar::class)->setPermissionsTeamId($companyId);
            $userCompany->syncRoles([$role]);

            return $user
                ->load('userCompanies.company', 'userCompanies.person')
                ->toResource();
        });
    }


    public function update(string $id, UserData $userData, ?string $companyId = null, ?string $role = null)
    {
        $data = $userData
            ->except('id')
            ->toArray();


        $user = $this->userRepository->update($id, $data);


        if (!$user) {
            throw (new ModelNotFoundException())
                ->setModel(User::class, [$id]);
        }

        if ($companyId && $role) {
            $userCompany = UserCompany::query()
                ->where('userId', $id)
                ->where('companyId', $companyId)
                ->first();

            if ($userCompany) {
                app(PermissionRegistrar::class)->setPermissionsTeamId($companyId);
                $userCompany->syncRoles([$role]);
            }
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

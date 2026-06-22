<?php

namespace Modules\Auth\Services;

use App\Contracts\UserRepositoryInterface;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Modules\Auth\Data\AuthUserData;
use Modules\Core\Http\Resources\UserResource;

class AuthService
{
    public function __construct(
        protected UserRepositoryInterface $userRepository
    ) {
    }

    public function login(AuthUserData $data): array
    {
        $user = $this->userRepository->findByEmail($data->email);

        if (!$user || !Hash::check($data->password, $user->password)) {
            throw new AuthenticationException(
                'As credenciais fornecidas estão incorretas.'
            );
        }

        Auth::guard('web')->login($user);

        $userCompanies = $user->userCompanies()
            ->with('company')
            ->get();

        $companyLogged = false;

        if ($userCompanies->count() === 1) {
            $company = $userCompanies->first();

            session([
                'companyId' => $company->companyId
            ]);

            $companyLogged = true;
        }

        return [
            'user' => $user->toResource(),
            'companyLogged' => $companyLogged,
        ];
    }


    public function selectCompany(string $companyId): void
    {
        $user = auth()->guard('web')->user();

        $userCompany = $user->userCompanies()
            ->where('companyId', $companyId)
            ->first();

        if (!$userCompany) {
            throw new AuthenticationException(
                'Usuário não possui acesso a esta empresa.'
            );
        }

        session([
            'companyId' => $userCompany->companyId
        ]);
    }


    public function logout(): void
    {
        Auth::guard('web')->logout();
    }
}

<?php

namespace Modules\Auth\Services;

use App\Contracts\UserRepositoryInterface;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Modules\Auth\Data\AuthUserData;

class AuthService
{
    public function __construct(
        protected UserRepositoryInterface $userRepository
    ) {
    }

    public function login(AuthUserData $data)
    {
        $usuario = $this->userRepository->findByEmail($data->email);

        if (!$usuario || !Hash::check($data->password, $usuario->password)) {
            throw new AuthenticationException(
                'As credenciais fornecidas estão incorretas.'
            );
        }

        Auth::guard('web')->login($usuario);

        return $usuario;
    }

    public function logout(): void
    {
        Auth::guard('web')->logout();
    }
}

<?php

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Auth\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;
use Modules\Auth\Http\Requests\AuthUserRequest;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {
    }

    public function login(AuthUserRequest $request): JsonResponse
    {
        $dto = $request->toDTO();

        try {
            $usuario = $this->authService->login($dto);

            $request->session()->regenerate();

            return response()->json([
                'usuario' => $usuario->toResource()
            ], 200);
        } catch (AuthenticationException $e) {
            return response()->json([
                'mensagem' => $e->getMessage()
            ], 401);
        }
    }

    public function me(): JsonResponse
    {
        $usuarioLogado = auth()->guard('web')->user();

        return response()->json([
            'usuario' => $usuarioLogado->toResource()
        ], 200);
    }

    public function logout(): JsonResponse
    {
        $this->authService->logout();

        return response()->json([
            'mensagem' => 'Logout realizado com sucesso'
        ], 200);
    }
}

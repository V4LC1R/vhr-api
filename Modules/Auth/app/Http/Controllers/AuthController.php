<?php

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Auth\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Auth\AuthenticationException;
use Modules\Auth\Http\Requests\AuthUserRequest;
use Modules\Auth\Http\Requests\SelectCompanyRequest;

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
            $authData = $this->authService->login($dto);

            $request->session()->regenerate(true);

            return response()->json($authData);
        } catch (AuthenticationException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 401);
        }
    }

    public function selectCompany(SelectCompanyRequest $request): JsonResponse
    {
        try {
            $this->authService->selectCompany(
                $request->validated('companyId')
            );

            return response()->json([
                'message' => 'Empresa selecionada com sucesso'
            ], 200);
        } catch (AuthenticationException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 403);
        }
    }

    public function me(): JsonResponse
    {
        $usuarioLogado = auth()->guard('web')->user();

        return response()->json([
            'user' => $usuarioLogado->toResource()
        ], 200);
    }

    public function logout(): JsonResponse
    {
        $this->authService->logout();

        return response()->json([
            'message' => 'Logout realizado com sucesso'
        ], 200);
    }
}

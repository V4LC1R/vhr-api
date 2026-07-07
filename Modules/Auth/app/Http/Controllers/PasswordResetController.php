<?php

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Auth\Http\Requests\ForgotPasswordRequest;
use Modules\Auth\Http\Requests\ResetPasswordRequest;
use Modules\Auth\Services\PasswordResetService;

class PasswordResetController extends Controller
{
    public function __construct(
        protected PasswordResetService $passwordResetService
    ) {
    }

    public function forgot(ForgotPasswordRequest $request): JsonResponse
    {
        $this->passwordResetService->request($request->toDTO());

        // Resposta mascarada: idêntica exista ou não o e-mail (anti-enumeração).
        return response()->json([
            'message' => 'Se o e-mail existir, enviaremos as instruções.',
        ], 200);
    }

    public function reset(ResetPasswordRequest $request): JsonResponse
    {
        $this->passwordResetService->reset($request->toDTO());

        return response()->json([
            'message' => 'Senha redefinida com sucesso.',
        ], 200);
    }
}

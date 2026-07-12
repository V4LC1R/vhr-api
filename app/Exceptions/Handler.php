<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class Handler
{
    public static function register(Exceptions $exceptions): void
    {

        $exceptions->renderable(fn (UniqueConstraintException $e) => response()->json([
            'message' => $e->getMessage(),
        ], Response::HTTP_CONFLICT));

        // Regras de negócio violadas (ex.: excluir jornada em uso) → 409 com a
        // mensagem da exceção. Registrada depois da UniqueConstraintException
        // pra subclasse mais específica casar primeiro.
        $exceptions->renderable(fn (DomainException $e) => response()->json([
            'message' => $e->getMessage(),
        ], Response::HTTP_CONFLICT));

        $exceptions->renderable(fn (ModelNotFoundException $e) => response()->json([
            'message' => 'Registro não encontrado.',
        ], Response::HTTP_NOT_FOUND));

        // API → 401 JSON (o front intercepta e manda pro login).
        // Web/Inertia → redireciona pro login (sessão expirada / não autenticado).
        $exceptions->renderable(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Não autenticado.',
                ], Response::HTTP_UNAUTHORIZED);
            }

            return redirect()->guest(route('login'));
        });

        $exceptions->renderable(fn (AccessDeniedHttpException $e) => response()->json([
            'message' => 'Acesso negado.',
        ], Response::HTTP_FORBIDDEN));

        $exceptions->renderable(fn (ValidationException $e) => response()->json([
            'message' => 'Os dados informados são inválidos.',
            'errors'  => $e->errors(),
        ], Response::HTTP_UNPROCESSABLE_ENTITY));
    }
}

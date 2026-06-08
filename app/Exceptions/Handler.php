<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Configuration\Exceptions;
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

        $exceptions->renderable(fn (ModelNotFoundException $e) => response()->json([
            'message' => 'Registro não encontrado.',
        ], Response::HTTP_NOT_FOUND));

        $exceptions->renderable(fn (AuthenticationException $e) => response()->json([
            'message' => 'Não autenticado.',
        ], Response::HTTP_UNAUTHORIZED));

        $exceptions->renderable(fn (AccessDeniedHttpException $e) => response()->json([
            'message' => 'Acesso negado.',
        ], Response::HTTP_FORBIDDEN));

        $exceptions->renderable(fn (ValidationException $e) => response()->json([
            'message' => 'Os dados informados são inválidos.',
            'errors'  => $e->errors(),
        ], Response::HTTP_UNPROCESSABLE_ENTITY));
    }
}

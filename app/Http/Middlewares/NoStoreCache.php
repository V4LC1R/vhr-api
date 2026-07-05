<?php

namespace App\Http\Middlewares;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Impede o browser de servir páginas autenticadas a partir do cache/bfcache.
 *
 * Sem isso, depois do logout o botão "voltar" reexibe o dashboard direto do
 * cache do navegador — sem bater no servidor e, portanto, sem passar pelo
 * middleware `auth`. Com `no-store` o voltar/reload sempre refaz a requisição
 * e cai na regra de redirecionamento pro login.
 */
class NoStoreCache
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set(
            'Cache-Control',
            'no-store, no-cache, must-revalidate, max-age=0'
        );
        $response->headers->set('Pragma', 'no-cache');

        return $response;
    }
}

<?php

namespace App\Http\Middlewares;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        // UserCompany ativo (com person/company já carregados pelo SetActiveCompany).
        // null = nenhuma empresa selecionada → front renderiza a tela de seleção.
        $current = app()->bound('currentCompany') ? app('currentCompany') : null;

        return [
            ...parent::share($request),
            'auth' => [
                // identidade de login
                'user' => $user ? [
                    'id'    => $user->id,
                    'email' => $user->email,
                ] : null,

                // contexto da empresa ativa — só aqui vivem nome, roles e permissões
                'current' => $current ? [
                    'companyId'   => $current->companyId,
                    'company'     => $current->company?->only(['id', 'name']),
                    'name'        => $current->person?->name,
                    'roles'       => $current->getRoleNames(),
                    'permissions' => $current->getAllPermissions()->pluck('name'),
                ] : null,

                // todas as empresas do user — alimenta o seletor de empresa
                'companies' => $user
                    ? $user->userCompanies()
                        ->with('company')
                        ->get()
                        ->map(fn ($uc) => [
                            'companyId' => $uc->companyId,
                            'name'      => $uc->company?->name,
                        ])
                        ->values()
                    : [],
            ],
        ];
    }
}

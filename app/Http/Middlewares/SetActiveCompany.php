<?php

namespace App\Http\Middlewares;

use Closure;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;
use Modules\Core\Models\UserCompany;

class SetActiveCompany
{
    public function handle(Request $request, Closure $next): Response
    {
        $companyId = session('companyId');


        if (!$companyId || !$request->user()) {
            return $next($request);
        }

        $userId = $request->user()->id;

        $userCompanyId = Cache::remember(
            "user.company.id:{$userId}:{$companyId}",
            now()->addMinutes(30),
            fn () => UserCompany::query()
                ->where('userId', $userId)
                ->where('companyId', $companyId)
                ->value('id')
                ?: throw new ModelNotFoundException()
        );

        $userCompany = UserCompany::with('company', 'person')->findOrFail($userCompanyId);

        if (!$userCompany) {
            abort(403, 'Empresa ativa inválida.');
        }

        app()->instance(
            'currentCompany',
            $userCompany
        );

        app(\Spatie\Permission\PermissionRegistrar::class)
            ->setPermissionsTeamId($companyId);

        return $next($request);
    }
}

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

        $userCompany = Cache::remember(
            "user.company:{$userId}:{$companyId}",
            now()->addMinutes(30),
            fn () => UserCompany::query()
                ->with('company', 'person')
                ->where('userId', $userId)
                ->where('companyId', $companyId)
                ->first()
                ?: throw new ModelNotFoundException()
        );

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

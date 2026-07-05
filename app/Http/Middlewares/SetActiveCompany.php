<?php

namespace App\Http\Middlewares;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;
use Modules\Core\Models\Company;
use Modules\Core\Models\Person;
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

        $data = Cache::remember(
            "user.company:{$userId}:{$companyId}",
            now()->addMinutes(30),
            fn () => UserCompany::query()
                ->with('company', 'person')
                ->where('userId', $userId)
                ->where('companyId', $companyId)
                ->firstOrFail()
                ->toArray()
        );

        $userCompany = (new UserCompany())->newFromBuilder(
            collect($data)->except(['company', 'person'])->toArray()
        );

        if (!empty($data['company'])) {
            $userCompany->setRelation('company', (new Company())->newFromBuilder($data['company']));
        }

        if (!empty($data['person'])) {
            $userCompany->setRelation('person', (new Person())->newFromBuilder($data['person']));
        }

        app()->instance('currentCompany', $userCompany);

        app(\Spatie\Permission\PermissionRegistrar::class)
            ->setPermissionsTeamId($companyId);

        return $next($request);
    }
}

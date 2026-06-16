<?php

namespace Modules\Core\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Core\Models\Company;
use Modules\Core\Models\User;

class CompanyPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $auth): bool
    {
        return $auth->can('core.companies.view');
    }

    public function view(User $auth, Company $company): bool
    {
        return $auth->can('core.companies.view');
    }

    public function create(User $auth): bool
    {
        return $auth->can('core.companies.create');
    }

    public function update(User $auth, Company $company): bool
    {
        return $auth->can('core.companies.update');
    }

    public function delete(User $auth, Company $company): bool
    {
        return $auth->can('core.companies.delete');
    }
}

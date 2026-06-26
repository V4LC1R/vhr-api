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
        if (!currentCompany()) {
            return false;
        }

        return currentCompany()?->can(
            'core.companies.view'
        );
    }

    public function view(
        User $auth,
        Company $company
    ): bool {
        if (!currentCompany()) {
            return false;
        }

        return currentCompany()?->can(
            'core.companies.view'
        );
    }

    public function create(User $auth): bool
    {
        if (!currentCompany()) {
            return false;
        }

        return currentCompany()?->hasRole(
            'owner'
        );
    }

    public function update(
        User $auth,
        Company $company
    ): bool {
        if (!currentCompany()) {
            return false;
        }

        return currentCompany()?->hasRole(
            'owner'
        );
    }

    public function delete(
        User $auth,
        Company $company
    ): bool {
        if (!currentCompany()) {
            return false;
        }

        return currentCompany()?->hasRole(
            'owner'
        );
    }
}

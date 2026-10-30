<?php

namespace Modules\Job\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Core\Models\User;
use Modules\Job\Models\Employment;

class EmploymentPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $auth): bool
    {
        if (!currentCompany()) {
            return false;
        }

        return currentCompany()?->can('job.employment.view');
    }

    public function view(User $auth, Employment $employment): bool
    {
        if (!currentCompany()) {
            return false;
        }

        return currentCompany()?->can('job.employment.view');
    }

    public function create(User $auth): bool
    {
        if (!currentCompany()) {
            return false;
        }

        return currentCompany()?->can('job.employment.create');
    }

    public function update(User $auth, Employment $employment): bool
    {
        return $this->canManageEmployment(
            $auth,
            $employment
        );
    }

    public function left(User $auth, Employment $employment): bool
    {
        return $this->canManageEmployment(
            $auth,
            $employment
        );
    }

    private function canManageEmployment(
        User $auth,
        Employment $employment
    ): bool {
        if (!currentCompany()) {
            return false;
        }

        if (currentCompany()->companyId != $employment->companyId) {
            return false;
        }

        if (
            ! currentCompany()?->hasRole('owner')
            && ! currentCompany()?->hasRole('humanResource')
        ) {
            return false;
        }

        return true;
    }
}

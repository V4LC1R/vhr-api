<?php

namespace Modules\Job\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Core\Models\User;
use Modules\Job\Models\Employee;

class EmployeePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $auth): bool
    {
        if (!currentCompany()) {
            return false;
        }

        return currentCompany()?->can('job.employees.view');
    }

    public function view(User $auth, Employee $employee): bool
    {
        if (!currentCompany()) {
            return false;
        }

        return currentCompany()?->can('job.employees.view');
    }

    public function create(User $auth): bool
    {
        if (!currentCompany()) {
            return false;
        }

        return currentCompany()?->can('job.employees.create');
    }

    public function update(User $auth, Employee $employee): bool
    {
        return $this->canManageEmployee(
            $auth,
            $employee
        );
    }

    public function delete(User $auth, Employee $employee): bool
    {
        return $this->canManageEmployee(
            $auth,
            $employee
        );
    }

    public function dismiss(User $auth, Employee $employee): bool
    {
        return $this->canManageEmployee(
            $auth,
            $employee
        );
    }

    private function canManageEmployee(
        User $auth,
        Employee $employee
    ): bool {
        if (!currentCompany()) {
            return false;
        }

        if (
            ! currentCompany()?->hasRole('owner')
            && ! currentCompany()?->hasRole('humanResource')
        ) {
            return false;
        }

        if (
            currentCompany()?->hasRole('humanResource')
            && currentCompany()?->personId === $employee->personId
        ) {
            return false;
        }

        return true;
    }
}

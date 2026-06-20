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
        return $auth->can('job.employees.view');
    }

    public function view(User $auth, Employee $employee): bool
    {
        return $auth->can('job.employees.view');
    }

    public function create(User $auth): bool
    {
        return $auth->can('job.employees.create');
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
        if (
            ! $auth->hasRole('owner')
            && ! $auth->hasRole('humanResource')
        ) {
            return false;
        }

        if (
            $auth->hasRole('humanResource')
            && $auth->personId === $employee->personId
        ) {
            return false;
        }

        return true;
    }
}

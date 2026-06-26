<?php

namespace Modules\Job\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Core\Models\User;
use Modules\Job\Models\Workload;

class WorkloadPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $auth): bool
    {
        if (! currentCompany()) {
            return false;
        }

        return currentCompany()?->can('job.workloads.view');
    }

    public function view(User $auth, Workload $workload): bool
    {
        if (! currentCompany()) {
            return false;
        }

        return currentCompany()?->can('job.workloads.view');
    }

    public function create(User $auth): bool
    {
        if (! currentCompany()) {
            return false;
        }

        return currentCompany()?->can('job.workloads.create');
    }

    public function update(User $auth, Workload $workload): bool
    {
        return $this->canManageWorkload($workload);
    }

    public function delete(User $auth, Workload $workload): bool
    {
        return $this->canManageWorkload($workload);
    }

    private function canManageWorkload(Workload $workload): bool
    {
        if (! currentCompany()) {
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

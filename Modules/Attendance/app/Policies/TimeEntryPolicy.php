<?php

namespace Modules\Attendance\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Attendance\Models\TimeEntry;
use Modules\Core\Models\User;

class TimeEntryPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $auth): bool
    {
        if (! currentCompany()) {
            return false;
        }

        return currentCompany()?->can('attendance.timeEntries.view');
    }

    public function view(User $auth, TimeEntry $timeEntry): bool
    {
        if (! currentCompany()) {
            return false;
        }

        return currentCompany()?->can('attendance.timeEntries.view');
    }

    public function create(User $auth): bool
    {
        if (! currentCompany()) {
            return false;
        }

        return currentCompany()?->can('attendance.timeEntries.create');
    }

    public function update(User $auth, TimeEntry $timeEntry): bool
    {
        return $this->canManage();
    }

    public function delete(User $auth, TimeEntry $timeEntry): bool
    {
        return $this->canManage();
    }

    private function canManage(): bool
    {
        if (! currentCompany()) {
            return false;
        }

        return currentCompany()?->hasRole('owner')
            || currentCompany()?->hasRole('humanResource');
    }
}

<?php

namespace Modules\Core\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Core\Models\User;

class UserPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $auth): bool
    {
        if (!currentCompany()) {
            return false;
        }

        return currentCompany()?->can('core.users.view');
    }

    public function view(User $auth, User $user): bool
    {
        if (!currentCompany()) {
            return false;
        }

        return currentCompany()?->can('core.users.view');
    }

    public function create(User $auth): bool
    {
        if (!currentCompany()) {
            return false;
        }

        return currentCompany()?->can('core.users.create');
    }

    public function update(User $auth, User $user): bool
    {
        if (!currentCompany()) {
            return false;
        }

        return currentCompany()?->can('core.users.update');
    }

    public function delete(User $auth, User $user): bool
    {
        if (!currentCompany()) {
            return false;
        }

        return currentCompany()?->can('core.users.delete');
    }
}

<?php

namespace Modules\Core\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Core\Models\Person;
use Modules\Core\Models\User;

class PersonPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('core.persons.view');
    }

    public function view(User $user, Person $person): bool
    {
        return $user->can('core.persons.view');
    }

    public function create(User $user): bool
    {
        return $user->can('core.persons.create');
    }

    public function update(User $user, Person $person): bool
    {
        return $user->can('core.persons.update');
    }

    public function delete(User $user, Person $person): bool
    {
        return $user->can('core.persons.delete');
    }
}

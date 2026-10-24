<?php

namespace Modules\Core\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Core\Models\Person;
use Modules\Core\Models\User;

class PersonPolicy
{
    use HandlesAuthorization;


    public function viewAny(
        User $user
    ): bool {
        return false;
    }


    public function view(
        User $user,
        Person $person
    ): bool {
        if (!currentCompany()) {
            return false;
        }

        return currentCompany()?->can('core.persons.view') && $person->hasCompanyLink();
    }


    public function create(
        User $user
    ): bool {
        if (!currentCompany()) {
            return false;
        }

        return currentCompany()?->can(
            'core.persons.create'
        );
    }


    public function update(
        User $user,
        Person $person
    ): bool {
        if (!currentCompany()) {
            return false;
        }

        if (
            ! currentCompany()?->can(
                'core.persons.update'
            )
        ) {
            return false;
        }

        return $person->hasCompanyLink();
    }


    public function delete(
        User $user,
        Person $person
    ): bool {
        return false;
    }
}

<?php

declare(strict_types=1);

namespace Modules\Core\Contracts;

use Modules\Core\Data\PersonData;
use Modules\Core\Models\Person;
use Modules\Core\Exceptions\PersonUpdateException;
use App\Exceptions\UniqueConstraintException;

interface PersonServiceInterface
{
     /**
     * @throws UniqueConstraintException
     */
    public function create(PersonData $data): Person;

    /**
     * @throws PersonUpdateException
     */
    public function update(Person $personId, PersonData $data): Person;
}

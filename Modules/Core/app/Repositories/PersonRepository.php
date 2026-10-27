<?php

declare(strict_types=1);

namespace Modules\Core\Repositories;

use App\Supports\Abstracts\BaseRepository;
use Modules\Core\Contracts\PersonRepositoryInterface;
use Modules\Core\Models\Person;

/**
 * @extends BaseRepository<Person>
 */
class PersonRepository extends BaseRepository implements PersonRepositoryInterface
{
    public function __construct(
        Person $model
    ) {
        parent::__construct($model);
    }

    public function findByCPF(string $cpf): ?Person
    {
        return $this->model
            ->newQuery()
            ->where('cpf', $cpf)
            ->first();
    }
}

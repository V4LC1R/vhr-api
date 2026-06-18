<?php

declare(strict_types=1);

namespace Modules\Core\Repositories;

use App\Contracts\PersonRepositoryInterface;
use App\Supports\Abstracts\BaseRepository;
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
}

<?php

declare(strict_types=1);

namespace Modules\Job\Repositories;

use App\Contracts\EmployeeRepositoryInterface;
use App\Supports\Abstracts\BaseRepository;
use Illuminate\Database\Eloquent\Model;
use Modules\Job\Models\Employee;

/**
 * @extends BaseRepository<Employee>
 */
class EmployeeRepository extends BaseRepository implements EmployeeRepositoryInterface
{
    public function __construct(
        Employee $model
    ) {
        parent::__construct($model);
    }

    public function findByPersonId(
        string $personId,
        array $relations = []
    ): ?Model {
        return $this->model
            ->newQuery()
            ->with($relations)
            ->where('personId', $personId)
            ->first();
    }
}

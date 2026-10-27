<?php

declare(strict_types=1);

namespace Modules\Job\Repositories;

use App\Supports\Abstracts\BaseRepository;
use Modules\Job\Contract\EmploymentRepositoryInterface;
use Modules\Job\Models\Employment;
use Override;

/**
 * @extends BaseRepository<Employment>
 */
class EmploymentRepository extends BaseRepository implements EmploymentRepositoryInterface
{
    public function __construct(Employment $model)
    {
        parent::__construct($model);
    }

    #[Override]
    public function findByEmployeeId(string $employeeId): ?Employment
    {
         return $this->model
            ->newQuery()
            ->where('employeeId', $employeeId)
            ->first();
    }

    public function bulkCreate(array $data)
    {
        $this->model
            ->newQuery()
            ->insert($data);
    }
}

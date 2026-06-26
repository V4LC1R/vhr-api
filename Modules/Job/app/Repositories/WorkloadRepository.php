<?php

declare(strict_types=1);

namespace Modules\Job\Repositories;

use App\Contracts\WorkloadRepositoryInterface;
use App\Supports\Abstracts\BaseRepository;
use Modules\Job\Models\Employee;
use Modules\Job\Models\Workload;

/**
 * @extends BaseRepository<Employee>
 */
class WorkloadRepository extends BaseRepository implements WorkloadRepositoryInterface
{
    public function __construct(
        Workload $model
    ) {
        parent::__construct($model);
    }
}

<?php

declare(strict_types=1);

namespace Modules\Job\Contract;

use App\Contracts\RepositoryInterface;
use Modules\Job\Models\Workload;

/**
 * @extends RepositoryInterface<Workload>
 */
interface WorkloadRepositoryInterface extends RepositoryInterface
{
}

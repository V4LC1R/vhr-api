<?php

declare(strict_types=1);

namespace Modules\Job\Contract;

use App\Contracts\RepositoryInterface;
use Modules\Job\Models\Employment;

/**
 * @extends RepositoryInterface<Employment>
 */
interface EmploymentRepositoryInterface extends RepositoryInterface
{
    public function findByCompanyAndPerson(string $companyId, string $personId): ?Employment;
}

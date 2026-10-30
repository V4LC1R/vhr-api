<?php

declare(strict_types=1);

namespace Modules\Job\Contract;

use App\Contracts\RepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Modules\Job\Models\Employee;

/**
 * @extends RepositoryInterface<Employee>
 */
interface EmployeeRepositoryInterface extends RepositoryInterface
{
    public function findByPersonId(
        string $personId,
        array $relations = []
    ): ?Model;

    public function findByIdAndCompany(
        string $id,
        string $companyId,
        array $relations = []
    ): ?Model;

    public function findByPersonIdAndCompany(
        string $personId,
        string $companyId,
        array $relations = []
    ): ?Model;
}

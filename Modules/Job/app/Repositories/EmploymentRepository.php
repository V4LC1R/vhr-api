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
    public function findByCompanyAndPerson(string $companyId, string $personId): ?Employment
    {
         return $this->model
            ->newQuery()
            ->where('companyId', $companyId)
            ->where('personId', $personId)
            ->first();
    }

    public function bulkCreate(array $data)
    {
        $this->model
            ->newQuery()
            ->insert($data);
    }
}

<?php

declare(strict_types=1);

namespace Modules\Job\Repositories;

use App\Contracts\EmploymentRepositoryInterface;
use App\Supports\Abstracts\BaseRepository;
use Illuminate\Database\Eloquent\Model;
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
    public function findByCompanyAndPerson(string $companyId, string $personId): ?Model
    {
         return $this->model
            ->newQuery()
            ->where('companyId', $companyId)
            ->where('personId', $personId)
            ->first();
    }

    #[Override]
    public function bulkCreate(array $data): void
    {
        $this->model
            ->newQuery()
            ->insert($data);
    }
}

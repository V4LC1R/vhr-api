<?php

declare(strict_types=1);

namespace Modules\Job\Repositories;

use App\Contracts\EmploymentRepositoryInterface;
use App\Supports\Abstracts\BaseRepository;
use Modules\Job\Models\Employment;

/**
 * @extends BaseRepository<Employment>
 */
class EmploymentRepository extends BaseRepository implements EmploymentRepositoryInterface
{
    public function __construct(Employment $model)
    {
        parent::__construct($model);
    }
}

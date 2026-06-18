<?php

declare(strict_types=1);

namespace Modules\Core\Repositories;

use App\Contracts\CompanyRepositoryInterface;
use App\Supports\Abstracts\BaseRepository;
use Modules\Core\Models\Company;

/**
 * @extends BaseRepository<Company>
 */
class CompanyRepository extends BaseRepository implements CompanyRepositoryInterface
{
    public function __construct(
        Company $model
    ) {
        parent::__construct($model);
    }
}

<?php

declare(strict_types=1);

namespace Modules\Core\Contracts;

use App\Contracts\RepositoryInterface;
use Modules\Core\Models\Company;

/**
 * @extends RepositoryInterface<Company>
 */

interface CompanyRepositoryInterface extends RepositoryInterface
{
}

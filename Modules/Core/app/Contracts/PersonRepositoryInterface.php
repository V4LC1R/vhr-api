<?php

declare(strict_types=1);

namespace Modules\Core\Contracts;

use App\Contracts\RepositoryInterface;
use Modules\Core\Models\Person;

/**
 * @extends RepositoryInterface<Person>
 */
interface PersonRepositoryInterface extends RepositoryInterface
{
}

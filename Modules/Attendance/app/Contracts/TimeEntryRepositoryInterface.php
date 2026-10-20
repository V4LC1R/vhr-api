<?php

declare(strict_types=1);

namespace Modules\Attendance\Contracts;

use App\Contracts\RepositoryInterface;
use Modules\Attendance\Models\TimeEntry;

/**
 * @extends RepositoryInterface<TimeEntry>
 */
interface TimeEntryRepositoryInterface extends RepositoryInterface
{
}

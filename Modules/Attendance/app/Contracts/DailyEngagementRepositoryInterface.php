<?php

declare(strict_types=1);

namespace Modules\Attendance\Contracts;

use App\Contracts\RepositoryInterface;
use Modules\Attendance\Models\DailyEngagement;

/**
 * @extends RepositoryInterface<DailyEngagement>
 */
interface DailyEngagementRepositoryInterface extends RepositoryInterface
{
}

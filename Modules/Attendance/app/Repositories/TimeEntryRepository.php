<?php

declare(strict_types=1);

namespace Modules\Attendance\Repositories;

use App\Contracts\TimeEntryRepositoryInterface;
use App\Supports\Abstracts\BaseRepository;
use Modules\Attendance\Models\TimeEntry;

/**
 * @extends BaseRepository<TimeEntry>
 */
class TimeEntryRepository extends BaseRepository implements TimeEntryRepositoryInterface
{
    public function __construct(
        TimeEntry $model
    ) {
        parent::__construct($model);
    }
}

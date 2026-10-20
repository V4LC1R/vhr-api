<?php

declare(strict_types=1);

namespace Modules\Attendance\Repositories;

use App\Supports\Abstracts\BaseRepository;
use Modules\Attendance\Contracts\DailyEngagementRepositoryInterface;
use Modules\Attendance\Models\DailyEngagement;

/**
 * @extends BaseRepository<DailyEngagement>
 */
class DailyEngagementRepository extends BaseRepository implements DailyEngagementRepositoryInterface
{
    public function __construct(
        DailyEngagement $model
    ) {
        parent::__construct($model);
    }
}

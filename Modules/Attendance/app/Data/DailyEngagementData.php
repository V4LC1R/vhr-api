<?php

declare(strict_types=1);

namespace Modules\Attendance\Data;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class DailyEngagementData extends Data
{
    public function __construct(
        public string|Optional $type,
        public string|Optional|null $note,
    ) {
    }
}

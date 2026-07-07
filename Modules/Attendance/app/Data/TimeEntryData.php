<?php

declare(strict_types=1);

namespace Modules\Attendance\Data;

use Spatie\LaravelData\Attributes\Validation\Uuid;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class TimeEntryData extends Data
{
    public function __construct(
        #[Uuid]
        public string|Optional $employeeId,
        public string|Optional $punchedAt,
        public string|Optional $type,
        public string|Optional $note,
    ) {
    }
}

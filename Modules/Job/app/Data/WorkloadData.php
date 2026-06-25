<?php

declare(strict_types=1);

namespace Modules\Job\Data;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\DateFormat;
use Spatie\LaravelData\Optional;

class WorkloadData extends Data
{
    public function __construct(
        public string|Optional $description,
        #[IntegerType]
        public int|Optional $monthly_hours,
        #[IntegerType]
        public int|Optional $weekly_hours,
        #[DateFormat('H:i:s')]
        public string|Optional $entry_time,
        #[DateFormat('H:i:s')]
        public string|Optional $left_time,
        #[DateFormat('H:i:s')]
        public string|Optional $interval_start_at,
        #[DateFormat('H:i:s')]
        public string|Optional $interval_end_at,
    ) {
    }
}

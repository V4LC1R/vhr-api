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
        public int|Optional $monthlyHours,
        #[IntegerType]
        public int|Optional $weeklyHours,
        #[DateFormat('H:i:s')]
        public string|Optional $entryTime,
        #[DateFormat('H:i:s')]
        public string|Optional $leftTime,
        #[DateFormat('H:i:s')]
        public string|Optional $intervalStartAt,
        #[DateFormat('H:i:s')]
        public string|Optional $intervalEndAt,
    ) {
    }
}

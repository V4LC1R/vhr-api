<?php

namespace Modules\Attendance\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Attendance\Enums\DailyEngagementStatusEnum;
use Modules\Attendance\Enums\DailyEngagementTypeEnum;
use Modules\Attendance\Models\DailyEngagement;
use Modules\Job\Models\Employee;

#[UseModel(DailyEngagement::class)]
class DailyEngagementFactory extends Factory
{
    public function definition(): array
    {
        return [
            'employeeId' => Employee::factory(),
            'companyId'  => fn (array $attrs) => Employee::query()
                ->whereKey($attrs['employeeId'])
                ->value('companyId'),
            'workloadId' => null,
            'date'       => now()->toDateString(),
            'type'       => DailyEngagementTypeEnum::WORK->value,
            'status'     => DailyEngagementStatusEnum::PENDING->value,
        ];
    }

    public function approved(): static
    {
        return $this->state(['status' => DailyEngagementStatusEnum::APPROVED->value]);
    }

    public function holiday(): static
    {
        return $this->state(['type' => DailyEngagementTypeEnum::HOLIDAY->value]);
    }
}

<?php

namespace Modules\Attendance\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Attendance\Enums\TimeEntrySourceEnum;
use Modules\Attendance\Enums\TimeEntryTypeEnum;
use Modules\Attendance\Models\DailyEngagement;
use Modules\Attendance\Models\TimeEntry;

#[UseModel(TimeEntry::class)]
class TimeEntryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'dailyEngagementId' => DailyEngagement::factory(),
            'companyId'         => fn (array $attrs) => DailyEngagement::query()
                ->whereKey($attrs['dailyEngagementId'])
                ->value('companyId'),
            'punched_at'        => now()->utc(),
            'type'              => TimeEntryTypeEnum::ENTRY->value,
            'source'            => TimeEntrySourceEnum::MANUAL->value,
        ];
    }
}

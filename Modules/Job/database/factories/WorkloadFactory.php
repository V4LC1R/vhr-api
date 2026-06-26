<?php

namespace Modules\Job\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\Company;
use Modules\Job\Models\Workload;

#[UseModel(Workload::class)]
class WorkloadFactory extends Factory
{
    public function definition(): array
    {
        return [
            'companyId' => Company::factory(),
            'description' => fake()->jobTitle(),
            'monthly_hours' => 220,
            'weekly_hours' => 44,
            'entry_time' => '08:00:00',
            'left_time' => '18:00:00',
            'interval_start_at' => '12:00:00',
            'interval_end_at' => '13:00:00',
        ];
    }
}

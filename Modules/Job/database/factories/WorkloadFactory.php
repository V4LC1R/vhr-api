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
            'monthlyHours' => 220,
            'weeklyHours' => 44,
            'entryTime' => '08:00:00',
            'leftTime' => '18:00:00',
            'intervalStartAt' => '12:00:00',
            'intervalEndAt' => '13:00:00',
        ];
    }
}

<?php

namespace Modules\Job\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Job\Models\Employee;
use Modules\Job\Models\PersonCompany;
use Modules\Job\Models\Workload;

#[UseModel(Employee::class)]
class EmployeeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'personCompanyId' => PersonCompany::factory(),
            'workloadId' => Workload::factory(),
            'registerNumber' => fake()->unique()->numberBetween(1, 999999),
            'register_at' => now(),
            'left_at' => null,
        ];
    }

    public function active(): static
    {
        return $this->state([
            'left_at' => null,
        ]);
    }

    public function dismissed(): static
    {
        return $this->state([
            'left_at' => now(),
        ]);
    }
}

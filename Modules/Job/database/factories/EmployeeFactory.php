<?php

namespace Modules\Job\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\Company;
use Modules\Core\Models\Person;
use Modules\Job\Models\Employee;
use Modules\Job\Models\Workload;

#[UseModel(Employee::class)]
class EmployeeFactory extends Factory
{
    public function definition(): array
    {
        $company = Company::factory()->create();

        return [
            'companyId' => $company->id,

            'personId' => Person::factory(),

            'workloadId' => Workload::factory([
                'companyId' => $company->id,
            ]),

            'status' => 'hired',

            'role' => 'employee',

            'registerNumber' => fake()
                ->unique()
                ->numberBetween(1, 999999),

            'register_at' => now()->utc(),

            'left_at' => null,
        ];
    }


    public function active(): static
    {
        return $this->state([
            'status' => 'hired',
            'left_at' => null,
        ]);
    }


    public function dismissed(): static
    {
        return $this->state([
            'status' => 'out',
            'left_at' => now()->utc(),
        ]);
    }
}

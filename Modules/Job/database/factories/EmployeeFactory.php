<?php

namespace Modules\Job\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\Company;
use Modules\Core\Models\Person;
use Modules\Job\Models\Employee;

#[UseModel(Employee::class)]
class EmployeeFactory extends Factory
{
    public function definition(): array
    {
        $company = Company::factory()->create();

        return [
            'companyId'      => $company->id,
            'personId'       => Person::factory(),
            'registerNumber' => fake()->unique()->numberBetween(1, 999999),
        ];
    }
}

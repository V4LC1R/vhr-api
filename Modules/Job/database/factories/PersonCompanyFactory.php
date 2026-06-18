<?php

namespace Modules\Job\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\Company;
use Modules\Core\Models\Person;
use Modules\Job\Enums\PersonCompanyStatusEnum;
use Modules\Job\Models\PersonCompany;

#[UseModel(PersonCompany::class)]
class PersonCompanyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'companyId' => Company::factory(),
            'personId' => Person::factory(),
            'status' => PersonCompanyStatusEnum::HIRED,
            'role' => PersonCompanyStatusEnum::EMPLOYEE,
        ];
    }

    public function hired(): static
    {
        return $this->state([
            'status' => PersonCompanyStatusEnum::HIRED,
        ]);
    }

    public function experience(): static
    {
        return $this->state([
            'status' => PersonCompanyStatusEnum::EXPERIENCE,
        ]);
    }

    public function out(): static
    {
        return $this->state([
            'status' => PersonCompanyStatusEnum::OUT,
        ]);
    }
}

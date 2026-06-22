<?php

namespace Modules\Core\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\UserCompany;
use Modules\Core\Models\User;
use Modules\Core\Models\Company;
use Modules\Core\Models\Person;

#[UseModel(UserCompany::class)]
class UserCompanyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'userId' => User::factory(),

            'companyId' => Company::factory(),

            'personId' => fake()->boolean(70)
                ? Person::factory()
                : null,
        ];
    }
}

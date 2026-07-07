<?php

namespace Modules\Job\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Job\Enums\EmploymentStatusEnum;
use Modules\Job\Enums\EmploymentTypeEnum;
use Modules\Job\Models\Employment;
use Modules\Job\Models\Workload;

#[UseModel(Employment::class)]
class EmploymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'kind'        => EmploymentTypeEnum::CLT->value,
            'status'      => EmploymentStatusEnum::EXPERIENCE->value,
            'workloadId'  => Workload::factory(),
            'registerAt' => now()->utc(),
            'leftAt'     => null,
        ];
    }

    public function hired(): static
    {
        return $this->state(['status' => EmploymentStatusEnum::HIRED->value]);
    }

    public function left(): static
    {
        return $this->state([
            'status'  => EmploymentStatusEnum::LEFT->value,
            'leftAt' => now()->utc(),
        ]);
    }
}

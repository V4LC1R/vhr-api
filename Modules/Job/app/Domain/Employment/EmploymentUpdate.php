<?php

namespace Modules\Job\Domain\Employment;

use Modules\Job\Contract\EmploymentRepositoryInterface;
use Modules\Job\Data\EmploymentData;
use Modules\Job\Enums\EmploymentStatusEnum;
use Modules\Job\Enums\EmploymentTypeEnum;
use Modules\Job\Models\Employee;
use Modules\Job\Models\Employment;
use Spatie\LaravelData\Optional;

class EmploymentUpdate
{
    public function __construct()
    {
    }

    public function execute(Employment $employment, EmploymentData $data): Employment
    {

        return $this->employmentRepository->update();
    }
}

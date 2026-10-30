<?php

namespace Modules\Job\UseCases\Employment;

use DB;
use Modules\Job\Contract\EmploymentRepositoryInterface;
use Modules\Job\Data\EmploymentData;
use Modules\Job\Domain\Employment\EmploymentEligibilityGuard;
use Modules\Job\Enums\EmploymentStatusEnum;
use Modules\Job\Enums\EmploymentTypeEnum;
use Modules\Job\Models\Employment;
use Spatie\LaravelData\Optional;

class EditEmploymentUseCase
{
    public function __construct(
        protected EmploymentRepositoryInterface $employmentRepository,
        protected EmploymentEligibilityGuard $employmentEligibilityGuard,
    ) {
    }

    /**
     * Summary of fill
     * @param array<EmploymentData> $data
     */
    public function execute(Employment $employment, EmploymentData $data): Employment
    {
        return DB::transaction(function () use ($data, $employment) {

            $employment->loadMissing(['employee']);

            $employee = $employment->employee;

            if ($data->kind == EmploymentTypeEnum::CLT->value) {
                $this
                    ->employmentEligibilityGuard
                    ->ensureNoActiveCltEmployment(
                        $employee->companyId,
                        $employee->personId,
                        [$employment->id]
                    );
            }

            $payload = $employment->fill($data->toArray())->toArray();

            $payload['status'] = ! ($data->isProbationary instanceof Optional) && $data->isProbationary
                ? EmploymentStatusEnum::EXPERIENCE->value
                : $data->status;

            return $this->employmentRepository->update($employment->id, $payload);
        });
    }
}

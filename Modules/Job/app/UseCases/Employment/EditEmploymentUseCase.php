<?php

namespace Modules\Job\UseCases\Employment;

use DB;
use Modules\Job\Contract\EmploymentRepositoryInterface;
use Modules\Job\Data\EmploymentData;
use Modules\Job\Domain\Employment\EmploymentEligibilityGuard;
use Modules\Job\Enums\EmploymentStatusEnum;
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


            return $this->employmentRepository->update($employment->id, [
                'workloadId'  => $data->workloadId,
                'kind'        => $kind,
                'status'      => ! ($data->isProbationary instanceof Optional) && $data->isProbationary
                    ? EmploymentStatusEnum::EXPERIENCE->value
                    : EmploymentStatusEnum::HIRED->value,
            ]);
        });
    }
}

<?php

namespace Modules\Job\UseCases\Employment;

use DB;
use Modules\Job\Data\EmploymentData;
use App\Contracts\EmploymentRepositoryInterface;
use Modules\Job\Domain\Employment\EmploymentEligibilityGuard;
use Modules\Job\Models\Employment;

class BulkEditEmploymentUseCase
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
    public function execute(Employment $employment, EmploymentData $data)
    {
        DB::transaction(function () use ($data) {
             return $this->employementCreator->execute($employee, EmploymentData::from([
                'workloadId'     => $data->workloadId,
                'kind'           => $data->kind,
                'isProbationary' => $data->isProbationary,
             ]));
        });
    }
}

<?php

namespace Modules\Job\UseCases\Employment;

use DB;
use Modules\Job\Data\EmploymentData;
use App\Contracts\EmploymentRepositoryInterface;
use Modules\Job\Domain\Employment\EmploymentEligibilityGuard;

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
    public function execute(array $data)
    {
        DB::transaction(function () use ($data) {
        });
    }
}

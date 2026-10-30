<?php

namespace Modules\Job\Domain\Employment;

use Modules\Job\Contract\EmployeeRepositoryInterface;
use Modules\Job\Enums\EmploymentStatusEnum;
use Modules\Job\Enums\EmploymentTypeEnum;
use Modules\Job\Exceptions\EmployeeAlreadyCltException;

class WorkloadEligibilityGuard
{
    public function __construct(
        protected EmployeeRepositoryInterface $employeeRepository,
    ) {
    }

    public function ensureWorkloadBelongsToCompany(
        $workload,
        string $companyId,
        string $message = 'A jornada não pertence à empresa informada.'
    ): void {
        if ($workload->companyId !== $companyId) {
            throw new \RuntimeException($message);
        }
    }

    public function ensureNoActiveCltEmployment(string $companyId, string $personId, string $kind): void
    {
        if ($kind !== EmploymentTypeEnum::CLT->value) {
            return;
        }

        $hasActiveClt = $this->employeeRepository
            ->getModel()
            ->newQuery()
            ->where('companyId', $companyId)
            ->where('personId', $personId)
            ->whereHas('employments', fn ($q) => $q
                ->where('kind', EmploymentTypeEnum::CLT->value)
                ->whereIn('status', [
                    EmploymentStatusEnum::HIRED->value,
                    EmploymentStatusEnum::EXPERIENCE->value,
                ]))
            ->exists();

        if ($hasActiveClt) {
            throw new EmployeeAlreadyCltException(
                'Esta pessoa já possui vínculo CLT ativo com a empresa.'
            );
        }
    }
}

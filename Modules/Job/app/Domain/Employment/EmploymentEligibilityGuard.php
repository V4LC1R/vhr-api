<?php

namespace Modules\Job\Domain\Employment;

use Modules\Job\Contract\EmployeeRepositoryInterface;
use Modules\Job\Enums\EmploymentStatusEnum;
use Modules\Job\Enums\EmploymentTypeEnum;
use Modules\Job\Exceptions\EmployeeAlreadyCltException;
use Modules\Job\Models\Workload;

class EmploymentEligibilityGuard
{
    public function __construct(
        protected EmployeeRepositoryInterface $employeeRepository,
    ) {
    }

    public function ensureWorkloadBelongsToCompany(
        Workload $workload,
        string $companyId,
        string $message = 'A jornada não pertence à empresa informada.'
    ): void {
        if ($workload->companyId !== $companyId) {
            throw new \RuntimeException($message);
        }
    }

    public function ensureNoActiveCltEmployment(
        string $companyId,
        string $personId,
        array $except = [],
    ): void {
        $hasActiveClt = $this->employeeRepository
            ->getModel()
            ->newQuery()
            ->where('companyId', $companyId)
            ->where('personId', $personId)
            ->whereHas('employments', function ($query) use ($except) {
                $query
                    ->where('kind', EmploymentTypeEnum::CLT->value)
                    ->whereIn('status', [
                        EmploymentStatusEnum::HIRED->value,
                        EmploymentStatusEnum::EXPERIENCE->value,
                    ])
                    ->when(
                        ! empty($except),
                        fn ($query) => $query->whereNotIn('id', $except)
                    );
            })
            ->exists();

        if ($hasActiveClt) {
              throw new EmployeeAlreadyCltException(
                  'Esta pessoa já possui vínculo CLT ativo com a empresa.'
              );
        }
    }
}

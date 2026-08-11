<?php

namespace Modules\Job\Domain\Employment;

use App\Contracts\EmployeeRepositoryInterface;
use App\Contracts\WorkloadRepositoryInterface;
use App\Exceptions\UniqueConstraintException;
use Modules\Job\Enums\EmploymentStatusEnum;
use Modules\Job\Enums\EmploymentTypeEnum;

class EmploymentEligibilityGuard
{
    public function __construct(
        protected WorkloadRepositoryInterface $workloadRepository,
        protected EmployeeRepositoryInterface $employeeRepository,
    ) {
    }

    public function findWorkload(string $workloadId)
    {
        $workload = $this->workloadRepository->findById($workloadId);

        if (! $workload) {
            throw new \RuntimeException('Jornada não encontrada.');
        }

        return $workload;
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

    public function ensureNoActiveEmployment(string $companyId, string $personId, string $kind): void
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
            throw new UniqueConstraintException(
                'Esta pessoa já possui vínculo CLT ativo com a empresa.'
            );
        }
    }

    public function ensureBulkWorkloadBelongsToCompany(array $workloadsIds, string $companyId)
    {
        $count = $this->workloadRepository
            ->getModel()
            ->newQuery()
            ->whereIn('id', $workloadsIds)
            ->where('companyId', $companyId)
            ->count();

        if ($count < count($workloadsIds)) {
            throw new \RuntimeException("Jornadas informadas não pertecem à Empresa");
        }
    }

    public function ensureBulkNoActiveEmployment(array $kinds, string $personId, string $companyId)
    {

        if (!in_array(EmploymentTypeEnum::CLT->value, $kinds)) {
            return;
        }

        $this
            ->ensureNoActiveEmployment(
                $companyId,
                $personId,
                EmploymentTypeEnum::CLT->value
            );
    }
}

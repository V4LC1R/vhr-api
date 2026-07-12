<?php

namespace Modules\Job\Services;

use DB;
use App\Contracts\WorkloadRepositoryInterface;
use App\Exceptions\DomainException;
use Modules\Job\Data\WorkloadData;
use Modules\Job\Enums\EmploymentStatusEnum;
use Modules\Job\Models\Workload;

class WorkloadService
{
    public function __construct(
        protected WorkloadRepositoryInterface $workloadRepository,
    ) {
    }

    public function create(WorkloadData $data)
    {
        $company = $this->resolveCompany();

        $payload = array_merge($data->toArray(), [
            'companyId' => $company->companyId,
        ]);

        return DB::transaction(function () use ($payload) {
            return $this->workloadRepository
                ->create($payload)
                ->toResource();
        });
    }

    public function update(Workload $workload, WorkloadData $data)
    {
        return DB::transaction(function () use ($workload, $data) {
            $workload->update($data->toArray());

            return $workload
                ->fresh()
                ->toResource();
        });
    }

    public function delete(Workload $workload): void
    {
        // Só vínculo ATIVO bloqueia. Vínculos encerrados não impedem: a
        // exclusão é soft delete, então o histórico segue apontando pra jornada.
        $hasActiveEmployment = $workload->employments()
            ->whereIn('status', [
                EmploymentStatusEnum::HIRED->value,
                EmploymentStatusEnum::EXPERIENCE->value,
            ])
            ->exists();

        if ($hasActiveEmployment) {
            throw new DomainException(
                'Esta jornada está vinculada a colaboradores ativos e não pode ser excluída.'
            );
        }

        DB::transaction(fn () => $workload->delete());
    }

    public function list(int $perPage = 15)
    {
        $company = $this->resolveCompany();

        return $this->workloadRepository
            ->getModel()
            ->newQuery()
            ->where('companyId', $company->companyId)
            ->latest()
            ->paginate($perPage)
            ->through(fn (Workload $workload) => $workload->toResource());
    }

    private function resolveCompany()
    {
        $company = currentCompany();

        if (! $company) {
            throw new \RuntimeException('Empresa não encontrada.');
        }

        return $company;
    }
}

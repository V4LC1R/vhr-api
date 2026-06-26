<?php

namespace Modules\Job\Services;

use DB;
use App\Contracts\WorkloadRepositoryInterface;
use Modules\Job\Data\WorkloadData;
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

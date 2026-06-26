<?php

namespace Modules\Job\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Job\Http\Requests\Workload\StoreWorkloadRequest;
use Modules\Job\Http\Requests\Workload\UpdateWorkloadRequest;
use Modules\Job\Models\Workload;
use Modules\Job\Services\WorkloadService;

class WorkloadController extends Controller
{
    public function __construct(
        protected readonly WorkloadService $service
    ) {
        $this->authorizeResource(Workload::class, 'workload');
    }

    public function index(Request $request)
    {
        $workloads = $this->service->list(
            perPage: $request->integer('per_page', 15)
        );

        return response()->json($workloads);
    }

    public function store(StoreWorkloadRequest $request)
    {
        $workload = $this->service->create($request->toDTO());

        return response()->json($workload, Response::HTTP_CREATED);
    }

    public function show(Workload $workload)
    {
        return response()->json($workload->toResource());
    }

    public function update(
        UpdateWorkloadRequest $request,
        Workload $workload
    ) {
        $workload = $this->service->update(
            $workload,
            $request->toDTO()
        );

        return response()->json($workload);
    }

    public function destroy(Workload $workload)
    {
        $this->service->delete($workload);

        return response()->noContent();
    }
}

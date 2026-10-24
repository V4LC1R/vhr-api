<?php

namespace Modules\Job\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Job\Models\Employee;
use App\Http\Controllers\Controller;
use Modules\Job\Services\EmployeeService;
use Modules\Job\Http\Requests\Employee\StoreEmployeeRequest;

class EmploymentController extends Controller
{
    public function __construct(
        protected readonly EmployeeService $service
    ) {
        $this->authorizeResource(Employee::class, 'employee');
    }


    public function index(Request $request)
    {
        $employees = $this->service->list(
            perPage: $request->integer(
                'per_page',
                15
            )
        );

        return response()->json($employees);
    }

    public function store(StoreEmployeeRequest $request)
    {
        $dto =  $request->toDTO();

        $dto->companyId = currentCompany()->companyId;

        $employee = $this->service->create($dto);

        return response()->json(
            $employee,
            Response::HTTP_CREATED
        );
    }

    public function show(Employee $employee)
    {
        $employee->load([
            'person',
            'activeEmployment.workload',
            'employments' => fn ($query) => $query->latest('registerAt')->with('workload'),
        ]);

        return response()->json(
            $employee->toResource()
        );
    }

    public function dismiss(Employee $employee)
    {
        $employee = $this->service->dismiss(
            $employee
        );

        return response()->json($employee);
    }

    public function nextRegisterNumber()
    {
        $this->authorize('create', Employee::class);

        $number = $this->service->previewNextRegisterNumber();

        return response()->json(['registerNumber' => $number]);
    }

    public function destroy(Employee $employee)
    {
        $this->service->delete($employee);

        return response()->noContent();
    }
}

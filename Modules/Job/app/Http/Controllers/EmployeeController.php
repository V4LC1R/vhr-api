<?php

namespace Modules\Job\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Modules\Job\Models\Employee;
use Modules\Job\Services\EmployeeService;
use Modules\Job\Http\Requests\StoreEmployeeRequest;
use Modules\Job\Http\Requests\UpdateEmployeeRequest;

class EmployeeController extends Controller
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
        $employee = $this->service->create(
            $request->toDTO()
        );

        return response()->json(
            $employee,
            Response::HTTP_CREATED
        );
    }

    public function show(Employee $employee)
    {
        return response()->json(
            $employee->toResource()
        );
    }

    public function update(
        UpdateEmployeeRequest $request,
        Employee $employee
    ) {
        $employee = $this->service->update(
            $employee,
            $request->toDTO()
        );

        return response()->json($employee);
    }

    public function dismiss(Employee $employee)
    {
        $employee = $this->service->dismiss(
            $employee
        );

        return response()->json($employee);
    }

    public function destroy(Employee $employee)
    {
        $this->service->delete($employee);

        return response()->noContent();
    }
}

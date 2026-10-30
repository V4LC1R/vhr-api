<?php

namespace Modules\Job\Http\Controllers;

use Illuminate\Http\Response;
use Modules\Job\Models\Employment;
use App\Http\Controllers\Controller;
use Modules\Job\UseCases\Employment\CreateEmploymentUseCase;
use Modules\Job\Http\Requests\Employment\StoreEmploymentRequest;
use Modules\Job\Http\Requests\Employment\UpdateEmploymentRequest;
use Modules\Job\UseCases\Employment\EditEmploymentUseCase;

class EmploymentController extends Controller
{
    public function __construct(
        protected readonly CreateEmploymentUseCase $createUseCase,
        protected readonly EditEmploymentUseCase $editUseCase
    ) {
        $this->authorizeResource(Employment::class, 'employment');
    }

    public function store(StoreEmploymentRequest $request)
    {
        $dto =  $request->toDTO();

        $dto->companyId = currentCompany()->companyId;

        $employee = $this->createUseCase->execute($dto);

        return response()->json(
            $employee,
            Response::HTTP_CREATED
        );
    }

    public function update(Employment $employment, UpdateEmploymentRequest $request)
    {
        $dto =  $request->toDTO();

        $dto->companyId = currentCompany()->companyId;

        $employee = $this->editUseCase->execute($employment, $dto);

        return response()->json(
            $employee,
            Response::HTTP_OK
        );
    }
}

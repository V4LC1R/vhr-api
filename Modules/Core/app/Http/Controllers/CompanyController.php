<?php

namespace Modules\Core\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Core\Models\Company;
use App\Http\Controllers\Controller;
use Modules\Core\Services\CompanyService;
use Modules\Core\Http\Requests\Companies\StoreCompanyRequest;
use Modules\Core\Http\Requests\Companies\UpdateCompanyRequest;

class CompanyController extends Controller
{
    public function __construct(
        readonly protected CompanyService $service
    ) {
        $this->authorizeResource(Company::class, 'company');
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $companies = $this->service->list(
            filters: $request->only(['name', 'email', 'cellphone']),
            perPage: $request->integer('per_page', 15),
        );

        return response()->json($companies);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCompanyRequest $request)
    {
        $data = $request->toDTO();

        $company = $this->service->create(
            $data,
            $request->user()->id,
            currentCompany()?->personId
        );

        return response()->json($company, Response::HTTP_CREATED);
    }

    /**
     * Show the specified resource.
     */
    public function show(Company $company)
    {
        return response()->json($company->toResource());
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCompanyRequest $request, Company $company)
    {
        $company = $this->service->update($company, $request->toDTO());

        return response()->json($company);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Company $company)
    {
        $this->service->delete($company);

        return response()->noContent();
    }
}

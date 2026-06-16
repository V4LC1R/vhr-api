<?php

namespace Modules\Core\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Core\Models\Person;
use App\Http\Controllers\Controller;
use Modules\Core\Services\PersonService;
use Modules\Core\Http\Requests\Persons\StorePersonRequest;
use Modules\Core\Http\Requests\Persons\UpdatePersonRequest;

class PersonController extends Controller
{
    public function __construct(
        readonly protected PersonService $service
    ) {
        $this->authorizeResource(Person::class, 'person');
    }

    public function index(Request $request)
    {
        $persons = $this->service->list(
            filters: $request->only(['name', 'email', 'cellphone']),
            perPage: $request->integer('per_page', 15),
        );

        return response()->json($persons);
    }

    public function store(StorePersonRequest $request)
    {
        $data = $request->toDTO();

        $person = $this->service->create($data);

        return response()->json($person, Response::HTTP_CREATED);
    }

    public function show(Person $person)
    {
        return response()->json($person->toResource());
    }

    public function update(UpdatePersonRequest $request, Person $person)
    {
        $person = $this->service->update($person, $request->toDTO());

        return response()->json($person);
    }

    public function destroy(Person $person)
    {
        $this->service->delete($person);

        return response()->noContent();
    }
}

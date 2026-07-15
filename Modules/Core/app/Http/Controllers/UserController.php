<?php

namespace Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Core\Http\Requests\Users\StoreUserRequest;
use Modules\Core\Http\Requests\Users\UpdateUserRequest;
use Modules\Core\Models\User;
use Modules\Core\Services\UserService;

class UserController extends Controller
{
    public function __construct(
        readonly protected UserService $service
    ) {
        $this->authorizeResource(User::class, 'user');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $users = $this->service->list(
            companyId: currentCompany()->companyId,
            filters: $request->only(['email', 'status']),
            perPage: $request->integer('per_page', 15),
        );

        return response()->json($users);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        $data = $request->toDTO();

        $personId = $request->input('personId', null);
        $role = $request->input('role');

        $user = $this->service->create(
            $data,
            currentCompany()->companyId,
            $role,
            $personId
        );

        return response()->json($user, Response::HTTP_CREATED);
    }

    /**
     * Show the specified resource.
     */
    public function show(User $user)
    {
        return response()->json($user->toResource());
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $newUser = $this->service->update(
            $user->id,
            $request->toDTO(),
            currentCompany()->companyId,
            $request->input('role')
        );

        return response()->json($newUser);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $this->service->delete($user->id);

        return response()->noContent();
    }
}

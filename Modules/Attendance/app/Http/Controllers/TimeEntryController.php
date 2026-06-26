<?php

namespace Modules\Attendance\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Attendance\Http\Requests\TimeEntry\StoreTimeEntryRequest;
use Modules\Attendance\Http\Requests\TimeEntry\UpdateTimeEntryRequest;
use Modules\Attendance\Models\TimeEntry;
use Modules\Attendance\Services\TimeEntryService;

class TimeEntryController extends Controller
{
    public function __construct(
        protected readonly TimeEntryService $service
    ) {
        $this->authorizeResource(TimeEntry::class, 'time_entry');
    }

    public function index(Request $request)
    {
        $timeEntries = $this->service->list(
            perPage: $request->integer('per_page', 15)
        );

        return response()->json($timeEntries);
    }

    public function store(StoreTimeEntryRequest $request)
    {
        $timeEntry = $this->service->create($request->toDTO());

        return response()->json($timeEntry, Response::HTTP_CREATED);
    }

    public function show(TimeEntry $timeEntry)
    {
        return response()->json($timeEntry->toResource());
    }

    public function update(UpdateTimeEntryRequest $request, TimeEntry $timeEntry)
    {
        $timeEntry = $this->service->update($timeEntry, $request->toDTO());

        return response()->json($timeEntry);
    }

    public function destroy(TimeEntry $timeEntry)
    {
        $this->service->delete($timeEntry);

        return response()->noContent();
    }
}

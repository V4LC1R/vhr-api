<?php

namespace Modules\Attendance\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Attendance\Http\Requests\DailyEngagement\RejectRequest;
use Modules\Attendance\Http\Requests\DailyEngagement\UpsertExceptionRequest;
use Modules\Attendance\Models\DailyEngagement;
use Modules\Attendance\Services\DailyEngagementService;

class DailyEngagementController extends Controller
{
    public function __construct(
        protected readonly DailyEngagementService $service
    ) {
        $this->authorizeResource(DailyEngagement::class, 'daily_engagement');
    }

    public function index(Request $request)
    {
        $days = $this->service->list(
            perPage: $request->integer('per_page', 15)
        );

        return response()->json($days);
    }

    public function show(DailyEngagement $dailyEngagement)
    {
        return response()->json(
            $this->service->show($dailyEngagement)
        );
    }

    public function exception(UpsertExceptionRequest $request, DailyEngagement $dailyEngagement)
    {
        $this->authorize('upsertException', $dailyEngagement);

        return response()->json(
            $this->service->upsertException($dailyEngagement, $request->toDTO())
        );
    }

    public function submit(DailyEngagement $dailyEngagement)
    {
        $this->authorize('submit', $dailyEngagement);

        return response()->json(
            $this->service->submit($dailyEngagement)
        );
    }

    public function approve(DailyEngagement $dailyEngagement)
    {
        $this->authorize('approve', $dailyEngagement);

        return response()->json(
            $this->service->approve($dailyEngagement)
        );
    }

    public function reject(RejectRequest $request, DailyEngagement $dailyEngagement)
    {
        $this->authorize('reject', $dailyEngagement);

        return response()->json(
            $this->service->reject($dailyEngagement, $request->input('note'))
        );
    }
}

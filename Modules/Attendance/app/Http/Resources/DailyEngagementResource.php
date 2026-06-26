<?php

namespace Modules\Attendance\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DailyEngagementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'companyId'       => $this->companyId,
            'employeeId'      => $this->employeeId,
            'workloadId'      => $this->workloadId,
            'date'            => $this->date?->toDateString(),
            'type'            => $this->type,
            'status'          => $this->status,
            'workedMinutes'   => $this->worked_minutes,
            'expectedMinutes' => $this->expected_minutes,
            'balanceMinutes'  => $this->balance_minutes,
            'diariaValue'     => $this->diaria_value,
            'note'            => $this->note,
            'draftedBy'       => $this->draftedBy,
            'approval'        => [
                'by' => $this->approvedBy,
                'at' => $this->approvedAt,
            ],
            'timeEntries'     => TimeEntryResource::collection(
                $this->whenLoaded('timeEntries')
            ),
            'employee'        => $this->whenLoaded(
                'employee',
                fn () => $this->employee->toResource()
            ),
        ];
    }
}

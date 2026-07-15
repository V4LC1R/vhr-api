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
            'workedMinutes'   => $this->workedMinutes,
            'workedHoursDecimal' => round($this->workedMinutes / 60, 2),
            'expectedMinutes' => $this->expectedMinutes,
            'balanceMinutes'  => $this->balanceMinutes,
            'diariaValue'     => $this->diariaValue,
            'note'            => $this->note,
            'draftedBy'       => $this->draftedBy,
            'approval'        => [
                'by'     => $this->approvedBy,
                'byName' => $this->whenLoaded(
                    'approvedByUserCompany',
                    fn () => $this->approvedByUserCompany?->person?->name,
                    null
                ),
                'at'     => $this->approvedAt,
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

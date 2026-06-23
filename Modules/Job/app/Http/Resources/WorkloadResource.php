<?php

namespace Modules\Job\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkloadResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'companyId'   => $this->companyId,
            'description' => $this->description,
            'monthlyHours' => $this->monthly_hours,
            'weeklyHours'  => $this->weekly_hours,
            'entryTime'   => $this->entry_time,
            'leftTime'    => $this->left_time,
            'interval'    => [
                'startAt' => $this->interval_start_at,
                'endAt'   => $this->interval_end_at,
            ],
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
        ];
    }
}

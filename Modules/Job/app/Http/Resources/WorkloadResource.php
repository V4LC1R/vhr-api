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
            'monthlyHours' => $this->monthlyHours,
            'weeklyHours'  => $this->weeklyHours,
            'entryTime'   => $this->entryTime,
            'leftTime'    => $this->leftTime,
            'interval'    => [
                'startAt' => $this->intervalStartAt,
                'endAt'   => $this->intervalEndAt,
            ],
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
        ];
    }
}

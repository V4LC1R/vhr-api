<?php

namespace Modules\Job\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmploymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'employeeId' => $this->employeeId,
            'workloadId' => $this->workloadId,
            'kind'       => $this->kind,
            'status'     => $this->status,
            'registerAt' => $this->register_at,
            'leftAt'     => $this->left_at,
            'workload'   => $this->whenLoaded(
                'workload',
                fn () => $this->workload->toResource()
            ),
        ];
    }
}

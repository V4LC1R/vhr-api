<?php

namespace Modules\Job\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    public function toArray(
        Request $request
    ): array {
        return [
            'id' => $this->id,

            'companyId' => $this->companyId,
            'personId' => $this->personId,
            'workloadId' => $this->workloadId,

            'registerNumber' => $this->registerNumber,

            'status' => $this->status,
            'role' => $this->role,

            'registerAt' => $this->register_at,
            'leftAt' => $this->left_at,

            'company' => $this->whenLoaded(
                'company',
                fn () => $this->company->toResource()
            ),

            'person' => $this->whenLoaded(
                'person',
                fn () => $this->person->toResource()
            ),

            'workload' => $this->whenLoaded(
                'workload',
                fn () => $this->workload->toResource()
            ),
        ];
    }
}

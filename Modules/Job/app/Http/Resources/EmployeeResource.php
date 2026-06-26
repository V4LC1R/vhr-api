<?php

namespace Modules\Job\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'companyId'      => $this->companyId,
            'personId'       => $this->personId,
            'registerNumber' => $this->registerNumber,
            'person'         => $this->whenLoaded(
                'person',
                fn () => $this->person->toResource()
            ),
            'activeEmployment' => $this->whenLoaded(
                'activeEmployment',
                fn () => new EmploymentResource($this->activeEmployment)
            ),
        ];
    }
}

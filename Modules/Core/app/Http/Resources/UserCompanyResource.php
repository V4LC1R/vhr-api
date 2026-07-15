<?php

namespace Modules\Core\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;

class UserCompanyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'role' => $this->getRoleNames()->first(),

            'company' => $this->whenLoaded('company', function () {
                return [
                    'id' => $this->company->id,
                    'name' => $this->company->name,
                    'cnpj' => $this->company->cnpj,
                ];
            }),

            'person' => $this->whenLoaded('person', function () {
                return [
                    'id' => $this->person->id,
                    'name' => $this->person->name,
                    'email' => $this->person->email,
                    'cellphone' => $this->person->cellphone,
                ];
            }),

            'createdAt' => $this->created_at?->toIso8601String(),
            'updatedAt' => $this->updated_at?->toIso8601String(),
        ];
    }
}

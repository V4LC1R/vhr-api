<?php

namespace Modules\Core\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;

class PersonResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'cpf'        => $this->cpf,
            'name'       => $this->name,
            'email'      => $this->email,
            'cellphone'  => preg_replace('/\D/', '', $this->cellphone),
            'pixKey'     => $this->pixKey,
            'birthdate' => $this->birthdate,
            'employments' => $this->whenLoaded(
                'employees',
                fn () => $this->employees
                    ->flatMap(fn ($employee) => $employee->employments)
                    ->map(fn ($employment) => $employment->toResource())
                    ->values()
            ),
            'createdAt' => $this->created_at?->toIso8601String(),
            'updatedAt' => $this->updated_at?->toIso8601String(),
        ];
    }
}

<?php

namespace Modules\Core\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'     => $this->id,
            'email'  => $this->email,
            'status' => $this->status,

            'companies' => $this->when(
                $this->relationLoaded('userCompanies'),
                fn () => UserCompanyResource::collection($this->userCompanies)
            ),

            'createdAt' => $this->created_at?->toIso8601String(),
            'updatedAt' => $this->updated_at?->toIso8601String(),
        ];
    }
}

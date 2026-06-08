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
            'name'       => $this->name,
            'email'      => $this->email,
            'cellphone'  => $this->cellphone, // <-- Adicionado para não sumir no retorno do JSON
            'created_at' => $this->created_at?->toIso8601String(), // Boa prática: formatar datas para API
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}

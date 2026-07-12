<?php

namespace Modules\Attendance\Http\Requests\DailyEngagement;

use Illuminate\Foundation\Http\FormRequest;

class BatchRejectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ids'   => ['required', 'array', 'min:1'],
            'ids.*' => ['uuid'],
            'note'  => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'ids.required'  => 'Informe ao menos um dia.',
            'ids.min'       => 'Informe ao menos um dia.',
            'ids.*.uuid'    => 'Dia inválido na seleção.',
            'note.required' => 'O motivo da rejeição é obrigatório.',
            'note.max'      => 'O motivo não pode ter mais de 255 caracteres.',
        ];
    }
}

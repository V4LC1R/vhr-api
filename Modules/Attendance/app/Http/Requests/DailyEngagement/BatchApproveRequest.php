<?php

namespace Modules\Attendance\Http\Requests\DailyEngagement;

use Illuminate\Foundation\Http\FormRequest;

class BatchApproveRequest extends FormRequest
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
        ];
    }

    public function messages(): array
    {
        return [
            'ids.required' => 'Informe ao menos um dia.',
            'ids.min'      => 'Informe ao menos um dia.',
            'ids.*.uuid'   => 'Dia inválido na seleção.',
        ];
    }
}

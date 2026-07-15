<?php

namespace Modules\Attendance\Http\Requests\Report;

use Illuminate\Foundation\Http\FormRequest;

class ReportFiltersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'from' => ['required', 'date'],
            'to'   => ['required', 'date', 'after_or_equal:from'],
            'name' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'from.required' => 'Informe a data inicial do período.',
            'from.date'     => 'A data inicial deve ser uma data válida.',
            'to.required'   => 'Informe a data final do período.',
            'to.date'       => 'A data final deve ser uma data válida.',
            'to.after_or_equal' => 'A data final não pode ser anterior à data inicial.',
        ];
    }

    public function filters(): array
    {
        return $this->only(['from', 'to', 'name']);
    }
}

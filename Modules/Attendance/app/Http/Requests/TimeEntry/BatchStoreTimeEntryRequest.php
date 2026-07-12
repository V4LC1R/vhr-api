<?php

namespace Modules\Attendance\Http\Requests\TimeEntry;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Attendance\Enums\TimeEntryTypeEnum;
use Modules\Job\Models\Employee;

class BatchStoreTimeEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Autoriza aqui (antes das rules) pra requisição sem permissão levar
        // 403 em vez de 422 — igual ao fluxo do store, que autoriza via
        // authorizeResource antes da validação.
        return (bool) currentCompany()?->can('attendance.timeEntries.create');
    }

    public function rules(): array
    {
        return [
            'employeeId' => [
                'required',
                'uuid',
                Rule::exists(Employee::class, 'id')
                    ->where('companyId', currentCompany()?->companyId),
            ],
            'entries'              => ['required', 'array', 'min:1', 'max:50'],
            'entries.*.punchedAt' => ['required', 'date'],
            'entries.*.type'       => ['required', Rule::in(TimeEntryTypeEnum::values())],
            'entries.*.note'       => ['nullable', 'string', 'max:255'],
            'replace'              => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'employeeId.required' => 'O funcionário é obrigatório.',
            'employeeId.uuid'     => 'O funcionário informado é inválido.',
            'employeeId.exists'   => 'O funcionário não pertence à empresa atual.',

            'entries.required' => 'Informe ao menos uma marcação.',
            'entries.min'      => 'Informe ao menos uma marcação.',
            'entries.max'      => 'Máximo de 50 marcações por lote.',

            'entries.*.punchedAt.required' => 'O horário da marcação é obrigatório.',
            'entries.*.punchedAt.date'     => 'O horário da marcação é inválido.',
            'entries.*.type.required'       => 'O tipo da marcação é obrigatório.',
            'entries.*.type.in'             => 'O tipo da marcação é inválido.',
        ];
    }
}

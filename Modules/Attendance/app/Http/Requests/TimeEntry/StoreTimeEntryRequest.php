<?php

namespace Modules\Attendance\Http\Requests\TimeEntry;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Attendance\Data\TimeEntryData;
use Modules\Attendance\Enums\TimeEntryTypeEnum;
use Modules\Job\Models\Employee;

class StoreTimeEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
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
            'punchedAt' => ['required', 'date'],
            'type'       => ['required', Rule::in(TimeEntryTypeEnum::values())],
            'note'       => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'employeeId.required' => 'O funcionário é obrigatório.',
            'employeeId.uuid'     => 'O funcionário informado é inválido.',
            'employeeId.exists'   => 'O funcionário não pertence à empresa atual.',

            'punchedAt.required' => 'O horário da marcação é obrigatório.',
            'punchedAt.date'     => 'O horário da marcação é inválido.',

            'type.required'       => 'O tipo da marcação é obrigatório.',
            'type.in'             => 'O tipo da marcação é inválido.',
        ];
    }

    public function toDTO(): TimeEntryData
    {
        return TimeEntryData::from($this->validated());
    }
}

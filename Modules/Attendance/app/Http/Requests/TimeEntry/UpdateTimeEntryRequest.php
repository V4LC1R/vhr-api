<?php

namespace Modules\Attendance\Http\Requests\TimeEntry;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Attendance\Data\TimeEntryData;
use Modules\Attendance\Enums\TimeEntryTypeEnum;

class UpdateTimeEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'punched_at' => ['sometimes', 'date'],
            'type'       => ['sometimes', Rule::in(TimeEntryTypeEnum::values())],
            'note'       => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'punched_at.date' => 'O horário da marcação é inválido.',
            'type.in'         => 'O tipo da marcação é inválido.',
        ];
    }

    public function toDTO(): TimeEntryData
    {
        return TimeEntryData::from($this->validated());
    }
}

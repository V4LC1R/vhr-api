<?php

namespace Modules\Attendance\Http\Requests\DailyEngagement;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Attendance\Data\DailyEngagementData;
use Modules\Attendance\Enums\DailyEngagementTypeEnum;
use Modules\Job\Models\Employee;

class StoreDailyEngagementRequest extends FormRequest
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
            'date' => ['required', 'date_format:Y-m-d'],
            'type' => ['required', Rule::in(DailyEngagementTypeEnum::values())],
            'note' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'employeeId.required' => 'O funcionário é obrigatório.',
            'employeeId.uuid'     => 'O funcionário informado é inválido.',
            'employeeId.exists'   => 'O funcionário não pertence à empresa atual.',

            'date.required'    => 'A data é obrigatória.',
            'date.date_format' => 'A data deve estar no formato YYYY-MM-DD.',

            'type.required' => 'O tipo do dia é obrigatório.',
            'type.in'       => 'O tipo do dia é inválido.',

            'note.max' => 'A observação não pode ter mais de 255 caracteres.',
        ];
    }

    public function toDTO(): DailyEngagementData
    {
        return DailyEngagementData::from(
            $this->safe()->only(['type', 'note'])
        );
    }
}

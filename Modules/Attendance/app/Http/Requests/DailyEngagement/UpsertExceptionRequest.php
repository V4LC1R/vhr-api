<?php

namespace Modules\Attendance\Http\Requests\DailyEngagement;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Attendance\Data\DailyEngagementData;
use Modules\Attendance\Enums\DailyEngagementTypeEnum;

class UpsertExceptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(DailyEngagementTypeEnum::values())],
            'note' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'O tipo do dia é obrigatório.',
            'type.in'       => 'O tipo do dia é inválido.',
        ];
    }

    public function toDTO(): DailyEngagementData
    {
        return DailyEngagementData::from($this->validated());
    }
}

<?php

namespace Modules\Job\Http\Requests\Workload;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Job\Data\WorkloadData;

class UpdateWorkloadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'description'       => ['sometimes', 'string', 'max:255'],
            'monthlyHours'     => ['sometimes', 'integer', 'min:1'],
            'weeklyHours'      => ['sometimes', 'integer', 'min:1', 'lte:monthlyHours'],
            'entryTime'        => ['sometimes', 'date_format:H:i:s'],
            'leftTime'         => ['sometimes', 'date_format:H:i:s', 'after:entryTime'],
            'intervalStartAt' => ['sometimes', 'date_format:H:i:s', 'after_or_equal:entryTime', 'before:leftTime'],
            'intervalEndAt'   => ['sometimes', 'date_format:H:i:s', 'after:intervalStartAt', 'before_or_equal:leftTime'],
        ];
    }

    public function messages(): array
    {
        return [
            'description.max'              => 'A descrição não pode ter mais de 255 caracteres.',

            'monthlyHours.min'            => 'As horas mensais devem ser pelo menos 1.',

            'weeklyHours.min'             => 'As horas semanais devem ser pelo menos 1.',
            'weeklyHours.lte'             => 'As horas semanais não podem exceder as horas mensais.',

            'entryTime.date_format'       => 'O horário de entrada deve estar no formato HH:MM:SS.',

            'leftTime.date_format'        => 'O horário de saída deve estar no formato HH:MM:SS.',
            'leftTime.after'              => 'O horário de saída deve ser posterior ao de entrada.',

            'intervalStartAt.date_format' => 'O início do intervalo deve estar no formato HH:MM:SS.',
            'intervalStartAt.after_or_equal' => 'O início do intervalo deve ser a partir do horário de entrada.',
            'intervalStartAt.before'     => 'O início do intervalo deve ser anterior ao horário de saída.',

            'intervalEndAt.date_format'  => 'O fim do intervalo deve estar no formato HH:MM:SS.',
            'intervalEndAt.after'        => 'O fim do intervalo deve ser posterior ao início.',
            'intervalEndAt.before_or_equal' => 'O fim do intervalo não pode ultrapassar o horário de saída.',
        ];
    }

    public function toDTO()
    {
        return  WorkloadData::from($this->validated());
    }
}

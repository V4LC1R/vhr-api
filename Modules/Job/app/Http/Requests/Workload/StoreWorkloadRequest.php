<?php

namespace Modules\Job\Http\Requests\Workload;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Job\Data\WorkloadData;

class StoreWorkloadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'description'       => ['required', 'string', 'max:255'],
            'monthlyHours'     => ['required', 'integer', 'min:1'],
            'weeklyHours'      => ['required', 'integer', 'min:1', 'lte:monthlyHours'],
            'entryTime'        => ['required', 'date_format:H:i:s'],
            'leftTime'         => ['required', 'date_format:H:i:s', 'after:entryTime'],
            'intervalStartAt' => ['required', 'date_format:H:i:s', 'after_or_equal:entryTime', 'before:leftTime'],
            'intervalEndAt'   => ['required', 'date_format:H:i:s', 'after:intervalStartAt', 'before_or_equal:leftTime'],
        ];
    }

    public function messages(): array
    {
        return [
            'description.required'         => 'A descrição é obrigatória.',
            'description.max'              => 'A descrição não pode ter mais de 255 caracteres.',

            'monthlyHours.required'       => 'As horas mensais são obrigatórias.',
            'monthlyHours.min'            => 'As horas mensais devem ser pelo menos 1.',

            'weeklyHours.required'        => 'As horas semanais são obrigatórias.',
            'weeklyHours.min'             => 'As horas semanais devem ser pelo menos 1.',
            'weeklyHours.lte'             => 'As horas semanais não podem exceder as horas mensais.',

            'entryTime.required'          => 'O horário de entrada é obrigatório.',
            'entryTime.date_format'       => 'O horário de entrada deve estar no formato HH:MM:SS.',

            'leftTime.required'           => 'O horário de saída é obrigatório.',
            'leftTime.date_format'        => 'O horário de saída deve estar no formato HH:MM:SS.',
            'leftTime.after'              => 'O horário de saída deve ser posterior ao de entrada.',

            'intervalStartAt.required'   => 'O início do intervalo é obrigatório.',
            'intervalStartAt.date_format' => 'O início do intervalo deve estar no formato HH:MM:SS.',
            'intervalStartAt.after_or_equal' => 'O início do intervalo deve ser a partir do horário de entrada.',
            'intervalStartAt.before'     => 'O início do intervalo deve ser anterior ao horário de saída.',

            'intervalEndAt.required'     => 'O fim do intervalo é obrigatório.',
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

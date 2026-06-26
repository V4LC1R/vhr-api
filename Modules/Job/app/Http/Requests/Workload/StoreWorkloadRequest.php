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
            'monthly_hours'     => ['required', 'integer', 'min:1'],
            'weekly_hours'      => ['required', 'integer', 'min:1', 'lte:monthly_hours'],
            'entry_time'        => ['required', 'date_format:H:i:s'],
            'left_time'         => ['required', 'date_format:H:i:s', 'after:entry_time'],
            'interval_start_at' => ['required', 'date_format:H:i:s', 'after_or_equal:entry_time', 'before:left_time'],
            'interval_end_at'   => ['required', 'date_format:H:i:s', 'after:interval_start_at', 'before_or_equal:left_time'],
        ];
    }

    public function messages(): array
    {
        return [
            'description.required'         => 'A descrição é obrigatória.',
            'description.max'              => 'A descrição não pode ter mais de 255 caracteres.',

            'monthly_hours.required'       => 'As horas mensais são obrigatórias.',
            'monthly_hours.min'            => 'As horas mensais devem ser pelo menos 1.',

            'weekly_hours.required'        => 'As horas semanais são obrigatórias.',
            'weekly_hours.min'             => 'As horas semanais devem ser pelo menos 1.',
            'weekly_hours.lte'             => 'As horas semanais não podem exceder as horas mensais.',

            'entry_time.required'          => 'O horário de entrada é obrigatório.',
            'entry_time.date_format'       => 'O horário de entrada deve estar no formato HH:MM:SS.',

            'left_time.required'           => 'O horário de saída é obrigatório.',
            'left_time.date_format'        => 'O horário de saída deve estar no formato HH:MM:SS.',
            'left_time.after'              => 'O horário de saída deve ser posterior ao de entrada.',

            'interval_start_at.required'   => 'O início do intervalo é obrigatório.',
            'interval_start_at.date_format' => 'O início do intervalo deve estar no formato HH:MM:SS.',
            'interval_start_at.after_or_equal' => 'O início do intervalo deve ser a partir do horário de entrada.',
            'interval_start_at.before'     => 'O início do intervalo deve ser anterior ao horário de saída.',

            'interval_end_at.required'     => 'O fim do intervalo é obrigatório.',
            'interval_end_at.date_format'  => 'O fim do intervalo deve estar no formato HH:MM:SS.',
            'interval_end_at.after'        => 'O fim do intervalo deve ser posterior ao início.',
            'interval_end_at.before_or_equal' => 'O fim do intervalo não pode ultrapassar o horário de saída.',
        ];
    }

    public function toDTO()
    {
        return  WorkloadData::from($this->validated());
    }
}

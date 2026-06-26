<?php

namespace Modules\Attendance\Http\Requests\DailyEngagement;

use Illuminate\Foundation\Http\FormRequest;

class RejectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'note' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'note.max' => 'O motivo não pode ter mais de 255 caracteres.',
        ];
    }
}

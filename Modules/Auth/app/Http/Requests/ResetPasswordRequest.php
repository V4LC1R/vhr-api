<?php

namespace Modules\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use Modules\Auth\Data\ResetPasswordData;

class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'token' => [
                'required',
                'string',
            ],
            'password' => [
                'required',
                'confirmed',
                Password::min(8),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'token.required'     => 'O token é obrigatório.',
            'password.required'  => 'A senha é obrigatória.',
            'password.confirmed' => 'A confirmação de senha não confere.',
        ];
    }

    public function toDTO(): ResetPasswordData
    {
        return ResetPasswordData::from($this->validated());
    }
}

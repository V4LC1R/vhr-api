<?php

namespace Modules\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Auth\Data\AuthUserData;

class AuthUserRequest extends FormRequest
{
    /**
     * Determina se o usuário está autorizado a fazer esta requisição.
     */
    public function authorize(): bool
    {
        return true; // Altere para auth()->check() se quiser restringir nativamente aqui
    }

    /**
     * Regras de validação para a criação de uma pessoa.
     */
    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'string',

                'email',
            ],
            'password' => [
                'required',
                'string',
            ]
        ];
    }

    /**
     * Customização das mensagens de erro (Opcional).
     */
    public function messages(): array
    {
        return [
            'password.required' => 'A senha é obrigatória.',
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'Insira um endereço de e-mail válido.',
        ];
    }

    public function toDTO(): AuthUserData
    {
        return AuthUserData::from($this->validated());
    }
}

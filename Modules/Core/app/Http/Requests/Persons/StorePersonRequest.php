<?php

namespace Modules\Core\Http\Requests\Persons;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Core\Data\PersonData;

class StorePersonRequest extends FormRequest
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
            'name' => [
                'required',
                'string',
                'max:255'
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                //Rule::unique('core.persons', 'email') // Valida o único apontando para o seu schema 'core'
            ],
            'cellphone' => [
                'required',
                'string',
                'max:20' // Espaço seguro caso usem máscaras como (XX) 9XXXX-XXXX
            ],
        ];
    }

    /**
     * Customização das mensagens de erro (Opcional).
     */
    public function messages(): array
    {
        return [
            'name.required' => 'O nome é obrigatório.',
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'Insira um endereço de e-mail válido.',
            'email.unique' => 'Este e-mail já está cadastrado.',
            'cellphone.required' => 'O celular é obrigatório.',
        ];
    }

    public function toDTO(): PersonData
    {
        return PersonData::from($this->validated());
    }
}

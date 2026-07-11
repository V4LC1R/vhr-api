<?php

namespace Modules\Core\Http\Requests\Persons;

use App\Rules\ValidCpf;
use Illuminate\Foundation\Http\FormRequest;
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
     * Normaliza o CPF removendo qualquer máscara antes da validação.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('cpf')) {
            $this->merge([
                'cpf' => preg_replace('/\D/', '', (string) $this->input('cpf')),
            ]);
        }
    }

    /**
     * Regras de validação para a criação de uma pessoa.
     */
    public function rules(): array
    {
        return [
            'cpf' => [
                'required',
                'string',
                'size:11',
                new ValidCpf(),
                // Sem Rule::unique('core.persons', 'cpf'): a unicidade é garantida
                // pela constraint do banco e tratada de forma genérica pelo
                // PersonService (QueryException -> UniqueConstraintException),
                // no mesmo padrão já usado pelo campo 'email'.
            ],
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
            'cpf.required' => 'O CPF é obrigatório.',
            'cpf.size' => 'O CPF deve conter 11 dígitos.',
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

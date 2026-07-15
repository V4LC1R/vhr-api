<?php

namespace Modules\Core\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;
use Modules\Core\Models\Person;
use Modules\Core\Models\Company;
use Modules\Core\Models\User;
use Modules\Core\Data\UserData;
use Spatie\Permission\Models\Role;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'personId' => [
                'nullable',
                'uuid',
                Rule::exists(Person::class, 'id')
            ],

            'role' => [
                'required',
                'string',
                Rule::in(Role::query()->where('guard_name', 'web')->pluck('name')),
            ],

            'password' => [
                'required',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
            ],

            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class, 'email')
            ],
        ];
    }


    public function messages(): array
    {
        return [

            'companyId.required' => 'A empresa vinculada é obrigatória.',
            'companyId.uuid'     => 'O código da empresa deve ser um UUID válido.',
            'companyId.exists'   => 'A empresa selecionada não foi encontrada.',

            'personId.uuid'     => 'O código da pessoa deve ser um UUID válido.',
            'personId.exists'   => 'A pessoa selecionada não foi encontrada no sistema.',

            'role.required' => 'O papel do usuário é obrigatório.',
            'role.in'       => 'Selecione um papel válido.',

            'email.required' => 'O e-mail é obrigatório.',
            'email.string'   => 'O e-mail deve ser um texto válido.',
            'email.email'    => 'Insira um endereço de e-mail válido.',
            'email.max'      => 'O e-mail não pode ter mais que 255 caracteres.',
            'email.unique'   => 'Este e-mail já está cadastrado.',

            'password.required' => 'A senha é obrigatória.',
            'password.min'      => 'A senha deve conter no mínimo 8 caracteres.',
            'password.letters'  => 'A senha deve conter pelo menos uma letra.',
            'password.mixed'    => 'A senha deve conter letras maiúsculas e minúsculas.',
            'password.numbers'  => 'A senha deve conter pelo menos um número.',
            'password.symbols'  => 'A senha deve conter pelo menos um caractere especial (!, @, #, $, etc.).',
        ];
    }


    public function toDTO(): UserData
    {
        return UserData::from($this->validated());
    }
}

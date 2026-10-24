<?php

namespace Modules\Core\Http\Requests\Persons;

use App\Rules\ValidCpf;
use Illuminate\Foundation\Http\FormRequest;
use Modules\Core\Data\PersonData;
use Illuminate\Validation\Rule;
use Modules\Core\Models\Person;

class UpdatePersonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
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

    public function rules(): array
    {
        return [
            'cpf'   => [
                'required',
                'string',
                'size:11',
                new ValidCpf(),
                Rule::unique(Person::class, 'cpf')
                    ->ignore($this->route('person'))
            ],
            'name'  => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(Person::class, 'email')
                    ->ignore($this->route('person'))
            ],
            'cellphone' => ['required', 'string', 'max:20'],
            'pixKey'    => ['nullable', 'string', 'max:255'],
            'birthdate' => [
                'required',
                'date'
            ],
        ];
    }

    public function toDTO(): PersonData
    {
        return PersonData::from($this->validated());
    }
}

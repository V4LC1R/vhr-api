<?php

namespace Modules\Core\Http\Requests\Persons;

use App\Rules\ValidCpf;
use Illuminate\Foundation\Http\FormRequest;
use Modules\Core\Data\PersonData;

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
            // Sem Rule::unique('core.persons', 'cpf'): mesmo padrão do campo
            // 'email' — a unicidade é tratada pelo PersonService via constraint
            // do banco (QueryException -> UniqueConstraintException).
            'cpf'       => ['required', 'string', 'size:11', new ValidCpf()],
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'string', 'email', 'max:255'],
            'cellphone' => ['required', 'string', 'max:20'],
            'pixKey'    => ['nullable', 'string', 'max:255'],
        ];
    }

    public function toDTO(): PersonData
    {
        return PersonData::from($this->validated());
    }
}

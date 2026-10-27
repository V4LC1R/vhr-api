<?php

namespace Modules\Job\Http\Requests\Employee;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Core\Models\Person;
use Modules\Job\Data\EmployeeData;
use Modules\Job\Enums\EmploymentTypeEnum;
use Modules\Job\Models\Workload;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'employment' => [
                'required',
                'array:workloadId,kind,isProbationary',
            ],

            'employment.workloadId' => [
                'required',
                'uuid',
                Rule::exists(Workload::class, 'id')
                    ->where('companyId', currentCompany()->companyId)
                    ->withoutTrashed(),
            ],

            'employment.kind' => [
                'required',
                'string',
                Rule::in(EmploymentTypeEnum::values()),
            ],

            'employment.isProbationary' => [
                'required_if:employment.kind,clt',
                'boolean',
            ],

            'person' => [
                'required',
                'array',
            ],

            'person.birthdate' => [
                'required',
                'date'
            ],

            'person.cpf' => [
                'required',
                'string',
                'size:11',
            ],
            'person.name' => [
                'required',
                'string',
                'max:255'
            ],
            'person.email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(Person::class, 'email')
                    ->ignore(Person::where('cpf', $this->input('person.cpf'))->value('id'))
            ],
            'person.cellphone' => [
                'required',
                'string',
                'max:20' // Espaço seguro caso usem máscaras como (XX) 9XXXX-XXXX
            ],
            'person.pixKey' => [
                'required',
                'string',
                'max:255',
            ]
        ];
    }

    public function messages(): array
    {
        return [

            'employment.workloadId.required' => 'A jornada é obrigatória.',
            'employment.workloadId.uuid' => 'A jornada informada é inválida.',

            'employment.kind.required' => 'O tipo de contratação é obrigatório.',
            'employment.kind.in' => 'O tipo de contratação informado é inválido.',
            'employment.isProbationary.required' => 'Informe se é um contrato de experiência.',
            'employment.isProbationary.boolean' => 'O valor de contrato de experiência é inválido.',

            'person.cpf.required' => 'O CPF é obrigatório.',
            'person.cpf.size' => 'O CPF deve conter 11 dígitos.',
            'person.name.required' => 'O nome é obrigatório.',
            'person.email.required' => 'O e-mail é obrigatório.',
            'person.email.email' => 'Insira um endereço de e-mail válido.',
            'person.email.unique' => 'Este e-mail já está cadastrado.',
            'person.cellphone.required' => 'O celular é obrigatório.',
            'person.pixKey.max' => 'A chave Pix não pode ter mais de 255 caracteres.'
        ];
    }

    public function toDTO(): EmployeeData
    {
        return EmployeeData::from(
            $this->validated()
        );
    }
}

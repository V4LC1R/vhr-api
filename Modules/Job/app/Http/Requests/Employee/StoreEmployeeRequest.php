<?php

namespace Modules\Job\Http\Requests\Employee;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
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
            'workloadId' => [
                'required',
                'uuid',
                Rule::exists(
                    Workload::class,
                    'id'
                )->where(
                    'companyId',
                    currentCompany()->companyId
                )->withoutTrashed(),
            ],

            'kind' => [
                'required',
                'string',
                Rule::in(EmploymentTypeEnum::values()),
            ],

            'isProbationary' => [
                'required_if:kind,clt',
                'boolean',
            ],

            'personId' => [
                'sometimes',
                'uuid',
                Rule::exists(
                    personRepo()->getModelClass(),
                    'id'
                )
            ],

            'person' => [
                'required_without:personId',
                'sometimes',
                'array',
            ],

            'person.birthdate' => [
                'required_without:personId',
                'sometimes',
                'date'
            ],

            'person.cpf' => [
                'required_without:personId',
                'string',
                'size:11',
            ],
            'person.name' => [
                'required_without:personId',
                'string',
                'max:255'
            ],
            'person.email' => [
                'required_without:personId',
                'string',
                'email',
                'max:255',
                //Rule::unique('core.persons', 'email') // Valida o único apontando para o seu schema 'core'
            ],
            'person.cellphone' => [
                'required_without:personId',
                'string',
                'max:20' // Espaço seguro caso usem máscaras como (XX) 9XXXX-XXXX
            ],
            'person.pixKey' => [
                'required_without:personId',
                'string',
                'max:255',
            ]
        ];
    }

    public function messages(): array
    {
        return [

            'workloadId.required' => 'A jornada é obrigatória.',
            'workloadId.uuid' => 'A jornada informada é inválida.',

            'kind.required' => 'O tipo de contratação é obrigatório.',
            'kind.in' => 'O tipo de contratação informado é inválido.',

            'isProbationary.required' => 'Informe se é um contrato de experiência.',
            'isProbationary.boolean' => 'O valor de contrato de experiência é inválido.',

            'personId.required' => 'A pessoa é obrigatória.',
            'personId.uuid' => 'A pessoa informada é inválida.',

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

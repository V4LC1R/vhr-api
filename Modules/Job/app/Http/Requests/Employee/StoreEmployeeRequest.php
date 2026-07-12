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
            'companyId' => [
                'required',
                'uuid',
                Rule::exists(
                    companyRepo()->getModelClass(),
                    'id'
                ),
            ],

            'personId' => [
                'required',
                'uuid',
                Rule::exists(
                    personRepo()->getModelClass(),
                    'id'
                )
            ],

            'workloadId' => [
                'required',
                'uuid',
                Rule::exists(
                    Workload::class,
                    'id'
                )->where(
                    'companyId',
                    $this->input('companyId')
                )->withoutTrashed(),
            ],

            'kind' => [
                'required',
                'string',
                Rule::in(EmploymentTypeEnum::values()),
            ],

            'isProbationary' => [
                'required',
                'boolean',
            ],
        ];
    }

    public function messages(): array
    {
        return [
        'companyId.required' => 'A empresa é obrigatória.',
        'companyId.uuid' => 'A empresa informada é inválida.',

        'personId.required' => 'A pessoa é obrigatória.',
        'personId.uuid' => 'A pessoa informada é inválida.',

        'workloadId.required' => 'A jornada é obrigatória.',
        'workloadId.uuid' => 'A jornada informada é inválida.',

        'kind.required' => 'O tipo de contratação é obrigatório.',
        'kind.in' => 'O tipo de contratação informado é inválido.',

        'isProbationary.required' => 'Informe se é um contrato de experiência.',
        'isProbationary.boolean' => 'O valor de contrato de experiência é inválido.',
        ];
    }

    public function toDTO(): EmployeeData
    {
        return EmployeeData::from(
            $this->validated()
        );
    }
}

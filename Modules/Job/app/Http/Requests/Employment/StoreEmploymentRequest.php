<?php

namespace Modules\Job\Http\Requests\Employment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Job\Enums\EmploymentTypeEnum;
use Modules\Job\Data\EmploymentData;
use Modules\Job\Models\Employee;
use Modules\Job\Models\Workload;

class StoreEmploymentRequest extends FormRequest
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
                Rule::exists(Workload::class, 'id')
                    ->where('companyId', currentCompany()->companyId),
            ],

            'employeeId' => [
                'required',
                'uuid',
                Rule::exists(Employee::class, 'id')
                    ->where('companyId', currentCompany()->companyId),
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

        ];
    }

    public function messages(): array
    {
        return [

            'workloadId.required' => 'A jornada é obrigatória.',
            'workloadId.uuid' => 'A jornada informada é inválida.',

            'employeeId.required' => 'O funcionario é obrigatório.',
            'employeeId.uuid' => 'O funcionario informado é inválido.',

            'kind.required' => 'O tipo de contratação é obrigatório.',
            'kind.in' => 'O tipo de contratação informado é inválido.',

            'isProbationary.required' => 'Informe se é um contrato de experiência.',
            'isProbationary.boolean' => 'O valor de contrato de experiência é inválido.',

        ];
    }

    public function toDTO(): EmploymentData
    {
        return EmploymentData::from(
            $this->validated()
        );
    }
}

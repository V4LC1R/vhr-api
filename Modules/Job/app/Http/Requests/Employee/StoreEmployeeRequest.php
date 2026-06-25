<?php

namespace Modules\Job\Http\Requests\Employee;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Job\Data\EmployeeData;
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
                ),
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
        ];
    }

    public function toDTO(): EmployeeData
    {
        return EmployeeData::from(
            $this->validated()
        );
    }
}

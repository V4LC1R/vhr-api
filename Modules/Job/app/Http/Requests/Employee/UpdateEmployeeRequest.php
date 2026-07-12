<?php

namespace Modules\Job\Http\Requests\Employee;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Job\Data\EmployeeData;
use Modules\Job\Enums\EmploymentStatusEnum;
use Modules\Job\Enums\EmploymentTypeEnum;
use Modules\Job\Models\Employee;
use Modules\Job\Models\Workload;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var Employee $employee */
        $employee = $this->route('employee');

        return [
            'status' => [
                'required',
                Rule::in(EmploymentStatusEnum::values()),
            ],
            'workloadId' => [
                'required',
                'uuid',
                Rule::exists(Workload::class, 'id')
                    ->where('companyId', $employee->companyId)
                    ->withoutTrashed(),
            ],
            'kind' => [
                'required',
                'string',
                Rule::in(EmploymentTypeEnum::values()),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' =>
                'O status é obrigatório.',
            'status.in' =>
                'O status informado é inválido.',
            'workloadId.required' =>
                'A jornada é obrigatória.',
            'workloadId.uuid' =>
                'A jornada informada é inválida.',
            'workloadId.exists' =>
                'A jornada informada não pertence à empresa do funcionário.',
            'kind.required' =>
                'O tipo de contratação é obrigatório.',
            'kind.in' =>
                'O tipo de contratação informado é inválido.',
        ];
    }

    public function toDTO(): EmployeeData
    {
        return EmployeeData::from($this->validated());
    }
}

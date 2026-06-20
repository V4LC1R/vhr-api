<?php

namespace Modules\Job\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Job\Data\EmployeeData;
use Modules\Job\Enums\EmployeeRoleEnum;
use Modules\Job\Enums\EmployeeStatusEnum;
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
                Rule::in(
                    EmployeeStatusEnum::values()
                ),
            ],

            'role' => [
                'required',
                Rule::in(
                    EmployeeRoleEnum::values()
                ),
            ],

            'workloadId' => [
                'required',
                'uuid',
                Rule::exists(
                    Workload::class,
                    'id'
                )->where(
                    'companyId',
                    $employee->companyId
                ),
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


            'role.required' =>
                'A função é obrigatória.',

            'role.in' =>
                'A função informada é inválida.',


            'workloadId.required' =>
                'A jornada é obrigatória.',

            'workloadId.uuid' =>
                'A jornada informada é inválida.',

            'workloadId.exists' =>
                'A jornada informada não pertence à empresa do funcionário.',
        ];
    }

    public function toDTO(): EmployeeData
    {
        return EmployeeData::from(
            $this->validated()
        );
    }
}

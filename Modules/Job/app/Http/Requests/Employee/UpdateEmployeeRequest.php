<?php

namespace Modules\Job\Http\Requests\Employee;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Job\Data\EmployeeData;
use Modules\Job\Data\UpdateEmployeeData;
use Modules\Job\Enums\EmploymentStatusEnum;
use Modules\Job\Enums\EmploymentTypeEnum;
use Modules\Job\Models\Employee;
use Modules\Job\Models\Employment;
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

        $workloadRule = [
            'required',
            'uuid',
            Rule::exists(Workload::class, 'id')
                ->where('companyId', $employee->companyId)
                ->withoutTrashed(),
        ];

        $kindRule = [
            'required',
            'string',
            Rule::in(EmploymentTypeEnum::values()),
        ];

        $employmentExists = fn () => Rule::exists(Employment::class, 'id')
            ->where('employeeId', $employee->id);

        return [
            'employment' => ['sometimes', 'array'],

            'employment.add' => ['sometimes', 'array'],
            'employment.add.*.workloadId' => $workloadRule,
            'employment.add.*.kind' => $kindRule,
            'employment.add.*.isProbationary' => ['sometimes', 'boolean'],

            'employment.update' => ['sometimes', 'array'],
            'employment.update.*.id' => ['required', 'uuid', $employmentExists()],
            'employment.update.*.status' => ['required', Rule::in(EmploymentStatusEnum::values())],
            'employment.update.*.workloadId' => $workloadRule,
            'employment.update.*.kind' => $kindRule,

            // demissao
            'employment.remove' => ['sometimes', 'array'],
            'employment.remove.*' => ['uuid', $employmentExists()],

            'person' => ['sometimes', 'array'],
            'person.birthdate' => ['sometimes', 'date'],
            'person.cpf' => ['string', 'size:11'],
            'person.name' => ['string', 'max:255'],
            'person.email' => ['string', 'email', 'max:255'],
            'person.cellphone' => ['string', 'max:20'],
            'person.pixKey' => ['string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        $employmentMessages = fn (string $prefix) => [
            "{$prefix}.workloadId.required" => 'A jornada é obrigatória.',
            "{$prefix}.workloadId.exists" => 'A jornada informada não pertence à empresa do funcionário.',
            "{$prefix}.kind.required" => 'O tipo de contratação é obrigatório.',
            "{$prefix}.kind.in" => 'O tipo de contratação informado é inválido.',
        ];

        return [
            ...$employmentMessages('employment.add.*'),
            ...$employmentMessages('employment.update.*'),

            'employment.update.*.id.required' => 'O vínculo a ser atualizado é obrigatório.',
            'employment.update.*.id.exists' => 'O vínculo informado não pertence a este funcionário.',
            'employment.update.*.status.required' => 'O status é obrigatório.',
            'employment.update.*.status.in' => 'O status informado é inválido.',

            'employment.remove.*.exists' => 'O vínculo informado não pertence a este funcionário.',
        ];
    }

    public function toDTO(): UpdateEmployeeData
    {
        return UpdateEmployeeData::from($this->validated());
    }
}

<?php

namespace Modules\Job\Http\Requests\Employment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Job\Enums\EmploymentTypeEnum;
use Modules\Job\Enums\EmploymentStatusEnum;
use Modules\Job\Data\EmploymentData;
use Modules\Job\Models\Workload;

class UpdateEmploymentRequest extends FormRequest
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

            'kind' => [
                'required',
                'string',
                Rule::in(EmploymentTypeEnum::values()),
            ],

            'status' => [
                'string',
                Rule::in(EmploymentStatusEnum::values()),
            ],

            'isProbationary' => [
                'required_if:employment.kind,clt',
                'boolean',
            ],

        ];
    }

    public function messages(): array
    {
        return [

            'workloadId.required' => 'A jornada é obrigatória.',
            'workloadId.uuid' => 'A jornada informada é inválida.',

            'kind.required' => 'O tipo de contratação é obrigatório.',
            'kind.in' => 'O tipo de contratação informado é inválido.',


            'status.required' => 'O status da contratação é obrigatório.',
            'status.in' => 'O status de contratação informado é inválido.',

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

<?php

namespace Modules\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Core\Models\Company;

class SelectCompanyRequest extends FormRequest
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
                Rule::exists(Company::class, 'id')
            ],
        ];
    }


    public function messages(): array
    {
        return [
            'companyId.required' => 'A empresa é obrigatória.',
            'companyId.uuid'     => 'O código da empresa deve ser um UUID válido.',
            'companyId.exists'   => 'A empresa selecionada não foi encontrada.',
        ];
    }
}

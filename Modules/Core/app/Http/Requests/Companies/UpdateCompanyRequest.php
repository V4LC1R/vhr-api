<?php

namespace Modules\Core\Http\Requests\Companies;

// Ajustado para a pasta correta de Companies

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Core\Data\CompanyData; // Assumindo que você tenha um CompanyData DTO
use Modules\Core\Models\Company;

class UpdateCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:5',
                Rule::unique(Company::class, 'name')
            ],
            'cnpj' => [
                'required',
                'string',
                'min:14',
                Rule::unique(Company::class, 'cnpj')
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome da empresa é obrigatório.',
            'name.string'   => 'O nome da empresa deve ser um texto válido.',
            'name.min'      => 'O nome da empresa deve ter no mínimo 5 caracteres.',
            'name.unique'   => 'Este nome de empresa já está cadastrado.',

            'cnpj.required' => 'O CNPJ é obrigatório.',
            'cnpj.string'   => 'O CNPJ deve ser um texto válido.',
            'cnpj.min'      => 'O CNPJ deve ter no mínimo 14 caracteres.',
            'cnpj.unique'   => 'Este CNPJ já está cadastrado.',
        ];
    }

    public function toDTO(): CompanyData
    {
        return CompanyData::from($this->validated());
    }
}

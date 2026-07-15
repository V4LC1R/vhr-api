<?php

namespace Modules\Attendance\Http\Requests\Report;

class ExportReportRequest extends ReportFiltersRequest
{
    public function rules(): array
    {
        return parent::rules() + [
            'format' => ['required', 'string', 'in:csv,xlsx,pdf'],
        ];
    }

    public function messages(): array
    {
        return parent::messages() + [
            'format.required' => 'Informe o formato de exportação.',
            'format.in'       => 'Formato inválido — use csv, xlsx ou pdf.',
        ];
    }
}

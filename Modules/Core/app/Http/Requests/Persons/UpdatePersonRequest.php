<?php

namespace Modules\Core\Http\Requests\Persons;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Core\Data\PersonData;

class UpdatePersonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'string', 'email', 'max:255'],
            'cellphone' => ['required', 'string', 'max:20'],
        ];
    }

    public function toDTO(): PersonData
    {
        return PersonData::from($this->validated());
    }
}

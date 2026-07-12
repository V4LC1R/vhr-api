<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidCpf implements ValidationRule
{
    /**
     * Valida o CPF informado (algoritmo oficial dos dígitos verificadores).
     *
     * Assume que o valor já chega normalizado (somente dígitos, 11 caracteres);
     * o tamanho/tipo é responsabilidade das regras 'string' e 'size:11' que
     * devem acompanhar esta regra no FormRequest.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $cpf = preg_replace('/\D/', '', (string) $value);

        if (strlen($cpf) !== 11 || preg_match('/^(\d)\1{10}$/', $cpf) === 1) {
            $fail('O :attribute informado não é válido.');
            return;
        }

        for ($position = 9; $position <= 10; $position++) {
            $sum = 0;

            for ($i = 0; $i < $position; $i++) {
                $sum += (int) $cpf[$i] * (($position + 1) - $i);
            }

            $checkDigit = ((10 * $sum) % 11) % 10;

            if ((int) $cpf[$position] !== $checkDigit) {
                $fail('O :attribute informado não é válido.');
                return;
            }
        }
    }
}

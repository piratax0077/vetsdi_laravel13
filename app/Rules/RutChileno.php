<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Valida el RUT chileno con su dígito verificador (módulo 11).
 */
class RutChileno implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! rut_es_valido(is_string($value) ? $value : null)) {
            $fail('Ingrese un RUT válido, con su dígito verificador.');
        }
    }
}

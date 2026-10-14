<?php

namespace App\Http\Requests;

use App\Rules\RutChileno;
use App\Services\CuentasService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validaciones del registro. El RUT y el correo se normalizan antes de validar
 * para que la unicidad no dependa de cómo los escribió la persona.
 */
class RegistroCuentaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'rut' => rut_normalizar($this->input('rut')) ?? $this->input('rut'),
            'email' => correo_normalizar($this->input('email')),
            'telefono' => telefono_normalizar($this->input('telefono')) ?? $this->input('telefono'),
            'nombres' => trim((string) $this->input('nombres')),
            'apellido_uno' => trim((string) $this->input('apellido_uno')),
            'apellido_dos' => trim((string) $this->input('apellido_dos')),
        ]);
    }

    public function rules(): array
    {
        return [
            'tipo_cuenta' => ['required', Rule::in(CuentasService::TIPOS)],
            'nombres' => ['required', 'string', 'max:100'],
            'apellido_uno' => ['required', 'string', 'max:100'],
            'apellido_dos' => ['nullable', 'string', 'max:100'],
            'rut' => ['required', 'string', new RutChileno],
            'email' => ['required', 'string', 'email:filter', 'max:255'],
            'telefono' => ['required', 'string', 'regex:/^\+56 [29] \d{4} \d{4}$/'],
            // Una sola expresión para que el mensaje de la política salga una vez.
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*\p{Ll})(?=.*\p{Lu})(?=.*\d).+$/u',
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'tipo_cuenta' => 'tipo de cuenta',
            'nombres' => 'nombres',
            'apellido_uno' => 'apellido paterno',
            'apellido_dos' => 'apellido materno',
            'rut' => 'RUT',
            'email' => 'correo electrónico',
            'telefono' => 'teléfono',
            'password' => 'contraseña',
        ];
    }

    public function messages(): array
    {
        return [
            'tipo_cuenta.required' => 'Elige el tipo de cuenta que quieres crear.',
            'tipo_cuenta.in' => 'Elige el tipo de cuenta que quieres crear.',
            'nombres.required' => 'Ingresa tus nombres.',
            'apellido_uno.required' => 'Ingresa tu apellido paterno.',
            'rut.required' => 'Ingresa tu RUT.',
            'email.required' => 'Ingresa tu correo electrónico.',
            'email.email' => 'Ingresa un correo electrónico válido.',
            'telefono.required' => 'Ingresa tu teléfono.',
            'telefono.regex' => 'Ingresa un teléfono chileno con el formato +56 9 1234 5678.',
            'nombres.string' => 'Los nombres no son válidos.',
            'nombres.max' => 'Los nombres no pueden superar los 100 caracteres.',
            'apellido_uno.string' => 'El apellido paterno no es válido.',
            'apellido_uno.max' => 'El apellido paterno no puede superar los 100 caracteres.',
            'apellido_dos.string' => 'El apellido materno no es válido.',
            'apellido_dos.max' => 'El apellido materno no puede superar los 100 caracteres.',
            'rut.string' => 'Ingresa un RUT válido.',
            'email.string' => 'Ingresa un correo electrónico válido.',
            'email.max' => 'El correo electrónico no puede superar los 255 caracteres.',
            'telefono.string' => 'Ingresa un teléfono válido.',
            'password.string' => 'Ingresa una contraseña válida.',
            'password.required' => 'Ingresa una contraseña.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'password.regex' => 'La contraseña debe incluir una mayúscula, una minúscula y un número.',
        ];
    }
}

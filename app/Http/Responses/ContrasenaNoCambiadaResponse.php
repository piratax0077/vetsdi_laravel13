<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\FailedPasswordResetResponse;

/**
 * El enlace para cambiar la contraseña venció o ya se usó: se vuelve a la
 * tarjeta de recuperación para pedir uno nuevo.
 */
class ContrasenaNoCambiadaResponse implements FailedPasswordResetResponse
{
    public function toResponse($request)
    {
        return redirect()
            ->route('home.ingreso')
            ->with('recuperar', 'correo')
            ->with('mensaje_error', 'El enlace venció o ya fue usado. Pide uno nuevo.');
    }
}

<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\PasswordResetResponse;

/** Con la contraseña nueva guardada se vuelve al ingreso de Veterchile. */
class ContrasenaCambiadaResponse implements PasswordResetResponse
{
    public function toResponse($request)
    {
        return redirect()
            ->route('home.ingreso')
            ->with('mensaje', 'Tu contraseña fue cambiada. Ya puedes ingresar con ella.');
    }
}

<?php

namespace App\Exceptions;

use Illuminate\Http\Request;
use RuntimeException;

// Se lanza al intentar reservar una hora para un tutor que el profesional bloqueó
class TutorBloqueadoException extends RuntimeException
{
    public function __construct(string $message = 'Este tutor está bloqueado y no puede tomar horas con este profesional.')
    {
        parent::__construct($message);
    }

    public function render(Request $request)
    {
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'estado' => 0,
                'msj' => $this->getMessage(),
                'mensaje' => $this->getMessage(),
                'message' => $this->getMessage(),
            ], 422);
        }

        return back()->withInput()->with('error', $this->getMessage());
    }
}

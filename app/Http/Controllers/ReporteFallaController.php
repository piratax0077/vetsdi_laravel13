<?php

namespace App\Http\Controllers;

use App\Models\SolicitudSoporte;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReporteFallaController extends Controller
{
    public function guardar(Request $request)
    {
        abort_unless(Auth::check(), 401);
        $datos = $request->validate([
            'asunto_falla' => 'required|string|max:255',
            'descripcion_falla' => 'required|string|max:10000',
            'prioridad_falla' => 'nullable|in:baja,media,alta,urgente',
        ]);
        $reporte = SolicitudSoporte::create([
            'usuario_id' => Auth::id(), 'tipo_solicitud' => 'reporte_error',
            'asunto' => $datos['asunto_falla'], 'descripcion' => $datos['descripcion_falla'],
            'prioridad' => $datos['prioridad_falla'] ?? 'media', 'estado' => 'abierta',
        ]);
        return response()->json(['estado' => 1, 'msj' => 'Reporte registrado.', 'ticket' => $reporte->numero_ticket]);
    }
}

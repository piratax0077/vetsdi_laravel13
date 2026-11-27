<?php

namespace App\Http\Controllers;

use App\Services\AvisosEncabezado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Campana de avisos del encabezado: al abrirla, los avisos que tiene el usuario
 * quedan como vistos y el contador se apaga (como en Facebook).
 */
class AvisosEncabezadoController extends Controller
{
    public function marcarVistos(Request $request, AvisosEncabezado $avisos)
    {
        $avisos->marcarVistos(Auth::user(), $request->input('perfil') === 'tutor');

        return response()->json(['estado' => 1]);
    }
}

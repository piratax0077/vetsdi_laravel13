<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Televisor;
use App\Models\LlamadoPaciente;
use Carbon\Carbon;

class PantallaController extends Controller
{
    public function pantallaPorToken($token)
    {
        $televisor = Televisor::where('token', $token)->with('salaEspera')->firstOrFail();

        $logoInsi = asset('images/logo_instituciones/logo_institucion_generico.jpg');

        if ($televisor->salaEspera && $televisor->salaEspera->institucion) {
            $logoInstitucion = $televisor->salaEspera->institucion->logo;

            // Verificar que el logo no esté vacío y que el archivo exista
            if (!empty($logoInstitucion) && file_exists(public_path('images/logo_instituciones/' . $logoInstitucion))) {
                $logoInsi = asset('images/logo_instituciones/' . $logoInstitucion);
            }
        }

        $piso = $televisor->salaEspera ? $televisor->salaEspera->piso : '-';

        return view('pantalla.pantalla', [
            'televisor' => $televisor,
            'logoInsi' => $logoInsi,
            'piso' => $piso,
        ]);
    }

    public function pantalla2PorToken($token)
    {
        $televisor = Televisor::where('token', $token)->with('salaEspera')->firstOrFail();

        $logoInsi = asset('images/logo_instituciones/logo_institucion_generico.jpg');

        if ($televisor->salaEspera && $televisor->salaEspera->institucion) {
            $logoInstitucion = $televisor->salaEspera->institucion->logo;

            // Verificar que el logo no esté vacío y que el archivo exista
            if (!empty($logoInstitucion) && file_exists(public_path('images/logo_instituciones/' . $logoInstitucion))) {
                $logoInsi = asset('images/logo_instituciones/' . $logoInstitucion);
            }
        }

        $piso = $televisor->salaEspera ? $televisor->salaEspera->piso : '-';

        return view('pantalla.pantalla2', [
            'televisor' => $televisor,
            'logoInsi' => $logoInsi,
            'piso' => $piso,
        ]);
    }

    public function pantallaPorAlias($alias_sala, $alias_televisor)
    {
        $televisor = Televisor::where('alias', $alias_televisor)
            ->whereHas('salaEspera', function($q) use ($alias_sala) {
                $q->where('alias', $alias_sala);
            })
            ->with('salaEspera')
            ->firstOrFail();
        return view('pantalla.pantalla', [
            'televisor' => $televisor,
        ]);
    }

    // API para recargar datos sin refrescar la página
    public function apiPantallaPorToken($token)
    {
        $televisor = Televisor::where('token', $token)->firstOrFail();
        $salaEspera = $televisor->salaEspera;
        $piso = $salaEspera ? $salaEspera->piso : '';
        $cantidad = $televisor->cantidad;

        $hoy = Carbon::now()->format('Y-m-d');

        $llamados = LlamadoPaciente::with(['paciente', 'box'])
            ->where('id_televisor', $televisor->id)
            ->where('estado', 1)
            ->where('fecha_llamado', $hoy)
            ->orderBy('updated_at', 'desc')
            ->limit($cantidad)
            ->get()
            ->map(function($item) {
                $minutos = Carbon::parse($item->hora_llamado)->diffInMinutes(now());
                return [
                    'minutos' => $minutos,
                    'paciente' => $item->paciente ? (mb_strtoupper($item->paciente->nombres . ' ' . $item->paciente->apellido_uno)) : '',
                    'box' => $item->box ? $item->box->numero_box : '',
                    'cantidad_llamados' => $item->cantidad_llamados,
                ];
            });

        return response()->json([
            'hora' => now()->format('H:i'),
            'piso' => $piso,
            'llamados' => $llamados,
        ]);
    }
}

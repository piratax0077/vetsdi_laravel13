<?php

namespace App\Http\Controllers;

use App\Models\Recomendacion;
use App\Models\RecomendacionDetalle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EnfermeriaTratamientoController extends Controller
{
    public function administrar(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id_tratamiento' => ['required', 'integer'],
            'ficha_atencion_id' => ['nullable', 'integer'],
        ]);

        $detalle = DB::transaction(function () use ($validated) {
            $detalle = RecomendacionDetalle::query()
                ->lockForUpdate()
                ->findOrFail($validated['id_tratamiento']);

            $detalle->fecha_administrado = now()->toDateString();
            $detalle->hora_administrado = now()->format('H:i:s');
            $detalle->estado = 1;
            $detalle->contador_dosis = ((int) $detalle->contador_dosis) + 1;
            $detalle->save();

            return $detalle;
        });

        return response()->json([
            'mensaje' => 'OK',
            'tipo' => 'normal',
            'receta' => [[
                'id' => $detalle->id,
                'id_detalle' => $detalle->id,
                'estado' => $detalle->estado,
                'contador_dosis' => $detalle->contador_dosis,
                'fecha_administrado' => $detalle->fecha_administrado,
                'hora_administrado' => $detalle->hora_administrado,
            ]],
            'tiempo_transcurrido' => 0,
        ]);
    }

    public function eliminar(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id_detalle' => ['required', 'integer'],
        ]);

        $remaining = DB::transaction(function () use ($validated) {
            $detalle = RecomendacionDetalle::query()
                ->lockForUpdate()
                ->findOrFail($validated['id_detalle']);
            $recommendationId = $detalle->id_recomendacion;
            $detalle->delete();

            $remaining = RecomendacionDetalle::where('id_recomendacion', $recommendationId)->count();
            if ($remaining === 0) {
                Recomendacion::whereKey($recommendationId)->delete();
            }

            return $remaining;
        });

        return response()->json([
            'estado' => 1,
            'mensaje' => 'OK',
            'detalles_restantes' => $remaining,
        ]);
    }

    public function actualizarObservacion(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id_detalle' => ['required', 'integer'],
            'observacion' => ['nullable', 'string', 'max:5000'],
        ]);

        $detalle = RecomendacionDetalle::findOrFail($validated['id_detalle']);
        $detalle->observaciones = encrypt($validated['observacion'] ?? '');
        $detalle->save();

        return response()->json([
            'estado' => 1,
            'mensaje' => 'OK',
            'observacion' => $validated['observacion'] ?? '',
        ]);
    }
}

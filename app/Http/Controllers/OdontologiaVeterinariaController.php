<?php

namespace App\Http\Controllers;

use App\Models\Mascota;
use App\Models\PagosPresupuestoDental;
use App\Models\PresupuestosDental;
use App\Models\Profesional;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OdontologiaVeterinariaController extends Controller
{
    private function profesional(): Profesional
    {
        return Profesional::where('id_usuario', Auth::id())->firstOrFail();
    }

    private function catalogoEfectivo(int $profesionalId, ?int $lugarId)
    {
        $base = DB::table('tarifas_uco_odontologia_veterinaria')
            ->whereNull('id_profesional')->whereNull('id_lugar_atencion')->where('activo', 1)->get()->keyBy('codigo');
        $propias = DB::table('tarifas_uco_odontologia_veterinaria')
            ->where('id_profesional', $profesionalId)
            ->where(function ($q) use ($lugarId) {
                $q->whereNull('id_lugar_atencion');
                if ($lugarId) $q->orWhere('id_lugar_atencion', $lugarId);
            })->where('activo', 1)->orderByRaw('id_lugar_atencion is null desc')->get();

        foreach ($propias as $fila) $base->put($fila->codigo, $fila);
        return $base->values()->sortBy(fn ($fila) => $fila->categoria.'|'.$fila->nombre)->values()
            ->map(function ($fila) {
                $fila->valor_total = round((float) $fila->uco * (int) $fila->valor_uco);
                return $fila;
            });
    }

    public function catalogo(Request $request)
    {
        $data = $request->validate(['id_lugar_atencion' => 'nullable|integer']);
        $profesional = $this->profesional();
        return response()->json([
            'estado' => 1,
            'prestaciones' => $this->catalogoEfectivo($profesional->id, $data['id_lugar_atencion'] ?? null),
        ]);
    }

    public function guardarCatalogo(Request $request)
    {
        $data = $request->validate([
            'id_lugar_atencion' => 'nullable|integer',
            'valor_uco' => 'required|integer|min:1|max:10000000',
            'prestaciones' => 'required|array|min:1',
            'prestaciones.*.codigo' => 'nullable|string|max:40',
            'prestaciones.*.categoria' => 'required|string|max:100',
            'prestaciones.*.nombre' => 'required|string|max:190',
            'prestaciones.*.uco' => 'required|numeric|min:0.01|max:9999',
        ]);
        $profesional = $this->profesional();
        $lugar = $data['id_lugar_atencion'] ?? null;

        DB::transaction(function () use ($data, $profesional, $lugar) {
            DB::table('tarifas_uco_odontologia_veterinaria')
                ->where('id_profesional', $profesional->id)
                ->where(function ($q) use ($lugar) {
                    $lugar ? $q->where('id_lugar_atencion', $lugar) : $q->whereNull('id_lugar_atencion');
                })->update(['activo' => false, 'updated_at' => now()]);
            foreach ($data['prestaciones'] as $indice => $prestacion) {
                $codigo = trim((string) ($prestacion['codigo'] ?? ''));
                if ($codigo === '') $codigo = 'ODO-PER-'.strtoupper(substr(sha1($profesional->id.'|'.$prestacion['nombre'].'|'.$indice), 0, 8));
                $base = DB::table('tarifas_uco_odontologia_veterinaria')->whereNull('id_profesional')->where('codigo', $codigo)->exists();
                DB::table('tarifas_uco_odontologia_veterinaria')->updateOrInsert(
                    ['id_profesional' => $profesional->id, 'id_lugar_atencion' => $lugar, 'codigo' => $codigo],
                    ['categoria' => $prestacion['categoria'], 'nombre' => $prestacion['nombre'],
                        'uco' => $prestacion['uco'], 'valor_uco' => $data['valor_uco'],
                        'es_base' => $base, 'activo' => true, 'updated_at' => now(), 'created_at' => now()]
                );
            }
        });

        return response()->json(['estado' => 1, 'msj' => 'Tarifario odontologico UCOV guardado.',
            'prestaciones' => $this->catalogoEfectivo($profesional->id, $lugar)]);
    }

    public function eliminarTarifa(Request $request, int $tarifa)
    {
        $profesional = $this->profesional();
        $afectadas = DB::table('tarifas_uco_odontologia_veterinaria')->where('id', $tarifa)
            ->where('id_profesional', $profesional->id)->update(['activo' => false, 'updated_at' => now()]);
        abort_if(!$afectadas, 404, 'La prestacion personalizada no existe.');
        return response()->json(['estado' => 1, 'msj' => 'Prestacion retirada del tarifario.']);
    }

    private function datosPieza(Request $request): array
    {
        return $request->validate([
            'mascota_id' => 'required|integer|exists:mascotas,id', 'ficha_atencion_id' => 'required|integer',
            'lugar_atencion_id' => 'nullable|integer', 'pieza' => 'required|integer',
            'hallazgo' => 'required|string|max:100', 'tratamiento' => 'nullable|string|max:190',
            'caras' => 'nullable|array', 'caras.*' => 'string|in:vestibular,lingual,palatina,mesial,distal,oclusal,incisal,Vestibular,Palatina/Lingual,Mesial,Distal,Oclusal',
            'observaciones' => 'nullable|string|max:3000',
        ]);
    }

    public function guardarPieza(Request $request, \App\Services\OdontologiaVeterinaria $servicio)
    {
        $pieza = $servicio->guardarPieza($this->datosPieza($request), $this->profesional()->id);
        return response()->json(['estado' => 1, 'msj' => 'Pieza dental guardada.', 'pieza' => $pieza]);
    }

    public function guardarPeriodonto(Request $request, \App\Services\OdontologiaVeterinaria $servicio)
    {
        $data = $request->validate([
            'mascota_id' => 'required|integer|exists:mascotas,id', 'ficha_atencion_id' => 'required|integer',
            'pieza' => 'required|integer', 'profundidad' => 'required|integer|min:0|max:15',
            'recesion' => 'required|integer|min:0|max:15', 'sangrado' => 'nullable|boolean', 'placa' => 'nullable|boolean',
            'movilidad' => 'nullable|integer|min:0|max:3', 'furcacion' => 'nullable|integer|min:0|max:3', 'observaciones' => 'nullable|string|max:3000',
        ]);
        [$ficha, $mascota, $especie] = $servicio->contexto($data, $this->profesional()->id);
        $servicio->piezaValida($especie, (int) $data['pieza']);
        DB::transaction(function () use ($data, $ficha, $mascota, $especie) {
            Mascota::whereKey($mascota->id)->lockForUpdate()->firstOrFail();
            $clave = ['mascota_id' => $mascota->id, 'ficha_atencion_id' => $ficha->id, 'pieza' => (string) $data['pieza']];
            $valores = ['profesional_id' => $ficha->id_profesional, 'especie' => $especie,
                'profundidad' => $data['profundidad'], 'recesion' => $data['recesion'],
                'sangrado' => $data['sangrado'] ?? false, 'placa' => $data['placa'] ?? false,
                'movilidad' => $data['movilidad'] ?? 0, 'furcacion' => $data['furcacion'] ?? 0,
                'observaciones' => $data['observaciones'] ?? null, 'updated_at' => now()];
            if (!DB::table('periodontogramas_mascotas')->where($clave)->exists()) $valores['created_at'] = now();
            DB::table('periodontogramas_mascotas')->updateOrInsert($clave, $valores);
        });
        return response()->json(['estado' => 1, 'msj' => 'Periodontograma actualizado.']);
    }

    public function crearPresupuesto(Request $request, \App\Services\OdontologiaVeterinaria $servicio)
    {
        $data = $request->validate([
            'mascota_id' => 'required|integer|exists:mascotas,id', 'paciente_id' => 'nullable|integer',
            'ficha_atencion_id' => 'required|integer', 'lugar_atencion_id' => 'nullable|integer',
            'pieza' => 'required|integer', 'tarifa_uco_id' => 'nullable|integer',
            'tratamiento' => 'required_without:tarifa_uco_id|nullable|string|max:190',
            'valor' => 'required_without:tarifa_uco_id|nullable|integer|min:0', 'observaciones' => 'nullable|string|max:1000',
            'hallazgo' => 'nullable|string|max:100', 'caras' => 'nullable|array',
            'caras.*' => 'string|in:vestibular,lingual,palatina,mesial,distal,oclusal,incisal,Vestibular,Palatina/Lingual,Mesial,Distal,Oclusal',
            'referencia_operacion' => 'nullable|uuid',
        ]);
        $profesional = $this->profesional();
        [$ficha] = $servicio->contexto($data, $profesional->id);
        if (!empty($data['tarifa_uco_id'])) {
            $tarifa = $this->catalogoEfectivo($profesional->id, $ficha->id_lugar_atencion)->firstWhere('id', (int) $data['tarifa_uco_id']);
            abort_unless($tarifa, 422, 'La prestación UCOV no está disponible.');
            $data = array_replace($data, ['tratamiento' => $tarifa->nombre, 'valor' => (int) $tarifa->valor_total,
                'codigo_uco' => $tarifa->codigo, 'uco' => $tarifa->uco, 'valor_uco' => $tarifa->valor_uco]);
        }
        $presupuesto = $servicio->agregarTratamiento($data, $profesional->id);
        return response()->json(['estado' => 1, 'msj' => 'Tratamiento agregado al presupuesto.', 'presupuesto' => $servicio->resumen($presupuesto)]);
    }

    public function abonar(Request $request, PresupuestosDental $presupuesto, \App\Services\OdontologiaVeterinaria $servicio)
    {
        $data = $request->validate(['monto' => 'required|integer|min:1', 'metodo_pago' => 'required|string|max:80',
            'observaciones' => 'nullable|string|max:500', 'referencia_operacion' => 'required|uuid']);
        $presupuesto = $servicio->abonar($presupuesto, $data, $this->profesional()->id);
        return response()->json(['estado' => 1, 'msj' => 'Pago registrado.', 'presupuesto' => $servicio->resumen($presupuesto)]);
    }

    public function estadoTrabajo(Request $request, PresupuestosDental $presupuesto, \App\Services\OdontologiaVeterinaria $servicio)
    {
        $data = $request->validate(['item' => 'required|string|max:100', 'estado' => 'required|in:pendiente,en_proceso,terminado,cancelado']);
        $p = $servicio->actualizarTrabajo($presupuesto, $data['item'], $data['estado'], $this->profesional()->id);
        return response()->json(['estado' => 1, 'msj' => 'Trabajo actualizado.', 'presupuesto' => $servicio->resumen($p)]);
    }

    public function aprobar(Request $request, PresupuestosDental $presupuesto, \App\Services\OdontologiaVeterinaria $servicio)
    {
        $datos = $request->validate(['aprobado' => 'required|boolean']);
        $servicio->autorizarPresupuesto($presupuesto, $this->profesional()->id);
        $presupuesto->aprobado = $datos['aprobado'];
        $presupuesto->saveOrFail();
        return response()->json(['estado' => 1, 'msj' => 'Aprobación actualizada.', 'presupuesto' => $servicio->resumen($presupuesto)]);
    }

    public function estado(Request $request, \App\Services\OdontologiaVeterinaria $servicio)
    {
        $datos = $request->validate(['mascota_id' => 'required|integer', 'ficha_atencion_id' => 'required|integer']);
        $profesional = $this->profesional();
        [$ficha, $mascota, $especie] = $servicio->contexto($datos, $profesional->id);
        $presupuestos = PresupuestosDental::where('id_mascota', $mascota->id)->where('id_profesional', $profesional->id)
            ->where('id_paciente', $ficha->id_paciente)
            ->where('id_lugar_atencion', $ficha->id_lugar_atencion)->where('estado', 1)->orderByDesc('id')->get();
        return response()->json(['estado' => 1, 'especie' => $especie, 'presupuestos' => $presupuestos->map(fn ($p) => $servicio->resumen($p))]);
    }

    public function pdf(PresupuestosDental $presupuesto, \App\Services\OdontologiaVeterinaria $servicio)
    {
        $ficha = $servicio->autorizarPresupuesto($presupuesto, $this->profesional()->id);
        return \Barryvdh\DomPDF\Facade\Pdf::loadView('atencion_odontologica.veterinaria.presupuesto_pdf', [
            'presupuesto' => $servicio->resumen($presupuesto), 'mascota' => Mascota::findOrFail($ficha->id_mascota),
            'profesional' => $this->profesional(),
        ])->download('presupuesto-dental-veterinario-'.$presupuesto->id.'.pdf');
    }
}

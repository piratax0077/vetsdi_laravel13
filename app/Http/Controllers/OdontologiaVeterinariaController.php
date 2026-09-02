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

    private function especie(Mascota $mascota): string
    {
        $mascota->loadMissing('especieMascota');
        $nombre = mb_strtolower((string) optional($mascota->especieMascota)->nombre);
        return str_contains($nombre, 'felin') || (int) $mascota->especie_id === 2 ? 'felino' : 'canino';
    }

    private function piezas(string $especie): array
    {
        if ($especie === 'felino') {
            return [101,102,103,104,106,107,108,109,201,202,203,204,206,207,208,209,
                301,302,303,304,307,308,309,401,402,403,404,407,408,409];
        }
        return array_merge(range(101,111), range(201,211), range(301,311), range(401,411));
    }

    private function catalogoEfectivo(int $profesionalId, ?int $lugarId)
    {
        $base = DB::table('tarifas_uco_odontologia_veterinaria')
            ->whereNull('id_profesional')->where('activo', 1)->get()->keyBy('codigo');
        $propias = DB::table('tarifas_uco_odontologia_veterinaria')
            ->where('id_profesional', $profesionalId)
            ->where(function ($q) use ($lugarId) {
                $q->whereNull('id_lugar_atencion');
                if ($lugarId) $q->orWhere('id_lugar_atencion', $lugarId);
            })->where('activo', 1)->orderByRaw('id_lugar_atencion is null')->get();

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

    public function guardarPieza(Request $request)
    {
        $data = $request->validate([
            'mascota_id' => 'required|integer|exists:mascotas,id',
            'ficha_atencion_id' => 'nullable|integer',
            'lugar_atencion_id' => 'nullable|integer',
            'pieza' => 'required|integer',
            'hallazgo' => 'required|string|max:100',
            'tratamiento' => 'nullable|string|max:150',
            'caras' => 'nullable|array',
            'observaciones' => 'nullable|string|max:3000',
        ]);
        $mascota = Mascota::findOrFail($data['mascota_id']);
        $especie = $this->especie($mascota);
        abort_unless(in_array((int) $data['pieza'], $this->piezas($especie), true), 422, 'La pieza no corresponde a la especie de la mascota.');
        $profesional = $this->profesional();

        return DB::transaction(function () use ($data, $especie, $profesional) {
            DB::table('odontogramas_mascotas')->where('mascota_id', $data['mascota_id'])
                ->where('pieza', $data['pieza'])->where('estado', 1)->update(['estado' => 0, 'updated_at' => now()]);
            $id = DB::table('odontogramas_mascotas')->insertGetId([
                'mascota_id' => $data['mascota_id'], 'ficha_atencion_id' => $data['ficha_atencion_id'] ?? null,
                'profesional_id' => $profesional->id, 'lugar_atencion_id' => $data['lugar_atencion_id'] ?? null,
                'especie' => $especie, 'pieza' => $data['pieza'], 'hallazgo' => $data['hallazgo'],
                'tratamiento' => $data['tratamiento'] ?? null, 'caras' => json_encode($data['caras'] ?? []),
                'observaciones' => $data['observaciones'] ?? null, 'estado' => 1,
                'created_at' => now(), 'updated_at' => now(),
            ]);
            return response()->json(['estado' => 1, 'id' => $id, 'msj' => 'Pieza dental guardada.']);
        });
    }

    public function guardarPeriodonto(Request $request)
    {
        $data = $request->validate([
            'mascota_id' => 'required|integer|exists:mascotas,id', 'ficha_atencion_id' => 'nullable|integer',
            'pieza' => 'required|integer', 'profundidad' => 'required|integer|min:0|max:15',
            'recesion' => 'required|integer|min:0|max:15', 'sangrado' => 'nullable|boolean',
            'placa' => 'nullable|boolean', 'movilidad' => 'nullable|integer|min:0|max:3',
            'furcacion' => 'nullable|integer|min:0|max:3', 'observaciones' => 'nullable|string|max:3000',
        ]);
        $mascota = Mascota::findOrFail($data['mascota_id']);
        $especie = $this->especie($mascota);
        abort_unless(in_array((int) $data['pieza'], $this->piezas($especie), true), 422, 'Pieza inválida para la especie.');
        DB::table('periodontogramas_mascotas')->updateOrInsert(
            ['mascota_id' => $mascota->id, 'ficha_atencion_id' => $data['ficha_atencion_id'] ?? null, 'pieza' => $data['pieza']],
            ['profesional_id' => $this->profesional()->id, 'especie' => $especie,
                'profundidad' => $data['profundidad'], 'recesion' => $data['recesion'],
                'sangrado' => (bool) ($data['sangrado'] ?? false), 'placa' => (bool) ($data['placa'] ?? false),
                'movilidad' => $data['movilidad'] ?? 0, 'furcacion' => $data['furcacion'] ?? 0,
                'observaciones' => $data['observaciones'] ?? null, 'updated_at' => now(), 'created_at' => now()]
        );
        return response()->json(['estado' => 1, 'msj' => 'Periodontograma actualizado.']);
    }

    public function crearPresupuesto(Request $request)
    {
        $data = $request->validate([
            'mascota_id' => 'required|integer|exists:mascotas,id', 'paciente_id' => 'required|integer',
            'ficha_atencion_id' => 'required|integer', 'lugar_atencion_id' => 'required|integer',
            'pieza' => 'required|integer', 'tarifa_uco_id' => 'nullable|integer',
            'tratamiento' => 'required_without:tarifa_uco_id|nullable|string|max:190',
            'valor' => 'required_without:tarifa_uco_id|nullable|numeric|min:0', 'observaciones' => 'nullable|string|max:1000',
        ]);
        $profesional = $this->profesional();
        $tarifa = null;
        if (!empty($data['tarifa_uco_id'])) {
            $tarifa = $this->catalogoEfectivo($profesional->id, $data['lugar_atencion_id'])
                ->firstWhere('id', (int) $data['tarifa_uco_id']);
            abort_unless($tarifa, 422, 'La prestacion UCOV no esta disponible para este profesional.');
            $data['tratamiento'] = $tarifa->nombre;
            $data['valor'] = $tarifa->valor_total;
        }
        $presupuesto = PresupuestosDental::firstOrNew([
            'id_paciente' => $data['paciente_id'], 'id_profesional' => $profesional->id,
            'id_ficha_atencion' => $data['ficha_atencion_id'], 'estado' => 1,
        ]);
        $items = collect(json_decode($presupuesto->datos_piezas_dentales ?: '[]', true));
        $items->push(['mascota_id' => $data['mascota_id'], 'pieza' => (string) $data['pieza'],
            'tarifa_uco_id' => $tarifa->id ?? null, 'codigo_uco' => $tarifa->codigo ?? null,
            'uco' => isset($tarifa) ? (float) $tarifa->uco : null, 'valor_uco' => $tarifa->valor_uco ?? null,
            'tratamiento' => $data['tratamiento'], 'valor' => (float) $data['valor']]);
        $presupuesto->id_lugar_atencion = $data['lugar_atencion_id'];
        $presupuesto->datos_piezas_dentales = $items->values()->toJson();
        $presupuesto->aprobado = $presupuesto->aprobado ?? 0;
        $presupuesto->fecha = $presupuesto->fecha ?? now()->toDateString();
        $presupuesto->fecha_control = $presupuesto->fecha_control ?? now()->toDateString();
        $presupuesto->boca = $presupuesto->boca ?? 0;
        $presupuesto->observaciones = $data['observaciones'] ?? $presupuesto->observaciones;
        $presupuesto->valor_total = $items->sum('valor');
        $presupuesto->valor_abonado = $presupuesto->valor_abonado ?? 0;
        $presupuesto->save();
        return response()->json(['estado' => 1, 'msj' => 'Tratamiento agregado al presupuesto.',
            'presupuesto' => $this->resumen($presupuesto)]);
    }

    public function abonar(Request $request, PresupuestosDental $presupuesto)
    {
        $data = $request->validate(['monto' => 'required|numeric|min:1', 'metodo_pago' => 'required|string|max:80', 'observaciones' => 'nullable|string|max:500']);
        abort_unless((int) $presupuesto->id_profesional === (int) $this->profesional()->id, 403);
        $saldo = max(0, (float) $presupuesto->valor_total - (float) $presupuesto->valor_abonado);
        abort_if((float) $data['monto'] > $saldo, 422, 'El abono no puede superar el saldo.');
        DB::transaction(function () use ($data, $presupuesto) {
            $pago = new PagosPresupuestoDental();
            $pago->id_paciente = $presupuesto->id_paciente; $pago->id_profesional = $presupuesto->id_profesional;
            $pago->id_lugar_atencion = $presupuesto->id_lugar_atencion; $pago->id_ficha_atencion = $presupuesto->id_ficha_atencion;
            $pago->id_presupuesto = $presupuesto->id; $pago->id_metodo_pago = 0; $pago->metodo_pago = $data['metodo_pago'];
            $pago->fecha_pago = now()->toDateString(); $pago->total = $data['monto']; $pago->estado = 1;
            $pago->observaciones = $data['observaciones'] ?? null; $pago->save();
            $presupuesto->valor_abonado = (float) $presupuesto->valor_abonado + (float) $data['monto'];
            $presupuesto->aprobado = $presupuesto->valor_abonado >= $presupuesto->valor_total ? 1 : $presupuesto->aprobado;
            $presupuesto->save();
        });
        return response()->json(['estado' => 1, 'msj' => 'Abono registrado.', 'presupuesto' => $this->resumen($presupuesto->fresh())]);
    }

    private function resumen(PresupuestosDental $p): array
    {
        return ['id' => $p->id, 'total' => (float) $p->valor_total, 'abonado' => (float) $p->valor_abonado,
            'saldo' => max(0, (float) $p->valor_total - (float) $p->valor_abonado),
            'estado_pago' => $p->valor_abonado >= $p->valor_total ? 'Pagado' : ($p->valor_abonado > 0 ? 'Abonado' : 'Pendiente')];
    }
}

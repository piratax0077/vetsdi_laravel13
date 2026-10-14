<?php

namespace App\Services;

use App\Models\{FichaAtencion, Mascota, PagosPresupuestoDental, PresupuestosDental};
use App\Support\DenticionVeterinaria;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OdontologiaVeterinaria
{
    public function contexto(array $datos, int $profesional): array
    {
        $ficha = FichaAtencion::findOrFail($datos['ficha_atencion_id']);
        abort_unless((int) $ficha->id_profesional === $profesional && (int) $ficha->id_mascota === (int) $datos['mascota_id'], 403, 'La mascota no pertenece a esta ficha profesional.');
        foreach (['paciente_id' => 'id_paciente', 'lugar_atencion_id' => 'id_lugar_atencion'] as $entrada => $campo) {
            abort_if(isset($datos[$entrada]) && (int) $datos[$entrada] !== (int) $ficha->{$campo}, 403);
        }
        $mascota = Mascota::findOrFail($ficha->id_mascota);
        abort_unless((int) $mascota->id_responsable === (int) $ficha->id_paciente, 403, 'La ficha no corresponde al tutor actual de la mascota.');
        return [$ficha, $mascota, DenticionVeterinaria::especie($mascota)];
    }

    public function piezaValida(string $especie, int $pieza): void
    {
        if (!in_array($pieza, DenticionVeterinaria::piezas($especie), true)) {
            throw ValidationException::withMessages(['pieza' => 'La pieza no corresponde a la dentición '.$especie.'.']);
        }
    }

    public function guardarPieza(array $datos, int $profesional): array
    {
        [$ficha, $mascota, $especie] = $this->contexto($datos, $profesional);
        $this->piezaValida($especie, (int) $datos['pieza']);
        return DB::transaction(function () use ($datos, $profesional, $ficha, $mascota, $especie) {
            Mascota::whereKey($mascota->id)->lockForUpdate()->firstOrFail();
            DB::table('odontogramas_mascotas')->where('mascota_id', $mascota->id)->where('especie', $especie)
                ->where('pieza', $datos['pieza'])->where('estado', 1)->update(['estado' => 0, 'updated_at' => now()]);
            $registro = [
                'mascota_id' => $mascota->id, 'ficha_atencion_id' => $ficha->id, 'profesional_id' => $profesional,
                'lugar_atencion_id' => $ficha->id_lugar_atencion, 'especie' => $especie, 'pieza' => (string) $datos['pieza'],
                'hallazgo' => $datos['hallazgo'], 'tratamiento' => $datos['tratamiento'] ?? null,
                'caras' => json_encode($datos['caras'] ?? [], JSON_THROW_ON_ERROR), 'observaciones' => $datos['observaciones'] ?? null,
                'estado' => 1, 'created_at' => now(), 'updated_at' => now(),
            ];
            $registro['id'] = DB::table('odontogramas_mascotas')->insertGetId($registro);
            return $registro;
        });
    }

    public function agregarTratamiento(array $datos, int $profesional): PresupuestosDental
    {
        [$ficha, $mascota, $especie] = $this->contexto($datos, $profesional);
        $this->piezaValida($especie, (int) $datos['pieza']);
        return DB::transaction(function () use ($datos, $profesional, $ficha, $mascota, $especie) {
            FichaAtencion::whereKey($ficha->id)->lockForUpdate()->firstOrFail();
            $presupuesto = PresupuestosDental::where('id_ficha_atencion', $ficha->id)->where('id_mascota', $mascota->id)->where('id_profesional', $profesional)->where('estado', 1)->lockForUpdate()->first();
            if (!$presupuesto) {
                $presupuesto = new PresupuestosDental();
                $presupuesto->id_paciente = $ficha->id_paciente;
                $presupuesto->id_mascota = $mascota->id;
                $presupuesto->id_profesional = $profesional;
                $presupuesto->id_ficha_atencion = $ficha->id;
                $presupuesto->id_lugar_atencion = $ficha->id_lugar_atencion;
                $presupuesto->estado = 1;
                $presupuesto->aprobado = 0;
                $presupuesto->valor_abonado = 0;
                $presupuesto->fecha = now()->toDateString();
                $presupuesto->fecha_control = now()->toDateString();
                $presupuesto->boca = 0;
            }
            $items = $this->items($presupuesto);
            $referencia = $datos['referencia_operacion'] ?? (string) Str::uuid();
            if (collect($items)->contains('referencia_operacion', $referencia)) return $presupuesto;
            if (!empty($datos['hallazgo'])) $this->guardarPieza($datos, $profesional);
            $items[] = [
                'id' => (string) Str::uuid(), 'referencia_operacion' => $referencia, 'mascota_id' => $mascota->id,
                'especie' => $especie, 'pieza' => (string) $datos['pieza'], 'tratamiento' => $datos['tratamiento'],
                'valor' => (int) $datos['valor'], 'tarifa_uco_id' => $datos['tarifa_uco_id'] ?? null,
                'codigo_uco' => $datos['codigo_uco'] ?? null, 'uco' => $datos['uco'] ?? null,
                'valor_uco' => $datos['valor_uco'] ?? null, 'estado_trabajo' => 'pendiente',
                'observaciones' => $datos['observaciones'] ?? null, 'created_at' => now()->toIso8601String(),
            ];
            $presupuesto->datos_piezas_dentales = json_encode($items, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
            $presupuesto->valor_total = collect($items)->where('estado_trabajo', '!=', 'cancelado')->sum('valor');
            $presupuesto->saveOrFail();
            return $presupuesto;
        });
    }

    public function autorizarPresupuesto(PresupuestosDental $presupuesto, int $profesional): FichaAtencion
    {
        [$ficha] = $this->contexto(['ficha_atencion_id' => $presupuesto->id_ficha_atencion, 'mascota_id' => $presupuesto->id_mascota], $profesional);
        abort_unless((int) $presupuesto->id_profesional === $profesional && (int) $presupuesto->id_lugar_atencion === (int) $ficha->id_lugar_atencion && (int) $presupuesto->id_paciente === (int) $ficha->id_paciente, 403);
        return $ficha;
    }

    public function abonar(PresupuestosDental $presupuesto, array $datos, int $profesional): PresupuestosDental
    {
        $this->autorizarPresupuesto($presupuesto, $profesional);
        return DB::transaction(function () use ($presupuesto, $datos) {
            $p = PresupuestosDental::whereKey($presupuesto->id)->lockForUpdate()->firstOrFail();
            $anterior = PagosPresupuestoDental::where('id_presupuesto', $p->id)->where('referencia_operacion', $datos['referencia_operacion'])->first();
            if ($anterior) {
                if ((int) $anterior->total !== (int) $datos['monto'] || $anterior->metodo_pago !== $datos['metodo_pago']) {
                    throw ValidationException::withMessages(['referencia_operacion' => 'Esta operación ya fue registrada con otros datos. Cierre el formulario y registre un nuevo pago.']);
                }
                return $p;
            }
            $saldo = max(0, (int) $p->valor_total - (int) $p->valor_abonado);
            if ($datos['monto'] > $saldo) throw ValidationException::withMessages(['monto' => 'El pago no puede superar el saldo actual.']);
            $pago = new PagosPresupuestoDental();
            foreach (['id_paciente','id_profesional','id_lugar_atencion','id_ficha_atencion'] as $campo) $pago->{$campo} = $p->{$campo};
            $pago->id_presupuesto = $p->id;
            $pago->referencia_operacion = $datos['referencia_operacion'];
            $pago->id_metodo_pago = 0;
            $pago->metodo_pago = $datos['metodo_pago'];
            $pago->fecha_pago = now()->toDateString();
            $pago->total = $datos['monto'];
            $pago->observaciones = $datos['observaciones'] ?? null;
            $pago->estado = 1;
            $pago->saveOrFail();
            $p->valor_abonado = (int) $p->valor_abonado + (int) $datos['monto'];
            $p->saveOrFail();
            return $p;
        });
    }

    public function actualizarTrabajo(PresupuestosDental $presupuesto, string $item, string $estado, int $profesional): PresupuestosDental
    {
        $this->autorizarPresupuesto($presupuesto, $profesional);
        return DB::transaction(function () use ($presupuesto, $item, $estado) {
            $p = PresupuestosDental::whereKey($presupuesto->id)->lockForUpdate()->firstOrFail();
            $items = $this->items($p);
            $encontrado = false;
            foreach ($items as &$fila) {
                if ($fila['id'] !== $item) continue;
                $fila['estado_trabajo'] = $estado;
                $fila['updated_at'] = now()->toIso8601String();
                $encontrado = true;
            }
            unset($fila);
            abort_unless($encontrado, 404, 'Trabajo no encontrado.');
            $total = collect($items)->where('estado_trabajo', '!=', 'cancelado')->sum('valor');
            if ($total < (int) $p->valor_abonado) throw ValidationException::withMessages(['estado' => 'No se puede cancelar un trabajo si el total queda por debajo de los pagos registrados.']);
            $p->valor_total = $total;
            $p->datos_piezas_dentales = json_encode($items, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
            $p->saveOrFail();
            return $p;
        });
    }

    public function items(PresupuestosDental $presupuesto): array
    {
        $items = json_decode($presupuesto->datos_piezas_dentales ?: '[]', true, 512, JSON_THROW_ON_ERROR);
        return array_values(array_map(function ($fila, $indice) use ($presupuesto) {
            $fila['id'] ??= 'historico-'.$presupuesto->id.'-'.$indice;
            $fila['estado_trabajo'] ??= !empty($fila['atendido']) ? 'terminado' : 'pendiente';
            return $fila;
        }, $items, array_keys($items)));
    }

    public function resumen(PresupuestosDental $p): array
    {
        $items = $this->items($p);
        return [
            'id' => $p->id, 'ficha_atencion_id' => $p->id_ficha_atencion, 'mascota_id' => $p->id_mascota,
            'total' => (int) $p->valor_total, 'abonado' => (int) $p->valor_abonado,
            'saldo' => max(0, (int) $p->valor_total - (int) $p->valor_abonado), 'aprobado' => (bool) $p->aprobado,
            'estado_pago' => (int) $p->valor_total === 0 ? 'Sin cargos' : ($p->valor_abonado >= $p->valor_total ? 'Pagado' : ($p->valor_abonado > 0 ? 'Abonado' : 'Pendiente')),
            'trabajos' => $items, 'pendientes' => collect($items)->whereIn('estado_trabajo', ['pendiente','en_proceso'])->count(),
            'pagos' => PagosPresupuestoDental::where('id_presupuesto', $p->id)->where('estado', 1)->orderByDesc('id')->get(['id','fecha_pago','metodo_pago','total','observaciones']),
        ];
    }

    public function guardarEnFicha(FichaAtencion $ficha): array
    {
        $mascota = Mascota::whereKey($ficha->id_mascota)->lockForUpdate()->firstOrFail();
        $especie = DenticionVeterinaria::especie($mascota);
        $piezas = DenticionVeterinaria::piezas($especie);
        $actuales = DB::table('odontogramas_mascotas')->where('mascota_id', $mascota->id)->where('especie', $especie)->where('estado', 1)->whereIn('pieza', $piezas)->orderBy('id')->get()->keyBy('pieza');
        $odontograma = collect($piezas)->map(fn ($pieza) => $actuales->get((string) $pieza) ?? ['pieza' => (string) $pieza, 'hallazgo' => 'Sin registro', 'tratamiento' => null, 'caras' => []])->values()->all();
        $periodonto = DB::table('periodontogramas_mascotas')->where('mascota_id', $mascota->id)->where('ficha_atencion_id', $ficha->id)->where('especie', $especie)->whereIn('pieza', $piezas)->get()->all();
        $existe = DB::table('odontogramas_fichas_mascotas')->where('ficha_atencion_id', $ficha->id)->exists();
        $registro = [
            'mascota_id' => $mascota->id, 'profesional_id' => $ficha->id_profesional, 'lugar_atencion_id' => $ficha->id_lugar_atencion,
            'especie' => $especie, 'odontograma' => json_encode($odontograma, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
            'periodontograma' => json_encode($periodonto, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR), 'updated_at' => now(),
        ];
        if (!$existe) $registro['created_at'] = now();
        DB::table('odontogramas_fichas_mascotas')->updateOrInsert(['ficha_atencion_id' => $ficha->id], $registro);
        return ['especie' => $especie, 'piezas' => $odontograma, 'periodontograma' => $periodonto];
    }
}

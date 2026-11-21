<?php
namespace App\Services;

use App\Models\{FichaAtencion, Profesional, User};
use App\Support\LugarAtencionInstitucionResolver;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ConsumoInventarioVeterinario
{
    public function contexto(int $profesional, int $lugar): array
    {
        $centro = LugarAtencionInstitucionResolver::resolve($lugar);
        $titular = $centro ? 'centro:'.$centro : 'profesional:'.$profesional;
        $usuario = Profesional::findOrFail($profesional)->id_usuario;
        $personas = $centro ? app(HospitalizacionAcceso::class)->candidatos($centro) : collect();
        $personas = $personas->push(User::findOrFail($usuario))->unique('id');
        return [$titular, $personas];
    }

    public static function validarCantidad(string $modalidad, $cantidad): void
    {
        $valor = (float) $cantidad;
        if (!is_finite($valor) || $valor <= 0 || ($modalidad === 'individual' && floor($valor) !== $valor)) {
            throw ValidationException::withMessages(['cantidad_stock' => 'El material de uso individual requiere unidades enteras. Los productos fraccionables se descuentan en la unidad de control del lote.']);
        }
    }

    public function registrar(FichaAtencion $ficha, array $registro): array
    {
        if (empty($registro['lote_id'])) return $registro;
        [$titular, $personas] = $this->contexto($ficha->id_profesional, $ficha->id_lugar_atencion);
        $lote = DB::table('vet_inventario_lotes')->where('titular', $titular)
            ->where('id', $registro['lote_id'])->lockForUpdate()->first();
        abort_unless($lote, 403, 'El lote no pertenece al inventario de esta consulta.');
        $administrador = $personas->firstWhere('id', (int) ($registro['administrador_id'] ?? 0));
        if (!$administrador) throw ValidationException::withMessages(['nuevas_inyecciones' => 'Selecciona quién administró el producto.']);
        $despachador = $personas->firstWhere('id', (int) ($registro['despachador_id'] ?? 0));
        if (!$despachador) throw ValidationException::withMessages(['nuevas_inyecciones' => 'Selecciona quién despachó el producto.']);
        if ($lote->vencimiento < now()->toDateString()) throw ValidationException::withMessages(['nuevas_inyecciones' => 'El lote seleccionado está vencido.']);
        self::validarCantidad($lote->modalidad_consumo ?? 'fraccionable', $registro['cantidad_stock']);
        $cantidad = (int) round((float) $registro['cantidad_stock'] * 1000);
        $saldo = (int) round((float) $lote->saldo * 1000) - $cantidad;
        if ($saldo < 0) throw ValidationException::withMessages(['nuevas_inyecciones' => 'El lote no tiene existencias suficientes.']);
        if ($lote->condicion_venta !== 'libre' && empty($registro['receta_stock'])) {
            throw ValidationException::withMessages(['nuevas_inyecciones' => 'Registra la referencia de receta o autorización.']);
        }
        $profesional = Profesional::findOrFail($ficha->id_profesional);
        $registro['medicamento'] = $lote->producto;
        $registro['lote'] = $lote->lote;
        $registro['unidad_stock'] = $lote->unidad;
        $registro['modalidad_consumo'] = $lote->modalidad_consumo ?? 'fraccionable';
        $registro['indicado_por'] = $profesional->nombreCompleto();
        $registro['administrado_por'] = $administrador->name;
        $registro['despachado_por'] = $despachador->name;
        DB::table('vet_inventario_movimientos')->insert([
            'lote_id'=>$lote->id, 'usuario_id'=>auth()->id(), 'operacion'=>(string) \Illuminate\Support\Str::uuid(),
            'tipo'=>'salida', 'cantidad'=>$cantidad / 1000, 'saldo'=>$saldo / 1000,
            'contraparte'=>'Mascota #'.$ficha->id_mascota, 'documento'=>'Ficha #'.$ficha->id,
            'receta'=>$registro['receta_stock'] ?? null, 'observaciones'=>$registro['observaciones'] ?? null,
            'ficha_id'=>$ficha->id, 'indicado_por'=>$registro['indicado_por'],
            'profesional_uso_id'=>$profesional->id, 'profesional_uso'=>$profesional->nombreCompleto(),
            'despachador_id'=>$despachador->id, 'despachado_por'=>$despachador->name,
            'uso_clinico'=>mb_substr($registro['procedimiento'] ?? 'Administración en consulta', 0, 250),
            'administrado_por'=>$registro['administrado_por'], 'administrador_id'=>$administrador->id, 'created_at'=>now(),
        ]);
        DB::table('vet_inventario_lotes')->where('id',$lote->id)->update(['saldo'=>$saldo / 1000,'updated_at'=>now()]);
        return $registro;
    }
}

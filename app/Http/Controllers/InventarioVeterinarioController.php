<?php
namespace App\Http\Controllers;

use App\Models\Profesional;
use App\Services\HospitalizacionAcceso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class InventarioVeterinarioController extends Controller
{
    private function contexto(Request $r): array
    {
        $r->validate(['id_institucion' => 'nullable|integer|min:1']);
        if ($r->filled('id_institucion')) {
            $centro = app(HospitalizacionAcceso::class)->administrados($r->user())
                ->firstWhere('id', $r->integer('id_institucion'));
            abort_unless($centro, 403);
            return ['centro:'.$centro->id, 'template.adm_cm.template', $centro->nombre];
        }
        $profesional = Profesional::where('id_usuario', $r->user()->id)->firstOrFail();
        return ['profesional:'.$profesional->id, 'template.profesional.template', 'Inventario profesional'];
    }

    public function index(Request $r)
    {
        [$titular, $template, $nombre] = $this->contexto($r);
        $lotes = DB::table('vet_inventario_lotes')->where('titular', $titular)
            ->orderBy('vencimiento')->paginate(25)->withQueryString();
        $lotesMovimiento = DB::table('vet_inventario_lotes')->where('titular', $titular)->orderBy('producto')->get();
        $movimientos = DB::table('vet_inventario_movimientos as m')
            ->join('vet_inventario_lotes as l', 'l.id', '=', 'm.lote_id')
            ->where('l.titular', $titular)->select('m.*', 'l.producto', 'l.lote', 'l.unidad')
            ->orderByDesc('m.id')->paginate(30, ['*'], 'movimientos')->withQueryString();
        return view('app.veterinaria.inventario.index', compact('template', 'nombre', 'lotes', 'lotesMovimiento', 'movimientos'));
    }

    public function lote(Request $r)
    {
        [$titular] = $this->contexto($r);
        $datos = $r->validate([
            'categoria' => 'required|in:medicamento,vacuna,alimento,insumo',
            'producto' => 'required|string|max:180',
            'registro_sag' => 'nullable|string|max:100',
            'condicion_venta' => ['required', Rule::in(['libre','receta','retenida','control_saldo','uso_veterinario'])],
            'lote' => 'required|string|max:100',
            'vencimiento' => 'required|date_format:Y-m-d',
            'unidad' => 'required|string|max:40',
            'ubicacion' => 'required|string|max:150',
            'conservacion' => 'required|string|max:250',
            'cantidad_inicial' => 'required|numeric|decimal:0,3|min:0.001|max:999999999',
            'proveedor_inicial' => 'nullable|string|max:200',
            'documento_inicial' => 'nullable|string|max:150',
        ]);
        DB::transaction(function () use ($datos, $titular, $r) {
            $lote = collect($datos)->except(['cantidad_inicial', 'proveedor_inicial', 'documento_inicial'])->all();
            $cantidad = number_format((float) $datos['cantidad_inicial'], 3, '.', '');
            $registrado = DB::table('vet_inventario_lotes')->insertOrIgnore($lote + [
                'titular' => $titular, 'saldo' => $cantidad, 'created_at' => now(), 'updated_at' => now(),
            ]);
            if (!$registrado) {
                throw ValidationException::withMessages(['lote' => 'Este producto y lote ya están registrados en esa ubicación. Selecciónalo para registrar su movimiento.']);
            }
            $id = DB::table('vet_inventario_lotes')->where('titular', $titular)
                ->where('producto', $datos['producto'])->where('lote', $datos['lote'])
                ->where('ubicacion', $datos['ubicacion'])->value('id');
            DB::table('vet_inventario_movimientos')->insert([
                'lote_id' => $id, 'usuario_id' => $r->user()->id,
                'operacion' => (string) \Illuminate\Support\Str::uuid(),
                'tipo' => 'entrada', 'cantidad' => $cantidad, 'saldo' => $cantidad,
                'contraparte' => ($datos['proveedor_inicial'] ?? null) ?: 'Inventario inicial',
                'documento' => ($datos['documento_inicial'] ?? null) ?: 'Alta de lote #'.$id,
                'observaciones' => 'Entrada inicial al registrar el lote.', 'created_at' => now(),
            ]);
        });
        return back()->with('success', 'Lote registrado con sus unidades y entrada inicial.');
    }

    public function movimiento(Request $r)
    {
        [$titular] = $this->contexto($r);
        $datos = $r->validate([
            'lote_id' => 'required|integer|min:1',
            'operacion' => 'required|uuid',
            'tipo' => 'required|in:entrada,salida,baja',
            'cantidad' => 'required|numeric|decimal:0,3|min:0.001|max:999999999',
            'contraparte' => 'required|string|max:200',
            'documento' => 'required|string|max:150',
            'receta' => 'nullable|string|max:150',
            'observaciones' => 'nullable|string|max:2000',
        ]);
        DB::transaction(function () use ($titular, $datos, $r) {
            $lote = DB::table('vet_inventario_lotes')->where('titular', $titular)
                ->where('id', $datos['lote_id'])->lockForUpdate()->first();
            abort_unless($lote, 404);
            $existente = DB::table('vet_inventario_movimientos')->where('operacion', $datos['operacion'])->first();
            if ($existente) {
                abort_unless((int) $existente->lote_id === (int) $lote->id, 409);
                return;
            }
            if ($datos['tipo'] === 'salida') {
                if ($lote->vencimiento < now()->toDateString()) {
                    throw ValidationException::withMessages(['lote_id' => 'El lote está vencido. Registra una baja o devolución.']);
                }
                if ($lote->condicion_venta !== 'libre' && empty($datos['receta'])) {
                    throw ValidationException::withMessages(['receta' => 'Registra la referencia de la receta o autorización correspondiente.']);
                }
            }
            $cantidad = (int) round((float) $datos['cantidad'] * 1000);
            $saldo = (int) round((float) $lote->saldo * 1000) + ($datos['tipo'] === 'entrada' ? $cantidad : -$cantidad);
            if ($saldo < 0) {
                throw ValidationException::withMessages(['cantidad' => 'La cantidad supera las existencias del lote.']);
            }
            DB::table('vet_inventario_movimientos')->insert($datos + [
                'usuario_id' => $r->user()->id, 'saldo' => $saldo / 1000, 'created_at' => now(),
            ]);
            DB::table('vet_inventario_lotes')->where('id', $lote->id)->update(['saldo' => $saldo / 1000, 'updated_at' => now()]);
        });
        return back()->with('success', 'Movimiento registrado.');
    }

    public function exportar(Request $r)
    {
        [$titular] = $this->contexto($r);
        return response()->streamDownload(function () use ($titular) {
            $salida = fopen('php://output', 'w');
            fwrite($salida, "\xEF\xBB\xBF");
            fputcsv($salida, ['Fecha','Producto','Categoría','Registro SAG','Condición venta','Lote','Vencimiento','Ubicación','Unidad','Movimiento','Cantidad','Saldo','Procedencia / destino','Documento','Receta','Observaciones','Usuario','Ficha','Indicado por','Administrado por'], ';', '"', '');
            $filas = DB::table('vet_inventario_movimientos as m')->join('vet_inventario_lotes as l','l.id','=','m.lote_id')
                ->where('l.titular',$titular)->select('m.*','l.producto','l.categoria','l.registro_sag','l.condicion_venta','l.lote','l.vencimiento','l.ubicacion','l.unidad')->orderBy('m.id')->cursor();
            foreach ($filas as $fila) {
                $valores = [$fila->created_at,$fila->producto,$fila->categoria,$fila->registro_sag,$fila->condicion_venta,$fila->lote,$fila->vencimiento,$fila->ubicacion,$fila->unidad,$fila->tipo,$fila->cantidad,$fila->saldo,$fila->contraparte,$fila->documento,$fila->receta,$fila->observaciones,$fila->usuario_id,$fila->ficha_id,$fila->indicado_por,$fila->administrado_por];
                fputcsv($salida, array_map(fn ($valor) => is_string($valor) && preg_match('/^[=+@\-\t\r]/', $valor) ? "'".$valor : $valor, $valores), ';', '"', '');
            }
            fclose($salida);
        }, 'trazabilidad-inventario.csv', ['Content-Type'=>'text/csv; charset=UTF-8','Cache-Control'=>'private, no-store']);
    }
}

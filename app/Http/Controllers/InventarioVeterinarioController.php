<?php
namespace App\Http\Controllers;

use App\Models\{Profesional, User, FichaAtencion};
use App\Services\HospitalizacionAcceso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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
        [$profesionalesUso, $funcionariosDespacho] = $this->responsables($r);
        $lotes = DB::table('vet_inventario_lotes')->where('titular', $titular)
            ->orderBy('vencimiento')->paginate(25)->withQueryString();
        $facturasPorLote = DB::table('vet_inventario_movimientos')->whereIn('lote_id', $lotes->pluck('id'))
            ->where('tipo', 'entrada')->whereNotNull('documento')->where('documento', '!=', '')
            ->orderBy('id')->get(['lote_id', 'documento'])->groupBy('lote_id')
            ->map(fn ($entradas) => $entradas->pluck('documento')->unique()->implode(', '));
        $lotesMovimiento = DB::table('vet_inventario_lotes')->where('titular', $titular)->orderBy('producto')->get();
        $movimientos = DB::table('vet_inventario_movimientos as m')
            ->join('vet_inventario_lotes as l', 'l.id', '=', 'm.lote_id')
            ->leftJoin('users as registrador', 'registrador.id', '=', 'm.usuario_id')
            ->where('l.titular', $titular)->select('m.*', 'l.producto', 'l.lote', 'l.unidad', 'registrador.name as registrado_por')
            ->orderByDesc('m.id')->paginate(30, ['*'], 'movimientos')->withQueryString();
        $ingresosCompra = DB::table('vet_ingresos_compra')->where('titular', $titular)->orderByDesc('created_at')->paginate(15, ['*'], 'compras')->withQueryString();
        return view('app.veterinaria.inventario.index', compact('template', 'nombre', 'lotes', 'lotesMovimiento', 'movimientos', 'ingresosCompra', 'facturasPorLote', 'profesionalesUso', 'funcionariosDespacho'));
    }

    private function responsables(Request $r): array
    {
        if ($r->filled('id_institucion')) {
            $centro = $r->integer('id_institucion');
            $ids = app(HospitalizacionAcceso::class)->profesionales($centro);
            $profesionales = Profesional::whereIn('id_usuario', $ids)->orderBy('nombre')->get();
            $funcionarios = app(HospitalizacionAcceso::class)->candidatos($centro)->push($r->user())->unique('id');
        } else {
            $profesionales = Profesional::where('id_usuario', $r->user()->id)->get();
            $funcionarios = collect([$r->user()]);
        }
        return [$profesionales, $funcionarios];
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
            'modalidad_consumo' => 'sometimes|required|in:fraccionable,individual',
            'ubicacion' => 'required|string|max:150',
            'conservacion' => 'required|string|max:250',
            'cantidad_inicial' => 'required|numeric|decimal:0,3|min:0.001|max:999999999',
            'proveedor_inicial' => 'nullable|string|max:200',
            'documento_inicial' => 'nullable|string|max:150',
        ]);
        DB::transaction(function () use ($datos, $titular, $r) {
            $lote = collect($datos)->except(['cantidad_inicial', 'proveedor_inicial', 'documento_inicial'])->all();
            $cantidad = number_format((float) $datos['cantidad_inicial'], 3, '.', '');
            \App\Services\ConsumoInventarioVeterinario::validarCantidad($datos['modalidad_consumo'] ?? 'fraccionable', $cantidad);
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

    public function ingresoCompra(Request $r)
    {
        [$titular] = $this->contexto($r);
        $datos = $r->validate([
            'ingreso_id' => 'required|uuid',
            'proveedor_inicial' => 'required|string|max:200',
            'documento_inicial' => 'required|string|max:150',
            'productos' => 'required|array|list|min:1|max:40',
            'productos.*' => 'required|array:categoria,producto,registro_sag,condicion_venta,lote,vencimiento,unidad,ubicacion,conservacion,cantidad_inicial,modalidad_consumo',
            'productos.*.categoria' => 'required|in:medicamento,vacuna,alimento,insumo',
            'productos.*.producto' => 'required|string|max:180',
            'productos.*.registro_sag' => 'nullable|string|max:100',
            'productos.*.condicion_venta' => 'required|in:libre,receta,retenida,control_saldo,uso_veterinario',
            'productos.*.lote' => 'required|string|max:100',
            'productos.*.vencimiento' => 'required|date_format:Y-m-d',
            'productos.*.unidad' => 'required|string|max:40',
            'productos.*.modalidad_consumo' => 'sometimes|required|in:fraccionable,individual',
            'productos.*.ubicacion' => 'required|string|max:150',
            'productos.*.conservacion' => 'required|string|max:250',
            'productos.*.cantidad_inicial' => 'required|numeric|decimal:0,3|min:0.001|max:999999999',
        ]);
        DB::transaction(function () use ($datos, $titular, $r) {
            $creado = DB::table('vet_ingresos_compra')->insertOrIgnore([
                'id' => $datos['ingreso_id'], 'titular' => $titular,
                'proveedor' => $datos['proveedor_inicial'], 'documento' => $datos['documento_inicial'],
                'usuario_id' => $r->user()->id, 'created_at' => now(),
            ]);
            if (!$creado) {
                abort_unless(DB::table('vet_ingresos_compra')->where('id', $datos['ingreso_id'])->where('titular', $titular)->exists(), 409);
                return;
            }
            foreach ($datos['productos'] as $indice => $producto) {
                $producto['modalidad_consumo'] ??= 'fraccionable';
                \App\Services\ConsumoInventarioVeterinario::validarCantidad($producto['modalidad_consumo'], $producto['cantidad_inicial']);
                $cantidad = (int) round((float) $producto['cantidad_inicial'] * 1000);
                unset($producto['cantidad_inicial']);
                DB::table('vet_inventario_lotes')->insertOrIgnore($producto + [
                    'titular' => $titular, 'saldo' => 0, 'created_at' => now(), 'updated_at' => now(),
                ]);
                $lote = DB::table('vet_inventario_lotes')->where('titular', $titular)
                    ->where('producto', $producto['producto'])->where('lote', $producto['lote'])
                    ->where('ubicacion', $producto['ubicacion'])->lockForUpdate()->first();
                if (!$lote || $lote->unidad !== $producto['unidad'] || $lote->vencimiento !== $producto['vencimiento']
                    || $lote->modalidad_consumo !== $producto['modalidad_consumo'] || $lote->categoria !== $producto['categoria'] || $lote->condicion_venta !== $producto['condicion_venta']) {
                    throw ValidationException::withMessages(['productos.'.$indice.'.lote' => 'El lote existente tiene datos diferentes. Revisa unidad, categoría, vencimiento y condición de venta.']);
                }
                $saldo = (int) round((float) $lote->saldo * 1000) + $cantidad;
                if ($saldo > 99999999999999) {
                    throw ValidationException::withMessages(['productos.'.$indice.'.cantidad_inicial' => 'La cantidad supera el saldo permitido.']);
                }
                DB::table('vet_inventario_movimientos')->insert([
                    'lote_id' => $lote->id, 'usuario_id' => $r->user()->id,
                    'operacion' => (string) \Illuminate\Support\Str::uuid(), 'tipo' => 'entrada',
                    'cantidad' => $cantidad / 1000, 'saldo' => $saldo / 1000,
                    'contraparte' => $datos['proveedor_inicial'], 'documento' => $datos['documento_inicial'],
                    'observaciones' => 'Ingreso de compra '.$datos['ingreso_id'], 'created_at' => now(),
                ]);
                DB::table('vet_inventario_lotes')->where('id', $lote->id)->update(['saldo' => $saldo / 1000, 'updated_at' => now()]);
            }
        });
        return back()->with('success', 'Productos ingresados con una misma factura.');
    }

    public function respaldoCompra(Request $r, string $ingreso)
    {
        [$titular] = $this->contexto($r);
        $r->validate(['archivo_compra' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240']);
        $ruta = null;
        try {
            DB::transaction(function () use ($r, $titular, $ingreso, &$ruta) {
                $compra = DB::table('vet_ingresos_compra')->where('titular', $titular)->where('id', $ingreso)->lockForUpdate()->first();
                abort_unless($compra, 404);
                abort_if($compra->ruta, 409, 'Este ingreso ya tiene un respaldo.');
                $archivo = $r->file('archivo_compra');
                $ruta = $archivo->store('inventario-compras', 'local');
                if (!$ruta) throw new \RuntimeException('No se pudo guardar el respaldo.');
                DB::table('vet_ingresos_compra')->where('id', $ingreso)->update([
                    'ruta' => $ruta, 'archivo' => mb_substr($archivo->getClientOriginalName(), 0, 255), 'mime' => $archivo->getMimeType(),
                ]);
            });
        } catch (\Throwable $error) {
            if ($ruta) Storage::disk('local')->delete($ruta);
            throw $error;
        }
        return back()->with('success', 'Respaldo asociado a la factura y a sus productos.');
    }

    public function verRespaldoCompra(Request $r, string $ingreso)
    {
        [$titular] = $this->contexto($r);
        $compra = DB::table('vet_ingresos_compra')->where('titular', $titular)->where('id', $ingreso)->first();
        abort_unless($compra && $compra->ruta && Storage::disk('local')->exists($compra->ruta), 404);
        return Storage::disk('local')->response($compra->ruta, $compra->archivo, [
            'Content-Type' => $compra->mime, 'Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff',
        ]);
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
            'profesional_uso_id' => 'nullable|required_if:tipo,salida|integer|min:1',
            'despachador_id' => 'nullable|required_if:tipo,salida|integer|min:1',
            'uso_clinico' => 'nullable|required_if:tipo,salida|string|max:250',
            'ficha_id' => 'nullable|integer|min:1',
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
                [$profesionales, $funcionarios] = $this->responsables($r);
                $profesional = $profesionales->firstWhere('id', (int) $datos['profesional_uso_id']);
                $despachador = $funcionarios->firstWhere('id', (int) $datos['despachador_id']);
                if (!$profesional || !$despachador) {
                    throw ValidationException::withMessages(['profesional_uso_id' => 'Selecciona responsables vinculados a este inventario.']);
                }
                $datos['profesional_uso'] = $profesional->nombreCompleto();
                $datos['despachado_por'] = $despachador->name;
                if (!empty($datos['ficha_id'])) {
                    $ficha = FichaAtencion::whereKey($datos['ficha_id'])->where('id_profesional', $profesional->id)->first();
                    $centro = $ficha ? \App\Support\LugarAtencionInstitucionResolver::resolve((int) $ficha->id_lugar_atencion) : null;
                    if (!$ficha || ($r->filled('id_institucion') && $centro !== $r->integer('id_institucion'))) {
                        throw ValidationException::withMessages(['ficha_id' => 'La atención no pertenece al profesional y al inventario seleccionados.']);
                    }
                }
                if ($lote->vencimiento < now()->toDateString()) {
                    throw ValidationException::withMessages(['lote_id' => 'El lote está vencido. Registra una baja o devolución.']);
                }
                if ($lote->condicion_venta !== 'libre' && empty($datos['receta'])) {
                    throw ValidationException::withMessages(['receta' => 'Registra la referencia de la receta o autorización correspondiente.']);
                }
            } else {
                foreach (['profesional_uso_id', 'despachador_id', 'uso_clinico', 'ficha_id'] as $campo) unset($datos[$campo]);
            }
            \App\Services\ConsumoInventarioVeterinario::validarCantidad($lote->modalidad_consumo, $datos['cantidad']);
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
            fputcsv($salida, ['Fecha','Producto','Categoría','Registro SAG','Condición venta','Lote','Vencimiento','Ubicación','Unidad','Movimiento','Cantidad','Saldo','Procedencia / destino','Documento','Receta','Observaciones','Usuario','Ficha','Indicado por','Administrado por','Profesional que utilizó','Despachado por','Uso clínico'], ';', '"', '');
            $filas = DB::table('vet_inventario_movimientos as m')->join('vet_inventario_lotes as l','l.id','=','m.lote_id')
                ->where('l.titular',$titular)->select('m.*','l.producto','l.categoria','l.registro_sag','l.condicion_venta','l.lote','l.vencimiento','l.ubicacion','l.unidad')->orderBy('m.id')->cursor();
            foreach ($filas as $fila) {
                $valores = [$fila->created_at,$fila->producto,$fila->categoria,$fila->registro_sag,$fila->condicion_venta,$fila->lote,$fila->vencimiento,$fila->ubicacion,$fila->unidad,$fila->tipo,$fila->cantidad,$fila->saldo,$fila->contraparte,$fila->documento,$fila->receta,$fila->observaciones,$fila->usuario_id,$fila->ficha_id,$fila->indicado_por,$fila->administrado_por,$fila->profesional_uso,$fila->despachado_por,$fila->uso_clinico];
                fputcsv($salida, array_map(fn ($valor) => is_string($valor) && preg_match('/^[=+@\-\t\r]/', $valor) ? "'".$valor : $valor, $valores), ';', '"', '');
            }
            fclose($salida);
        }, 'trazabilidad-inventario.csv', ['Content-Type'=>'text/csv; charset=UTF-8','Cache-Control'=>'private, no-store']);
    }
}

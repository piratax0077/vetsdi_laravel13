<?php

namespace App\Services;

use App\Models\Instituciones;
use App\Support\UserCenterContext;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB, Schema};
use Illuminate\Validation\{Rule, ValidationException};

class OperacionVeterinaria
{
    public const SERVICIOS = ['peluqueria'=>'Peluquería y belleza', 'laboratorio'=>'Laboratorio', 'imagenologia'=>'Imagenología', 'vacunatorio'=>'Vacunatorio', 'dental'=>'Dental'];
    public const SECCIONES = ['configuracion'=>'Tipo de establecimiento', 'pacientes'=>'Pacientes del establecimiento', 'hospitalizacion'=>'Hospitalización', 'plazas'=>'Plazas de hospitalización', 'asistentes'=>'Selección de asistentes', 'impuestos'=>'Impuestos y obligaciones', 'resumen'=>'Estadísticas veterinarias'];

    public function institucion(Request $r): Instituciones
    {
        $r->validate(['id_institucion'=>'nullable|integer|min:1']);
        $perfil = app(PerfilCentroMedico::class);
        if ($r->filled('id_institucion')) {
            $propia = $perfil->permitidos()->find($r->id_institucion);
            if ($propia) return $propia;
        }
        if ($r->hasSession()) {
            $contextos = UserCenterContext::forAdmin(Auth::user(), $r);
            $contexto = $r->filled('id_institucion')
                ? $contextos['contexts']->firstWhere('id_institucion',(int)$r->id_institucion)
                : ($contextos['active'] ?? null);
            if ($contexto) return Instituciones::findOrFail($contexto['id_institucion']);
        }
        // Este módulo administra establecimientos; los servicios independientes conservan su escritorio.
        $r = $r->duplicate(array_merge($r->all(), ['tipo_institucion'=>'institucion']));
        return $perfil->centro($r);
    }

    public function configuracion(int $id): object
    {
        $fila = DB::table('vet_establecimientos')->where('id_institucion', $id)->first();
        return (object) [
            'tipo'=>$fila->tipo ?? 'centro',
            'hospitalizacion_habilitada'=>(bool)($fila->hospitalizacion_habilitada ?? true),
            'servicios_habilitados'=>$fila ? json_decode($fila->servicios_habilitados, true) : array_keys(self::SERVICIOS),
        ];
    }

    public function avisos(int $institucion): array
    {
        if (!Schema::hasTable('vet_internaciones')) return [];
        $hoy = Carbon::now(config('operacion_veterinaria.timezone','America/Santiago'))->startOfDay();
        $avisos = [];
        $hospitalizadas = DB::table('vet_internaciones')->where('id_institucion',$institucion)->where('estado','hospitalizada')->count();
        if ($hospitalizadas) $avisos[] = ['color'=>'morado','icono'=>'activity','titulo'=>'Hospitalización','texto'=>$hospitalizadas.' mascotas con ingreso activo.','cuando'=>'Ahora','seccion'=>'hospitalizacion'];
        $reservas = DB::table('vet_reservas_servicios')->where('id_institucion',$institucion)->where('estado','reservada')->whereDate('inicio',$hoy->copy()->addDay())->count();
        if ($reservas) $avisos[] = ['color'=>'naranja','icono'=>'calendar','titulo'=>'Reservas sin confirmar','texto'=>$reservas.' reservas de mañana esperan confirmación.','cuando'=>'Mañana','seccion'=>'resumen'];
        $vencidas = DB::table('vet_obligaciones')->where('id_institucion',$institucion)->where('estado','pendiente')->whereDate('vencimiento','<',$hoy)->count();
        if ($vencidas) $avisos[] = ['color'=>'rojo','icono'=>'file-text','titulo'=>'Obligaciones vencidas','texto'=>$vencidas.' obligaciones pendientes fuera de plazo.','cuando'=>'Pendiente','seccion'=>'impuestos'];
        return $avisos;
    }

    public function mascotas(Instituciones $i)
    {
        $lugares = collect(preg_split('/[,;]/', (string)$i->id_lugar_atencion))->filter()->map(fn($id)=>(int)$id);
        if (Schema::hasTable('sucursal')) $lugares = $lugares->merge(DB::table('sucursal')->where('id_institucion',$i->id)->pluck('id_lugar_atencion'));
        return DB::table('mascotas')->where(function($q) use ($i,$lugares) {
            $q->whereIn('id', function ($horas) use ($lugares) {
                $horas->select('id_mascota')->from('horas_medicas')->whereIn('id_lugar_atencion', $lugares->filter()->unique())->whereNotNull('id_mascota');
            })->orWhereIn('id',function($vinculos) use ($i){$vinculos->select('id_mascota')->from('vet_pacientes_establecimiento')->where('id_institucion',$i->id);});
        })->select('id','nombre','especie','id_responsable')->orderBy('nombre');
    }

    public function mascotasPorTutor(string $rut)
    {
        $normalizado = strtoupper(preg_replace('/[^0-9kK]/','',$rut));
        $this->exigir((bool)preg_match('/^[0-9]{6,8}[0-9K]$/',$normalizado), 'rut_tutor', 'Ingrese un RUT de tutor válido.');
        $tutores = DB::table('pacientes')->whereRaw("REPLACE(REPLACE(UPPER(TRIM(rut)), '.', ''), '-', '') = ?",[$normalizado])->pluck('id');
        return DB::table('mascotas')->whereIn('id_responsable',$tutores)->select('id','nombre','especie')->orderBy('nombre')->get();
    }

    public function registro(string $tabla, int $institucion, int $id): object
    {
        $fila = DB::table($tabla)->where('id_institucion', $institucion)->where('id', $id)->first();
        abort_unless($fila, 404);
        return $fila;
    }

    private function exigir(bool $condicion, string $campo, string $mensaje): void
    {
        if (!$condicion) throw ValidationException::withMessages([$campo=>$mensaje]);
    }

    private function guardar(string $tabla, Instituciones $i, ?int $id, array $datos): int
    {
        $antes = $id ? $this->registro($tabla, $i->id, $id) : null;
        $datos['updated_at'] = now();
        if ($id) DB::table($tabla)->where('id', $id)->update($datos);
        else $id = DB::table($tabla)->insertGetId($datos + ['id_institucion'=>$i->id, 'created_at'=>now()]);
        $this->auditar($i->id, $tabla, $id, $antes, DB::table($tabla)->find($id));
        return $id;
    }

    private function auditar(int $institucion, string $tabla, int $id, $antes, $despues): void
    {
        DB::table('vet_operacion_auditoria')->insert(['id_institucion'=>$institucion, 'id_usuario'=>Auth::id(), 'tabla'=>$tabla, 'registro_id'=>$id, 'antes'=>$antes ? json_encode($antes) : null, 'despues'=>json_encode($despues), 'created_at'=>now()]);
    }

    public function ejecutar(Request $r, Instituciones $i, string $accion): int
    {
        $r->validate(['id'=>'nullable|integer|min:1']);
        // Serializa ocupación y agenda del establecimiento para evitar dobles asignaciones.
        return DB::transaction(function () use ($r, $i, $accion) {
            Instituciones::whereKey($i->id)->lockForUpdate()->firstOrFail();
            $id = $r->filled('id') ? (int)$r->id : null;
            return match ($accion) {
                'configuracion'=>$this->guardarConfiguracion($r, $i),
                'vincular'=>$this->vincularPaciente($r, $i),
                'plaza'=>$this->guardarPlaza($r, $i, $id),
                'internacion'=>$this->guardarInternacion($r, $i, $id),
                'evolucion'=>$this->guardarEvolucion($r, $i),
                'servicio'=>$this->guardarServicio($r, $i, $id),
                'reserva'=>$this->guardarReserva($r, $i, $id),
                'convocatoria'=>$this->guardarConvocatoria($r, $i, $id),
                'postulacion'=>$this->guardarPostulacion($r, $i, $id),
                'obligacion'=>$this->guardarObligacion($r, $i, $id),
                default=>abort(404),
            };
        });
    }

    private function vincularPaciente(Request $r, Instituciones $i): int
    {
        $d=$r->validate(['rut_tutor'=>'required|string|min:7|max:20','id_mascota'=>'required|integer']);
        $this->exigir($this->mascotasPorTutor($d['rut_tutor'])->contains('id',(int)$d['id_mascota']), 'id_mascota', 'La mascota no corresponde al RUT del tutor indicado.');
        $id=DB::table('vet_pacientes_establecimiento')->where('id_institucion',$i->id)->where('id_mascota',$d['id_mascota'])->value('id');
        if ($id) return $id;
        return $this->guardar('vet_pacientes_establecimiento',$i,null,['id_mascota'=>$d['id_mascota'],'id_usuario'=>Auth::id()]);
    }

    private function guardarConfiguracion(Request $r, Instituciones $i): int
    {
        $d = $r->validate(['tipo'=>['required',Rule::in(['centro','hospital'])], 'hospitalizacion_habilitada'=>'required|boolean', 'servicios_habilitados'=>'sometimes|array', 'servicios_habilitados.*'=>[Rule::in(array_keys(self::SERVICIOS))]]);
        if (!$d['hospitalizacion_habilitada']) $this->exigir(!DB::table('vet_internaciones')->where('id_institucion',$i->id)->where('estado','hospitalizada')->exists(), 'hospitalizacion_habilitada', 'Debe dar egreso a las mascotas hospitalizadas antes de deshabilitar el área.');
        $d['servicios_habilitados'] = json_encode(array_values(array_unique($d['servicios_habilitados'] ?? [])));
        $id = DB::table('vet_establecimientos')->where('id_institucion',$i->id)->value('id');
        return $this->guardar('vet_establecimientos', $i, $id, $d);
    }

    private function guardarPlaza(Request $r, Instituciones $i, ?int $id): int
    {
        $d = $r->validate(['codigo'=>['required','string','max:50',Rule::unique('vet_plazas')->where('id_institucion',$i->id)->ignore($id)], 'tipo'=>['required',Rule::in(['jaula','canil','sala','aislamiento','observacion'])], 'especies'=>'nullable|string|max:150', 'observaciones'=>'nullable|string|max:5000', 'activa'=>'required|boolean']);
        if ($id && !$d['activa']) $this->exigir(!DB::table('vet_internaciones')->where('plaza_id',$id)->where('estado','hospitalizada')->exists(), 'activa', 'La plaza tiene una mascota hospitalizada.');
        return $this->guardar('vet_plazas', $i, $id, $d);
    }

    private function guardarInternacion(Request $r, Instituciones $i, ?int $id): int
    {
        $d = $r->validate(['id_mascota'=>'required|integer', 'plaza_id'=>'required|integer', 'responsable'=>'required|string|max:150', 'ingreso'=>'required|date|before_or_equal:now', 'egreso'=>'nullable|date|after_or_equal:ingreso|before_or_equal:now', 'estado'=>['required',Rule::in(['hospitalizada','alta','traslado','fallecida','anulada'])], 'motivo'=>'required|string|max:10000', 'indicaciones'=>'nullable|string|max:20000', 'observaciones_alta'=>'nullable|string|max:10000']);
        $d += ['egreso'=>null,'observaciones_alta'=>null];
        $antes = $id ? $this->registro('vet_internaciones', $i->id, $id) : null;
        $this->exigir(!$antes || $antes->estado === 'hospitalizada', 'estado', 'La internación ya tiene egreso; su historia se conserva sin reabrirla.');
        $this->exigir(($antes && $antes->id_mascota == $d['id_mascota']) || $this->mascotas($i)->where('id',$d['id_mascota'])->exists(), 'id_mascota', 'Vincule la mascota al establecimiento antes de registrar su ingreso.');
        $plaza = $this->registro('vet_plazas', $i->id, (int)$d['plaza_id']);
        if (!$id) {
            $this->exigir($this->configuracion($i->id)->hospitalizacion_habilitada, 'estado', 'Hospitalización está deshabilitada.');
            $this->exigir($d['estado'] === 'hospitalizada', 'estado', 'Registre primero el ingreso de la mascota.');
        }
        if ($d['estado'] === 'hospitalizada') {
            $this->exigir((bool)$plaza->activa, 'plaza_id', 'La plaza está deshabilitada.');
            $ocupadas = DB::table('vet_internaciones')->where('id_institucion',$i->id)->where('estado','hospitalizada')->when($id,fn($q)=>$q->where('id','!=',$id));
            $this->exigir(!(clone $ocupadas)->where(fn($q)=>$q->where('plaza_id',$plaza->id)->orWhere('id_mascota',$d['id_mascota']))->exists(), 'plaza_id', 'La plaza o la mascota ya tiene una internación activa.');
            $d['egreso'] = null; $d['observaciones_alta'] = null;
        } else $this->exigir(!empty($d['egreso']) && !empty(trim($d['observaciones_alta'] ?? '')), 'egreso', 'Indique la fecha y las observaciones del egreso.');
        $d['ingreso'] = Carbon::parse($d['ingreso'])->format('Y-m-d H:i:s');
        if ($d['egreso']) $d['egreso'] = Carbon::parse($d['egreso'])->format('Y-m-d H:i:s');
        $d['id_usuario'] = Auth::id();
        return $this->guardar('vet_internaciones', $i, $id, $d);
    }

    private function guardarEvolucion(Request $r, Instituciones $i): int
    {
        $d = $r->validate(['internacion_id'=>'required|integer', 'fecha'=>'required|date|before_or_equal:now', 'observaciones'=>'required|string|max:20000', 'peso'=>'nullable|numeric|gt:0|max:99999', 'temperatura'=>'nullable|numeric|min:0|max:99']);
        $internacion = $this->registro('vet_internaciones', $i->id, (int)$d['internacion_id']);
        $this->exigir($internacion->estado === 'hospitalizada', 'internacion_id', 'Solo puede agregar evolución a una internación activa.');
        $this->exigir(Carbon::parse($d['fecha'])->gte(Carbon::parse($internacion->ingreso)), 'fecha', 'La evolución debe ser posterior al ingreso.');
        $d['fecha'] = Carbon::parse($d['fecha'])->format('Y-m-d H:i:s');
        $id = DB::table('vet_evoluciones')->insertGetId($d + ['id_usuario'=>Auth::id(), 'created_at'=>now(), 'updated_at'=>now()]);
        $this->auditar($i->id, 'vet_evoluciones', $id, null, DB::table('vet_evoluciones')->find($id));
        return $id;
    }

    private function guardarServicio(Request $r, Instituciones $i, ?int $id): int
    {
        $d = $r->validate(['modulo'=>['required',Rule::in(array_keys(self::SERVICIOS))], 'nombre'=>['required','string','max:150',Rule::unique('vet_catalogo_servicios')->where('id_institucion',$i->id)->where('modulo',$r->modulo)->ignore($id)], 'duracion_minutos'=>'required|integer|min:5|max:1440', 'precio'=>'required|integer|min:0|max:1000000000', 'activo'=>'required|boolean']);
        if ($id) $this->exigir($this->registro('vet_catalogo_servicios',$i->id,$id)->modulo === $d['modulo'], 'modulo', 'El servicio pertenece a otra área.');
        return $this->guardar('vet_catalogo_servicios', $i, $id, $d);
    }

    private function guardarReserva(Request $r, Instituciones $i, ?int $id): int
    {
        $d = $r->validate(['id_mascota'=>'required|integer', 'servicio_id'=>'required|integer', 'responsable'=>'required|string|max:150', 'inicio'=>'required|date', 'estado'=>['required',Rule::in(['reservada','confirmada','en_atencion','completada','cancelada','no_asiste'])], 'observaciones'=>'nullable|string|max:10000', 'resultado'=>'nullable|string|max:20000']);
        $servicio = $this->registro('vet_catalogo_servicios', $i->id, (int)$d['servicio_id']);
        $antes = $id ? $this->registro('vet_reservas_servicios', $i->id, $id) : null;
        $this->exigir(!$antes || !in_array($antes->estado,['completada','cancelada','no_asiste']), 'estado', 'La reserva ya está cerrada.');
        $this->exigir($this->mascotas($i)->where('id',$d['id_mascota'])->exists(), 'id_mascota', 'La mascota no pertenece a este establecimiento.');
        if (!$id || $antes->servicio_id != $servicio->id) {
            $this->exigir((bool)$servicio->activo && in_array($servicio->modulo,$this->configuracion($i->id)->servicios_habilitados), 'servicio_id', 'El servicio está deshabilitado.');
        }
        $inicio = Carbon::parse($d['inicio']);
        if (in_array($d['estado'],['en_atencion','completada'])) $this->exigir($inicio->lte(now()), 'inicio', 'La atención no puede comenzar ni completarse antes de su fecha reservada.');
        $fin = $inicio->copy()->addMinutes($servicio->duracion_minutos);
        if ($antes && $antes->servicio_id == $servicio->id && $inicio->eq(Carbon::parse($antes->inicio))) $fin = Carbon::parse($antes->fin);
        if (!in_array($d['estado'],['cancelada','no_asiste'])) {
            $choque = DB::table('vet_reservas_servicios')->where('id_institucion',$i->id)->whereNotIn('estado',['cancelada','no_asiste'])->when($id,fn($q)=>$q->where('id','!=',$id))
                ->where('inicio','<',$fin)->where('fin','>',$inicio)
                ->where(fn($q)=>$q->whereRaw('LOWER(TRIM(responsable)) = ?', [mb_strtolower(trim($d['responsable']))])->orWhere('id_mascota',$d['id_mascota']))->exists();
            $this->exigir(!$choque, 'inicio', 'La mascota o el responsable ya tiene una reserva en ese horario.');
        }
        if ($d['estado'] === 'completada') $this->exigir(!empty(trim($d['resultado'] ?? '')), 'resultado', 'Registre el resultado de la atención.');
        $d['responsable'] = trim($d['responsable']);
        $d += ['modulo'=>$servicio->modulo, 'servicio'=>$servicio->nombre, 'precio'=>$antes && $antes->servicio_id == $servicio->id ? $antes->precio : $servicio->precio, 'fin'=>$fin->format('Y-m-d H:i:s'), 'id_usuario'=>Auth::id()];
        $d['inicio'] = $inicio->format('Y-m-d H:i:s');
        return $this->guardar('vet_reservas_servicios', $i, $id, $d);
    }

    private function guardarConvocatoria(Request $r, Instituciones $i, ?int $id): int
    {
        $d = $r->validate(['cargo'=>'required|string|max:150', 'jornada'=>'required|string|max:100', 'descripcion'=>'required|string|max:10000', 'fecha_cierre'=>'nullable|date', 'estado'=>['required',Rule::in(['borrador','abierta','cerrada'])]]);
        return $this->guardar('vet_convocatorias', $i, $id, $d + ['id_usuario'=>Auth::id()]);
    }

    private function guardarPostulacion(Request $r, Instituciones $i, ?int $id): int
    {
        $r->merge(['email'=>mb_strtolower(trim((string)$r->email))]);
        $d = $r->validate(['convocatoria_id'=>'required|integer', 'nombre'=>'required|string|max:150', 'email'=>['required','email','max:255',Rule::unique('vet_postulaciones')->where('convocatoria_id',$r->convocatoria_id)->ignore($id)], 'telefono'=>'nullable|string|max:30', 'estado'=>['required',Rule::in(['recibida','entrevista','seleccionada','descartada'])], 'observaciones'=>'nullable|string|max:10000']);
        $convocatoria = $this->registro('vet_convocatorias', $i->id, (int)$d['convocatoria_id']);
        if ($id) {
            $antes = DB::table('vet_postulaciones')->where('convocatoria_id',$convocatoria->id)->find($id); abort_unless($antes,404);
        } else $this->exigir($convocatoria->estado === 'abierta' && (!$convocatoria->fecha_cierre || $convocatoria->fecha_cierre >= today()->toDateString()), 'convocatoria_id', 'La convocatoria no está abierta.');
        $datos = $d + ['id_usuario'=>Auth::id(), 'updated_at'=>now()];
        if ($id) DB::table('vet_postulaciones')->where('id',$id)->update($datos);
        else $id = DB::table('vet_postulaciones')->insertGetId($datos + ['created_at'=>now()]);
        $this->auditar($i->id, 'vet_postulaciones', $id, $antes ?? null, DB::table('vet_postulaciones')->find($id));
        return $id;
    }

    private function guardarObligacion(Request $r, Instituciones $i, ?int $id): int
    {
        $d = $r->validate(['concepto'=>'required|string|max:150', 'periodo'=>'required|date_format:Y-m', 'referencia'=>'nullable|string|max:100', 'monto'=>'required|integer|min:0|max:1000000000000', 'vencimiento'=>'required|date', 'fecha_pago'=>'nullable|date|before_or_equal:today', 'estado'=>['required',Rule::in(['pendiente','pagada','anulada'])], 'observaciones'=>'nullable|string|max:10000']);
        if ($d['estado'] === 'pagada') $this->exigir(!empty($d['fecha_pago']) && !empty(trim($d['referencia'] ?? '')), 'fecha_pago', 'Indique fecha de pago y referencia del comprobante.');
        else $d['fecha_pago'] = null;
        return $this->guardar('vet_obligaciones', $i, $id, $d + ['id_usuario'=>Auth::id()]);
    }
}

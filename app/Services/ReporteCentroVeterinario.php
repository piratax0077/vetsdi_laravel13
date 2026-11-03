<?php

namespace App\Services;

use App\Models\{Instituciones,LugarAtencion};
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB,Schema};
use Illuminate\Validation\Rule;

class ReporteCentroVeterinario
{
    private array $columnas = [];
    private array $faltantes = [];

    private function disponible(string $tabla, array $campos): bool
    {
        $this->columnas[$tabla] ??= Schema::hasTable($tabla) ? Schema::getColumnListing($tabla) : [];
        if (array_diff($campos,$this->columnas[$tabla])) {
            $this->faltantes[$tabla] = $tabla;
            return false;
        }
        return true;
    }

    public function datos(Request $r): array
    {
        $this->columnas = []; $this->faltantes = [];
        $r->validate(['mes'=>['nullable',Rule::in(array_merge(['actual'],range(1,12)))], 'anio'=>'nullable|integer|min:2000|max:2100', 'lugar'=>['nullable','regex:/^(todos|[1-9][0-9]*)$/'], 'formato'=>['nullable',Rule::in(['html','csv','pdf'])], 'contexto'=>'nullable|string|max:100']);
        $institucion = app(OperacionVeterinaria::class)->institucion($r);
        $ids = collect(preg_split('/[,;]/',(string)$institucion->id_lugar_atencion))->filter()->map(fn($id)=>(int)$id);
        if ($this->disponible('sucursal',['id_institucion','id_lugar_atencion'])) $ids=$ids->merge(DB::table('sucursal')->where('id_institucion',$institucion->id)->pluck('id_lugar_atencion'));
        // Una instalación antigua puede no usar sucursales; no es una fuente estadística faltante.
        unset($this->faltantes['sucursal']);
        $lugares = LugarAtencion::whereIn('id',$ids->filter()->unique())->orderBy('nombre')->get();
        $idLugar = $r->filled('lugar') && $r->lugar !== 'todos' ? (int)$r->lugar : null;
        $contexto = $r->query('contexto') ?: ($r->hasSession() ? $r->session()->get('admin_center_context') : null);
        if (!$r->filled('lugar') && preg_match('/^admin:'.(int)$institucion->id.':([1-9][0-9]*)$/',(string)$contexto,$coincidencia)) $idLugar=(int)$coincidencia[1];
        abort_if($idLugar && !$lugares->contains('id',$idLugar),403,'La sucursal no pertenece al establecimiento.');
        $scope = $idLugar ? [$idLugar] : $lugares->pluck('id')->all();
        $zona = config('operacion_veterinaria.timezone','America/Santiago');
        $hoy = CarbonImmutable::now($zona)->startOfDay();
        $mes = (string)($r->input('mes') ?: 'actual');
        $anio = (int)($r->input('anio') ?: $hoy->year);
        $numeroMes = $mes === 'actual' ? $hoy->month : (int)$mes;
        if ($mes === 'actual' && $anio !== $hoy->year) $mes = (string)$numeroMes;
        // Partir siempre del día 1 evita saltar meses al consultar desde el día 29, 30 o 31.
        $inicio = CarbonImmutable::create($anio,$numeroMes,1,0,0,0,$zona);
        $fin = $inicio->addMonth();
        $periodo = fn($q,string $campo)=>$q->where($campo,'>=',$inicio->toDateTimeString())->where($campo,'<',$fin->toDateTimeString());
        $horasOk=$this->disponible('horas_medicas',['id','id_mascota','id_lugar_atencion','fecha_consulta','id_estado','comentarios_cancelacion']);
        $fichasOk=$this->disponible('fichas_atenciones',['id','id_mascota','id_lugar_atencion','finalizada','created_at']);
        $reservasOk=$this->disponible('vet_reservas_servicios',['id_institucion','id_mascota','inicio','estado']);
        $horas=fn()=>DB::table('horas_medicas')->whereIn('id_lugar_atencion',$scope)->whereNotNull('id_mascota')->where('id_estado','!=',14);
        $fichas=fn()=>DB::table('fichas_atenciones')->whereIn('id_lugar_atencion',$scope)->whereNotNull('id_mascota');
        $reservas=fn()=>DB::table('vet_reservas_servicios')->where('id_institucion',$institucion->id);
        $datos = compact('institucion','lugares','idLugar','mes','anio','inicio','fin');
        $datos['fechaHoy']=$hoy->toDateString();
        $datos['contexto']=$contexto;
        $datos['incluyeServicios']=!$idLugar;
        $asistencia = ['asistieron'=>0,'noAsistieron'=>0,'canceladasPaciente'=>0,'canceladasLugar'=>0,'canceladasSinOrigen'=>0,'pendientesAgenda'=>0];
        if ($horasOk) {
            // Agrupar en SQL evita cargar las fichas y los datos de pacientes en memoria.
            $grupos=$horas()->where('fecha_consulta','>=',$inicio->toDateString())->where('fecha_consulta','<',$fin->toDateString())
                ->selectRaw('id_estado, comentarios_cancelacion, COUNT(*) as total')->groupBy('id_estado','comentarios_cancelacion')->get();
            foreach ($grupos as $g) {
                $estado=(int)$g->id_estado; $texto=mb_strtolower((string)$g->comentarios_cancelacion);
                $clave = match(true) {
                    in_array($estado,[4,5,6],true)=>'asistieron', $estado===7=>'noAsistieron',
                    $estado===15=>'canceladasLugar',
                    $estado===3 && (str_contains($texto,'tutor') || str_contains($texto,'paciente'))=>'canceladasPaciente',
                    $estado===3 && preg_match('/centro|profesional|asistente|instituci/',$texto)=>'canceladasLugar',
                    $estado===3=>'canceladasSinOrigen', default=>'pendientesAgenda',
                };
                $asistencia[$clave]+=(int)$g->total;
            }
        }
        if (!$idLugar && $reservasOk) {
            foreach($periodo($reservas(),'inicio')->selectRaw('estado, COUNT(*) as total')->groupBy('estado')->get() as $g) {
                $clave=match($g->estado){'en_atencion','completada'=>'asistieron','no_asiste'=>'noAsistieron','cancelada'=>'canceladasSinOrigen',default=>'pendientesAgenda'};
                $asistencia[$clave]+=(int)$g->total;
            }
        }
        $agendaDisponible=$horasOk && ($idLugar || $reservasOk);
        foreach($asistencia as $clave=>$valor)$datos[$clave]=$agendaDisponible?$valor:null;
        $datos['totalAgenda']=$agendaDisponible?array_sum($asistencia):null;
        $datos['totalEvaluadas']=$agendaDisponible?$asistencia['asistieron']+$asistencia['noAsistieron']:null;
        $completadas = function(CarbonImmutable $desde, CarbonImmutable $hasta) use ($fichasOk,$reservasOk,$idLugar,$fichas,$reservas) {
            if (!$fichasOk || (!$idLugar && !$reservasOk)) return null;
            $cantidad=$fichas()->where('finalizada',1)->where('created_at','>=',$desde->toDateTimeString())->where('created_at','<',$hasta->toDateTimeString())->count();
            if(!$idLugar)$cantidad+=$reservas()->where('estado','completada')->where('inicio','>=',$desde->toDateTimeString())->where('inicio','<',$hasta->toDateTimeString())->count();
            return $cantidad;
        };
        $datos['atencionesRealizadas']=$completadas($inicio,$fin);
        $datos['atencionesMes']=collect(range(5,0))->map(function($resta)use($inicio,$completadas){
            $desde=$inicio->subMonthsNoOverflow($resta);
            return ['mes'=>ucfirst(str_replace('.','',$desde->locale('es')->shortMonthName)), 'periodo'=>$desde->format('Y-m'), 'total'=>$completadas($desde,$desde->addMonth())];
        });
        $bonosOk=$horasOk && $this->disponible('bonos',['id_referencia','id_tipo_bono','devuelto','pago_confirmado_en','a_pagar','fecha_atencion']);
        $bonos=fn()=>DB::table('bonos')->where('id_tipo_bono',1)->where('devuelto',0)->whereIn('id_referencia',$horas()->select('id'));
        $datos['ingresoTotal']=$bonosOk?(int)$periodo($bonos()->whereNotNull('pago_confirmado_en'),'pago_confirmado_en')->sum('a_pagar'):null;
        $datos['recaudadoHoy']=$bonosOk?(int)$bonos()->where('pago_confirmado_en','>=',$hoy->toDateTimeString())->where('pago_confirmado_en','<',$hoy->addDay()->toDateTimeString())->sum('a_pagar'):null;
        $datos['cobrosPendientes']=$bonosOk?(int)$bonos()->whereNull('pago_confirmado_en')->where('fecha_atencion','>=',$inicio->toDateString())->where('fecha_atencion','<',$fin->toDateString())->sum('a_pagar'):null;
        $datos['pacientesNuevos']=$this->pacientesNuevos($institucion,$scope,$idLugar,$inicio,$fin,$horasOk,$fichasOk);
        $datos['documentos']=[];
        foreach([
            'Recetas veterinarias'=>['detalles_receta','id_ficha',true],
            'Certificados veterinarios'=>['certificados_reposos','id_ficha_atencion',false],
            'Consentimientos informados'=>['con_consentimientos_pcte','id_fc',false],
            'Órdenes de examen'=>['examen_especialidad','id_ficha_atencion',false],
        ] as $etiqueta=>[$tabla,$campo,$porFicha]) {
            $disponible=$fichasOk && $this->disponible($tabla,[$campo,'created_at']);
            $consulta=$disponible?$periodo(DB::table($tabla)->whereIn($campo,$fichas()->select('id')),'created_at'):null;
            $datos['documentos'][$etiqueta]=$consulta?($porFicha?$consulta->distinct()->count($campo):$consulta->count()):null;
        }
        $base=fn($tabla)=>DB::table($tabla)->where('id_institucion',$institucion->id);
        $internaciones=$this->disponible('vet_internaciones',['id_institucion','estado']);
        $plazas=$this->disponible('vet_plazas',['id_institucion','activa']);
        $obligaciones=$this->disponible('vet_obligaciones',['id_institucion','estado','monto','vencimiento']);
        $convocatorias=$this->disponible('vet_convocatorias',['id_institucion','estado']);
        $datos['resumenVeterinario']=[
            'Mascotas hospitalizadas'=>!$idLugar && $internaciones?$base('vet_internaciones')->where('estado','hospitalizada')->count():null,
            'Plazas habilitadas'=>!$idLugar && $plazas?$base('vet_plazas')->where('activa',1)->count():null,
            'Reservas de hoy'=>$horasOk && ($idLugar || $reservasOk)
                ? $horas()->whereDate('fecha_consulta',$hoy->toDateString())->whereNotIn('id_estado',[3,7,15])->count()
                    + (!$idLugar?$reservas()->where('inicio','>=',$hoy->toDateTimeString())->where('inicio','<',$hoy->addDay()->toDateTimeString())->whereNotIn('estado',['cancelada','no_asiste'])->count():0)
                : null,
            'Atenciones completadas'=>$datos['atencionesRealizadas'],
            'Obligaciones pendientes (CLP)'=>$obligaciones?(int)$base('vet_obligaciones')->where('estado','pendiente')->sum('monto'):null,
            'Obligaciones vencidas'=>$obligaciones?$base('vet_obligaciones')->where('estado','pendiente')->whereDate('vencimiento','<',$hoy->toDateString())->count():null,
            'Convocatorias abiertas'=>$convocatorias?$base('vet_convocatorias')->where('estado','abierta')->count():null,
        ];
        $etiquetas=['horas_medicas'=>'Agenda clínica','fichas_atenciones'=>'Fichas veterinarias','bonos'=>'Pagos de consultas','vet_reservas_servicios'=>'Reservas de servicios','mascotas_lugares_atencion'=>'Vinculaciones a sucursales','vet_pacientes_establecimiento'=>'Vinculaciones al establecimiento','vet_internaciones'=>'Hospitalización','vet_plazas'=>'Plazas','vet_obligaciones'=>'Obligaciones','vet_convocatorias'=>'Convocatorias','detalles_receta'=>'Recetas','certificados_reposos'=>'Certificados','con_consentimientos_pcte'=>'Consentimientos','examen_especialidad'=>'Órdenes de examen'];
        $datos['fuentesNoDisponibles']=array_map(fn($tabla)=>$etiquetas[$tabla] ?? $tabla,array_values($this->faltantes));
        return $datos;
    }

    private function pacientesNuevos(Instituciones $i,array $scope,?int $lugar,CarbonImmutable $inicio,CarbonImmutable $fin,bool $horasOk,bool $fichasOk): ?int
    {
        if (!$horasOk || !$fichasOk) return null;
        $fuentes=[];
        $fuentes[]=DB::table('horas_medicas')->whereIn('id_lugar_atencion',$scope)->whereNotNull('id_mascota')->where('id_estado','!=',14)->selectRaw('id_mascota, fecha_consulta as fecha');
        $fuentes[]=DB::table('fichas_atenciones')->whereIn('id_lugar_atencion',$scope)->whereNotNull('id_mascota')->selectRaw('id_mascota, created_at as fecha');
        if($this->disponible('mascotas_lugares_atencion',['id_mascota','id_lugar_atencion','created_at']))$fuentes[]=DB::table('mascotas_lugares_atencion')->whereIn('id_lugar_atencion',$scope)->selectRaw('id_mascota, created_at as fecha');
        if(!$lugar && $this->disponible('vet_pacientes_establecimiento',['id_institucion','id_mascota','created_at']))$fuentes[]=DB::table('vet_pacientes_establecimiento')->where('id_institucion',$i->id)->selectRaw('id_mascota, created_at as fecha');
        $union=array_shift($fuentes);foreach($fuentes as $fuente)$union->unionAll($fuente);
        $primeras=DB::query()->fromSub($union,'registros')->whereNotNull('fecha')->selectRaw('id_mascota, MIN(fecha) as primera_fecha')->groupBy('id_mascota');
        return DB::query()->fromSub($primeras,'primeras')->where('primera_fecha','>=',$inicio->toDateString())->where('primera_fecha','<',$fin->toDateString())->count();
    }

    public function csv(array $d)
    {
        $filas=[['Estadísticas del establecimiento',$d['institucion']->nombre],['Período',$d['inicio']->format('Y-m')],['Sucursal',$d['idLugar']?$d['lugares']->firstWhere('id',$d['idLugar'])->nombre:'Todas'],['Recaudado hoy CLP',$d['recaudadoHoy']],['Fecha de hoy',$d['fechaHoy']],['Recaudado en período CLP',$d['ingresoTotal']],['Cobros sin confirmación CLP',$d['cobrosPendientes']],['Atenciones completadas',$d['atencionesRealizadas']],['Pacientes nuevos',$d['pacientesNuevos']]];
        foreach(['totalAgenda'=>'Total agendadas','asistieron'=>'Asistieron','noAsistieron'=>'No asistieron','canceladasPaciente'=>'Canceladas por tutor','canceladasLugar'=>'Canceladas por centro','canceladasSinOrigen'=>'Canceladas sin origen registrado','pendientesAgenda'=>'Pendientes o sin cierre registrado'] as $key=>$label)$filas[]=[$label,$d[$key]];
        foreach($d['documentos'] as $label=>$cantidad)$filas[]=[$label,$cantidad];
        foreach($d['resumenVeterinario'] as $label=>$cantidad)$filas[]=[$label,$cantidad];
        foreach($d['atencionesMes'] as $mes)$filas[]=['Atenciones '.$mes['periodo'],$mes['total']];
        $filas[]=['Alcance','Recaudación de bonos de consulta con pago confirmado, sin devoluciones. Los precios de reservas no equivalen a pagos.'];
        if($d['idLugar'])$filas[]=['Servicios sin sucursal','Excluidos del filtro por sucursal. Hospitalización y plazas sin desglose.'];
        foreach($d['fuentesNoDisponibles'] as $fuente)$filas[]=['Fuente no disponible',$fuente];
        return response()->streamDownload(function()use($filas){
            $salida=fopen('php://output','w');fwrite($salida,"\xEF\xBB\xBF");
            foreach($filas as $fila){$fila=array_map(fn($v)=>$v===null?'No disponible':(is_string($v)&&preg_match('/^\s*[=+@\-]/u',$v)?"'".$v:$v),$fila);fputcsv($salida,$fila,';','"','');}fclose($salida);
        },'reporte-centro-'.$d['institucion']->id.'-'.$d['inicio']->format('Y-m').'.csv',['Content-Type'=>'text/csv; charset=UTF-8']);
    }
}

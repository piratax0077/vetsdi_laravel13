<?php

namespace App\Http\Controllers;

use App\Services\OperacionVeterinaria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OperacionVeterinariaController extends Controller
{
    public function index(Request $r, OperacionVeterinaria $s, string $seccion = 'resumen')
    {
        abort_unless(isset((OperacionVeterinaria::SECCIONES + OperacionVeterinaria::SERVICIOS)[$seccion]),404);
        $r->validate(['editar'=>'nullable|integer|min:1','catalogo'=>'nullable|integer|min:1','detalle'=>'nullable|integer|min:1','postulacion'=>'nullable|integer|min:1','estado'=>'nullable|string|max:25','desde'=>'nullable|date','hasta'=>'nullable|date|after_or_equal:desde','rut_tutor'=>'nullable|string|min:7|max:20']);
        $institucion = $s->institucion($r);
        $configuracion = $s->configuracion($institucion->id);
        $ctx = ['id_institucion'=>$institucion->id];
        $base = fn(string $tabla)=>DB::table($tabla)->where('id_institucion',$institucion->id);
        $editar = function(string $tabla, string $parametro = 'editar') use ($r,$s,$institucion) {
            return $r->filled($parametro) ? $s->registro($tabla,$institucion->id,(int)$r->input($parametro)) : null;
        };
        $datos = compact('institucion','configuracion','seccion','ctx');
        $datos['titulo'] = (OperacionVeterinaria::SECCIONES + OperacionVeterinaria::SERVICIOS)[$seccion];
        $datos['campos'] = []; $datos['filas'] = collect(); $datos['columnas'] = [];
        $datos['registro'] = null; $datos['accion'] = null;
        $opcionesMascotas = fn()=>$s->mascotas($institucion)->get()->mapWithKeys(fn($m)=>[$m->id=>$m->nombre.' · #'.$m->id])->all();
        $campo = fn($nombre,$etiqueta,$tipo='text',$opciones=[])=>compact('nombre','etiqueta','tipo','opciones');
        if ($seccion === 'pacientes') {
            $datos['pacientes'] = $s->mascotas($institucion)->paginate(20)->withQueryString();
            $datos['resultadosTutor'] = $r->filled('rut_tutor') ? $s->mascotasPorTutor($r->rut_tutor) : collect();
        } elseif ($seccion === 'configuracion') {
            $datos['accion'] = 'configuracion'; $datos['registro'] = $configuracion;
            $datos['campos'] = [$campo('tipo','Tipo de establecimiento','select',OperacionVeterinaria::TIPOS),$campo('hospitalizacion_habilitada','Hospitalización y observación','select',[1=>'Habilitada',0=>'Deshabilitada']),$campo('servicios_habilitados','Áreas habilitadas','checklist',OperacionVeterinaria::SERVICIOS)];
        } elseif ($seccion === 'plazas') {
            $datos['accion'] = 'plaza'; $datos['registro'] = $editar('vet_plazas');
            $datos['campos'] = [$campo('codigo','Código de plaza'),$campo('tipo','Tipo','select',['jaula'=>'Jaula','canil'=>'Canil','sala'=>'Sala','aislamiento'=>'Aislamiento','observacion'=>'Observación']),$campo('especies','Especies admitidas (opcional)'),$campo('observaciones','Observaciones (opcional)','textarea'),$campo('activa','Disponibilidad','select',[1=>'Habilitada',0=>'Deshabilitada'])];
            $datos['filas'] = $base('vet_plazas')->orderBy('codigo')->paginate(20,['*'],'pagina_plazas')->withQueryString();
            $datos['columnas'] = ['codigo'=>'Código','tipo'=>'Tipo','especies'=>'Especies','activa'=>'Habilitada'];
            $datos['ocupadas'] = $base('vet_internaciones')->where('estado','hospitalizada')->pluck('plaza_id')->all();
        } elseif ($seccion === 'hospitalizacion') {
            $datos['accion'] = 'internacion'; $datos['registro'] = $editar('vet_internaciones');
            $datos['mascotas'] = $opcionesMascotas();
            $plazas = $base('vet_plazas')->orderBy('codigo')->get()->mapWithKeys(fn($p)=>[$p->id=>$p->codigo.($p->activa ? '' : ' (deshabilitada)')])->all();
            $datos['campos'] = [$campo('id_mascota','Mascota','select',$datos['mascotas']),$campo('plaza_id','Plaza','select',$plazas),$campo('responsable','Veterinario responsable'),$campo('ingreso','Fecha de ingreso','datetime-local'),$campo('motivo','Motivo de ingreso','textarea'),$campo('indicaciones','Indicaciones (opcional)','textarea'),$campo('estado','Estado','select',['hospitalizada'=>'Hospitalizada','alta'=>'Alta','traslado'=>'Traslado','fallecida'=>'Fallecida','anulada'=>'Anulada']),$campo('egreso','Fecha de egreso (para cerrar)','datetime-local'),$campo('observaciones_alta','Observaciones del egreso (para cerrar)','textarea')];
            $datos['filas'] = $base('vet_internaciones')->when($r->estado,fn($q)=>$q->where('estado',$r->estado))->orderByDesc('ingreso')->paginate(20)->withQueryString();
            $datos['columnas'] = ['id_mascota'=>'Mascota','plaza_id'=>'Plaza','responsable'=>'Responsable','ingreso'=>'Ingreso','estado'=>'Estado'];
            $datos['nombresPlazas'] = $plazas;
            $datos['detalle'] = $r->filled('detalle') ? $s->registro('vet_internaciones',$institucion->id,(int)$r->detalle) : $datos['registro'];
            if ($datos['detalle']) $datos['evoluciones'] = DB::table('vet_evoluciones')->where('internacion_id',$datos['detalle']->id)->orderByDesc('fecha')->get();
            $datos['camposEvolucion'] = [$campo('fecha','Fecha de evolución','datetime-local'),$campo('peso','Peso en kg (opcional)','number'),$campo('temperatura','Temperatura en °C (opcional)','number'),$campo('observaciones','Evolución y cuidados','textarea')];
        } elseif (isset(OperacionVeterinaria::SERVICIOS[$seccion])) {
            $datos['accion'] = 'reserva'; $datos['registro'] = $editar('vet_reservas_servicios');
            abort_if($datos['registro'] && $datos['registro']->modulo !== $seccion,404);
            $datos['catalogo'] = $base('vet_catalogo_servicios')->where('modulo',$seccion)->orderBy('nombre')->get();
            $datos['servicioEditado'] = $editar('vet_catalogo_servicios','catalogo');
            abort_if($datos['servicioEditado'] && $datos['servicioEditado']->modulo !== $seccion,404);
            $datos['mascotas'] = $opcionesMascotas();
            $opciones = $datos['catalogo']->mapWithKeys(fn($c)=>[$c->id=>$c->nombre.' · '.$c->duracion_minutos.' min · $'.number_format($c->precio,0,',','.').($c->activo ? '' : ' (inactivo)')])->all();
            $datos['campos'] = [$campo('id_mascota','Mascota','select',$datos['mascotas']),$campo('servicio_id','Servicio','select',$opciones),$campo('responsable','Responsable de la atención'),$campo('inicio','Fecha y hora','datetime-local'),$campo('estado','Estado','select',['reservada'=>'Reservada','confirmada'=>'Confirmada','en_atencion'=>'En atención','completada'=>'Completada','cancelada'=>'Cancelada','no_asiste'=>'No asiste']),$campo('observaciones','Observaciones y cuidados (opcional)','textarea'),$campo('resultado','Resultado de la atención (al completar)','textarea')];
            $datos['camposServicio'] = [$campo('nombre','Nombre del servicio'),$campo('duracion_minutos','Duración en minutos','number'),$campo('precio','Precio en CLP','number'),$campo('activo','Estado','select',[1=>'Activo',0=>'Inactivo'])];
            $datos['filas'] = $base('vet_reservas_servicios')->where('modulo',$seccion)->when($r->estado,fn($q)=>$q->where('estado',$r->estado))->when($r->desde,fn($q)=>$q->whereDate('inicio','>=',$r->desde))->when($r->hasta,fn($q)=>$q->whereDate('inicio','<=',$r->hasta))->orderByDesc('inicio')->paginate(20)->withQueryString();
            $datos['columnas'] = ['id_mascota'=>'Mascota','servicio'=>'Servicio','responsable'=>'Responsable','inicio'=>'Inicio','fin'=>'Término','precio'=>'Precio CLP','estado'=>'Estado'];
        } elseif ($seccion === 'asistentes') {
            $datos['accion'] = 'convocatoria'; $datos['registro'] = $editar('vet_convocatorias');
            $datos['campos'] = [$campo('cargo','Cargo requerido'),$campo('jornada','Jornada'),$campo('descripcion','Funciones y requisitos','textarea'),$campo('fecha_cierre','Fecha de cierre (opcional)','date'),$campo('estado','Estado','select',['borrador'=>'Borrador','abierta'=>'Abierta','cerrada'=>'Cerrada'])];
            $datos['filas'] = $base('vet_convocatorias')->orderByDesc('id')->paginate(20)->withQueryString();
            $datos['columnas'] = ['cargo'=>'Cargo','jornada'=>'Jornada','fecha_cierre'=>'Cierre','estado'=>'Estado'];
            $datos['detalle'] = $r->filled('detalle') ? $s->registro('vet_convocatorias',$institucion->id,(int)$r->detalle) : $datos['registro'];
            if ($datos['detalle']) {
                $datos['postulaciones'] = DB::table('vet_postulaciones')->where('convocatoria_id',$datos['detalle']->id)->orderBy('nombre')->get();
                $datos['postulacionEditada'] = $r->filled('postulacion') ? $datos['postulaciones']->firstWhere('id',(int)$r->postulacion) : null;
                abort_if($r->filled('postulacion') && !$datos['postulacionEditada'],404);
            }
            $datos['camposPostulacion'] = [$campo('nombre','Nombre del candidato'),$campo('email','Correo electrónico','email'),$campo('telefono','Teléfono (opcional)'),$campo('estado','Etapa','select',['recibida'=>'Recibida','entrevista'=>'Entrevista','seleccionada'=>'Seleccionada','descartada'=>'Descartada']),$campo('observaciones','Antecedentes y notas (opcional)','textarea')];
        } elseif ($seccion === 'impuestos') {
            $datos['accion'] = 'obligacion'; $datos['registro'] = $editar('vet_obligaciones');
            $datos['campos'] = [$campo('concepto','Concepto'),$campo('periodo','Período','month'),$campo('monto','Monto en CLP','number'),$campo('vencimiento','Vencimiento','date'),$campo('estado','Estado','select',['pendiente'=>'Pendiente','pagada'=>'Pagada','anulada'=>'Anulada']),$campo('fecha_pago','Fecha de pago (si está pagada)','date'),$campo('referencia','Referencia del comprobante (si está pagada)'),$campo('observaciones','Observaciones (opcional)','textarea')];
            $datos['filas'] = $base('vet_obligaciones')->when($r->estado,fn($q)=>$q->where('estado',$r->estado))->when($r->desde,fn($q)=>$q->whereDate('vencimiento','>=',$r->desde))->when($r->hasta,fn($q)=>$q->whereDate('vencimiento','<=',$r->hasta))->orderBy('vencimiento')->paginate(20)->withQueryString();
            $datos['columnas'] = ['concepto'=>'Concepto','periodo'=>'Período','monto'=>'Monto CLP','vencimiento'=>'Vencimiento','estado'=>'Estado','fecha_pago'=>'Pago'];
        } else {
            $datos['resumen'] = [
                'Mascotas hospitalizadas'=>$base('vet_internaciones')->where('estado','hospitalizada')->count(),
                'Plazas habilitadas'=>$base('vet_plazas')->where('activa',1)->count(),
                'Reservas de hoy'=>$base('vet_reservas_servicios')->whereDate('inicio',today())->whereNotIn('estado',['cancelada','no_asiste'])->count(),
                'Atenciones completadas'=>$base('vet_reservas_servicios')->where('estado','completada')->count(),
                'Obligaciones pendientes (CLP)'=>$base('vet_obligaciones')->where('estado','pendiente')->sum('monto'),
                'Obligaciones vencidas'=>$base('vet_obligaciones')->where('estado','pendiente')->whereDate('vencimiento','<',today())->count(),
                'Convocatorias abiertas'=>$base('vet_convocatorias')->where('estado','abierta')->count(),
            ];
        }
        return view('app.veterinaria.operacion.index',$datos);
    }

    // El historial de cambios del establecimiento tiene su propia vista, accesible desde el escritorio.
    public function historial(Request $r, OperacionVeterinaria $s)
    {
        $institucion = $s->institucion($r);
        $ctx = ['id_institucion'=>$institucion->id];
        $auditoria = DB::table('vet_operacion_auditoria')->where('id_institucion',$institucion->id)->orderByDesc('id')->paginate(20)->withQueryString();
        return view('app.veterinaria.historial.index',compact('institucion','ctx','auditoria'));
    }

    // Cada área del centro muestra la misma sección que la operación, en su propia vista.
    public function area(Request $r, OperacionVeterinaria $s, string $area)
    {
        $seccion = $area === 'hospitalizacion' && $r->input('vista') === 'plazas' ? 'plazas' : $area;
        $datos = $this->index($r,$s,$seccion)->getData();
        // Hospitalización trae sus dos secciones juntas: las pestañas cambian sin recargar la página
        if ($area === 'hospitalizacion') {
            $otra = $seccion === 'plazas' ? 'hospitalizacion' : 'plazas';
            // "editar" y "detalle" son de la sección abierta: en la otra apuntarían a otra tabla
            $secciones = [$seccion=>$datos,$otra=>$this->index($r->duplicate($r->except(['editar','detalle'])),$s,$otra)->getData()];
            $secciones['hospitalizacion']['filas']->appends(['vista'=>null,'editar'=>null,'detalle'=>null]);
            $secciones['plazas']['filas']->appends(['vista'=>'plazas','editar'=>null,'detalle'=>null]);
            $datos['seccionesHospitalizacion'] = $secciones;
        }
        return view('app.veterinaria.areas.index',$datos+['areaCentro'=>$area,'tituloArea'=>OperacionVeterinaria::AREAS[$area]]);
    }

    public function guardar(Request $r, OperacionVeterinaria $s, string $accion)
    {
        $institucion = $s->institucion($r);
        $r->validate(['_seccion'=>['required',\Illuminate\Validation\Rule::in(array_keys(OperacionVeterinaria::SECCIONES + OperacionVeterinaria::SERVICIOS))]]);
        $id = $s->ejecutar($r,$institucion,$accion);
        $params = ['seccion'=>$r->_seccion,'id_institucion'=>$institucion->id];
        if ($accion === 'evolucion') $params['detalle'] = $r->internacion_id;
        if ($accion === 'postulacion') $params['detalle'] = $r->convocatoria_id;
        if ($accion === 'convocatoria') $params['detalle'] = $id;
        // Un formulario enviado desde la vista de un área vuelve a esa misma vista.
        if (isset(OperacionVeterinaria::AREAS[$r->input('_area')])) {
            unset($params['seccion']);
            if ($r->_seccion === 'plazas') $params['vista'] = 'plazas';
            // Estas áreas separan el catálogo en una pestaña: al guardar un servicio se vuelve a ella
            if ($accion === 'servicio' && in_array($r->input('_area'),OperacionVeterinaria::AREAS_EN_PESTANAS)) $params['pestana'] = 'catalogo';
            // Con "success" la plantilla abriría además su cuadro de ficha clínica; las áreas usan su alerta flotante
            return redirect()->route('vet.areas.index',$params+['area'=>$r->input('_area')])->with('alerta_area','Cambios guardados correctamente.');
        }
        return redirect()->route('vet.operacion.index',$params)->with('success','Cambios guardados correctamente.');
    }
}

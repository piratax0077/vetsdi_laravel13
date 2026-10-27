<?php
namespace App\Services;
use App\Models\{Asistente,HoraMedica,Paciente,Profesional};
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AccesoAgendaVeterinaria
{
    public function reserva(\Illuminate\Http\Request $request,int $profesional,int $paciente): void
    {
        abort_unless(Auth::check(),403);
        $request->validate(['fecha_consulta'=>'required|date','id_lugar_atencion'=>'required|integer|min:1','id_mascota'=>'nullable|integer|min:1']);
        if(\Carbon\Carbon::parse($request->fecha_consulta)->lte(now()))throw ValidationException::withMessages(['fecha_consulta'=>'Selecciona una fecha y hora futura.']);
        if(Auth::user()->hasRole('Profesional')){
            abort_unless(Profesional::whereKey($profesional)->where('id_usuario',Auth::id())->exists(),403);
        }elseif(app(AutorizacionesAsistentes::class)->esAsistente(Auth::user())){
            $asistente=Asistente::where('id_usuario',Auth::id())->firstOrFail();
            app(AutorizacionesAsistentes::class)->exigir($asistente,(int)$request->id_lugar_atencion,$profesional,'permiso_reservar_hora');
        }else{
            abort_unless(Paciente::whereKey($paciente)->where('id_usuario',Auth::id())->exists(),403);
        }
        abort_unless(\App\Models\ProfesionalesLugaresAtencion::where('id_profesional',$profesional)->where('id_lugar_atencion',$request->id_lugar_atencion)->where('estado',1)->exists(),422,'El profesional no atiende en ese lugar.');
        if($request->filled('id_mascota'))abort_unless(\App\Models\Mascota::whereKey($request->id_mascota)->where('id_responsable',$paciente)->exists(),403,'La mascota no pertenece al tutor seleccionado.');
    }
    public function guardarCambio(HoraMedica $hora): bool
    {
        $cambios=$hora->getDirty();
        if(!$cambios)return true;
        $actualizada=HoraMedica::whereKey($hora->id)->where('id_estado',$hora->getRawOriginal('id_estado'))->update($cambios);
        if(!$actualizada)throw ValidationException::withMessages(['hora'=>'La hora cambió mientras la estabas revisando. Actualiza la agenda.']);
        $hora->syncOriginal();return true;
    }
    public function hora($id,string $accion,bool $soloTutor=false): HoraMedica
    {
        abort_unless(Auth::check(),403);
        abort_unless(filter_var($id,FILTER_VALIDATE_INT)&&((int)$id)>0,422,'Indica una hora válida.');
        $hora=HoraMedica::findOrFail((int)$id);
        $usuario=Auth::user();
        if(!$soloTutor&&$usuario->hasRole('Profesional')){
            abort_unless(Profesional::where('id_usuario',$usuario->id)->whereKey($hora->id_profesional)->exists(),403,'La hora no pertenece a tu agenda.');
        }elseif(!$soloTutor&&app(AutorizacionesAsistentes::class)->esAsistente($usuario)){
            abort_unless(in_array($accion,['ver','confirmar','cancelar'],true),403,'Solo el profesional puede continuar la atención.');
            abort_if(in_array((int)$hora->id_estado,[4,5,6,8],true)&&$accion!=='ver',403,'La hora fue tomada con éxito. No cuentas con permisos para continuar.');
            $asistente=Asistente::where('id_usuario',$usuario->id)->firstOrFail();
            $permiso=match($accion){'confirmar'=>'permiso_confirmar_hora','cancelar'=>'permiso_anular_hora',default=>'permiso_ver_agenda'};
            app(AutorizacionesAsistentes::class)->exigir($asistente,(int)$hora->id_lugar_atencion,(int)$hora->id_profesional,$permiso);
        }else{
            abort_unless(in_array($accion,['ver','confirmar','cancelar'],true),403,'Solo el profesional puede abrir la ficha.');
            abort_unless(Paciente::where('id_usuario',$usuario->id)->whereKey($hora->id_paciente)->exists(),403,'La hora no pertenece a este tutor.');
        }
        $validos=match($accion){'confirmar'=>[1,2,16],'cancelar'=>[1,2,16],'espera'=>[4,8],'atender'=>[4,5,6,8],default=>null};
        if($validos&&!in_array((int)$hora->id_estado,$validos,true))throw ValidationException::withMessages(['hora'=>'La acción no corresponde al estado actual de la hora. Actualiza la agenda.']);
        return $hora;
    }
}

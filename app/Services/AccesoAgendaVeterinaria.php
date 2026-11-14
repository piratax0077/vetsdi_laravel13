<?php
namespace App\Services;
use App\Models\{Asistente,HoraMedica,Paciente,Profesional};
use App\Models\{Mascota, ProfesionalHorario, ProfesionalHorariosBloqueo};
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\{Auth, DB};
use Illuminate\Validation\ValidationException;

class AccesoAgendaVeterinaria
{
    public function reserva(\Illuminate\Http\Request $request,int $profesional,int $paciente): void
    {
        abort_unless(Auth::check(),403);
        $request->validate(['fecha_consulta'=>'required|date','id_lugar_atencion'=>'required|integer|min:1','id_mascota'=>'nullable|integer|min:1']);
        if(\Carbon\Carbon::parse($request->fecha_consulta, 'America/Santiago')->lte(now()))throw ValidationException::withMessages(['fecha_consulta'=>'Selecciona una fecha y hora futura.']);
        if(Auth::user()->hasRole('Profesional')){
            abort_unless(Profesional::whereKey($profesional)->where('id_usuario',Auth::id())->exists(),403);
        }elseif(app(AutorizacionesAsistentes::class)->esAsistente(Auth::user())){
            $asistente=Asistente::where('id_usuario',Auth::id())->firstOrFail();
            app(AutorizacionesAsistentes::class)->exigir($asistente,(int)$request->id_lugar_atencion,$profesional,'permiso_reservar_hora');
        }else{
            abort_unless(Paciente::whereKey($paciente)->where('id_usuario',Auth::id())->exists(),403);
        }
        abort_unless(\App\Models\ProfesionalesLugaresAtencion::where('id_profesional',$profesional)->where('id_lugar_atencion',$request->id_lugar_atencion)->where('estado',1)->exists(),422,'El profesional no atiende en ese lugar.');
        if ($request->filled('id_mascota')) {
            $tutor = Paciente::findOrFail($paciente);
            $mascotas = Mascota::whereKey($request->id_mascota)->seleccionables();
            if ((int) $tutor->id_usuario === (int) Auth::id()) $mascotas->accesiblesPor($tutor);
            else $mascotas->where('id_responsable', $paciente);
            abort_unless($mascotas->exists(), 403, 'La mascota no está disponible para ese tutor.');
        }
    }
    public function guardarReserva(HoraMedica $hora, int $duracion): bool
    {
        return DB::transaction(function () use ($hora, $duracion) {
            Profesional::whereKey($hora->id_profesional)->lockForUpdate()->firstOrFail();
            $dia = CarbonImmutable::parse($hora->fecha_consulta)->toDateString();
            $inicio = CarbonImmutable::parse($dia.' '.$hora->hora_inicio, 'America/Santiago');
            $fin = $inicio->addMinutes($duracion);

            $inicioHora = $inicio->format('H:i:s');
            $finHora = $fin->format('H:i:s');
            $tipo = ['C' => 1, 'D' => 2, 'T' => 3, 'E' => 4, 'P' => 5][$hora->tipo_hora_medica] ?? null;
            $turnos = ProfesionalHorario::where('id_profesional', $hora->id_profesional)
                ->where('id_lugar_atencion', $hora->id_lugar_atencion)
                ->when($tipo, fn ($query) => $query->where('tipo_agenda', $tipo))
                ->get();

            // Los horarios se guardan con un segundo de desfase (09:30:01 a 15:30:00) y la
            // hora puede llegar en punto o con ese segundo, así que todo se compara por minuto.
            $inicioMinuto = $inicio->startOfMinute();
            $finMinuto = $fin->startOfMinute();
            $atiende = $turnos->contains(function ($turno) use ($inicioMinuto, $finMinuto, $dia) {
                $dias = preg_split('/[^0-9]+/', (string) $turno->dia, -1, PREG_SPLIT_NO_EMPTY);
                if (!in_array((string) $inicioMinuto->dayOfWeek, $dias, true)) return false;

                $apertura = CarbonImmutable::parse($dia.' '.$turno->hora_inicio, 'America/Santiago')->startOfMinute();
                $cierre = CarbonImmutable::parse($dia.' '.$turno->hora_termino, 'America/Santiago')->startOfMinute();
                if ($inicioMinuto->lt($apertura) || $finMinuto->gt($cierre)) return false;

                $partes = explode(':', (string) $turno->duracion_consulta);
                $paso = ((int) ($partes[0] ?? 0) * 60 + (int) ($partes[1] ?? 0)) * 60;
                return ($inicioMinuto->timestamp - $apertura->timestamp) % ($paso ?: 900) === 0;
            });
            if ($duracion <= 0 || $inicio->isPast() || !$inicio->isSameDay($fin) || !$atiende) {
                throw ValidationException::withMessages(['fecha_consulta' => 'La cita debe quedar dentro de un horario de atención disponible.']);
            }

            // Solo bloquean los bloqueos activos: uno anulado por el profesional deja libre el horario
            $bloqueada = ProfesionalHorariosBloqueo::where('id_profesional', $hora->id_profesional)
                ->where('id_lugar_atencion', $hora->id_lugar_atencion)
                ->where('estado', 1)
                ->whereDate('fecha_inicio', '<=', $dia)->whereDate('fecha_termino', '>=', $dia)->get()
                ->contains(function ($bloqueo) use ($inicio, $fin) {
                    if ((int) $bloqueo->todo_dia === 1) return true;
                    $desde = CarbonImmutable::parse($bloqueo->fecha_inicio.' '.$bloqueo->hora_inicio, 'America/Santiago');
                    $hasta = CarbonImmutable::parse($bloqueo->fecha_termino.' '.$bloqueo->hora_termino, 'America/Santiago');
                    return $desde->lt($fin) && $hasta->gt($inicio);
                });

            $ocupada = HoraMedica::where('id_profesional', $hora->id_profesional)
                ->whereDate('fecha_consulta', $dia)->whereNotIn('id_estado', [3, 14, 15])
                ->where('hora_inicio', '<', $finHora)->where('hora_termino', '>', $inicioHora)->exists();
            if ($bloqueada || $ocupada) {
                throw ValidationException::withMessages(['fecha_consulta' => 'El horario está ocupado o bloqueado. Selecciona otra hora.']);
            }

            return $hora->saveOrFail();
        });
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
        $validos=match($accion){'confirmar'=>[1,2,16],'cancelar'=>[1,2,16],'espera'=>[4,8],default=>null};
        if(($accion === 'atender' && !$hora->permiteAbrirAtencion()) || ($validos&&!in_array((int)$hora->id_estado,$validos,true)))throw ValidationException::withMessages(['hora'=>'La acción no corresponde al estado actual de la hora. Actualiza la agenda.']);
        return $hora;
    }
}

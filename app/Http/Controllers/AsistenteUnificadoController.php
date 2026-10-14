<?php

namespace App\Http\Controllers;

use App\Models\{Asistente, BoxesCm, LugarAtencion, Paciente, Prevision, Profesional, ProfesionalHorario, ProfesionOficio, Region, RegistroConfirmacionHoraAgenda, TipoBono};
use App\Services\AutorizacionesAsistentes;
use Illuminate\Http\Request;

class AsistenteUnificadoController extends Controller
{
    public function home(Request $request, AutorizacionesAsistentes $autorizaciones)
    {
        $asistente = Asistente::where('id_usuario', $request->user()->id)->first();
        if (!$asistente) {
            return view('auth.Registros.registro_asistente', ['region' => Region::all(), 'prevision' => Prevision::all()]);
        }
        $contextos = $autorizaciones->contextos($asistente);
        $contexto = $request->session()->get('asistente_contexto');
        $actual = $contextos->first(fn ($c) => $c['id_lugar_atencion'] === (int) ($contexto['id_lugar_atencion'] ?? 0)
            && $c['id_profesional'] === (int) ($contexto['id_profesional'] ?? 0));
        $actual ??= $contextos->first();
        if ($actual) {
            $request->session()->put('asistente_contexto', $actual);
        } else {
            $request->session()->forget('asistente_contexto');
        }
        $permisos = $actual ? $autorizaciones->permisos($asistente->id, $actual['id_lugar_atencion'], $actual['id_profesional']) : [];
        return view('app.asistente_unificado.home', compact('asistente', 'contextos', 'actual', 'permisos'));
    }

    public function contexto(Request $request, AutorizacionesAsistentes $autorizaciones)
    {
        $datos = $request->validate(['id_lugar_atencion' => 'required|integer', 'id_profesional' => 'required|integer']);
        $asistente = Asistente::where('id_usuario', $request->user()->id)->firstOrFail();
        $autorizaciones->validarContexto($asistente, $datos['id_lugar_atencion'], $datos['id_profesional']);
        $request->session()->put('asistente_contexto', $datos);
        return redirect()->route('asistentes.home');
    }

    public function permisos(Request $request, AutorizacionesAsistentes $autorizaciones)
    {
        $asistente = Asistente::where('id_usuario', $request->user()->id)->firstOrFail();
        $contexto = $request->session()->get('asistente_contexto', []);
        $lugar = (int) $request->input('id_lugar_atencion', $contexto['id_lugar_atencion'] ?? 0);
        $profesional = (int) $request->input('id_profesional', $contexto['id_profesional'] ?? 0);
        $autorizaciones->validarContexto($asistente, $lugar, $profesional);
        return response()->json(['estado' => 1, 'permisos' => $autorizaciones->permisos($asistente->id, $lugar, $profesional)]);
    }

    public function agenda(Request $request, AutorizacionesAsistentes $autorizaciones, string $tipo = 'agenda')
    {
        $asistente = Asistente::where('id_usuario', $request->user()->id)->firstOrFail();
        $contexto = $request->session()->get('asistente_contexto', []);
        $lugar = (int) ($contexto['id_lugar_atencion'] ?? 0);
        $profesional = (int) ($contexto['id_profesional'] ?? 0);
        $autorizaciones->exigir($asistente, $lugar, $profesional, $tipo === 'enfermeria' ? 'permiso_enfermeria' : 'permiso_ver_agenda');
        $asistente->id_lugar_atencion = $lugar;
        $profesionales = Profesional::whereKey($profesional)->get();
        foreach ($profesionales as $registro) {
            $registro->id_tipo_agenda = ProfesionalHorario::where('id_profesional', $profesional)->where('id_lugar_atencion', $lugar)->value('tipo_agenda') ?? 0;
        }
        $idInstitucion = \App\Models\ProfesionalesLugaresAtencion::where('id_profesional', $profesional)->where('id_lugar_atencion', $lugar)->value('id_institucion');
        return view($tipo === 'enfermeria' ? 'app.asistente_enfermeria.escritorio_asistente' : 'app.asistente_cm_publico.escritorio_asistente', [
            'asistente' => $asistente, 'profesionales' => $profesionales,
            'lugares_atencion' => LugarAtencion::findOrFail($lugar),
            'prevision' => Prevision::all(), 'region' => Region::all(), 'profesion_oficio' => ProfesionOficio::all(),
            'reg_confirmacion_hora' => RegistroConfirmacionHoraAgenda::where('estado', 1)->get(),
            'tipo_bonos' => TipoBono::where('estado', 1)->get(), 'boxes' => BoxesCm::where('id_lugar_atencion', $lugar)->where('estado', 1)->get(),
            'permisos_asistente' => (object) $autorizaciones->permisos($asistente->id, $lugar, $profesional),
            'institucion' => $idInstitucion ? \App\Models\Instituciones::find($idInstitucion) : null,
        ]);
    }

    public function pacientes(Request $request, AutorizacionesAsistentes $autorizaciones)
    {
        $asistente = Asistente::where('id_usuario', $request->user()->id)->firstOrFail();
        $contexto = $request->session()->get('asistente_contexto', []);
        $lugar = (int) ($contexto['id_lugar_atencion'] ?? 0);
        $profesional = (int) ($contexto['id_profesional'] ?? 0);
        $autorizaciones->exigir($asistente, $lugar, $profesional, 'permiso_ver_pacientes');
        $buscar = trim((string) $request->input('buscar', ''));
        $pacientes = Paciente::whereIn('id', \App\Models\HoraMedica::select('id_paciente')->where('id_lugar_atencion', $lugar)->where('id_profesional', $profesional))
            ->when($buscar !== '', fn ($q) => $q->where(fn ($f) => $f->where('rut', 'like', '%'.$buscar.'%')->orWhere('nombres', 'like', '%'.$buscar.'%')->orWhere('apellido_uno', 'like', '%'.$buscar.'%')))
            ->orderBy('apellido_uno')->paginate(25)->withQueryString();
        return view('app.asistente_unificado.pacientes', compact('pacientes', 'buscar'));
    }

    public function enfermeria(Request $request, AutorizacionesAsistentes $autorizaciones)
    {
        return $this->agenda($request, $autorizaciones, 'enfermeria');
    }
}

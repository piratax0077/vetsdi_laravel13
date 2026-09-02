<?php

namespace App\Http\Controllers;

use App\Models\EspecieMascota;
use App\Models\LugarAtencion;
use App\Models\Profesional;
use App\Models\SitioWeb;
use App\Models\SitioWebArticulo;
use Illuminate\Http\Request;

class SitioWebController extends Controller
{
    public function bienestar()
    {
        return view('sitio.bienestar', [
            'profesionales' => SitioWeb::publicados()
                ->with('usuario.profesional')
                ->where('tipo', 'profesional')
                ->orderBy('titulo')
                ->get(),
            'articulos' => SitioWebArticulo::with('sitio')->where('publicado', true)->latest('publicado_at')->get(),
        ]);
    }

    public function cuidadosPreventivos(string $slug)
    {
        $sitio = SitioWeb::publicados()
            ->with(['usuario.profesional.Especialidad', 'usuario.profesional.TipoEspecialidad'])
            ->where('tipo', 'profesional')
            ->where('slug', $slug)
            ->orderBy('titulo')
            ->firstOrFail();

        return view('sitio.cuidados-preventivos', [
            'sitio' => $sitio,
            'profesional' => $sitio->usuario?->profesional,
        ]);
    }

    public function inicio()
    {
        $sitios = SitioWeb::publicados()
            ->with(['usuario.profesional', 'usuario.institucion'])
            ->orderByRaw("FIELD(tipo, 'profesional', 'clinica')")
            ->orderBy('titulo')
            ->get()
            ->each(function (SitioWeb $sitio) {
                $sitio->sincronizarLugaresConectados();
                $sitio->setRelation('lugares', $sitio->lugaresPublicos());
            });

        $profesional = $sitios->firstWhere('slug', 'dr-jaimekriman')
            ?: $sitios->firstWhere('tipo', 'profesional');
        $clinica = $sitios->firstWhere('slug', 'centro-medico-patitas')
            ?: $sitios->firstWhere('tipo', 'clinica');

        return view('sitio.inicio', [
            'profesional' => $profesional,
            'clinica' => $clinica,
            'sitios' => $sitios,
        ]);
    }

    public function ficha(string $slug)
    {
        if (SitioWeb::slugEstaReservado($slug)) {
            abort(404);
        }

        if ($slug === 'jaime-kriman') {
            return redirect('/dr-jaimekriman', 301);
        }

        $sitio = SitioWeb::publicados()
            ->with([
                'usuario.profesional.Especialidad',
                'usuario.profesional.TipoEspecialidad',
                'usuario.institucion',
            ])
            ->where('slug', $slug)
            ->firstOrFail();

        $sitio->sincronizarLugaresConectados();
        $lugares = $sitio->lugaresPublicos();
        $sitio->setRelation('lugares', $lugares);

        $profesionalTitular = optional($sitio->usuario)->profesional;
        $equipo = $sitio->profesionalesEnLugares($lugares);
        $horarios = $sitio->horariosPublicos($lugares, $equipo);

        $equipoPorLugar = [];
        foreach ($equipo as $pro) {
            $idsLugar = $sitio->esProfesional()
                ? SitioWeb::idsLugaresDelProfesional($pro)
                : $pro->lugaresAtencionParaAgenda()->pluck('id')->map(fn ($id) => (int) $id)->all();

            foreach ($idsLugar as $idLugar) {
                if ($lugares->contains('id', $idLugar)) {
                    $equipoPorLugar[$idLugar][] = [
                        'id' => $pro->id,
                        'nombre' => $pro->nombreCompleto(),
                    ];
                }
            }
        }

        $horariosJs = $horarios->map(function ($horario) {
            return [
                'id_profesional' => (int) $horario->id_profesional,
                'id_lugar_atencion' => (int) $horario->id_lugar_atencion,
                'dia' => (string) $horario->dia,
                'hora_inicio' => substr((string) $horario->hora_inicio, 0, 5),
                'hora_termino' => substr((string) $horario->hora_termino, 0, 5),
            ];
        })->values();

        return view($sitio->esClinica() ? 'sitio.clinica' : 'sitio.profesional', [
            'sitio' => $sitio,
            'lugares' => $lugares,
            'profesionalTitular' => $profesionalTitular,
            'equipo' => $equipo,
            'horarios' => $horarios,
            'equipoPorLugar' => $equipoPorLugar,
            'horariosJs' => $horariosJs,
            'especies' => EspecieMascota::orderBy('nombre')->get(['id', 'nombre']),
        ]);
    }

    public function horas(Request $request, VetsdiInicioReservaController $reserva)
    {
        $bloqueo = $this->assertAgendaDelSitio($request);
        if ($bloqueo) {
            return $bloqueo;
        }

        try {
            return $reserva->horas($request);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'estado' => 0,
                'msj' => 'No se pudo leer la agenda de este lugar.',
                'horarios' => [],
            ], 500);
        }
    }

    public function reservar(Request $request, VetsdiInicioReservaController $reserva)
    {
        $bloqueo = $this->assertAgendaDelSitio($request);
        if ($bloqueo) {
            return $bloqueo;
        }

        return $reserva->reservar($request);
    }

    private function assertAgendaDelSitio(Request $request)
    {
        $request->validate([
            'slug' => 'required|string',
            'id_profesional' => 'required|integer',
            'id_lugar_atencion' => 'required|integer',
        ]);

        $sitio = SitioWeb::publicados()
            ->with(['usuario.profesional', 'usuario.institucion'])
            ->where('slug', $request->input('slug'))
            ->first();

        if (! $sitio) {
            return $this->respuestaAgendaBloqueada('No encontramos esta página pública.');
        }

        $idLugar = (int) $request->input('id_lugar_atencion');
        $idProfesional = (int) $request->input('id_profesional');
        $lugares = $sitio->lugaresPublicos();

        if (! $lugares->contains(fn ($lugar) => (int) $lugar->id === $idLugar)) {
            return $this->respuestaAgendaBloqueada('El lugar no pertenece a este sitio.');
        }

        if ($sitio->esProfesional()) {
            $profesional = optional($sitio->usuario)->profesional;
            if (! $profesional || (int) $profesional->id !== $idProfesional) {
                return $this->respuestaAgendaBloqueada('El profesional no corresponde a este sitio.');
            }
            if (! in_array($idLugar, SitioWeb::idsLugaresDelProfesional($profesional), true)) {
                return $this->respuestaAgendaBloqueada('El profesional no atiende en este lugar.');
            }

            return null;
        }

        $atiendeAqui = Profesional::where('id', $idProfesional)
            ->whereHas('LugaresAtencion', function ($query) use ($idLugar) {
                $query->where('lugares_atencion.id', $idLugar)
                    ->where(function ($pivot) {
                        $pivot->where('profesionales_lugares_atencion.estado', 1)
                            ->orWhereNull('profesionales_lugares_atencion.estado');
                    });
            })
            ->exists();

        if (! $atiendeAqui) {
            return $this->respuestaAgendaBloqueada('El profesional no atiende en este lugar.');
        }

        return null;
    }

    private function respuestaAgendaBloqueada(string $mensaje)
    {
        return response()->json([
            'estado' => 0,
            'msj' => $mensaje,
            'horarios' => [],
        ], 403);
    }

    public static function fotoProfesional(?Profesional $profesional): string
    {
        if ($profesional && $profesional->foto_perfil) {
            return storage_public_url($profesional->foto_perfil) ?: asset('images/iconos/usuario_profesional.svg');
        }

        return asset('images/iconos/usuario_profesional.svg');
    }

    public static function direccionLugar(LugarAtencion $lugar): string
    {
        $direccion = $lugar->Direccion;
        $ciudad = optional($direccion)->Ciudad;
        $region = optional($ciudad)->Region;

        return trim(collect([
            optional($direccion)->direccion,
            optional($direccion)->numero_dir,
            optional($ciudad)->nombre,
            optional($region)->nombre,
        ])->filter()->implode(', '));
    }

    public static function textoHorario($horarios, int $idLugar, ?int $idProfesional = null): string
    {
        $dias = [0 => 'dom', 1 => 'lun', 2 => 'mar', 3 => 'mié', 4 => 'jue', 5 => 'vie', 6 => 'sáb', 7 => 'dom'];

        $textos = $horarios
            ->filter(function ($horario) use ($idLugar, $idProfesional) {
                if ((int) $horario->id_lugar_atencion !== $idLugar) {
                    return false;
                }

                return $idProfesional === null || (int) $horario->id_profesional === $idProfesional;
            })
            ->map(function ($horario) use ($dias) {
                $nombres = collect(explode(',', (string) $horario->dia))
                    ->map(fn ($dia) => $dias[(int) trim($dia)] ?? null)
                    ->filter()
                    ->unique()
                    ->join(', ');
                $inicio = substr((string) $horario->hora_inicio, 0, 5);
                $termino = substr((string) $horario->hora_termino, 0, 5);

                return trim($nombres.' '.$inicio.'–'.$termino);
            })
            ->filter()
            ->unique()
            ->values();

        return $textos->isEmpty() ? 'Sin horario cargado en este lugar' : $textos->join(' · ');
    }
}

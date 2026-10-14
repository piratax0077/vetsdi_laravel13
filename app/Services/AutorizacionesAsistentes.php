<?php

namespace App\Services;

use App\Models\{AdminInstServ, Asistente, AsistenteLugarAtencion, AutorizacionAsistente, ContratoDependiente, Instituciones, LugarAtencion, Profesional, ProfesionalesLugaresAtencion, User};
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AutorizacionesAsistentes
{
    public function esAsistente(?User $user): bool
    {
        return $user && $user->hasAnyRole(config('asistentes.roles')) && !$user->hasAnyRole(['Admin', 'admin', 'Profesional', 'Institucion', 'Adm_Institucion']);
    }

    public function vinculado(int $asistente, int $lugar, ?int $profesional = null): bool
    {
        $asociado = AsistenteLugarAtencion::where('id_asistente', $asistente)
            ->where('id_lugar_atencion', $lugar)->where('estado', 1);
        if ($profesional) {
            $asociado->where(fn ($q) => $q->where('id_profesional', $profesional)->orWhereNull('id_profesional')->orWhere('id_profesional', 0));
        }
        return $asociado->exists() || ContratoDependiente::where('id_empleado', $asistente)
            ->where('id_lugar_atencion', $lugar)->where('estado', 2)
            ->where('tipo_empleado', 'like', 'ASISTENTE%')->exists();
    }

    public function contextos(Asistente $asistente): Collection
    {
        $lugares = AsistenteLugarAtencion::where('id_asistente', $asistente->id)->where('estado', 1)->pluck('id_lugar_atencion')
            ->merge(ContratoDependiente::where('id_empleado', $asistente->id)->where('estado', 2)->where('tipo_empleado', 'like', 'ASISTENTE%')->pluck('id_lugar_atencion'))->unique();
        $contextos = collect();
        foreach (LugarAtencion::whereIn('id', $lugares)->get() as $lugar) {
            $ids = ProfesionalesLugaresAtencion::where('id_lugar_atencion', $lugar->id)->where('estado', 1)->pluck('id_profesional');
            foreach (Profesional::whereIn('id', $ids)->orderBy('apellido_uno')->get() as $profesional) {
                if ($this->vinculado($asistente->id, $lugar->id, $profesional->id)) {
                    $contextos->push([
                        'id_lugar_atencion' => (int) $lugar->id, 'id_profesional' => (int) $profesional->id,
                        'lugar' => $lugar->nombre, 'profesional' => trim($profesional->nombre.' '.$profesional->apellido_uno.' '.$profesional->apellido_dos),
                    ]);
                }
            }
        }
        return $contextos;
    }

    public function validarContexto(Asistente $asistente, int $lugar, int $profesional): void
    {
        abort_unless($lugar && $profesional && $this->vinculado($asistente->id, $lugar, $profesional), 403, 'No tiene una vinculación activa con este lugar y profesional.');
        abort_unless(ProfesionalesLugaresAtencion::where('id_lugar_atencion', $lugar)->where('id_profesional', $profesional)->where('estado', 1)->exists(), 403);
    }

    public function permisos(int $asistente, int $lugar, int $profesional): array
    {
        // As in med-serv, an explicit centre policy takes priority, including revocations.
        $registro = AutorizacionAsistente::where('id_asistente', $asistente)->where('id_lugar_atencion', $lugar)
            ->whereIn('id_profesional', [0, $profesional])->orderBy('id_profesional')->first();
        return array_replace(array_fill_keys(array_keys(config('asistentes.permisos')), false),
            array_map(fn ($valor) => (bool) $valor, $registro?->permisos ?? []));
    }

    public function exigir(Asistente $asistente, int $lugar, int $profesional, string $permiso): void
    {
        $this->validarContexto($asistente, $lugar, $profesional);
        abort_unless($this->permisos($asistente->id, $lugar, $profesional)[$permiso] ?? false, 403, 'Esta función no está autorizada por su profesional o centro médico.');
    }

    public function ambitosAdministrador(User $user): Collection
    {
        $ambitos = collect();
        $profesional = Profesional::where('id_usuario', $user->id)->first();
        if ($profesional) {
            foreach (ProfesionalesLugaresAtencion::where('id_profesional', $profesional->id)->where('estado', 1)->get() as $relacion) {
                $ambitos->push(['id_lugar_atencion' => (int) $relacion->id_lugar_atencion, 'id_profesional' => (int) $profesional->id]);
            }
        }
        $responsables = AdminInstServ::where('id_admin', $user->id)->pluck('id');
        $centros = Instituciones::where(fn ($q) => $q->where('id_usuario', $user->id)->orWhereIn('id_responsable', $responsables))->get();
        foreach ($centros as $centro) {
            $lugares = DB::table('sucursal')->where('id_institucion', $centro->id)->where('estado', 1)->pluck('id_lugar_atencion')
                ->merge(ProfesionalesLugaresAtencion::where('id_institucion', $centro->id)->where('estado', 1)->pluck('id_lugar_atencion'))
                ->push($centro->id_lugar_atencion)->filter()->unique();
            foreach ($lugares as $lugar) {
                $ambitos->push(['id_lugar_atencion' => (int) $lugar, 'id_profesional' => 0]);
            }
        }
        return $ambitos->unique(fn ($a) => $a['id_lugar_atencion'].':'.$a['id_profesional'])->values();
    }

    public function guardar(User $user, Asistente $asistente, int $lugar, int $profesional, array $permisos): AutorizacionAsistente
    {
        abort_unless($this->ambitosAdministrador($user)->contains(fn ($a) => $a['id_lugar_atencion'] === $lugar && $a['id_profesional'] === $profesional), 403, 'Solo el profesional responsable o el administrador del centro puede autorizar.');
        abort_unless($this->vinculado($asistente->id, $lugar, $profesional ?: null), 403, 'El asistente no tiene una vinculación activa en este lugar.');
        $valores = [];
        foreach (config('asistentes.permisos') as $clave => $etiqueta) {
            $valores[$clave] = filter_var($permisos[$clave] ?? false, FILTER_VALIDATE_BOOLEAN);
        }
        return DB::transaction(function () use ($user, $asistente, $lugar, $profesional, $valores) {
            Asistente::whereKey($asistente->id)->lockForUpdate()->firstOrFail();
            return AutorizacionAsistente::updateOrCreate([
                'id_asistente' => $asistente->id, 'id_lugar_atencion' => $lugar, 'id_profesional' => $profesional,
            ], ['id_usuario_autoriza' => $user->id, 'permisos' => $valores]);
        });
    }
}

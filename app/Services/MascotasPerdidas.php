<?php

namespace App\Services;

use App\Models\{Mascota, Paciente};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\{Rule, ValidationException};

class MascotasPerdidas
{
    public function tutor(): Paciente
    {
        abort_unless(auth()->check(), 401);
        return Paciente::where('id_usuario', auth()->id())->firstOrFail();
    }

    private function mascota(int $id, int $tutor): Mascota
    {
        return Mascota::where('id_responsable', $tutor)->seleccionables()->lockForUpdate()->findOrFail($id);
    }

    private function fotos(Request $r, int $tutor, string $campo, int $id): void
    {
        foreach ($r->input('fotos', []) as $foto) {
            $q = DB::table('vet_perdidas_fotos')->where('id', $foto)->where('tutor_id', $tutor)
                ->whereNull('aviso_id')->whereNull('respuesta_id');
            if (!$q->update([$campo => $id, 'updated_at' => now()])) {
                throw ValidationException::withMessages(['fotos' => 'Una foto no está disponible. Vuelve a cargarla.']);
            }
        }
    }

    private function reglasFotos(): array
    {
        return ['fotos' => 'sometimes|array|max:8', 'fotos.*' => 'required|integer|distinct'];
    }

    public function publicar(Request $r): int
    {
        $tutor = $this->tutor();
        $d = $r->validate([
            'token' => 'required|uuid', 'estado' => 'required|in:perdida,encontrada',
            'mascota_id' => 'nullable|required_if:estado,perdida|integer',
            'nombre' => 'nullable|string|max:100', 'especie' => 'required_if:estado,encontrada|nullable|string|max:100', 'raza' => 'nullable|string|max:100',
            'region_id' => 'required|integer|exists:regiones,id',
            'comuna_id' => ['required', 'integer', Rule::exists('ciudades', 'id')->where('id_region', $r->input('region_id'))],
            'direccion' => 'required|string|max:500', 'latitud' => 'required|numeric|between:-90,90', 'longitud' => 'required|numeric|between:-180,180',
            'fecha' => 'required|date_format:Y-m-d|before_or_equal:today', 'hora' => 'nullable|date_format:H:i',
            'descripcion' => 'required|string|max:2000', 'recompensa' => 'required|boolean',
            'recompensa_monto' => ['nullable', Rule::requiredIf($r->boolean('recompensa') && $r->input('estado') === 'perdida'), 'integer', 'min:1', 'max:100000000'],
        ] + $this->reglasFotos());
        return DB::transaction(function () use ($r, $tutor, $d) {
            // Serializa publicaciones/reintentos del mismo tutor.
            Paciente::whereKey($tutor->id)->lockForUpdate()->firstOrFail();
            $previo = DB::table('vet_perdidas_avisos')->where('token', $d['token'])->first();
            if ($previo) { abort_unless($previo->tutor_id == $tutor->id, 403); return $previo->id; }
            $mascota = $d['estado'] === 'perdida' ? $this->mascota((int)$d['mascota_id'], $tutor->id) : null;
            if ($mascota && DB::table('vet_perdidas_avisos')->where('mascota_id', $mascota->id)->whereNull('cerrado_en')->exists()) {
                throw ValidationException::withMessages(['mascota_id' => 'Esta mascota ya tiene un aviso activo. Revísalo en Mis avisos.']);
            }
            $especie = $mascota ? ($mascota->especieMascota?->nombre ?: $mascota->especie ?: 'Otra') : $d['especie'];
            $id = DB::table('vet_perdidas_avisos')->insertGetId([
                'tutor_id' => $tutor->id, 'token' => $d['token'], 'mascota_id' => $mascota?->id, 'estado' => $d['estado'],
                'foto_perfil' => $mascota?->foto_url,
                'nombre' => $mascota?->nombre ?: ($d['nombre'] ?? 'Sin nombre'), 'especie' => $especie,
                'raza' => $mascota ? ($mascota->razaMascota?->nombre ?: 'Sin registro') : ($d['raza'] ?? 'Sin registro'),
                'region_id' => $d['region_id'], 'comuna_id' => $d['comuna_id'], 'direccion' => $d['direccion'],
                'latitud' => $d['latitud'], 'longitud' => $d['longitud'], 'fecha' => $d['fecha'], 'hora' => $d['hora'] ?? null,
                'descripcion' => $d['descripcion'], 'recompensa_monto' => $r->boolean('recompensa') && $d['estado'] === 'perdida' ? $d['recompensa_monto'] : null,
                'created_at' => now(), 'updated_at' => now(),
            ]);
            $this->fotos($r, $tutor->id, 'aviso_id', $id);
            if ($mascota) { $mascota->perdida = true; $mascota->save(); }
            return $id;
        });
    }

    public function responder(Request $r, int $id): int
    {
        $tutor = $this->tutor();
        return DB::transaction(function () use ($r, $id, $tutor) {
            $aviso = DB::table('vet_perdidas_avisos')->where('id', $id)->lockForUpdate()->first();
            abort_unless($aviso, 404); abort_if($aviso->tutor_id == $tutor->id, 403);
            $esMia = $aviso->estado === 'encontrada';
            $d = $r->validate([
                'token' => 'required|uuid', 'tipo' => 'required|in:'.($esMia ? 'es_mia' : 'la_vio,la_tiene'),
                'lugar' => ($esMia ? 'nullable' : 'required').'|string|max:500',
                'latitud' => ($esMia ? 'nullable' : 'required').'|numeric|between:-90,90',
                'longitud' => ($esMia ? 'nullable' : 'required').'|numeric|between:-180,180',
                'fecha' => ($esMia ? 'nullable' : 'required').'|date_format:Y-m-d|before_or_equal:today',
                'hora' => 'nullable|date_format:H:i', 'mensaje' => 'required|string|max:2000',
                'sena' => ($esMia ? 'required' : 'nullable').'|string|max:500',
                'mascota_id' => 'nullable|integer', 'compartir_telefono' => 'sometimes|boolean',
            ] + $this->reglasFotos());
            $previo = DB::table('vet_perdidas_respuestas')->where('token', $d['token'])->first();
            if ($previo) { abort_unless($previo->tutor_id == $tutor->id && $previo->aviso_id == $id, 403); return $previo->id; }
            if ($aviso->cerrado_en) throw ValidationException::withMessages(['aviso' => 'Este aviso ya está cerrado.']);
            if (!empty($d['mascota_id'])) $this->mascota((int)$d['mascota_id'], $tutor->id);
            if ($r->boolean('compartir_telefono') && !$tutor->telefono_uno) throw ValidationException::withMessages(['compartir_telefono' => 'Registra tu teléfono en tu perfil antes de compartirlo.']);
            unset($d['fotos']);
            $d['aviso_id'] = $id; $d['tutor_id'] = $tutor->id;
            $d['compartir_telefono'] = $r->boolean('compartir_telefono'); $d['created_at'] = $d['updated_at'] = now();
            $respuesta = DB::table('vet_perdidas_respuestas')->insertGetId($d);
            $this->fotos($r, $tutor->id, 'respuesta_id', $respuesta);
            $this->notificar($aviso->tutor_id, $id, $respuesta, 'respuesta');
            return $respuesta;
        });
    }

    private function notificar(int $tutor, int $aviso, ?int $respuesta, string $tipo): void
    {
        DB::table('vet_perdidas_notificaciones')->insert(['tutor_id' => $tutor, 'aviso_id' => $aviso,
            'respuesta_id' => $respuesta, 'tipo' => $tipo, 'created_at' => now(), 'updated_at' => now()]);
    }

    public function compartir(int $id): void
    {
        $tutor = $this->tutor();
        DB::transaction(function () use ($id, $tutor) {
            $r = DB::table('vet_perdidas_respuestas')->where('id', $id)->lockForUpdate()->first(); abort_unless($r, 404);
            $a = DB::table('vet_perdidas_avisos')->where('id', $r->aviso_id)->first(); abort_unless($a->tutor_id == $tutor->id, 403);
            if (!$tutor->telefono_uno) throw ValidationException::withMessages(['telefono' => 'Registra tu teléfono en tu perfil antes de compartirlo.']);
            if (!$r->contacto_compartido_en) {
                DB::table('vet_perdidas_respuestas')->where('id', $id)->update(['contacto_compartido_en' => now(), 'updated_at' => now()]);
                $this->notificar($r->tutor_id, $a->id, $id, 'contacto_compartido');
            }
        });
    }

    public function cerrar(int $id): void
    {
        $tutor = $this->tutor();
        DB::transaction(function () use ($id, $tutor) {
            $a = DB::table('vet_perdidas_avisos')->where('id', $id)->lockForUpdate()->first(); abort_unless($a, 404);
            abort_unless($a->tutor_id == $tutor->id, 403);
            if ($a->cerrado_en) return;
            DB::table('vet_perdidas_avisos')->where('id', $id)->update(['cerrado_en' => now(), 'updated_at' => now()]);
            if ($a->mascota_id && !DB::table('vet_perdidas_avisos')->where('mascota_id', $a->mascota_id)->whereNull('cerrado_en')->exists()) {
                DB::table('mascotas')->where('id', $a->mascota_id)->update(['perdida' => false]);
            }
            foreach (DB::table('vet_perdidas_respuestas')->where('aviso_id', $id)->distinct()->pluck('tutor_id') as $destino) $this->notificar($destino, $id, null, 'aviso_cerrado');
        });
    }

    public function pantalla(): array
    {
        $tutor = $this->tutor();
        $fotos = DB::table('vet_perdidas_fotos')->whereNotNull('aviso_id')->get()->groupBy('aviso_id');
        $base = DB::table('vet_perdidas_avisos as a')->join('pacientes as p', 'p.id', '=', 'a.tutor_id')
            ->join('ciudades as c', 'c.id', '=', 'a.comuna_id')->select('a.*', 'p.nombres as tutor', 'c.nombre as comuna')->orderByDesc('a.id');
        $proyectar = function ($a) use ($fotos, $tutor) {
            $f = ($fotos[$a->id] ?? collect())->map(fn ($f) => route('paciente.perdidas.foto', $f->id))->all();
            return (array)$a + ['propio' => $a->tutor_id == $tutor->id, 'foto' => $f[0] ?? ($a->foto_perfil ?: asset('images/iconos/mascotas.svg')),
                'fotos' => $f, 'recompensa' => $a->recompensa_monto, 'detalle' => $a->descripcion];
        };
        $avisos = (clone $base)->whereNull('a.cerrado_en')->get()->map($proyectar);
        $misAvisos = (clone $base)->where('a.tutor_id', $tutor->id)->get()->map(function ($a) use ($proyectar) {
            $d = $proyectar($a);
            $d['respuestas'] = DB::table('vet_perdidas_respuestas as r')->join('pacientes as p', 'p.id', '=', 'r.tutor_id')
                ->where('r.aviso_id', $a->id)->select('r.*', 'p.nombres as tutor', 'p.telefono_uno as telefono')->orderByDesc('r.id')->get()
                ->map(function ($r) {
                    $f = DB::table('vet_perdidas_fotos')->where('respuesta_id', $r->id)->pluck('id')->map(fn ($id) => route('paciente.perdidas.foto', $id))->all();
                    return array_merge((array)$r, ['telefono' => $r->compartir_telefono ? $r->telefono : null, 'fecha_avistamiento' => $r->fecha, 'fecha' => $r->created_at,
                        'mascota_nombre' => $r->mascota_id ? Mascota::whereKey($r->mascota_id)->value('nombre') : null,
                        'fotos' => $f, 'foto' => $f[0] ?? null]);
                })->all();
            return $d;
        });
        $misRespuestas = DB::table('vet_perdidas_respuestas as r')->join('vet_perdidas_avisos as a', 'a.id', '=', 'r.aviso_id')
            ->join('pacientes as p', 'p.id', '=', 'a.tutor_id')->where('r.tutor_id', $tutor->id)
            ->select('r.id', 'r.tipo', 'r.mensaje', 'r.created_at', 'r.contacto_compartido_en', 'a.nombre', 'a.cerrado_en', 'p.nombres as tutor', 'p.telefono_uno as telefono')->orderByDesc('r.id')->get()
            ->map(function ($r) { if (!$r->contacto_compartido_en) $r->telefono = null; return $r; });
        $notificaciones = DB::table('vet_perdidas_notificaciones as n')->join('vet_perdidas_avisos as a', 'a.id', '=', 'n.aviso_id')
            ->where('n.tutor_id', $tutor->id)->select('n.id', 'n.tipo', 'n.created_at', 'n.leida_en', 'a.nombre')->orderByDesc('n.id')->limit(50)->get();
        return compact('avisos', 'misAvisos', 'misRespuestas', 'notificaciones');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Direccion;
use App\Models\Ciudad;
use App\Models\Mascota;
use App\Models\Paciente;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use App\Services\SdiRegistry;

class ComercioSsoController extends Controller
{
    public function farmacia(): RedirectResponse
    {
        $user = Auth::user();
        abort_unless($user, 401);

        $paciente = Paciente::where('id_usuario', $user->id)->first();
        $direccion = $paciente?->id_direccion ? Direccion::find($paciente->id_direccion) : null;
        $mascotas = $paciente
            ? Mascota::where('id_responsable', $paciente->id)
                ->where(function ($query) { $query->whereNull('estado')->orWhere('estado', '<>', 0); })
                ->orderBy('nombre')->get(['id', 'nombre'])
                ->map(fn (Mascota $mascota) => ['id' => (int) $mascota->id, 'name' => (string) $mascota->nombre])->values()->all()
            : [];
        $payload = [
            'iss' => 'vet-sdi', 'aud' => 'veterfarma', 'iat' => now()->timestamp,
            'exp' => now()->addMinutes(2)->timestamp, 'nonce' => bin2hex(random_bytes(16)),
            'user_id' => (int) $user->id,
            'name' => trim((string) ($paciente?->nombres ?: $user->name)),
            'last_name' => trim((string) (($paciente?->apellido_uno ?? '').' '.($paciente?->apellido_dos ?? ''))),
            'email' => mb_strtolower(trim((string) $user->email)),
            'phone' => $paciente?->telefono_uno ?: $paciente?->telefono_dos,
            'address' => $this->textoDireccion($direccion),
            'pets' => $mascotas,
            'pets_url' => route('paciente.mis_mascotas'),
        ];
        abort_if(empty($payload['email']), 422, 'Tu cuenta VET SDI no tiene correo electrónico registrado.');
        $encoded = rtrim(strtr(base64_encode(json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)), '+/', '-_'), '=');
        $signature = hash_hmac('sha256', $encoded, (string) config('services.sdi_sso.key'));
        $url = rtrim((string) config('services.sdi_sso.farmacia_url'), '/');
        abort_if($url === '', 503, 'La conexión con Veterfarma no está configurada.');
        return redirect()->away($url.'?payload='.rawurlencode($encoded).'&signature='.rawurlencode($signature));
    }
    public function alimentos(SdiRegistry $registry): RedirectResponse
    {
        $user = Auth::user();
        abort_unless($user, 401);

        $paciente = Paciente::where('id_usuario', $user->id)->first();
        $direccion = $paciente?->id_direccion
            ? Direccion::find($paciente->id_direccion)
            : null;
        $ciudad = $direccion?->id_ciudad
            ? Ciudad::with('Region')->find($direccion->id_ciudad)
            : null;

        $mascotas = $paciente
            ? Mascota::with(['especieMascota:id,nombre', 'razaMascota:id,nombre'])
                ->where('id_responsable', $paciente->id)
                ->where(function ($query) {
                    $query->whereNull('estado')->orWhere('estado', '<>', 0);
                })
                ->orderBy('id')
                ->get()
                ->map(function (Mascota $mascota) {
                    $especie = $mascota->especieMascota?->nombre
                        ?: $mascota->otra_especie
                        ?: (is_string($mascota->especie) ? $mascota->especie : null);

                    return [
                        'vet_sdi_id' => (int) $mascota->id,
                        'nombre' => (string) $mascota->nombre,
                        'especie' => (string) ($especie ?: 'Otra'),
                        'raza' => $mascota->razaMascota?->nombre,
                        'sexo' => $this->normalizarSexo($mascota->sexo),
                        'fecha_nacimiento' => optional($mascota->fecha_nacimiento)->format('Y-m-d'),
                        'numero_chip' => $mascota->chip,
                        'esterilizado' => (bool) $mascota->esterilizado,
                        'observaciones' => $mascota->enfermedad_cronica,
                        'foto_url' => $this->urlFotoMascota($mascota->foto_perfil),
                    ];
                })
                ->values()
                ->all()
            : [];

        $payload = [
            'iss' => 'vet-sdi',
            'aud' => 'alimentos-vet',
            'iat' => now()->timestamp,
            'exp' => now()->addMinutes(2)->timestamp,
            'nonce' => bin2hex(random_bytes(16)),
            'user_id' => (int) $user->id,
            'name' => trim((string) ($paciente?->nombres ?: $user->name)),
            'email' => mb_strtolower(trim((string) $user->email)),
            'rut' => $paciente?->rut,
            'telefono' => $paciente?->telefono_uno ?: $paciente?->telefono_dos,
            'direccion' => $this->textoDireccion($direccion),
            'region_id' => $ciudad?->id_region,
            'region' => $ciudad?->Region?->nombre,
            'comuna_id' => $ciudad?->id,
            'comuna' => $ciudad?->nombre,
            'mascotas' => $mascotas,
        ];

        abort_if(empty($payload['email']), 422, 'Tu cuenta VET SDI no tiene correo electronico registrado.');

        $tutor = $registry->tutor([
            'rut' => $payload['rut'], 'email' => $payload['email'], 'name' => $payload['name'],
            'phone' => $payload['telefono'], 'source_id' => (string) ($paciente?->id ?? $user->id),
        ]);
        foreach ($mascotas as $mascota) {
            $registry->pet([
                'tutor_uuid' => data_get($tutor, 'tutor.id'), 'tutor_rut' => $payload['rut'], 'tutor_email' => $payload['email'],
                'name' => $mascota['nombre'], 'species' => $mascota['especie'], 'breed' => $mascota['raza'],
                'microchip' => $mascota['numero_chip'], 'source_id' => (string) $mascota['vet_sdi_id'], 'metadata' => $mascota,
            ]);
        }

        $encoded = rtrim(strtr(base64_encode(json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)), '+/', '-_'), '=');
        $signature = hash_hmac('sha256', $encoded, (string) config('services.sdi_sso.key'));
        $url = rtrim((string) config('services.sdi_sso.alimentos_url'), '/');

        return redirect()->away($url.'?payload='.rawurlencode($encoded).'&signature='.rawurlencode($signature));
    }

    private function normalizarSexo($sexo): string
    {
        $valor = mb_strtolower(trim((string) $sexo));

        return match (true) {
            in_array($valor, ['m', 'macho', 'masculino', '1'], true) => 'macho',
            in_array($valor, ['f', 'hembra', 'femenino', '2'], true) => 'hembra',
            default => 'desconocido',
        };
    }

    private function urlFotoMascota(?string $ruta): ?string
    {
        $ruta = str_replace('\\', '/', trim((string) $ruta));
        if ($ruta === '') {
            return null;
        }
        if (str_starts_with($ruta, 'http://') || str_starts_with($ruta, 'https://')) {
            return $ruta;
        }
        if (str_starts_with($ruta, '/storage/')) {
            return url($ruta);
        }
        if (str_starts_with($ruta, 'storage/')) {
            return asset($ruta);
        }
        if (str_contains($ruta, '/')) {
            return asset('storage/' . ltrim($ruta, '/'));
        }

        return asset('storage/imagenes/temp/' . $ruta);
    }

    private function textoDireccion(?Direccion $direccion): ?string
    {
        if (!$direccion) {
            return null;
        }

        $calle = trim((string) $direccion->direccion);
        $numero = trim((string) $direccion->numero_dir);
        if ($numero === '' || preg_match('/(?:^|\\s)' . preg_quote($numero, '/') . '$/u', $calle)) {
            return $calle ?: null;
        }

        return trim($calle . ' ' . $numero);
    }
}

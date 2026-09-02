<?php

namespace App\Http\Controllers;

use App\Models\Mascota;
use App\Models\Paciente;
use App\Models\Profesional;
use App\Models\VeterinaryEmergencyLink;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class VeterinaryEmergencyController extends Controller
{
    public function index(Request $request)
    {
        $user = $this->authenticatedUser($request);
        if (!$user) return $this->unauthorized();

        $role = strtolower((string) $request->query('role', 'patient'));

        if ($role === 'professional') {
            $profesional = Profesional::where('id_usuario', $user->id)->first();
            if (!$profesional) {
                return response()->json(['message' => 'Perfil veterinario no encontrado.'], 403);
            }

            $links = VeterinaryEmergencyLink::with($this->relations())
                ->where('id_profesional', $profesional->id)
                ->whereIn('status', ['pending', 'active'])
                ->latest()
                ->get()
                ->map(fn ($link) => $this->formatLink($link))
                ->values();

            return response()->json(['role' => 'professional', 'links' => $links]);
        }

        $tutor = Paciente::where('id_usuario', $user->id)->first();
        if (!$tutor) {
            return response()->json(['message' => 'Perfil tutor no encontrado.'], 403);
        }

        $link = VeterinaryEmergencyLink::with($this->relations())
            ->where('id_tutor', $tutor->id)
            ->whereIn('status', ['pending', 'active'])
            ->orderByRaw("CASE WHEN status = 'active' THEN 0 ELSE 1 END")
            ->latest()
            ->first();

        return response()->json([
            'role' => 'patient',
            'link' => $link ? $this->formatLink($link) : null,
        ]);
    }

    public function pets(Request $request)
    {
        $user = $this->authenticatedUser($request);
        if (!$user) return $this->unauthorized();

        $profesional = Profesional::where('id_usuario', $user->id)->first();
        if (!$profesional) {
            return response()->json(['message' => 'Perfil veterinario no encontrado.'], 403);
        }

        $query = mb_strtolower(trim((string) $request->query('q', '')));
        if (mb_strlen($query) < 2) return response()->json([]);
        $rut = preg_replace('/[^0-9k]/i', '', $query);

        $pets = Mascota::with(['especieMascota:id,nombre', 'Responsable:id,rut,nombres,apellido_uno,apellido_dos,email'])
            ->where('estado', 1)
            ->vivas()
            ->where(function ($builder) use ($query, $rut) {
                $builder->whereRaw('LOWER(nombre) LIKE ?', ["%{$query}%"])
                    ->orWhereRaw("LOWER(COALESCE(chip, '')) LIKE ?", ["%{$query}%"])
                    ->orWhereHas('Responsable', function ($responsable) use ($query, $rut) {
                        $responsable->whereRaw('LOWER(nombres) LIKE ?', ["%{$query}%"])
                            ->orWhereRaw('LOWER(apellido_uno) LIKE ?', ["%{$query}%"])
                            ->orWhereRaw('LOWER(email) LIKE ?', ["%{$query}%"]);
                        if ($rut !== '') {
                            $responsable->orWhereRaw("REPLACE(REPLACE(LOWER(rut), '.', ''), '-', '') LIKE ?", ["%{$rut}%"]);
                        }
                    });
            })
            ->orderBy('nombre')
            ->limit(20)
            ->get()
            ->map(function ($pet) {
                $tutor = $pet->Responsable;
                return [
                    'id' => $pet->id,
                    'name' => $pet->nombre,
                    'species' => $pet->especieMascota?->nombre ?? $pet->especie,
                    'chip' => $pet->chip,
                    'tutor' => $tutor ? trim($tutor->nombres.' '.$tutor->apellido_uno.' '.$tutor->apellido_dos) : '',
                ];
            })
            ->values();

        return response()->json($pets);
    }

    public function storeLink(Request $request)
    {
        $user = $this->authenticatedUser($request);
        if (!$user) return $this->unauthorized();

        if ($request->filled('pet_id')) {
            $profesional = Profesional::where('id_usuario', $user->id)->first();
            $mascota = Mascota::whereKey($request->integer('pet_id'))->where('estado', 1)->vivas()->first();
            if (!$profesional || !$mascota) {
                return response()->json(['message' => 'Mascota o perfil veterinario no válido.'], 422);
            }

            $link = VeterinaryEmergencyLink::updateOrCreate(
                ['id_mascota' => $mascota->id, 'id_profesional' => $profesional->id],
                ['id_tutor' => $mascota->id_responsable, 'status' => 'pending', 'requested_by' => 'professional']
            );
        } else {
            $tutor = Paciente::where('id_usuario', $user->id)->first();
            $profesional = Profesional::find($request->integer('professional_id'));
            $mascota = $tutor
                ? Mascota::where('id_responsable', $tutor->id)->where('estado', 1)->vivas()->orderBy('nombre')->first()
                : null;
            if (!$tutor || !$profesional || !$mascota) {
                return response()->json(['message' => 'Debe tener una mascota registrada y elegir un veterinario válido.'], 422);
            }

            $link = VeterinaryEmergencyLink::updateOrCreate(
                ['id_mascota' => $mascota->id, 'id_profesional' => $profesional->id],
                ['id_tutor' => $tutor->id, 'status' => 'pending', 'requested_by' => 'patient']
            );
        }

        return response()->json(['link' => $this->formatLink($link->load($this->relations()))], 201);
    }

    public function decide(Request $request, int $id)
    {
        $user = $this->authenticatedUser($request);
        if (!$user) return $this->unauthorized();
        $link = VeterinaryEmergencyLink::find($id);
        if (!$link || !$this->mayDecide($user->id, $link)) {
            return response()->json(['message' => 'Solicitud no disponible.'], 404);
        }

        $decision = $request->input('decision');
        if (!in_array($decision, ['accept', 'reject'], true)) {
            return response()->json(['message' => 'Decisión no válida.'], 422);
        }

        $link->status = $decision === 'accept' ? 'active' : 'rejected';
        $link->save();
        return response()->json(['link' => $this->formatLink($link->load($this->relations()))]);
    }

    public function destroy(Request $request, int $id)
    {
        $user = $this->authenticatedUser($request);
        if (!$user) return $this->unauthorized();
        $link = VeterinaryEmergencyLink::find($id);
        if (!$link || !$this->belongsToUser($user->id, $link)) {
            return response()->json(['message' => 'Vínculo no encontrado.'], 404);
        }
        $link->status = 'revoked';
        $link->save();
        return response()->json(['message' => 'Vínculo retirado.']);
    }

    public function alert(Request $request)
    {
        $user = $this->authenticatedUser($request);
        if (!$user) return $this->unauthorized();
        return response()->json(['message' => 'Alerta veterinaria registrada.', 'estado' => 1]);
    }

    private function authenticatedUser(Request $request)
    {
        $token = $request->header('X-Auth-Token');
        return $token ? PersonalAccessToken::findToken($token)?->tokenable : null;
    }

    private function unauthorized()
    {
        return response()->json(['message' => 'Sesión no válida.'], 401);
    }

    private function relations(): array
    {
        return [
            'mascota.especieMascota:id,nombre',
            'tutor:id,nombres,apellido_uno,apellido_dos,telefono_uno',
            'profesional.Especialidad:id,nombre',
            'profesional.TipoEspecialidad:id,nombre',
        ];
    }

    private function formatLink(VeterinaryEmergencyLink $link): array
    {
        $pet = $link->mascota;
        $tutor = $link->tutor;
        $professional = $link->profesional;
        return [
            'id' => $link->id,
            'status' => $link->status,
            'requested_by' => $link->requested_by,
            'phone' => $tutor?->telefono_uno,
            'pet' => [
                'id' => $pet?->id,
                'name' => $pet?->nombre,
                'species' => $pet?->especieMascota?->nombre ?? $pet?->especie,
                'chip' => $pet?->chip,
                'tutor' => $tutor ? trim($tutor->nombres.' '.$tutor->apellido_uno.' '.$tutor->apellido_dos) : '',
            ],
            'professional' => [
                'id' => $professional?->id,
                'name' => $professional?->nombreCompleto() ?: 'Veterinario',
                'specialty' => $professional?->TipoEspecialidad?->nombre
                    ?? $professional?->Especialidad?->nombre
                    ?? 'Medicina veterinaria',
                'phone' => $professional?->telefono,
            ],
        ];
    }

    private function mayDecide(int $userId, VeterinaryEmergencyLink $link): bool
    {
        if ($link->requested_by === 'patient') {
            return Profesional::where('id', $link->id_profesional)->where('id_usuario', $userId)->exists();
        }
        return Paciente::where('id', $link->id_tutor)->where('id_usuario', $userId)->exists();
    }

    private function belongsToUser(int $userId, VeterinaryEmergencyLink $link): bool
    {
        return Profesional::where('id', $link->id_profesional)->where('id_usuario', $userId)->exists()
            || Paciente::where('id', $link->id_tutor)->where('id_usuario', $userId)->exists();
    }
}

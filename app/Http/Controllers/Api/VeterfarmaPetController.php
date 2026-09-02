<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{Mascota, Paciente, User};
use Illuminate\Http\{JsonResponse, Request};

class VeterfarmaPetController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        if (! hash_equals((string) config('services.sdi_sso.key'), (string) $request->input('_integration_key'))) {
            return response()->json(['message' => 'No fue posible validar la conexión con VET-SDI.'], 403);
        }

        $data = $request->validate([
            'user_id' => 'required|integer',
            'email' => 'required|email',
            'name' => 'required|string|max:100',
            'species_id' => 'required|integer|between:1,8',
            'sex' => 'required|in:M,F',
        ]);

        // La identidad ya fue firmada por VET-SDI. El ID es la referencia canónica;
        // el correo puede haber cambiado desde que se abrió la sesión en Veterfarma.
        $user = User::find($data['user_id']);
        if (! $user || mb_strtolower((string) $user->email) !== mb_strtolower($data['email'])) {
            $user = User::whereRaw('LOWER(email) = ?', [mb_strtolower($data['email'])])->first();
        }
        if (! $user) {
            return response()->json(['message' => 'No encontramos al tutor autenticado en VET-SDI.'], 404);
        }

        $patient = Paciente::where('id_usuario', $user->id)->first();
        if (! $patient) {
            return response()->json(['message' => 'El tutor aún no tiene una ficha asociada en VET-SDI.'], 422);
        }

        $existingPet = Mascota::where('id_responsable', $patient->id)
            ->whereRaw('LOWER(TRIM(nombre)) = ?', [mb_strtolower(trim($data['name']))])
            ->where(function ($query) {
                $query->whereNull('estado')->orWhere('estado', '<>', 0);
            })
            ->first();

        if ($existingPet) {
            return response()->json([
                'data' => ['id' => $existingPet->id, 'name' => $existingPet->nombre],
                'existing' => true,
                'message' => $existingPet->nombre.' ya está registrada en VET-SDI.',
            ], 409);
        }

        $pet = Mascota::create([
            'id_responsable' => $patient->id,
            'id_user' => $user->id,
            'nombre' => $data['name'],
            'especie_id' => $data['species_id'],
            'especie' => $data['species_id'],
            'sexo' => $data['sex'],
            'tiene_chip' => false,
            'esterilizado' => false,
            'estado' => 1,
            'origen_registro' => 'veterfarma',
            'ficha_incompleta_veterfarma' => true,
            'vacunas_registro' => [],
            'desparasitaciones_registro' => [],
            'suscripciones_servicios_registro' => [],
            'reservas_servicios_registro' => [],
        ]);

        return response()->json([
            'data' => ['id' => $pet->id, 'name' => $pet->nombre],
            'incomplete' => true,
        ], 201);
    }
}

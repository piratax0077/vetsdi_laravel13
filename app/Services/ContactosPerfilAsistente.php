<?php
namespace App\Services;

use App\Models\{Asistente, AsistenteContactoEmergencia, ContactoEmergencia, Direccion};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB, Schema};

class ContactosPerfilAsistente
{
    public function guardar(Request $request, ?int $idContacto = null): ContactoEmergencia
    {
        $datos = $request->validate([
            'rut' => 'required|string|max:20', 'nombres' => 'required|string|max:255',
            'apellido_uno' => 'required|string|max:255', 'apellido_dos' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255', 'telefono' => 'nullable|string|max:30',
            'id_ciudad' => 'required|integer|exists:ciudades,id', 'direccion' => 'nullable|string|max:255',
            'numero_dir' => 'nullable|string|max:30', 'fecha' => 'nullable|date|before_or_equal:today',
            'parentezco' => 'nullable|string|max:100', 'prioridad' => 'nullable|integer|min:1|max:100',
        ]);
        return DB::transaction(function () use ($request, $datos, $idContacto) {
            $asistente = Asistente::where('id_usuario', Auth::id())->lockForUpdate()->firstOrFail();
            abort_if($request->filled('id_asistente') && (int) $request->id_asistente !== (int) $asistente->id, 403);
            // No reutilizar ni sobrescribir los datos privados de otro perfil por su RUT.
            $consulta = $asistente->ContactosEmergencia();
            $contacto = $idContacto ? $consulta->where('contactos_emergencia.id', $idContacto)->firstOrFail()
                : ($consulta->where('rut', $datos['rut'])->first() ?? new ContactoEmergencia());
            $originalId = $contacto->exists ? $contacto->id : null;
            $compartido = $originalId && (AsistenteContactoEmergencia::where('id_contacto', $originalId)->where('id_asistente', '<>', $asistente->id)->exists()
                || (Schema::hasTable('pacientes_contacto_emergencia') && DB::table('pacientes_contacto_emergencia')->where('id_contacto', $originalId)->exists()));
            $direccion = Direccion::find($contacto->id_direccion) ?? new Direccion();
            if ($compartido) {
                $contacto = $contacto->replicate();
                if ($direccion->exists) $direccion = $direccion->replicate();
            }
            $direccion->direccion = $datos['direccion'] ?? null;
            $direccion->numero_dir = $datos['numero_dir'] ?? null;
            $direccion->id_ciudad = $datos['id_ciudad'];
            $direccion->saveOrFail();
            $contacto->rut = $datos['rut'];
            $contacto->nombre = $datos['nombres'];
            foreach (['apellido_uno', 'apellido_dos', 'email', 'telefono', 'parentezco', 'prioridad'] as $campo) {
                if (array_key_exists($campo, $datos)) $contacto->{$campo} = $datos[$campo];
            }
            if (!empty($datos['fecha'])) $contacto->fecha_nac = $datos['fecha'];
            $contacto->id_direccion = $direccion->id;
            $contacto->saveOrFail();
            if ($compartido) $asistente->ContactosEmergencia()->detach($originalId);
            $asistente->ContactosEmergencia()->syncWithoutDetaching([$contacto->id => ['relacion' => $datos['parentezco'] ?? null]]);
            return $contacto;
        });
    }
}

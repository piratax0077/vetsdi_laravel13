<?php

namespace App\Http\Controllers;

use App\Mail\CorreoGenerico;
use App\Models\{Mascota, MascotaTutoria, Paciente, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Mail};
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TutoriaCompartidaController extends Controller
{
    private function tutor(): Paciente
    {
        abort_unless(auth()->check(), 401);
        return Paciente::where('id_usuario', auth()->id())->firstOrFail();
    }

    private function propia(int $id, Paciente $tutor, bool $bloquear = false): Mascota
    {
        return Mascota::where('id_responsable', $tutor->id)->seleccionables()
            ->when($bloquear, fn ($query) => $query->lockForUpdate())->findOrFail($id);
    }

    private function accesos(Mascota $mascota)
    {
        return MascotaTutoria::where('mascota_id', $mascota->id)->where('titular_id', $mascota->id_responsable);
    }

    private function datos(MascotaTutoria $acceso): array
    {
        return [
            'id' => $acceso->id, 'nombre' => $acceso->nombre, 'correo' => $acceso->correo,
            'rol' => $acceso->rol, 'estado' => $acceso->estadoActual(), 'vence_el' => $acceso->vence_el->toIso8601String(),
        ];
    }

    public function listar(int $mascota): array
    {
        $pet = $this->propia($mascota, $this->tutor());

        return ['invitaciones' => $this->accesos($pet)->where('estado', '!=', 'cancelada')
            ->orderByDesc('id')->get()->map(fn ($acceso) => $this->datos($acceso))];
    }

    private function destinatario(string $rut): array
    {
        $normalizado = strtoupper(preg_replace('/[.\s-]/', '', $rut));
        $persona = Paciente::whereRaw("REPLACE(REPLACE(UPPER(rut), '.', ''), '-', '') = ?", [$normalizado])->first();
        $usuario = $persona?->id_usuario ? User::find($persona->id_usuario) : null;
        if (!$usuario || !filter_var($usuario->email, FILTER_VALIDATE_EMAIL)) {
            throw ValidationException::withMessages(['rut' => 'No encontramos una cuenta de tutor con ese RUT. Ingresa su correo.']);
        }

        return ['id' => $persona->id, 'rut' => $persona->rut, 'email' => strtolower($usuario->email),
            'nombre' => trim($persona->nombres.' '.$persona->apellido_uno)];
    }

    public function buscarRut(Request $request, int $mascota): array
    {
        $this->propia($mascota, $this->tutor());
        $datos = $request->validate(['rut' => 'required|string|max:20']);
        $persona = $this->destinatario($datos['rut']);
        [$nombre, $dominio] = explode('@', $persona['email'], 2);
        $persona['email'] = mb_substr($nombre, 0, 2).'•••@'.$dominio;

        return ['tutor' => $persona];
    }

    public function invitar(Request $request, int $mascota): array
    {
        $tutor = $this->tutor();
        $datos = $request->validate([
            'rut' => 'nullable|string|max:20',
            'correo' => 'nullable|required_without:rut|email:rfc|max:254',
            'rol' => 'required|in:co_tutor,cuidador',
        ]);
        $persona = !empty($datos['rut']) ? $this->destinatario($datos['rut']) : null;
        $correo = $persona['email'] ?? strtolower(trim($datos['correo']));
        if (($persona['id'] ?? null) == $tutor->id || strcasecmp($correo, (string) auth()->user()->email) === 0) {
            throw ValidationException::withMessages(['correo' => 'Ingresa el correo de otra persona.']);
        }

        return DB::transaction(function () use ($mascota, $tutor, $datos, $persona, $correo) {
            $pet = $this->propia($mascota, $tutor, true);
            $acceso = $this->accesos($pet)->where('correo', $correo)->first()
                ?? new MascotaTutoria(['mascota_id' => $pet->id, 'titular_id' => $tutor->id, 'correo' => $correo]);
            if ($acceso->exists && in_array($acceso->estadoActual(), ['activa', 'pendiente'], true)) {
                throw ValidationException::withMessages(['correo' => 'Esta persona ya tiene acceso o una invitación pendiente.']);
            }

            $acceso->fill(['rol' => $datos['rol'], 'nombre' => $persona['nombre'] ?? null]);
            return $this->enviar($acceso, $pet, $tutor);
        });
    }

    private function enviar(MascotaTutoria $acceso, Mascota $mascota, Paciente $tutor): array
    {
        $token = Str::random(64);
        $acceso->fill(['token_hash' => hash('sha256', $token), 'estado' => 'pendiente', 'usuario_id' => null, 'vence_el' => now()->addDays(7)]);
        $acceso->saveOrFail();
        $nombre = trim($tutor->nombres.' '.$tutor->apellido_uno);
        $rol = $acceso->rol === 'cuidador' ? 'cuidador' : 'co-tutor';

        Mail::to($acceso->correo)->send(new CorreoGenerico([
            'blade' => 'invitacion_tutoria_compartida',
            'asunto' => $nombre.' te invitó a ser '.$rol.' de '.$mascota->nombre,
            'body' => ['tutor' => $nombre, 'mascota' => $mascota->nombre, 'rol' => $acceso->rol,
                'enlace' => route('tutoria.invitacion', $token), 'dias' => 7, 'mascota_foto' => $mascota->foto_url],
        ]));

        return ['estado' => 1, 'invitacion' => $this->datos($acceso)];
    }

    public function gestionar(int $mascota, int $id, string $accion): array
    {
        $tutor = $this->tutor();
        return DB::transaction(function () use ($mascota, $id, $accion, $tutor) {
            $pet = $this->propia($mascota, $tutor, true);
            $acceso = $this->accesos($pet)->findOrFail($id);
            $estado = $acceso->estadoActual();
            if ($accion === 'reenviar' && in_array($estado, ['pendiente', 'expirada', 'rechazada'], true)) {
                return $this->enviar($acceso, $pet, $tutor);
            }
            if (($accion === 'cancelar' && in_array($estado, ['pendiente', 'expirada'], true)) || ($accion === 'quitar' && $estado === 'activa')) {
                $acceso->update(['estado' => 'cancelada', 'usuario_id' => null]);
                return ['estado' => 1];
            }

            abort(409, 'La invitación cambió de estado. Vuelve a abrir el modal.');
        });
    }

    public function invitacion(Request $request, string $token)
    {
        $acceso = MascotaTutoria::where('token_hash', hash('sha256', $token))->firstOrFail();
        $mascota = Mascota::findOrFail($acceso->mascota_id);
        $titular = Paciente::find($acceso->titular_id);
        $estado = $mascota->id_responsable == $acceso->titular_id && !$mascota->fallecida && $mascota->estado != 0
            ? $acceso->estadoActual() : 'cancelada';
        if ($estado === 'activa') abort_unless($acceso->usuario_id == auth()->id(), 404);
        if (!$request->user() && $estado === 'pendiente') $request->session()->put('url.intended', $request->url());

        return view('auth.invitacion_tutoria', [
            'invitacion' => ['tutor' => trim(($titular?->nombres ?? '').' '.($titular?->apellido_uno ?? '')),
                'mascota' => $mascota->nombre, 'mascota_foto' => $mascota->foto_url, 'rol' => $acceso->rol, 'estado' => $estado],
            'rutaAceptar' => route('tutoria.responder', [$token, 'aceptar']),
            'rutaRechazar' => route('tutoria.responder', [$token, 'rechazar']),
        ]);
    }

    public function responder(Request $request, string $token, string $accion)
    {
        DB::transaction(function () use ($request, $token, $accion) {
            $acceso = MascotaTutoria::where('token_hash', hash('sha256', $token))->firstOrFail();
            $mascota = Mascota::seleccionables()->lockForUpdate()->findOrFail($acceso->mascota_id);
            abort_unless($mascota->id_responsable == $acceso->titular_id, 404);
            $acceso = MascotaTutoria::whereKey($acceso->id)->where('token_hash', hash('sha256', $token))->lockForUpdate()->firstOrFail();
            abort_unless(strcasecmp($request->user()->email, $acceso->correo) === 0, 403, 'Ingresa con la cuenta que recibió esta invitación.');
            $this->tutor();
            if ($acceso->estadoActual() !== 'pendiente') return;

            $acepta = $accion === 'aceptar';
            $acceso->update(['estado' => $acepta ? 'activa' : 'rechazada', 'usuario_id' => $acepta ? $request->user()->id : null]);
        });

        return redirect()->route('tutoria.invitacion', $token);
    }
}

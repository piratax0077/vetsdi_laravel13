<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegistroCuentaRequest;
use App\Models\User;
use App\Services\CuentasService;
use App\Services\VerificacionCorreoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Registro de una cuenta nueva desde la misma tarjeta del ingreso.
 *
 * Una persona = un registro de usuario. Si el RUT o el correo ya existen no se
 * crea otra cuenta: los roles adicionales se agregan después, al contratar un
 * plan, a través de CuentasService::asignarRol().
 */
class RegistroCuentaController extends Controller
{
    public function __construct(
        private readonly CuentasService $cuentas,
        private readonly VerificacionCorreoService $verificacion,
    ) {
    }

    public function store(RegistroCuentaRequest $request): RedirectResponse
    {
        $datos = $request->validated();

        $existente = $this->buscarCuentaExistente($datos['rut'], $datos['email']);

        if ($existente) {
            return back()
                ->withInput($request->except(['password', 'password_confirmation']))
                ->with('cuenta_existente', [
                    'email' => $existente->email,
                    'verificado' => $existente->correoVerificado(),
                ]);
        }

        $usuario = DB::transaction(function () use ($datos) {
            $usuario = User::create([
                'name' => trim($datos['nombres'].' '.$datos['apellido_uno'].' '.($datos['apellido_dos'] ?? '')),
                'nombres' => $datos['nombres'],
                'apellido_uno' => $datos['apellido_uno'],
                'apellido_dos' => $datos['apellido_dos'] ?? null,
                'email' => $datos['email'],
                'rut' => $datos['rut'],
                'telefono' => $datos['telefono'],
                'password' => Hash::make($datos['password']),
            ]);

            // Primer rol de la cuenta, con el perfil todavía por completar.
            $this->cuentas->asignarRol($usuario, $datos['tipo_cuenta']);

            return $usuario;
        });

        $enviado = $this->verificacion->enviar($usuario);

        return redirect()
            ->route('registro.enviado')
            ->with('registro_pendiente', $usuario->id)
            ->with('correo_enviado', $enviado);
    }

    /** El RUT y el correo son únicos en todo el sistema, sin importar el rol. */
    private function buscarCuentaExistente(string $rut, string $email): ?User
    {
        return User::where('rut', $rut)
            ->orWhereRaw('LOWER(email) = ?', [$email])
            ->first();
    }
}

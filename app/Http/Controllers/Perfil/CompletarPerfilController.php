<?php

namespace App\Http\Controllers\Perfil;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SeleccionCuentaController;
use App\Models\AdminInstServ;
use App\Models\Asistente;
use App\Models\Direccion;
use App\Models\EspecieMascota;
use App\Models\Instituciones;
use App\Models\LugarAtencion;
use App\Models\Mascota;
use App\Models\Paciente;
use App\Models\Prevision;
use App\Models\Profesional;
use App\Models\Region;
use App\Models\TipoEspecialidad;
use App\Models\User;
use App\Rules\RutChileno;
use App\Services\CuentasService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Formulario obligatorio que se pide la primera vez que alguien entra a un rol.
 *
 * Es por rol: tener completo el perfil de tutor no libera el de profesional.
 * El guard que impide saltarse esta pantalla está en el middleware PerfilCompleto.
 *
 * Varios modelos antiguos no declaran $fillable (o lo tienen incompleto), así
 * que los datos se asignan campo a campo, como en el resto del proyecto.
 */
class CompletarPerfilController extends Controller
{
    /** Médico Veterinario es la profesión principal y la que viene elegida. */
    public const ID_ESPECIALIDAD_VETERINARIA = 1;

    /** Centro médico: es el tipo de institución que usa el escritorio de clínica. */
    private const ID_TIPO_INSTITUCION_CLINICA = 1;

    /** Modalidades de trabajo del asistente, con los códigos que ya usa el sistema. */
    public const MODALIDADES = [
        2 => 'Presencial',
        1 => 'Online',
        3 => 'Ambas',
    ];

    public function __construct(private readonly CuentasService $cuentas)
    {
    }

    public function formulario(Request $request, string $tipo): View|RedirectResponse
    {
        $usuario = Auth::user();

        if (! CuentasService::esTipoValido($tipo) || ! $this->cuentas->tieneRol($usuario, $tipo)) {
            return redirect()->route('cuenta.seleccion')
                ->with('mensaje_error', 'No tienes una cuenta de ese tipo.');
        }

        if ($this->cuentas->perfilCompleto($usuario, $tipo)) {
            return redirect()->route($this->cuentas->rutaEscritorio($tipo));
        }

        $request->session()->put(SeleccionCuentaController::SESION_ROL_ACTIVO, $tipo);

        return view('perfil.completar_'.$tipo, array_merge([
            'usuario' => $usuario,
            'tipo' => $tipo,
            'etiqueta' => $this->cuentas->etiqueta($tipo),
        ], $this->catalogos($tipo)));
    }

    public function guardar(Request $request, string $tipo): RedirectResponse
    {
        $usuario = Auth::user();

        if (! CuentasService::esTipoValido($tipo) || ! $this->cuentas->tieneRol($usuario, $tipo)) {
            return redirect()->route('cuenta.seleccion')
                ->with('mensaje_error', 'No tienes una cuenta de ese tipo.');
        }

        if ($this->cuentas->perfilCompleto($usuario, $tipo)) {
            return redirect()->route($this->cuentas->rutaEscritorio($tipo));
        }

        match ($tipo) {
            'tutor' => $this->guardarTutor($request, $usuario),
            'profesional' => $this->guardarProfesional($request, $usuario),
            'asistente' => $this->guardarAsistente($request, $usuario),
            'clinica' => $this->guardarClinica($request, $usuario),
        };

        $this->cuentas->marcarPerfilCompleto($usuario, $tipo);

        return redirect()->route($this->cuentas->rutaEscritorio($tipo))
            ->with('mensaje', 'Listo, tu perfil quedó completo.');
    }

    /** Catálogos que necesita cada formulario. */
    private function catalogos(string $tipo): array
    {
        return match ($tipo) {
            'tutor' => [
                'regiones' => Region::orderBy('nombre')->get(['id', 'nombre']),
                'especies' => EspecieMascota::orderBy('id')->get(['id', 'nombre']),
                'razasPorEspecie' => razas_mascotas_catalogo_por_especie(),
            ],
            'clinica' => [
                'regiones' => Region::orderBy('nombre')->get(['id', 'nombre']),
            ],
            'profesional' => [
                'especialidades' => TipoEspecialidad::where('id_especialidad', self::ID_ESPECIALIDAD_VETERINARIA)
                    ->orderBy('nombre')
                    ->get(['id', 'nombre']),
            ],
            'asistente' => [
                'modalidades' => self::MODALIDADES,
            ],
        };
    }

    /** Tutor: dónde vive y su primera mascota. */
    private function guardarTutor(Request $request, User $usuario): void
    {
        $datos = $request->validate([
            'id_region' => ['required', 'exists:regiones,id'],
            'id_ciudad' => ['required', 'exists:ciudades,id'],
            'direccion' => ['required', 'string', 'max:190'],
            'depto' => ['nullable', 'string', 'max:40'],
            'mascota_nombre' => ['required', 'string', 'max:100'],
            'mascota_especie' => ['required', 'exists:especies_mascotas,id'],
            'mascota_raza' => ['nullable', 'string', 'max:100'],
            'mascota_sexo' => ['required', 'in:M,H'],
            'mascota_fecha_nacimiento' => ['nullable', 'date', 'before_or_equal:today'],
            'mascota_edad_aproximada' => ['nullable', 'integer', 'min:0', 'max:40'],
            'mascota_chip' => ['nullable', 'string', 'max:40'],
        ], $this->mensajes());

        $direccion = $this->guardarDireccion(
            $this->unirDireccion($datos['direccion'], $datos['depto'] ?? null),
            (int) $datos['id_ciudad']
        );

        [$nombres, $apellidoUno, $apellidoDos] = $usuario->partesDelNombre();

        $paciente = $this->perfilDe(Paciente::class, $usuario);
        $paciente->rut = $usuario->rut;
        $paciente->nombres = $nombres;
        $paciente->apellido_uno = $apellidoUno;
        $paciente->apellido_dos = $apellidoDos;
        $paciente->email = $usuario->email;
        $paciente->telefono_uno = $usuario->telefono;
        $paciente->id_direccion = $direccion->id;
        $paciente->token = $paciente->token ?: md5(uniqid((string) $usuario->id, true));
        $paciente->id_prevision = $paciente->id_prevision ?: $this->previsionPorDefecto();
        // Este formulario reemplaza la antigua pantalla de bienvenida del escritorio.
        $paciente->bienvenida = 1;
        $paciente->save();

        // mascotas es MyISAM: no participa de transacciones, por eso se guarda al
        // final, cuando el resto ya quedó bien guardado.
        $mascota = new Mascota();
        $mascota->nombre = $datos['mascota_nombre'];
        $mascota->especie_id = $datos['mascota_especie'];
        $mascota->raza_id = resolver_raza_mascota_id($datos['mascota_raza'] ?? null, (int) $datos['mascota_especie']);
        $mascota->sexo = $datos['mascota_sexo'];
        $mascota->fecha_nacimiento = $this->fechaNacimientoMascota($datos);
        $mascota->chip = $datos['mascota_chip'] ?? null;
        $mascota->tiene_chip = ! empty($datos['mascota_chip']) ? 1 : 0;
        $mascota->id_responsable = $paciente->id;
        $mascota->id_user = $usuario->id;
        $mascota->estado = 1;
        $mascota->origen_registro = 'registro_tutor';
        $mascota->save();
    }

    /** Si no saben la fecha exacta, la edad aproximada sirve igual. */
    private function fechaNacimientoMascota(array $datos): ?string
    {
        if (! empty($datos['mascota_fecha_nacimiento'])) {
            return $datos['mascota_fecha_nacimiento'];
        }

        if (isset($datos['mascota_edad_aproximada']) && $datos['mascota_edad_aproximada'] !== null) {
            return now()->subYears((int) $datos['mascota_edad_aproximada'])->toDateString();
        }

        return null;
    }

    /** Profesional: profesión y, si la tiene, su especialidad veterinaria. */
    private function guardarProfesional(Request $request, User $usuario): void
    {
        $datos = $request->validate([
            'id_especialidad' => ['required', 'exists:especialidades,id'],
            'id_tipo_especialidad' => ['nullable', 'exists:tipos_especialidad,id'],
        ], $this->mensajes());

        [$nombres, $apellidoUno, $apellidoDos] = $usuario->partesDelNombre();

        $profesional = $this->perfilDe(Profesional::class, $usuario);
        $profesional->rut = $usuario->rut;
        $profesional->nombre = $nombres;
        $profesional->apellido_uno = $apellidoUno;
        $profesional->apellido_dos = $apellidoDos;
        $profesional->email = $usuario->email;
        $profesional->telefono_uno = $usuario->telefono;
        $profesional->id_especialidad = $datos['id_especialidad'];
        $profesional->id_tipo_especialidad = $datos['id_tipo_especialidad'] ?: null;
        $profesional->estado = 1;
        $profesional->save();
    }

    /** Asistente: su modalidad de trabajo. */
    private function guardarAsistente(Request $request, User $usuario): void
    {
        $datos = $request->validate([
            'id_modalidad' => ['required', 'in:'.implode(',', array_keys(self::MODALIDADES))],
        ], $this->mensajes());

        [$nombres, $apellidoUno, $apellidoDos] = $usuario->partesDelNombre();

        $asistente = $this->perfilDe(Asistente::class, $usuario);
        $asistente->rut = $usuario->rut;
        $asistente->nombres = $nombres;
        $asistente->apellido_uno = $apellidoUno;
        $asistente->apellido_dos = $apellidoDos;
        $asistente->email = $usuario->email;
        $asistente->telefono_uno = $usuario->telefono;
        $asistente->id_modalidad = $datos['id_modalidad'];
        $asistente->bienvenido = 1;
        $asistente->save();
    }

    /** Clínica: los datos de la empresa, en formato chileno. */
    private function guardarClinica(Request $request, User $usuario): void
    {
        $datos = $request->validate([
            'rut_empresa' => ['required', 'string', new RutChileno],
            'razon_social' => ['required', 'string', 'max:150'],
            'nombre_fantasia' => ['required', 'string', 'max:150'],
            'giro' => ['required', 'string', 'max:150'],
            'id_region' => ['required', 'exists:regiones,id'],
            'id_ciudad' => ['required', 'exists:ciudades,id'],
            'direccion' => ['required', 'string', 'max:190'],
            'telefono' => ['required', 'string', 'regex:/^\+56 [29]\d? \d{4} \d{4}$/'],
            'email_contacto' => ['required', 'email:filter', 'max:255'],
        ], $this->mensajes());

        $rutEmpresa = rut_normalizar($datos['rut_empresa']);
        $responsable = $this->responsableDeLaClinica($usuario);

        $direccion = $this->guardarDireccion($datos['direccion'], (int) $datos['id_ciudad']);

        // Casa matriz: el escritorio de la clínica trabaja sobre un lugar de atención.
        $lugarAtencion = new LugarAtencion();
        $lugarAtencion->nombre = $datos['nombre_fantasia'];
        $lugarAtencion->rut = $rutEmpresa;
        $lugarAtencion->email = correo_normalizar($datos['email_contacto']);
        $lugarAtencion->telefono = $datos['telefono'];
        $lugarAtencion->id_direccion = $direccion->id;
        $lugarAtencion->tipo = 1;
        $lugarAtencion->save();

        // instituciones es MyISAM: se guarda cuando el resto ya existe.
        $institucion = $this->perfilDe(Instituciones::class, $usuario);
        $institucion->nombre = $datos['nombre_fantasia'];
        $institucion->razon_social = $datos['razon_social'];
        $institucion->nombre_fantasia = $datos['nombre_fantasia'];
        $institucion->giro = $datos['giro'];
        $institucion->rut = $rutEmpresa;
        $institucion->email = correo_normalizar($datos['email_contacto']);
        $institucion->telefono = $datos['telefono'];
        $institucion->id_direccion = $direccion->id;
        $institucion->id_lugar_atencion = $lugarAtencion->id;
        $institucion->id_tipo_institucion = self::ID_TIPO_INSTITUCION_CLINICA;
        $institucion->id_responsable = $responsable->id;
        $institucion->rut_responsable = $usuario->rut;
        $institucion->nombre_responsable = $usuario->nombreCompleto();
        $institucion->estado = 1;
        $institucion->bienvenido = 1;
        $institucion->save();
    }

    /**
     * Perfil del rol, nuevo o el que ya existía.
     *
     * No se usa firstOrNew() con el id_usuario porque varios de estos modelos
     * no lo declaran en $fillable y el dato se perdería en silencio.
     */
    private function perfilDe(string $modelo, User $usuario)
    {
        $registro = $modelo::where('id_usuario', $usuario->id)->first() ?: new $modelo();
        $registro->id_usuario = $usuario->id;

        return $registro;
    }

    /**
     * El escritorio de la clínica necesita un responsable registrado.
     * Es la misma persona que administra la cuenta.
     */
    private function responsableDeLaClinica(User $usuario): AdminInstServ
    {
        [$nombres, $apellidoUno, $apellidoDos] = $usuario->partesDelNombre();

        $responsable = AdminInstServ::where('rut', $usuario->rut)->first() ?: new AdminInstServ();

        $responsable->rut = $usuario->rut;
        $responsable->nombres = $nombres;
        $responsable->apellido_uno = $apellidoUno;
        $responsable->apellido_dos = $apellidoDos;
        $responsable->telefono_uno = $usuario->telefono;
        $responsable->email = $usuario->email;
        $responsable->estado = 1;
        $responsable->save();

        return $responsable;
    }

    /**
     * La previsión es un dato heredado de salud humana y la tabla la exige.
     * Se deja "Particular" cuando existe, igual que hace el escritorio del tutor.
     */
    private function previsionPorDefecto(): ?int
    {
        return Prevision::where('nombre', 'like', '%Particular%')->value('id')
            ?: Prevision::orderBy('id')->value('id');
    }

    /** Direccion tampoco declara $fillable, por eso se arma campo a campo. */
    private function guardarDireccion(string $calleYNumero, int $idCiudad): Direccion
    {
        $direccion = new Direccion();
        $direccion->direccion = $calleYNumero;
        $direccion->id_ciudad = $idCiudad;
        $direccion->save();

        return $direccion;
    }

    /** Suma el departamento o casa a la direccion escrita por la persona. */
    private function unirDireccion(string $direccion, ?string $depto): string
    {
        $depto = trim((string) $depto);

        return $depto !== '' ? trim($direccion).', '.$depto : trim($direccion);
    }

    private function mensajes(): array
    {
        return [
            'required' => 'Este dato es obligatorio.',
            'exists' => 'Elige una opción de la lista.',
            'in' => 'Elige una opción de la lista.',
            'date' => 'Ingresa una fecha válida.',
            'before_or_equal' => 'La fecha no puede ser futura.',
            'email' => 'Ingresa un correo electrónico válido.',
            'telefono.regex' => 'Ingresa un teléfono chileno con el formato +56 9 1234 5678.',
        ];
    }
}

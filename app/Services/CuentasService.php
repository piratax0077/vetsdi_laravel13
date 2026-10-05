<?php

namespace App\Services;

use App\Models\Asistente;
use App\Models\Instituciones;
use App\Models\Paciente;
use App\Models\Profesional;
use App\Models\User;
use App\Models\UsuarioRol;

/**
 * Centraliza todo lo que tiene que ver con los roles de cuenta de una persona.
 *
 * La identidad vive en users (un RUT, un correo, una contraseña) y cada rol
 * vive en usuario_roles. Los roles de spatie se siguen asignando en paralelo
 * porque los escritorios y permisos que ya existen se apoyan en ellos.
 */
class CuentasService
{
    /** Los cuatro tipos de cuenta que ofrece el registro. */
    public const TIPOS = ['tutor', 'profesional', 'asistente', 'clinica'];

    /** Equivalencia con los roles de spatie que ya usan los escritorios. */
    public const ROLES_SPATIE = [
        'tutor' => 'Paciente',
        'profesional' => 'Profesional',
        'asistente' => 'Asistente',
        'clinica' => 'Adm_Institucion',
    ];

    public const ETIQUETAS = [
        'tutor' => 'Tutor',
        'profesional' => 'Profesional',
        'asistente' => 'Asistente',
        'clinica' => 'Clínica veterinaria',
    ];

    /** Reseña que aparece al elegir el tipo de cuenta en el registro. */
    public const RESENAS = [
        'tutor' => 'Responsable de una o más mascotas; gestiona su ficha, horas y controles.',
        'profesional' => 'Médico veterinario u otro profesional del área que atiende pacientes.',
        'asistente' => 'Apoyo administrativo o clínico (secretaría, recepción, agenda) para profesionales o clínicas.',
        'clinica' => 'Centro que administra su equipo, pacientes y operación.',
    ];

    /** Bajada corta de cada card en la pantalla "Elige tu perfil". */
    public const DESCRIPCIONES = [
        'tutor' => 'Mascotas, horas, documentos y controles.',
        'profesional' => 'Agenda, pacientes, ficha clínica y herramientas profesionales.',
        'asistente' => 'Escritorio de asistencia y gestión de pacientes.',
        'clinica' => 'Escritorio de la clínica y administración de su equipo.',
    ];

    /** Ícono de la card en la pantalla "Elige tu perfil". */
    public const ICONOS = [
        'tutor' => 'images/iconos/mascotas.svg',
        'profesional' => 'images/iconos_ingreso/profesional.svg',
        'asistente' => 'images/iconos_ingreso/asistente.svg',
        'clinica' => 'images/iconos_ingreso/centro_medico.svg',
    ];

    /** Escritorio al que entra cada rol cuando su perfil está completo. */
    public const RUTAS_ESCRITORIO = [
        'tutor' => 'paciente.home',
        'profesional' => 'profesional.home',
        'asistente' => 'asistente.home',
        'clinica' => 'adm_cm.home',
    ];

    /** Tabla de perfil propia de cada rol. */
    public const MODELOS_PERFIL = [
        'tutor' => Paciente::class,
        'profesional' => Profesional::class,
        'asistente' => Asistente::class,
        'clinica' => Instituciones::class,
    ];

    /**
     * Agrega un rol a una cuenta que ya existe, sin crear otra identidad.
     * Es el punto de entrada que usará la contratación de planes.
     */
    public function asignarRol(User $usuario, string $tipo, ?int $idPlan = null): UsuarioRol
    {
        $tipo = $this->validarTipo($tipo);

        $rol = UsuarioRol::firstOrNew([
            'id_usuario' => $usuario->id,
            'tipo' => $tipo,
        ]);

        // Si el rol ya existía solo se reactiva: el perfil cargado no se pierde.
        $rol->estado = true;
        $rol->fecha_activacion = $rol->fecha_activacion ?: now();

        if ($idPlan !== null) {
            $rol->id_plan = $idPlan;
        }

        if (! $rol->exists) {
            $rol->perfil_completo = false;
        }

        $rol->save();

        $rolSpatie = self::ROLES_SPATIE[$tipo];
        if (! $usuario->hasRole($rolSpatie)) {
            $usuario->assignRole($rolSpatie);
        }

        return $rol;
    }

    /** Desactiva un rol sin borrar el perfil que la persona ya había cargado. */
    public function desactivarRol(User $usuario, string $tipo): void
    {
        UsuarioRol::where('id_usuario', $usuario->id)
            ->where('tipo', $this->validarTipo($tipo))
            ->update(['estado' => false]);
    }

    /** Roles activos de la persona, en el orden en que se muestran las cards. */
    public function rolesActivos(User $usuario)
    {
        $orden = array_flip(self::TIPOS);

        return UsuarioRol::where('id_usuario', $usuario->id)
            ->activos()
            ->get()
            ->sortBy(fn (UsuarioRol $rol) => $orden[$rol->tipo] ?? 99)
            ->values();
    }

    public function rol(User $usuario, string $tipo): ?UsuarioRol
    {
        return UsuarioRol::where('id_usuario', $usuario->id)
            ->where('tipo', $this->validarTipo($tipo))
            ->activos()
            ->first();
    }

    public function tieneRol(User $usuario, string $tipo): bool
    {
        return $this->rol($usuario, $tipo) !== null;
    }

    public function perfilCompleto(User $usuario, string $tipo): bool
    {
        return (bool) optional($this->rol($usuario, $tipo))->perfil_completo;
    }

    /** Marca el perfil del rol como terminado: no se vuelve a pedir. */
    public function marcarPerfilCompleto(User $usuario, string $tipo): void
    {
        $rol = $this->rol($usuario, $tipo);

        if (! $rol) {
            return;
        }

        $rol->perfil_completo = true;
        $rol->fecha_perfil_completo = $rol->fecha_perfil_completo ?: now();
        $rol->save();
    }

    /** Registro de perfil del rol (paciente, profesional, asistente o institución). */
    public function perfil(User $usuario, string $tipo)
    {
        $modelo = self::MODELOS_PERFIL[$this->validarTipo($tipo)];

        return $modelo::where('id_usuario', $usuario->id)->first();
    }

    public function etiqueta(string $tipo): string
    {
        return self::ETIQUETAS[$this->validarTipo($tipo)];
    }

    public function rutaEscritorio(string $tipo): string
    {
        return self::RUTAS_ESCRITORIO[$this->validarTipo($tipo)];
    }

    public static function esTipoValido(?string $tipo): bool
    {
        return in_array($tipo, self::TIPOS, true);
    }

    private function validarTipo(string $tipo): string
    {
        if (! self::esTipoValido($tipo)) {
            throw new \InvalidArgumentException('Tipo de cuenta no válido: '.$tipo);
        }

        return $tipo;
    }
}

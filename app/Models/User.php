<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'nombres',
        'apellido_uno',
        'apellido_dos',
        'email',
        'rut',
        'telefono',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'profile_photo_url',
    ];
    /** Roles de cuenta de la persona: tutor, profesional, asistente o clínica. */
    public function rolesCuenta()
    {
        return $this->hasMany(UsuarioRol::class, 'id_usuario');
    }

    public function verificacionesCorreo()
    {
        return $this->hasMany(VerificacionCorreo::class, 'id_usuario');
    }

    public function correoVerificado(): bool
    {
        return $this->email_verified_at !== null;
    }

    /** Nombre y apellido para saludar en la pantalla de selección de cuenta. */
    public function nombreParaMostrar(): string
    {
        [$nombres, $apellidoUno] = $this->partesDelNombre();

        $partes = array_filter([$nombres, $apellidoUno]);

        return $partes !== [] ? implode(' ', $partes) : (string) $this->email;
    }

    /**
     * Nombres y apellidos por separado.
     *
     * Las cuentas antiguas solo tienen el nombre completo en users.name, así que
     * en ese caso se parte en nombre y apellidos para poder llenar los perfiles.
     *
     * @return array{0:string,1:string,2:string}
     */
    public function partesDelNombre(): array
    {
        $nombres = trim((string) $this->nombres);
        $apellidoUno = trim((string) $this->apellido_uno);
        $apellidoDos = trim((string) $this->apellido_dos);

        if ($nombres !== '' && $apellidoUno !== '') {
            return [$nombres, $apellidoUno, $apellidoDos];
        }

        $partes = preg_split('/\s+/', trim((string) $this->name), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if ($nombres === '') {
            $nombres = (string) array_shift($partes);
        }

        if ($apellidoUno === '') {
            $apellidoUno = (string) array_shift($partes);
        }

        if ($apellidoDos === '') {
            $apellidoDos = implode(' ', $partes);
        }

        return [$nombres, $apellidoUno, $apellidoDos];
    }

    /** Nombre completo, con los dos apellidos, para los perfiles y documentos. */
    public function nombreCompleto(): string
    {
        $partes = array_filter($this->partesDelNombre());

        return $partes !== [] ? implode(' ', $partes) : trim((string) $this->name);
    }

    public function profesional()
    {
        return $this->hasOne(Profesional::class, 'id_usuario');
    }

    public function institucion()
    {
        return $this->hasOne(Instituciones::class, 'id_usuario');
    }

    public function sitioWeb()
    {
        return $this->hasOne(SitioWeb::class, 'id_usuario');
    }

    public function lugaresAtencion()
    {
        return $this->belongsToMany(LugarAtencion::class, 'lugar_atencion_user', 'id_user', 'id_lugar_atencion')
            ->withTimestamps();
    }
}

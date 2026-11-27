<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Mensajes extends Model
{
    use HasFactory;

    /**
     * Foto del remitente para la bandeja: la de su ficha de profesional o de tutor, la de su cuenta
     * o, si no tiene, la imagen por defecto de su tipo de usuario.
     */
    public static function fotoDelRemitente(?User $usuario): string
    {
        // Un mismo remitente suele repetirse en la bandeja, así que se busca una sola vez
        static $fotos = [];

        if ($usuario && isset($fotos[$usuario->id])) {
            return $fotos[$usuario->id];
        }

        $foto = null;
        $porDefecto = 'images/iconos/usuario_profesional.svg';

        if ($usuario) {
            try {
                $foto = Profesional::where('id_usuario', $usuario->id)->value('foto_perfil');

                if (empty($foto)) {
                    $tutor = Paciente::where('id_usuario', $usuario->id)->first(['id', 'foto_perfil']);
                    if ($tutor) {
                        $foto = $tutor->foto_perfil;
                        $porDefecto = 'images/iconos/usuario.svg';
                    }
                }
            } catch (\Throwable $error) {
                $foto = null;
            }

            $foto = $foto ?: $usuario->profile_photo_path;
        }

        if (empty($foto)) {
            $foto = asset($porDefecto);
        } elseif (! Str::startsWith($foto, ['http://', 'https://', '/'])) {
            $foto = asset('storage/'.$foto);
        }

        if ($usuario) {
            $fotos[$usuario->id] = $foto;
        }

        return $foto;
    }

    /** Fecha con hora del mensaje: fecha_envio solo guarda el día, la hora queda en created_at. */
    public function fechaConHora(): ?\Carbon\Carbon
    {
        $fecha = $this->created_at ?: $this->fecha_envio;

        return $fecha ? \Carbon\Carbon::parse($fecha) : null;
    }
}

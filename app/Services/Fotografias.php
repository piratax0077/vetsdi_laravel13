<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class Fotografias
{
    public function guardar(UploadedFile $archivo, string $directorio): string
    {
        $ruta = $archivo->storeAs($directorio, Str::uuid().'.'.$archivo->extension(), 'public');
        if (!$ruta) {
            throw new RuntimeException('No fue posible guardar la fotografía.');
        }
        try {
            $this->publicar($ruta);
        } catch (Throwable $error) {
            Storage::disk('public')->delete($ruta);
            throw $error;
        }
        return $ruta;
    }

    public function publicar(string $ruta): void
    {
        $origen = Storage::disk('public')->path($ruta);
        $destino = public_path('storage/' . $ruta);
        if (realpath($origen) === realpath($destino)) {
            return;
        }
        File::ensureDirectoryExists(dirname($destino));
        if (!File::copy($origen, $destino)) {
            throw new RuntimeException('No fue posible publicar la fotografía.');
        }
    }

    public function perfil(Model $perfil, UploadedFile $archivo): string
    {
        $ruta = $this->guardar($archivo, 'fotos_perfil');
        try {
            $perfil->foto_perfil = $ruta;
            if (!$perfil->save()) {
                throw new RuntimeException('No fue posible actualizar el perfil.');
            }
        } catch (Throwable $error) {
            $this->descartar($ruta);
            throw $error;
        }
        // Las fotos anteriores pueden estar referenciadas en fichas históricas.
        return $ruta;
    }

    public function descartar(string $ruta): void
    {
        File::delete(public_path('storage/' . $ruta));
        Storage::disk('public')->delete($ruta);
    }

    public function quitarPerfil(Model $perfil): void
    {
        $original=$perfil->foto_perfil;
        $ruta = normalizar_imagen_almacenada($perfil->foto_perfil);
        $perfil->foto_perfil = null;
        if (!$perfil->save()) {
            throw new RuntimeException('No fue posible actualizar el perfil.');
        }
        if ($ruta && str_starts_with($ruta, 'fotos_perfil/')) {
            // Un tutor que también es profesional puede compartir la misma foto.
            foreach (['pacientes','profesionales','asistentes','instituciones','mascotas'] as $tabla) {
                if (\Illuminate\Support\Facades\Schema::hasColumn($tabla,'foto_perfil') && \Illuminate\Support\Facades\DB::table($tabla)->whereIn('foto_perfil',[$ruta,'storage/'.$ruta,'/storage/'.$ruta,$original])->exists()) return;
            }
            $this->descartar($ruta);
        }
    }
}

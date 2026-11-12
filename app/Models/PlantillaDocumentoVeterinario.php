<?php

namespace App\Models;

use App\Services\ContenidoDocumentoVeterinario;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PlantillaDocumentoVeterinario extends Model
{
    use SoftDeletes;

    protected $table = 'documentos_profesional';
    protected $guarded = ['id', 'id_profesional'];
    protected $casts = ['activo' => 'boolean'];

    public const SECCIONES = ['indicacion' => 'indicaciones', 'consentimiento' => 'consentimientos'];

    public static function delProfesional(int $id, ?int $lugar = null, bool $incluirTodosSusLugares = false, bool $soloActivas = false): array
    {
        $secciones = ['indicaciones' => [], 'consentimientos' => []];
        $consulta = static::where(function ($visibles) use ($id, $lugar, $incluirTodosSusLugares) {
            $visibles->where('id_profesional', $id);
            if ($lugar || $incluirTodosSusLugares) {
                $lugares = static::lugaresDelProfesional($id)->select('id_lugar_atencion');
                if ($lugar) {
                    $lugares->where('id_lugar_atencion', $lugar);
                }
                $visibles->orWhere(function ($compartidas) use ($lugares) {
                    $compartidas->where('alcance', 'compartida')->where('activo', true)
                        ->whereIn('id_lugar_atencion', $lugares)
                        ->whereExists(function ($autor) {
                            $autor->from('profesionales_lugares_atencion')->where('estado', 1)
                                ->whereColumn('id_lugar_atencion', 'documentos_profesional.id_lugar_atencion')
                                ->whereColumn('id_profesional', 'documentos_profesional.id_profesional');
                        });
                });
            }
        });
        foreach ($consulta->when($soloActivas, fn ($query) => $query->where('activo', true))->orderBy('titulo')->get() as $plantilla) {
            $secciones[self::SECCIONES[$plantilla->seccion]][] = $plantilla->paraVista($lugar ?? $plantilla->id_lugar_atencion)
                + ['propia' => (int) $plantilla->id_profesional === $id];
        }
        return $secciones;
    }

    public static function lugaresDelProfesional(int $profesional): Builder
    {
        return DB::table('profesionales_lugares_atencion')->where('id_profesional', $profesional)
            ->where('estado', 1)->where('id_lugar_atencion', '>', 0);
    }

    public static function profesionalesDelLugar(int $lugar): Builder
    {
        return DB::table('profesionales_lugares_atencion')->where('id_lugar_atencion', $lugar)
            ->where('estado', 1)->select('id_profesional');
    }

    public function logoParaDocumento(): ?string
    {
        if (!$this->logo_ruta || str_starts_with($this->logo_ruta, 'data:')) {
            return $this->logo_ruta;
        }
        $bytes = Storage::disk('local')->get($this->logo_ruta);
        $formato = pathinfo($this->logo_ruta, PATHINFO_EXTENSION);
        return $bytes === null ? null : 'data:image/'.($formato === 'svg' ? 'svg+xml' : $formato).';base64,'.base64_encode($bytes);
    }

    public function paraVista(?int $lugar = null): array
    {
        return $this->only(['id', 'titulo', 'categoria', 'tipo', 'alcance', 'activo', 'id_lugar_atencion'])
            + [
                'contenido' => $this->contenido === null ? null : app(ContenidoDocumentoVeterinario::class)->limpiar($this->contenido),
                'logo' => $this->logoParaDocumento(),
                'archivo' => $this->archivo_nombre,
                'archivo_url' => $this->archivo_ruta ? route('profesional.plantillas.archivo', ['plantilla' => $this, 'lugar' => $lugar]) : null,
                'archivo_tipo' => $this->archivo_mime,
            ];
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

// Personalización de los PDF de receta y de orden de exámenes de cada profesional.
// Sin registro guardado, los PDF salen igual que siempre (valores de predeterminada()).
class PlantillaPdfProfesional extends Model
{
    protected $table = 'plantillas_pdf_profesional';

    protected $fillable = ['id_profesional', 'tipo', 'configuracion', 'logo_ruta'];

    protected $casts = ['configuracion' => 'array'];

    public const TIPOS = ['receta', 'examenes'];

    // Datos que se pueden mostrar u ocultar en cada documento
    public const SECCIONES = [
        'receta' => [
            'mascota' => 'Datos de la mascota',
            'tutor' => 'Datos del tutor',
            'profesional' => 'Datos del profesional',
            'centro' => 'Centro y fecha de emisión',
            'firmas' => 'Firma y códigos QR',
            'codigo' => 'Código de validación',
        ],
        'examenes' => [
            'profesional' => 'Datos del profesional',
            'paciente' => 'Datos del paciente',
            'fecha' => 'Fecha de la orden',
            'firmas' => 'Firma y códigos QR',
            'codigo' => 'Código de validación',
        ],
    ];

    public static function predeterminada(string $tipo): array
    {
        return [
            // Los mismos turquesas que ya usaba cada PDF
            'color' => $tipo === 'receta' ? '#168f86' : '#168c83',
            'texto_marca' => 'VET SDI',
            'posicion_logo' => 'izquierda',
            'titulo' => $tipo === 'receta' ? 'Receta veterinaria' : 'Orden de exámenes veterinarios',
            'formato' => 'tabla',
            'tamano_letra' => 'normal',
            'mostrar' => array_fill_keys(array_keys(self::SECCIONES[$tipo] ?? []), true),
            'pie' => '',
            'logo' => null,
        ];
    }

    // Configuración lista para el PDF: lo guardado sobre los valores por defecto, con el logo en base64
    public static function paraPdf(?int $idProfesional, string $tipo): array
    {
        $plantilla = $idProfesional
            ? static::where('id_profesional', $idProfesional)->where('tipo', $tipo)->first()
            : null;

        return $plantilla ? $plantilla->configuracionCompleta() : static::predeterminada($tipo);
    }

    public function configuracionCompleta(): array
    {
        $base = static::predeterminada($this->tipo);
        $guardada = $this->configuracion ?? [];
        $configuracion = array_merge($base, array_intersect_key($guardada, $base));
        $configuracion['mostrar'] = array_merge($base['mostrar'], array_intersect_key($guardada['mostrar'] ?? [], $base['mostrar']));
        $configuracion['logo'] = $this->logoEnBase64();

        return $configuracion;
    }

    public function logoEnBase64(): ?string
    {
        if (!$this->logo_ruta || !Storage::disk('local')->exists($this->logo_ruta)) {
            return null;
        }
        $formato = pathinfo($this->logo_ruta, PATHINFO_EXTENSION);

        return 'data:image/'.($formato === 'svg' ? 'svg+xml' : $formato).';base64,'
            .base64_encode(Storage::disk('local')->get($this->logo_ruta));
    }

    // Tamaño de letra base del PDF según la opción elegida
    public static function tamanoLetraPx(array $configuracion): int
    {
        return ['pequena' => 9, 'normal' => 10, 'grande' => 11][$configuracion['tamano_letra'] ?? 'normal'] ?? 10;
    }
}

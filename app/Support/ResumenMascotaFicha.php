<?php

namespace App\Support;

use App\Models\FichaVeterinariaGeneral;
use App\Models\Mascota;
use Carbon\Carbon;

/**
 * Arma los datos de la card resumen de la mascota que se muestra
 * arriba de la ficha de atención veterinaria.
 */
class ResumenMascotaFicha
{
    public static function para(?Mascota $mascota): ?array
    {
        if (! $mascota) {
            return null;
        }

        $sexo = strtoupper((string) $mascota->sexo);

        return [
            'foto' => $mascota->foto_url ?: asset('images/iconos/mascotas.svg'),
            'nombre' => $mascota->nombre ?: 'Sin nombre',
            'especie' => optional($mascota->especieMascota)->nombre ?: ($mascota->otra_especie ?: 'Sin registro'),
            'raza' => optional($mascota->razaMascota)->nombre ?: 'Sin registro',
            'edad' => self::edad($mascota->fecha_nacimiento),
            'sexo' => $sexo === 'M' ? 'Macho' : ($sexo === 'F' ? 'Hembra' : 'Sin registro'),
            'chip' => $mascota->tiene_chip && filled($mascota->chip) ? $mascota->chip : 'Sin chip',
            'comportamiento' => self::comportamiento($mascota),
            'enfermedad_cronica' => self::tieneEnfermedadCronica($mascota->enfermedad_cronica),
            'enfermedad_cronica_detalle' => trim((string) $mascota->enfermedad_cronica),
            'desparasitacion' => self::estadoDesparasitacion($mascota),
            // Con historial detallado la card permite abrir el modal del registro
            'desparasitacion_con_historial' => count(self::registros($mascota->desparasitaciones_registro)) > 0,
            'vacunas' => self::estadoVacunas($mascota->vacunas_registro),
        ];
    }

    private static function edad($fechaNacimiento): string
    {
        if (! $fechaNacimiento) {
            return 'Sin registro';
        }

        $diferencia = Carbon::parse($fechaNacimiento)->diff(now());
        if ($diferencia->y > 0) {
            return $diferencia->y . ($diferencia->y === 1 ? ' año' : ' años')
                . ($diferencia->m > 0 ? ' ' . $diferencia->m . ($diferencia->m === 1 ? ' mes' : ' meses') : '');
        }
        if ($diferencia->m > 0) {
            return $diferencia->m . ($diferencia->m === 1 ? ' mes' : ' meses');
        }

        return $diferencia->d . ($diferencia->d === 1 ? ' día' : ' días');
    }

    // Se toma lo último que el profesional anotó en "Comportamiento del animal"
    private static function comportamiento(Mascota $mascota): string
    {
        $fichas = FichaVeterinariaGeneral::query()
            ->join('fichas_atenciones', 'fichas_atenciones.id', '=', 'ficha_veterinaria_general.id_fichas_atenciones')
            ->where('fichas_atenciones.id_mascota', $mascota->id)
            ->orderByDesc('ficha_veterinaria_general.id')
            ->limit(15)
            ->pluck('ficha_veterinaria_general.datos');

        foreach ($fichas as $datos) {
            $decodificado = json_decode((string) $datos, true);
            $texto = trim((string) data_get($decodificado, 'comportamiento_animal', ''));
            if ($texto !== '') {
                return $texto;
            }
        }

        return trim((string) $mascota->comportamiento) ?: 'Sin registro';
    }

    private static function tieneEnfermedadCronica($texto): bool
    {
        $normalizado = strtolower(\Illuminate\Support\Str::ascii(trim((string) $texto)));

        return ! in_array($normalizado, ['', 'no', 'ninguna', 'ninguno', 'sin', 'n/a', 'na', '-', 'no tiene', 'no presenta'], true);
    }

    // Igual que las vacunas: atrasado si la próxima dosis de la última desparasitación de cada tipo ya pasó.
    // Si solo existe la fecha suelta de la ficha (sin registro detallado) no hay próxima dosis que vencer: queda al día
    private static function estadoDesparasitacion(Mascota $mascota): string
    {
        $desparasitaciones = collect(self::registros($mascota->desparasitaciones_registro))
            ->filter(fn ($item) => filled($item['fecha_dosis'] ?? null));

        if ($desparasitaciones->isEmpty()) {
            return $mascota->ultima_desparasitacion ? 'al_dia' : 'sin_registro';
        }

        $atrasada = $desparasitaciones
            ->groupBy(fn ($item) => strtolower(trim((string) ($item['tipo'] ?? ''))))
            ->map(fn ($dosis) => $dosis->sortByDesc('fecha_dosis')->first())
            ->contains(function ($ultima) {
                $proxima = $ultima['proxima_dosis'] ?? null;

                return filled($proxima) && rescue(fn () => Carbon::parse($proxima)->endOfDay()->isPast(), false, false);
            });

        return $atrasada ? 'atrasado' : 'al_dia';
    }

    // Al día: ninguna vacuna tiene su próxima dosis vencida (se mira la dosis más reciente de cada vacuna)
    private static function estadoVacunas($registros): string
    {
        $vacunas = collect(self::registros($registros))->filter(fn ($item) => filled($item['fecha_dosis'] ?? null));
        if ($vacunas->isEmpty()) {
            return 'sin_registro';
        }

        $atrasada = $vacunas
            ->groupBy(fn ($item) => strtolower(trim((string) ($item['vacuna'] ?? ''))))
            ->map(fn ($dosis) => $dosis->sortByDesc('fecha_dosis')->first())
            ->contains(function ($ultima) {
                $proxima = $ultima['proxima_dosis'] ?? null;

                return filled($proxima) && rescue(fn () => Carbon::parse($proxima)->endOfDay()->isPast(), false, false);
            });

        return $atrasada ? 'atrasado' : 'al_dia';
    }

    /**
     * Datos del campo "Esquema de vacunación" de Anamnesis: estado (al_dia, atrasado, sin_registro)
     * y la última vacuna registrada como texto ("Nombre - dd/mm/aaaa").
     */
    public static function esquemaVacunacion(?Mascota $mascota): array
    {
        if (! $mascota) {
            return ['estado' => 'sin_registro', 'ultima' => ''];
        }

        $ultima = collect(self::registros($mascota->vacunas_registro))
            ->filter(fn ($item) => filled($item['fecha_dosis'] ?? null))
            ->sortByDesc('fecha_dosis')
            ->first();

        return [
            'estado' => self::estadoVacunas($mascota->vacunas_registro),
            'ultima' => $ultima
                ? (trim((string) ($ultima['vacuna'] ?? '')) ?: 'Sin nombre') . ' - ' . self::fecha($ultima['fecha_dosis'])
                : '',
        ];
    }

    /**
     * Alertas clínicas para el botón "Alertas" de la ficha: vacunas atrasadas,
     * desparasitación atrasada y enfermedades crónicas. Usa las mismas reglas que la card.
     */
    public static function alertas(?Mascota $mascota): array
    {
        if (! $mascota) {
            return [];
        }

        $alertas = [];

        if (self::estadoVacunas($mascota->vacunas_registro) === 'atrasado') {
            $vacunas = collect(self::registros($mascota->vacunas_registro))->filter(fn ($item) => filled($item['fecha_dosis'] ?? null));
            $ultima = $vacunas->sortByDesc('fecha_dosis')->first();
            $vencidas = self::vencidas($vacunas, 'vacuna')
                ->map(fn ($item) => trim((string) ($item['vacuna'] ?? 'Vacuna')) . ' (vencía el ' . self::fecha($item['proxima_dosis']) . ')')
                ->values()->all();

            $alertas[] = [
                'icono' => 'icon-shield',
                'titulo' => 'Esquema de vacunación atrasado',
                'detalle' => [
                    'Última vacuna: ' . (trim((string) ($ultima['vacuna'] ?? '')) ?: 'Sin nombre') . ' el ' . self::fecha($ultima['fecha_dosis'] ?? null),
                    'Pendientes: ' . (implode(', ', $vencidas) ?: 'Sin detalle'),
                ],
            ];
        }

        if (self::estadoDesparasitacion($mascota) === 'atrasado') {
            $desparasitaciones = collect(self::registros($mascota->desparasitaciones_registro))->filter(fn ($item) => filled($item['fecha_dosis'] ?? null));
            $ultima = $desparasitaciones->sortByDesc('fecha_dosis')->first();
            $vencida = self::vencidas($desparasitaciones, 'tipo')->sortBy('proxima_dosis')->first();
            $producto = trim((string) ($ultima['antiparasitario'] ?? ''));

            $alertas[] = [
                'icono' => 'icon-alert-triangle',
                'titulo' => 'Desparasitación atrasada',
                'detalle' => array_values(array_filter([
                    'Última desparasitación: ' . self::fecha($ultima['fecha_dosis'] ?? null) . ($producto !== '' ? ' (' . $producto . ')' : ''),
                    $vencida ? 'La próxima dosis vencía el ' . self::fecha($vencida['proxima_dosis']) : null,
                ])),
            ];
        }

        if (self::tieneEnfermedadCronica($mascota->enfermedad_cronica)) {
            $alertas[] = [
                'icono' => 'icon-activity',
                'titulo' => 'Enfermedades crónicas',
                'detalle' => [trim((string) $mascota->enfermedad_cronica)],
            ];
        }

        return $alertas;
    }

    // Registros cuya dosis más reciente (por vacuna o por tipo) ya tiene la próxima dosis vencida
    private static function vencidas($registros, string $agrupar)
    {
        return collect($registros)
            ->groupBy(fn ($item) => strtolower(trim((string) ($item[$agrupar] ?? ''))))
            ->map(fn ($dosis) => $dosis->sortByDesc('fecha_dosis')->first())
            ->filter(function ($ultima) {
                $proxima = $ultima['proxima_dosis'] ?? null;

                return filled($proxima) && rescue(fn () => Carbon::parse($proxima)->endOfDay()->isPast(), false, false);
            });
    }

    private static function fecha($valor): string
    {
        return rescue(fn () => filled($valor) ? Carbon::parse($valor)->format('d/m/Y') : 'sin fecha', 'sin fecha', false);
    }

    private static function registros($registros): array
    {
        if (is_string($registros)) {
            $registros = json_decode($registros, true);
        }

        return is_array($registros) ? array_values(array_filter($registros, 'is_array')) : [];
    }
}

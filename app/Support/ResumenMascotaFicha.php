<?php

namespace App\Support;

use App\Models\DetalleReceta;
use App\Models\FichaAtencion;
use App\Models\FichaVeterinariaGeneral;
use App\Models\HoraMedica;
use App\Models\Mascota;
use App\Models\Profesional;
use Carbon\Carbon;
use Illuminate\Support\Str;

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

    /**
     * Resumen en texto de las atenciones anteriores, para la card que va arriba de la anamnesis.
     * Solo aparece cuando la mascota ya tiene al menos dos atenciones finalizadas (sin contar la actual
     * ni las confidenciales). El texto viene con <strong> y todo lo que sale de la base va escapado.
     */
    public static function historialAtenciones(?Mascota $mascota, $idFichaActual = null): ?array
    {
        if (! $mascota) {
            return null;
        }

        $atenciones = FichaAtencion::query()
            ->where('id_mascota', $mascota->id)
            ->where('finalizada', 1)
            ->where(fn ($consulta) => $consulta->whereNull('confidencial')->orWhere('confidencial', 0))
            ->when($idFichaActual, fn ($consulta) => $consulta->where('id', '!=', $idFichaActual))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get(['id', 'motivo', 'hipotesis_diagnostico', 'diagnostico_ce10', 'peso', 'created_at']);

        if ($atenciones->count() < 2) {
            return null;
        }

        $ultima = $atenciones->first();
        $nombre = e($mascota->nombre ?: 'La mascota');
        $atendido = strtoupper((string) $mascota->sexo) === 'F' ? 'atendida' : 'atendido';
        $frases = [];

        $frases[] = "<strong>{$nombre}</strong> ha sido {$atendido} en <strong>{$atenciones->count()} ocasiones</strong>"
            . ' desde <strong>' . e(self::fechaLarga($atenciones->last()->created_at, 'F \d\e Y')) . '</strong>.';

        $motivo = self::textoCorto($ultima->motivo);
        $frases[] = 'Su última consulta fue el <strong>' . e(self::fechaLarga($ultima->created_at)) . '</strong>'
            . ($motivo !== '' ? ' por motivo de <strong>' . e($motivo) . '</strong>' : '') . '.';

        $diagnostico = self::textoCorto($ultima->hipotesis_diagnostico ?: $ultima->diagnostico_ce10);
        $medicamentos = DetalleReceta::where('id_ficha', $ultima->id)
            ->pluck('producto')
            ->map(fn ($producto) => self::textoCorto($producto, 40))
            ->filter()
            ->unique()
            ->take(3)
            ->map(fn ($producto) => '<strong>' . e($producto) . '</strong>')
            ->all();
        if ($diagnostico !== '' && $medicamentos) {
            $frases[] = 'Se registró <strong>' . e($diagnostico) . '</strong> como diagnóstico y se le recetó ' . self::unirLista($medicamentos) . '.';
        } elseif ($diagnostico !== '') {
            $frases[] = 'Se registró <strong>' . e($diagnostico) . '</strong> como diagnóstico.';
        } elseif ($medicamentos) {
            $frases[] = 'Se le recetó ' . self::unirLista($medicamentos) . '.';
        }

        $peso = $atenciones->first(fn ($atencion) => filled($atencion->peso));
        if ($peso) {
            $valorPeso = is_numeric($peso->peso)
                ? str_replace('.', ',', (string) (float) $peso->peso) . ' kg'
                : self::textoCorto($peso->peso, 20);
            $frases[] = 'Su último peso registrado es de <strong>' . e($valorPeso) . '</strong>.';
        }

        $desparasitacion = self::fraseDesparasitacion($mascota);
        if ($desparasitacion !== '') {
            $frases[] = $desparasitacion;
        }

        // Próxima hora agendada o confirmada, desde mañana para no tomar la que se está atendiendo
        $cita = HoraMedica::where('id_mascota', $mascota->id)
            ->whereIn('id_estado', [1, 2])
            ->whereDate('fecha_consulta', '>', now()->toDateString())
            ->orderBy('fecha_consulta')
            ->orderBy('hora_inicio')
            ->first(['fecha_consulta', 'id_profesional']);
        if ($cita) {
            $fecha = Carbon::parse($cita->fecha_consulta);
            $profesional = optional(Profesional::find($cita->id_profesional))->nombreCompleto();
            $frases[] = 'Tiene una cita agendada para el <strong>' . e(self::fechaLarga($fecha, $fecha->isSameYear(now()) ? 'j \d\e F' : 'j \d\e F \d\e Y')) . '</strong>'
                . ($profesional ? ' con <strong>' . e($profesional) . '</strong>' : '') . '.';
        }

        $vacunasAtrasadas = self::estadoVacunas($mascota->vacunas_registro) === 'atrasado';
        $desparasitacionAtrasada = self::estadoDesparasitacion($mascota) === 'atrasado';
        if ($vacunasAtrasadas && $desparasitacionAtrasada) {
            $frases[] = 'Tiene el <strong>esquema de vacunación</strong> y la <strong>desparasitación</strong> atrasados.';
        } elseif ($vacunasAtrasadas) {
            $frases[] = 'Tiene el <strong>esquema de vacunación</strong> atrasado.';
        } elseif ($desparasitacionAtrasada) {
            $frases[] = 'Tiene la <strong>desparasitación</strong> atrasada.';
        }

        return [
            'texto' => implode(' ', $frases),
            'hasta' => self::fechaLarga($ultima->created_at),
        ];
    }

    // Última desparasitación de cada tipo (interna, externa, ambas u otra), con fecha y producto.
    // Si hay una "interna y externa" más reciente, deja fuera las internas o externas anteriores a ella
    private static function fraseDesparasitacion(Mascota $mascota): string
    {
        $ultimas = collect(self::registros($mascota->desparasitaciones_registro))
            ->filter(fn ($item) => filled($item['fecha_dosis'] ?? null) && rescue(fn () => Carbon::parse($item['fecha_dosis']), null, false))
            ->groupBy(fn ($item) => mb_strtolower(trim((string) ($item['tipo'] ?? ''))))
            ->map(fn ($dosis) => $dosis->sortByDesc('fecha_dosis')->first());

        if ($ultimas->isEmpty()) {
            return $mascota->ultima_desparasitacion
                ? 'Su última desparasitación fue el <strong>' . e(self::fechaLarga($mascota->ultima_desparasitacion)) . '</strong>.'
                : '';
        }

        $ambas = $ultimas->get('interno y externo');
        if ($ambas) {
            $ultimas = $ultimas->reject(fn ($item, $tipo) => in_array($tipo, ['interno', 'externo'], true) && $item['fecha_dosis'] <= $ambas['fecha_dosis']);
        }

        $tipos = ['interno' => 'interna', 'externo' => 'externa', 'interno y externo' => 'interna y externa', 'otro' => 'otro tipo'];
        $partes = $ultimas->sortByDesc('fecha_dosis')->take(3)->map(function ($item, $tipo) use ($tipos) {
            $nombreTipo = $tipos[$tipo] ?? self::textoCorto($tipo, 30);
            $producto = self::textoCorto($item['antiparasitario'] ?? '', 40);

            return ($nombreTipo !== '' ? '<strong>' . e($nombreTipo) . '</strong> ' : '')
                . 'el <strong>' . e(self::fechaLarga($item['fecha_dosis'])) . '</strong>'
                . ($producto !== '' ? ' con ' . e($producto) : '');
        })->values()->all();

        // Con coma y no con "y", porque el tipo "interna y externa" ya lleva una
        return 'Última desparasitación: ' . implode(', ', $partes) . '.';
    }

    private static function fechaLarga($fecha, string $formato = 'j \d\e F \d\e Y'): string
    {
        return Carbon::parse($fecha)->locale('es')->translatedFormat($formato);
    }

    // Texto libre de la ficha en una sola línea; si viene todo en mayúsculas se pasa a minúsculas para que se lea en la frase
    private static function textoCorto($texto, int $largo = 90): string
    {
        $texto = trim(preg_replace('/\s+/u', ' ', (string) $texto));
        if (preg_match('/\p{L}/u', $texto) && mb_strtoupper($texto) === $texto) {
            $texto = mb_strtolower($texto);
        }

        return Str::limit(rtrim($texto, '. '), $largo);
    }

    private static function unirLista(array $elementos): string
    {
        $ultimo = array_pop($elementos);

        return $elementos ? implode(', ', $elementos) . ' y ' . $ultimo : (string) $ultimo;
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

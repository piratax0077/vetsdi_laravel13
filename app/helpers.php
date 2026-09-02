<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

if (! function_exists('safe_route')) {
    /**
     * Resuelve una ruta nombrada sin romper el render si no existe.
     */
    function safe_route(string $name, array $parameters = [], bool $absolute = true, string $default = ''): string
    {
        if ($name === '' || ! Route::has($name)) {
            return $default;
        }

        try {
            return route($name, $parameters, $absolute);
        } catch (\Throwable $e) {
            return $default;
        }
    }
}

if (! function_exists('app_route_url')) {
    /**
     * URL absoluta de una ruta nombrada respetando host y subdirectorio actuales.
     */
    function app_route_url(string $name, array $parameters = [], string $default = ''): string
    {
        if ($name === '' || ! Route::has($name)) {
            return $default;
        }

        try {
            $path = route($name, $parameters, false);

            if ($path === '') {
                return $default;
            }

            if (app()->runningInConsole() || ! app()->bound('request') || ! request()->server->has('HTTP_HOST')) {
                return url($path);
            }

            return rtrim(app_public_base_url(), '/').$path;
        } catch (\Throwable $e) {
            return $default;
        }
    }
}

if (! function_exists('razas_mascotas_catalogo_por_especie')) {
    /**
     * Catálogo de razas agrupado por especie_id (clave string) para selects en JS.
     * Usa el catálogo oficial (config) para Canina/Felina y la BD para el resto.
     *
     * @return array<string, array<int, array{id:int, nombre:string, slug:string}>>
     */
    function razas_mascotas_catalogo_por_especie(): array
    {
        static $cache = null;

        if ($cache !== null) {
            return $cache;
        }

        $cache = [];
        $catalogoOficial = config('mascotas_razas', []);
        $especies = \App\Models\EspecieMascota::query()->pluck('id', 'nombre');

        foreach ($catalogoOficial as $especieNombre => $razasPorSlug) {
            $especieId = $especies[$especieNombre] ?? null;
            if (! $especieId) {
                continue;
            }

            $items = [];
            foreach ($razasPorSlug as $slug => $nombre) {
                $raza = \App\Models\RazaMascota::query()
                    ->where('especie_id', $especieId)
                    ->where('slug', $slug)
                    ->first(['id', 'nombre', 'slug']);

                $items[] = [
                    'id' => $raza?->id ?? 0,
                    'nombre' => $nombre,
                    'slug' => $slug,
                ];
            }

            $cache[(string) $especieId] = $items;
        }

        $idsEspeciesOficiales = [];
        foreach (array_keys($catalogoOficial) as $especieNombre) {
            if (isset($especies[$especieNombre])) {
                $idsEspeciesOficiales[] = (int) $especies[$especieNombre];
            }
        }

        \App\Models\RazaMascota::query()
            ->when($idsEspeciesOficiales !== [], fn ($q) => $q->whereNotIn('especie_id', $idsEspeciesOficiales))
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'slug', 'especie_id'])
            ->groupBy(fn ($raza) => (string) $raza->especie_id)
            ->each(function ($razas, $especieId) use (&$cache) {
                $cache[$especieId] = $razas->map(fn ($raza) => [
                    'id' => $raza->id,
                    'nombre' => $raza->nombre,
                    'slug' => $raza->slug ?: (string) $raza->id,
                ])->values()->all();
            });

        return $cache;
    }
}

if (! function_exists('resolver_raza_mascota_id')) {
    /**
     * Convierte slug o id de raza al id numérico en razas_mascotas.
     */
    function resolver_raza_mascota_id($raza, ?int $especieId = null): ?int
    {
        if ($raza === null || $raza === '' || $raza === 'sin') {
            return null;
        }

        if (is_numeric($raza)) {
            return (int) $raza;
        }

        $query = \App\Models\RazaMascota::query()->where('slug', $raza);
        if ($especieId) {
            $query->where('especie_id', $especieId);
        }

        return $query->value('id');
    }
}

if (! function_exists('app_public_base_url')) {
    /**
     * Base URL pública de la app, incluyendo subdirectorios como /vet-sdi_v2/public.
     */
    function app_public_base_url(): string
    {
        $request = request();
        if (! $request) {
            return rtrim((string) config('app.url'), '/');
        }

        $host = rtrim($request->getSchemeAndHttpHost(), '/');
        $baseUrl = (string) $request->getBaseUrl();

        if ($baseUrl !== '' && $baseUrl !== '/') {
            return $host . rtrim($baseUrl, '/');
        }

        $scriptName = str_replace('\\', '/', (string) $request->server->get('SCRIPT_NAME', ''));
        $publicPath = rtrim(str_replace('/index.php', '', $scriptName), '/');

        if ($publicPath !== '' && $publicPath !== '/') {
            return $host . $publicPath;
        }

        $requestPath = parse_url((string) $request->server->get('REQUEST_URI', ''), PHP_URL_PATH) ?: '';
        if ($requestPath !== '' && preg_match('#^(.*?)(/Paciente/|/paciente/|/public/)#i', $requestPath, $coincidencias)) {
            $prefijo = rtrim($coincidencias[1], '/');
            if ($prefijo !== '') {
                return $host . $prefijo;
            }
        }

        return $host;
    }
}

if (! function_exists('public_asset_url')) {
  /**
   * Genera URL pública respetando el subdirectorio actual de la app.
   */
  function public_asset_url(string $relativePath): string
  {
      $relativePath = ltrim(str_replace('\\', '/', $relativePath), '/');

      if (! app()->runningInConsole() && request()) {
          return app_public_base_url() . '/' . $relativePath;
      }

      return asset($relativePath);
  }
}

if (! function_exists('storage_public_url')) {
    /**
     * Convierte rutas guardadas en BD (nombre, storage/..., /storage/...) a URL pública.
     */
    function storage_public_url(?string $path): ?string
    {
        if ($path === null) {
            return null;
        }

        $ruta = str_replace('\\', '/', trim($path));
        if ($ruta === '') {
            return null;
        }

        if (Str::startsWith($ruta, ['http://', 'https://'])) {
            if (preg_match('#/storage/(.+)$#', $ruta, $coincidencias)) {
                return public_asset_url('storage/' . $coincidencias[1]);
            }

            return $ruta;
        }

        $ruta = preg_replace('#^public/#', '', $ruta) ?? $ruta;

        if (Str::startsWith($ruta, '/storage/')) {
            return public_asset_url(ltrim($ruta, '/'));
        }

        if (Str::startsWith($ruta, 'storage/')) {
            return public_asset_url($ruta);
        }

        if (str_contains($ruta, '/')) {
            return public_asset_url('storage/' . ltrim($ruta, '/'));
        }

        return public_asset_url('storage/imagenes/temp/' . $ruta);
    }
}

if (! function_exists('normalizar_imagen_almacenada')) {
    /**
     * Normaliza rutas de imagen para guardar en BD (nombre o ruta relativa, sin dominio).
     */
    function normalizar_imagen_almacenada(?string $path): ?string
    {
        if ($path === null) {
            return null;
        }

        $ruta = str_replace('\\', '/', trim($path));
        if ($ruta === '') {
            return null;
        }

        if (Str::startsWith($ruta, ['http://', 'https://'])) {
            if (preg_match('#/storage/(.+)$#', $ruta, $coincidencias)) {
                return $coincidencias[1];
            }

            $pathInfo = parse_url($ruta, PHP_URL_PATH);
            return $pathInfo ? ltrim(basename($pathInfo), '/') : basename($ruta);
        }

        $ruta = preg_replace('#^public/#', '', $ruta) ?? $ruta;

        return ltrim($ruta, '/');
    }
}

if (! function_exists('normalizar_galeria_mascota_almacenada')) {
    /**
     * @param  mixed  $galeria
     * @return array<string, mixed>|null
     */
    function normalizar_galeria_mascota_almacenada($galeria): ?array
    {
        if ($galeria === null || $galeria === '') {
            return null;
        }

        if (is_string($galeria)) {
            $decoded = json_decode($galeria, true);
            $galeria = json_last_error() === JSON_ERROR_NONE ? $decoded : null;
        }

        if (! is_array($galeria)) {
            return null;
        }

        foreach (['ven_pre', 'ven_post'] as $clave) {
            if (! isset($galeria[$clave]) || ! is_array($galeria[$clave])) {
                continue;
            }

            foreach ($galeria[$clave] as $indice => $item) {
                if (! is_array($item) || ! isset($item[0])) {
                    continue;
                }

                $normalizada = normalizar_imagen_almacenada((string) $item[0]);
                if ($normalizada !== null) {
                    $galeria[$clave][$indice][0] = $normalizada;
                }
            }
        }

        return $galeria;
    }
}

if (! function_exists('resolver_foto_perfil_mascota')) {
    /**
     * Usa foto_perfil o, si viene vacía, la primera imagen de la galería subida.
     */
    function resolver_foto_perfil_mascota(?string $fotoPerfil, ?array $galeria): ?string
    {
        $foto = normalizar_imagen_almacenada($fotoPerfil);
        if ($foto) {
            return $foto;
        }

        if (! is_array($galeria)) {
            return null;
        }

        foreach (['ven_pre', 'ven_post'] as $clave) {
            if (! empty($galeria[$clave][0][0])) {
                return normalizar_imagen_almacenada((string) $galeria[$clave][0][0]);
            }
        }

        return null;
    }
}

if (! function_exists('normalizar_album_memorial_mascota')) {
    /**
     * @param  mixed  $album
     * @return array<int, array{0: string, 1?: string, 2?: string, 3?: string}>
     */
    function normalizar_album_memorial_mascota($album): array
    {
        if ($album === null || $album === '') {
            return [];
        }

        if (is_string($album)) {
            $decoded = json_decode($album, true);
            $album = json_last_error() === JSON_ERROR_NONE ? $decoded : [];
        }

        if (! is_array($album)) {
            return [];
        }

        $items = [];
        foreach ($album as $item) {
            if (is_string($item) && $item !== '') {
                $ruta = normalizar_imagen_almacenada($item);
                if ($ruta) {
                    $items[] = [$ruta, '', basename($ruta), ''];
                }
                continue;
            }
            if (! is_array($item) || empty($item[0])) {
                continue;
            }
            $ruta = normalizar_imagen_almacenada((string) $item[0]);
            if ($ruta) {
                $items[] = [
                    $ruta,
                    $item[1] ?? '',
                    $item[2] ?? basename($ruta),
                    $item[3] ?? '',
                ];
            }
        }

        return $items;
    }
}

if (! function_exists('mascota_album_memorial')) {
    /**
     * Recopila todas las fotos para el memorial (álbum dedicado + galería + perfil).
     *
     * @return array<int, array{url: string, titulo: string}>
     */
    function mascota_album_memorial($mascota): array
    {
        if (! $mascota) {
            return [];
        }

        $vistas = [];
        $rutasVistas = [];

        $agregar = function (?string $ruta, string $titulo = 'Recuerdo') use (&$vistas, &$rutasVistas) {
            $normalizada = normalizar_imagen_almacenada($ruta);
            if (! $normalizada || in_array($normalizada, $rutasVistas, true)) {
                return;
            }
            $url = storage_public_url($normalizada);
            if (! $url) {
                return;
            }
            $rutasVistas[] = $normalizada;
            $vistas[] = ['url' => $url, 'titulo' => $titulo];
        };

        $memorial = $mascota->memorial_registro ?? [];
        if (is_string($memorial)) {
            $memorial = json_decode($memorial, true) ?: [];
        }

        foreach (normalizar_album_memorial_mascota($memorial['album'] ?? []) as $item) {
            $agregar($item[0], $item[1] ?: 'Álbum memorial');
        }

        $agregar($mascota->foto_perfil, 'Foto de perfil');

        $galeria = $mascota->galeria ?? [];
        if (is_string($galeria)) {
            $galeria = json_decode($galeria, true) ?: [];
        }

        foreach (['ven_pre' => 'Foto principal', 'ven_post' => 'Galería'] as $seccion => $tituloBase) {
            foreach (($galeria[$seccion] ?? []) as $item) {
                if (is_array($item) && ! empty($item[0])) {
                    $agregar((string) $item[0], $tituloBase);
                }
            }
        }

        return $vistas;
    }
}

if (! function_exists('mascota_imagenes_publicas')) {
    /**
     * URLs públicas de la foto de perfil y galería de una mascota.
     *
     * @return array<int, string>
     */
    function mascota_imagenes_publicas($mascota): array
    {
        if (! $mascota) {
            return [];
        }

        $urls = [];

        $fotoPrincipal = $mascota->foto_url ?? storage_public_url($mascota->foto_perfil ?? null);
        if ($fotoPrincipal) {
            $urls[] = $fotoPrincipal;
        }

        $galeria = $mascota->galeria ?? null;
        if (is_string($galeria)) {
            $galeria = json_decode($galeria, true);
        }

        if (is_array($galeria)) {
            foreach (['ven_pre', 'ven_post'] as $seccion) {
                if (empty($galeria[$seccion]) || ! is_array($galeria[$seccion])) {
                    continue;
                }
                foreach ($galeria[$seccion] as $grupo) {
                    if (is_string($grupo) && $grupo !== '') {
                        $publica = storage_public_url($grupo);
                        if ($publica) {
                            $urls[] = $publica;
                        }
                        continue;
                    }
                    foreach ((array) $grupo as $imagen) {
                        if (is_array($imagen)) {
                            foreach (['url', 'src', 'original'] as $key) {
                                if (! empty($imagen[$key])) {
                                    $publica = storage_public_url((string) $imagen[$key]);
                                    if ($publica) {
                                        $urls[] = $publica;
                                    }
                                }
                            }
                            continue;
                        }
                        if (! is_string($imagen) || $imagen === '') {
                            continue;
                        }
                        $publica = storage_public_url($imagen);
                        if ($publica) {
                            $urls[] = $publica;
                        }
                    }
                }
            }
        }

        return array_values(array_unique(array_filter($urls)));
    }
}

if (! function_exists('documento_vet_logo_url')) {
    /**
     * Logo institucional del lugar de atención (no foto del profesional).
     */
    function documento_vet_logo_url(?int $lugarAtencionId, $profesional = null): ?string
    {
        if (! $lugarAtencionId) {
            return null;
        }

        $institucion = \App\Models\Instituciones::where('id_lugar_atencion', $lugarAtencionId)->first();
        if ($institucion && ! empty($institucion->foto_perfil)) {
            return storage_public_url($institucion->foto_perfil);
        }

        return null;
    }
}

if (! function_exists('boton_flotante_especialidad_vet')) {
    /**
     * Icono y etiqueta del botón flotante de formularios según especialidad veterinaria.
     *
     * @return array{class: string, title: string, target: string}
     */
    function boton_flotante_especialidad_vet($profesional = null): array
    {
        $default = [
            'class' => 'fas fa-stethoscope fa-2x',
            'title' => 'Formularios veterinaria',
            'target' => '#formularios_medicina_gen',
        ];

        if (! $profesional) {
            return $default;
        }

        $subtipo = Str::lower(trim((string) optional($profesional->SubTipoEspecialidad()->first())->nombre));
        $tipo = Str::lower(trim((string) optional($profesional->TipoEspecialidad()->first())->nombre));
        $especialidad = Str::lower(trim((string) optional($profesional->Especialidad()->first())->nombre));
        $clave = $subtipo ?: $tipo ?: $especialidad;

        $mapa = [
            'otorrinolaringolog' => ['class' => 'fas fa-deaf fa-2x', 'title' => 'Formularios otorrinolaringología'],
            'oftalmolog' => ['class' => 'fas fa-eye fa-2x', 'title' => 'Formularios oftalmología'],
            'dermatolog' => ['class' => 'fas fa-allergies fa-2x', 'title' => 'Formularios dermatología'],
            'cardiolog' => ['class' => 'fas fa-heartbeat fa-2x', 'title' => 'Formularios cardiología'],
            'neurolog' => ['class' => 'fas fa-brain fa-2x', 'title' => 'Formularios neurología'],
            'traumatolog' => ['class' => 'fas fa-bone fa-2x', 'title' => 'Formularios traumatología'],
            'ortoped' => ['class' => 'fas fa-bone fa-2x', 'title' => 'Formularios ortopedia'],
            'urolog' => ['class' => 'fas fa-tint fa-2x', 'title' => 'Formularios urología'],
            'gastroenterolog' => ['class' => 'fas fa-apple-alt fa-2x', 'title' => 'Formularios gastroenterología'],
            'broncopulmonar' => ['class' => 'fas fa-lungs fa-2x', 'title' => 'Formularios broncopulmonar'],
            'cirug' => ['class' => 'fas fa-procedures fa-2x', 'title' => 'Formularios cirugía'],
            'homeopat' => ['class' => 'fas fa-leaf fa-2x', 'title' => 'Formularios homeopatía'],
            'siquiatr' => ['class' => 'fas fa-user-md fa-2x', 'title' => 'Formularios psiquiatría'],
            'psiquiatr' => ['class' => 'fas fa-user-md fa-2x', 'title' => 'Formularios psiquiatría'],
            'quiroprax' => ['class' => 'fas fa-hands fa-2x', 'title' => 'Formularios quiropraxia'],
            'oncolog' => ['class' => 'fas fa-ribbon fa-2x', 'title' => 'Formularios oncología'],
            'nefrolog' => ['class' => 'fas fa-tint fa-2x', 'title' => 'Formularios nefrología'],
            'geriatr' => ['class' => 'fas fa-user-clock fa-2x', 'title' => 'Formularios geriatría'],
            'odontolog' => ['class' => 'fas fa-tooth fa-2x', 'title' => 'Formularios odontología'],
            'medicina interna' => ['class' => 'fas fa-notes-medical fa-2x', 'title' => 'Formularios medicina interna'],
            'medicina general' => ['class' => 'fas fa-stethoscope fa-2x', 'title' => 'Formularios medicina general'],
        ];

        foreach ($mapa as $fragmento => $config) {
            if ($clave !== '' && str_contains($clave, $fragmento)) {
                return array_merge($default, $config);
            }
        }

        return $default;
    }
}

if (! function_exists('id_especialidad_odontologica')) {
    function id_especialidad_odontologica(): int
    {
        return 2;
    }
}

if (! function_exists('id_tipo_odontologia_general')) {
    function id_tipo_odontologia_general(): int
    {
        return 18;
    }
}

if (! function_exists('filtrar_tipos_especialidad_odontologica')) {
    /**
     * En veterinaria solo se usa Odontología general como sub-especialidad dental.
     *
     * @param  \Illuminate\Support\Collection|array  $registros
     * @return \Illuminate\Support\Collection
     */
    function filtrar_tipos_especialidad_odontologica($registros)
    {
        $coleccion = $registros instanceof \Illuminate\Support\Collection
            ? $registros
            : collect($registros);

        return $coleccion
            ->filter(function ($registro) {
                return (int) ($registro->id ?? 0) === id_tipo_odontologia_general();
            })
            ->values();
    }
}

if (! function_exists('aplicar_filtro_tipos_especialidad')) {
    function aplicar_filtro_tipos_especialidad($registros, $idEspecialidad)
    {
        if ((int) $idEspecialidad === id_especialidad_odontologica()) {
            return filtrar_tipos_especialidad_odontologica($registros);
        }

        return $registros instanceof \Illuminate\Support\Collection
            ? $registros
            : collect($registros);
    }
}

if (! function_exists('es_profesional_odontologico')) {
    function es_profesional_odontologico($profesional): bool
    {
        if (! $profesional) {
            return false;
        }

        if ((int) ($profesional->id_especialidad ?? 0) === id_especialidad_odontologica()) {
            return true;
        }

        $tiposOdontologia = [14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 56];

        return ! empty($profesional->id_tipo_especialidad)
            && in_array((int) $profesional->id_tipo_especialidad, $tiposOdontologia, true);
    }
}



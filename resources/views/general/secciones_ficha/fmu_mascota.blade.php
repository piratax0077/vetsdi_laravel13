@php
    $responsableFvu = $responsable_mascota ?? $responsable ?? null;
    $galeriaFvu = collect($galeria_mascota ?? [])->filter()->values();
    $vacunasFvu = collect($vacunas_fvu ?? [])->values();
    $desparasitacionesFvu = collect($desparasitaciones_fvu ?? [])->values();

    $normalizarRegistrosFvu = static function ($records) {
        if (empty($records)) {
            return [];
        }
        if (is_string($records)) {
            $decoded = json_decode($records, true);
            $records = is_array($decoded) ? $decoded : [];
        }
        if (!is_array($records)) {
            return [];
        }
        return array_values(array_filter($records, static function ($item) {
            return is_array($item);
        }));
    };

    if ($vacunasFvu->isEmpty() && !empty($mascota->vacunas_registro)) {
        $vacunasFvu = collect($normalizarRegistrosFvu($mascota->vacunas_registro))
            ->sortByDesc('fecha_dosis')
            ->values();
    }
    if ($desparasitacionesFvu->isEmpty() && !empty($mascota->desparasitaciones_registro)) {
        $desparasitacionesFvu = collect($normalizarRegistrosFvu($mascota->desparasitaciones_registro))
            ->sortByDesc('fecha_dosis')
            ->values();
    }

    $fmtFechaFvu = static function ($fecha) {
        if (empty($fecha)) {
            return '-';
        }
        try {
            return \Carbon\Carbon::parse($fecha)->format('d-m-Y');
        } catch (\Throwable $e) {
            return (string) $fecha;
        }
    };
    $fichasFvu = collect($fichas_veterinarias ?? [])->values();
    $documentosFvu = collect($documentos_mascota ?? [])->values();
    $contactoEmergenciaFvu = null;
    try {
        if (isset($paciente) && method_exists($paciente, 'ContactosEmergencia')) {
            $contactoEmergenciaFvu = $paciente->ContactosEmergencia()->first();
        }
    } catch (\Throwable $e) {
        $contactoEmergenciaFvu = null;
    }
    $contactoNombreFvu = trim((string) (
        data_get($contactoEmergenciaFvu, 'nombre')
        ?: data_get($contactoEmergenciaFvu, 'nombres')
        ?: data_get($responsableFvu, 'nombre')
        ?: data_get($responsableFvu, 'nombres')
        ?: 'Responsable de la mascota'
    ));
    $contactoRutFvu = data_get($contactoEmergenciaFvu, 'rut') ?: data_get($responsableFvu, 'rut') ?: 'Sin registro';
    $contactoTelefonoFvu = data_get($contactoEmergenciaFvu, 'telefono')
        ?: data_get($contactoEmergenciaFvu, 'celular')
        ?: data_get($responsableFvu, 'telefono')
        ?: data_get($responsableFvu, 'celular')
        ?: 'Sin registro';
    $contactoEmailFvu = data_get($contactoEmergenciaFvu, 'email')
        ?: data_get($responsableFvu, 'email')
        ?: 'Sin registro';

    $edadMascota = null;
    if (!empty($mascota->fecha_nacimiento)) {
        try {
            $edadMascota = \Carbon\Carbon::parse($mascota->fecha_nacimiento)->age;
        } catch (\Throwable $e) {
            $edadMascota = null;
        }
    }

    $fotoMascota = $mascota->foto_url ?? storage_public_url($mascota->foto_perfil ?? null) ?? asset('images/iconos/usuario_profesional.svg');
    $sexoMascota = $mascota->sexo === 'M' ? 'Masculino' : ($mascota->sexo === 'F' ? 'Femenino' : 'Sin registro');
    $especieMascota = optional($mascota->especieMascota)->nombre ?: ($mascota->otra_especie ?: $mascota->especie ?: 'N/N');
    $especieMascotaNormalizada = strtolower(trim((string) $especieMascota));
    $mostrarOdontogramaCanino = str_contains($especieMascotaNormalizada, 'canin') || str_contains($especieMascotaNormalizada, 'perro');
    $mostrarOdontogramaFelino = str_contains($especieMascotaNormalizada, 'felin') || str_contains($especieMascotaNormalizada, 'gato');
    $razaMascota = optional($mascota->razaMascota)->nombre ?: '-';
    $tamanoMascota = optional($mascota->tamanoMascota)->nombre ?: ($mascota->tamano ?: '-');
    $colorMascota = '-';

    $cronicosRegistradosFvu = collect();

    if (!empty($mascota->enfermedad_cronica)
        && !$cronicosRegistradosFvu->contains('nombre', trim((string) $mascota->enfermedad_cronica))) {
        $cronicosRegistradosFvu->push([
            'nombre' => trim((string) $mascota->enfermedad_cronica),
            'comentario' => 'Antecedente histórico',
        ]);
    }

    $cantidadVacunas = $vacunasFvu->count();
    $cantidadDesparasitaciones = $desparasitacionesFvu->count();
    $cantidadDocumentos = $documentosFvu->count();
    $cantidadAlergias = 0;
    $tieneCronico = $cronicosRegistradosFvu->isNotEmpty() || !empty($mascota->enfermedad_cronica);
    $ultimaDesparasitacion = !empty($mascota->ultima_desparasitacion)
        ? \Carbon\Carbon::parse($mascota->ultima_desparasitacion)->format('d-m-Y')
        : 'Sin registro';

    $cirugiasTexto = trim((string) ($mascota->cirugias ?? ''));
    $cirugiasLista = collect(preg_split('/[\r\n]+/', $cirugiasTexto))
        ->map(function ($item) {
            return trim($item);
        })
        ->filter()
        ->values();

    $tratamientosLista = $fichasFvu->flatMap(function ($ficha) {
        return collect(data_get($ficha, 'PresupuestosMascota', []))->map(function ($presupuesto) {
            return trim(
                (string) (
                    data_get($presupuesto, 'tratamiento') ?:
                    data_get($presupuesto, 'descripcion') ?:
                    data_get($presupuesto, 'observaciones') ?:
                    'Tratamiento registrado'
                )
            );
        });
    })->filter()->unique()->values();

    $diagnosticosLista = $fichasFvu->map(function ($ficha) {
        return trim((string) (
            data_get($ficha, 'hipotesis_diagnostico') ?:
            data_get($ficha, 'diagnostico_ce10') ?:
            data_get($ficha, 'motivo_consulta') ?:
            data_get($ficha, 'observaciones') ?:
            ''
        ));
    })->filter()->unique()->values();

    $microchip = $mascota->chip ?: 'Sin registro';
    $pesoMascota = data_get($mascota, 'peso') ?: 'Sin registro';
    $telefonoResponsable = $responsableFvu ? trim(($responsableFvu->telefono_uno ?? '') . ' / ' . ($responsableFvu->telefono_dos ?? '')) : 'Sin registro';
    $nombreResponsable = $responsableFvu
        ? trim(($responsableFvu->nombres ?? '') . ' ' . ($responsableFvu->apellido_uno ?? '') . ' ' . ($responsableFvu->apellido_dos ?? ''))
        : 'Sin registro';

    $esVistaPacienteMascota = request()->routeIs('paciente.mascota.ficha_veterinaria');

    $tarjetasVitalesFvu = [
        [
            'titulo' => 'Vacunas',
            'icono' => 'gruposanguineo.png',
            'tono' => 'verde',
            'valor' => $cantidadVacunas > 0 ? $cantidadVacunas . ' registro(s)' : 'Sin registro',
            'alerta' => false,
            'mostrar_ver' => $cantidadVacunas > 0,
            'ver_modal' => '#modal_fvu_vacunas',
            'ver_target' => '#vacunas_c',
        ],
        [
            'titulo' => 'Alergias',
            'icono' => 'alergias.png',
            'tono' => 'rojo',
            'valor' => $cantidadAlergias > 0 ? 'Sí' : 'No',
            'alerta' => $cantidadAlergias > 0,
            'mostrar_ver' => $cantidadAlergias > 0,
            'ver_url' => null,
            'ver_target' => '#seccion_alergias',
            'ver_mas_info' => true,
        ],
        [
            'titulo' => 'Desparasitaciones',
            'icono' => 'transfusion.jpg',
            'tono' => 'ambar',
            'valor' => $cantidadDesparasitaciones > 0 ? $cantidadDesparasitaciones . ' registro(s)' : 'Sin registro',
            'alerta' => false,
            'mostrar_ver' => $cantidadDesparasitaciones > 0,
            'ver_modal' => '#modal_fvu_desparasitaciones',
            'ver_target' => '#desparasitacion_c',
        ],
        [
            'titulo' => 'Condición crónica',
            'icono' => 'enfermedad-cronica.png',
            'tono' => 'morado',
            'valor' => $tieneCronico ? 'Sí' : 'No',
            'alerta' => $tieneCronico,
            'mostrar_ver' => $tieneCronico,
            'ver_url' => null,
            'ver_target' => '#seccion_enfer_cronicas',
            'ver_mas_info' => true,
        ],
        [
            'titulo' => 'Esterilizado/a',
            'icono' => 'esterilizacion.png',
            'tono' => 'azul',
            'valor' => $mascota->esterilizado ? 'Sí' : 'No',
            'alerta' => false,
            'mostrar_ver' => false,
        ],
        [
            'titulo' => 'Peso',
            'icono' => 'peso.png',
            'tono' => 'celeste',
            'valor' => $pesoMascota,
            'alerta' => false,
            'mostrar_ver' => false,
        ],
    ];

    // Textos de apoyo para la cabecera
    $nombreMascotaFvu = $mascota->nombre ?? 'Mascota';
    $edadTextoFvu = $edadMascota !== null
        ? $edadMascota . ' ' . ((int) $edadMascota === 1 ? 'año' : 'años')
        : 'Sin registro';
    $fechaNacimientoFvu = !empty($mascota->fecha_nacimiento)
        ? \Carbon\Carbon::parse($mascota->fecha_nacimiento)->format('d-m-Y')
        : null;
    $fechaUltimaFichaFvu = data_get($fichasFvu->first(), 'created_at');
    $ultimaAtencionFvu = $fechaUltimaFichaFvu ? \Carbon\Carbon::parse($fechaUltimaFichaFvu)->format('d-m-Y') : null;

    $arcadaSuperiorIzquierda = range(109, 101);
    $arcadaSuperiorDerecha = range(201, 209);
    $arcadaInferiorIzquierda = range(409, 401);
    $arcadaInferiorDerecha = range(301, 309);

    $fvuVistaPaciente = request()->routeIs('paciente.mascota.ficha_veterinaria');
    $fvuEmbebidaEnFicha = !empty($id_ficha_atencion ?? null);
    $fvuOcultarTituloInterno = !empty($fvuOcultarTituloInterno);
    $fvuMostrarTitulo = !$fvuVistaPaciente && !$fvuEmbebidaEnFicha && !$fvuOcultarTituloInterno;
    $fvuModoEmbebido = $fvuVistaPaciente || $fvuEmbebidaEnFicha;
@endphp

<link rel="stylesheet" href="{{ asset('css/ficha_medica_unica.css') }}">
<link rel="stylesheet" href="{{ asset('css/ficha_veterinaria_unica.css') }}?v={{ @filemtime(public_path('css/ficha_veterinaria_unica.css')) }}">

<div class="fvu-vet-shell {{ $fvuModoEmbebido ? 'fvu-vet-shell--embedded' : 'user-profile user-card mt-0' }} {{ $fvuEmbebidaEnFicha ? 'fvu-vet-shell--en-atencion' : '' }}" data-mascota-id="{{ $mascota->id ?? '' }}" data-fvu-registros-url="{{ !empty($mascota->id) ? route('paciente.mascotas.registros_sanitarios', ['mascotaId' => $mascota->id]) : '' }}">
    <div class="fvu-contenedor">
        @if($fvuMostrarTitulo)
            <div class="fvu-title-block">
                <span class="fvu-title-icon"><i class="fas fa-paw" aria-hidden="true"></i></span>
                <div>
                    <h4>Ficha Veterinaria Única</h4>
                    <small>Resumen clínico y antecedentes de {{ $mascota->nombre ?? 'la mascota' }}</small>
                </div>
            </div>
        @endif

        {{-- Cabecera con la identificación de la mascota --}}
        <section class="fvu-perfil" id="enf-cron">
            <input type="hidden" name="id_paciente" id="id_paciente" value="{{ $mascota->id }}">

            <div class="fvu-perfil__cabecera">
                <div class="fvu-perfil__identidad">
                    <div class="fvu-perfil__foto">
                        <img id="profile-image"
                            src="{{ $fotoMascota }}"
                            onerror="this.onerror=null;this.src='{{ asset('images/iconos/usuario_profesional.svg') }}';"
                            alt="Foto de {{ $mascota->nombre ?? 'la mascota' }}">
                    </div>
                    <div class="fvu-perfil__texto">
                        @unless($fvuMostrarTitulo)
                            <span class="fvu-perfil__etiqueta">Ficha Veterinaria Única</span>
                        @endunless
                        <h2 class="fvu-perfil__nombre">{{ $nombreMascotaFvu }}</h2>
                        <div class="fvu-perfil__chips">
                            <span class="fvu-chip"><i class="fas fa-microchip" aria-hidden="true"></i> Microchip: {{ $microchip }}</span>
                            <span class="fvu-chip">
                                <i class="fas fa-paw" aria-hidden="true"></i>
                                {{ $especieMascota }}@if ($razaMascota !== '-') · {{ $razaMascota }}@endif
                            </span>
                            @if ($ultimaAtencionFvu)
                                <span class="fvu-chip"><i class="fas fa-calendar-check" aria-hidden="true"></i> Última atención: {{ $ultimaAtencionFvu }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="fvu-perfil__acciones">
                    <button type="button" class="fvu-boton fvu-boton--claro"
                        data-toggle="modal" data-target="#modal_contacto_emergencia_fvu">
                        <i class="feather icon-phone-call" aria-hidden="true"></i>
                        Tutor y contacto de emergencia
                    </button>
                    <button class="fvu-boton fvu-boton--vidrio collapsed" type="button"
                        data-toggle="collapse" data-target="#cabecera_info"
                        aria-expanded="false" aria-controls="cabecera_info">
                        <i class="feather icon-plus-circle fvu-boton__icono-giro" aria-hidden="true"></i>
                        <span class="fvu-texto-abrir">Ver más información</span>
                        <span class="fvu-texto-cerrar">Ocultar información</span>
                    </button>
                </div>
            </div>

            <dl class="fvu-perfil__datos">
                <div class="fvu-dato">
                    <dt><i class="fas fa-birthday-cake" aria-hidden="true"></i> Edad</dt>
                    <dd>
                        {{ $edadTextoFvu }}
                        @if ($fechaNacimientoFvu)
                            <small title="Fecha de nacimiento">({{ $fechaNacimientoFvu }})</small>
                        @endif
                    </dd>
                </div>
                <div class="fvu-dato">
                    <dt><i class="fas fa-venus-mars" aria-hidden="true"></i> Sexo</dt>
                    <dd>{{ $sexoMascota }}</dd>
                </div>
                <div class="fvu-dato">
                    <dt><i class="fas fa-paw" aria-hidden="true"></i> Especie</dt>
                    <dd>{{ $especieMascota }}</dd>
                </div>
                <div class="fvu-dato">
                    <dt><i class="fas fa-dna" aria-hidden="true"></i> Raza</dt>
                    <dd>{{ $razaMascota }}</dd>
                </div>
                <div class="fvu-dato">
                    <dt><i class="fas fa-ruler-vertical" aria-hidden="true"></i> Tamaño</dt>
                    <dd>{{ $tamanoMascota }}</dd>
                </div>
                <div class="fvu-dato">
                    <dt><i class="fas fa-palette" aria-hidden="true"></i> Color</dt>
                    <dd>{{ $colorMascota }}</dd>
                </div>
            </dl>
        </section>

        {{-- Panel desplegable con información adicional --}}
        <div id="cabecera_info" class="collapse fvu-more-panel" aria-labelledby="enf-cron-mascota" data-parent="#cabecera_info">
            <div class="fvu-panel">
                <div class="fvu-panel__cabecera">
                    <div id="enf-cron-mascota">
                        <h3 class="fvu-seccion__titulo">Información adicional</h3>
                        <p class="fvu-seccion__bajada">Tutor, antecedentes y documentos de {{ $nombreMascotaFvu }}</p>
                    </div>
                    <button type="button" class="fvu-boton-cerrar" data-toggle="collapse" data-target="#cabecera_info"
                        aria-controls="cabecera_info" aria-expanded="false" aria-label="Cerrar información adicional">
                        <i class="feather icon-x" aria-hidden="true"></i>
                    </button>
                </div>

                <ul class="nav fvu-pestanas" id="myTabMascota" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="fvu-pestana active" id="seccion_ident_contacto-tab" data-toggle="tab" href="#seccion_ident_contacto" role="tab" aria-controls="seccion_ident_contacto" aria-selected="true">
                            <i class="fas fa-user" aria-hidden="true"></i> Responsable
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="fvu-pestana" id="seccion_enfer_cronicas-tab" data-toggle="tab" href="#seccion_enfer_cronicas" role="tab" aria-controls="seccion_enfer_cronicas" aria-selected="false">
                            <i class="fas fa-heartbeat" aria-hidden="true"></i> Enfermedades crónicas
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="fvu-pestana" id="seccion_alergias-tab" data-toggle="tab" href="#seccion_alergias" role="tab" aria-controls="seccion_alergias" aria-selected="false">
                            <i class="fas fa-allergies" aria-hidden="true"></i> Alergias
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="fvu-pestana" id="seccion_ultimas_cirugia-tab" data-toggle="tab" href="#seccion_ultimas_cirugia" role="tab" aria-controls="seccion_ultimas_cirugia" aria-selected="false">
                            <i class="fas fa-cut" aria-hidden="true"></i> Últimas cirugías
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="fvu-pestana" id="seccion_ultimo_tratamiento-tab" data-toggle="tab" href="#seccion_ultimo_tratamiento" role="tab" aria-controls="seccion_ultimo_tratamiento" aria-selected="false">
                            <i class="fas fa-pills" aria-hidden="true"></i> Últimos tratamientos
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="fvu-pestana" id="discap-tab" data-toggle="tab" href="#discap" role="tab" aria-controls="discap" aria-selected="false">
                            <i class="fas fa-folder-open" aria-hidden="true"></i> Documentos
                        </a>
                    </li>
                </ul>

                <div class="tab-content" id="at-oftalmo-mascota">
                    <div class="tab-pane fade show active" id="seccion_ident_contacto" role="tabpanel" aria-labelledby="seccion_ident_contacto-tab">
                        <div class="fvu-contactos">
                            <div class="fvu-contacto">
                                <span class="fvu-icono fvu-tono--morado"><i class="fas fa-user" aria-hidden="true"></i></span>
                                <div class="fvu-contacto__texto">
                                    <span class="fvu-contacto__etiqueta">Responsable</span>
                                    <span class="fvu-contacto__valor">{{ $nombreResponsable ?: 'Sin registro' }}</span>
                                    @if (!empty($responsableFvu->rut))
                                        <span class="fvu-contacto__extra">{{ $responsableFvu->rut }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="fvu-contacto">
                                <span class="fvu-icono fvu-tono--verde"><i class="fas fa-phone-alt" aria-hidden="true"></i></span>
                                <div class="fvu-contacto__texto">
                                    <span class="fvu-contacto__etiqueta">Teléfono</span>
                                    <span class="fvu-contacto__valor">{{ trim($telefonoResponsable, ' /') ?: 'Sin registro' }}</span>
                                </div>
                            </div>
                            <div class="fvu-contacto">
                                <span class="fvu-icono fvu-tono--azul"><i class="fas fa-envelope" aria-hidden="true"></i></span>
                                <div class="fvu-contacto__texto">
                                    <span class="fvu-contacto__etiqueta">Email</span>
                                    <span class="fvu-contacto__valor">{{ $responsableFvu->email ?? 'Sin registro' }}</span>
                                </div>
                            </div>
                            <div class="fvu-contacto">
                                <span class="fvu-icono fvu-tono--ambar"><i class="fas fa-paw" aria-hidden="true"></i></span>
                                <div class="fvu-contacto__texto">
                                    <span class="fvu-contacto__etiqueta">Mascota</span>
                                    <span class="fvu-contacto__valor">{{ $especieMascota }} / {{ $razaMascota }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="seccion_enfer_cronicas" role="tabpanel" aria-labelledby="seccion_enfer_cronicas-tab">
                        <div class="fvu-tabla-envoltura">
                            <table class="fvu-tabla">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Comentario</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="fvu-tabla__fuerte">Enfermedad crónica</td>
                                        <td>
                                            @forelse ($cronicosRegistradosFvu as $cronico)
                                                <strong>{{ $cronico['nombre'] }}</strong>
                                                @if (!$loop->last)<br>@endif
                                            @empty
                                                Sin registros
                                            @endforelse
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="seccion_alergias" role="tabpanel" aria-labelledby="seccion_alergias-tab">
                        <div class="fvu-tabla-envoltura">
                            <table class="fvu-tabla">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Comentario</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="2">Sin registros de alergias</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="seccion_ultimas_cirugia" role="tabpanel" aria-labelledby="seccion_ultimas_cirugia-tab">
                        <div class="fvu-tabla-envoltura">
                            <table class="fvu-tabla">
                                <thead>
                                    <tr>
                                        <th>Procedimiento</th>
                                        <th>Detalle</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($cirugiasLista as $cirugia)
                                        <tr>
                                            <td class="fvu-tabla__fuerte fvu-tabla__nowrap">Cirugía registrada</td>
                                            <td>{{ $cirugia }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2">Sin registros</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="seccion_ultimo_tratamiento" role="tabpanel" aria-labelledby="seccion_ultimo_tratamiento-tab">
                        <ul class="fvu-lista">
                            @forelse ($tratamientosLista as $tratamiento)
                                <li><i class="fas fa-check-circle" aria-hidden="true"></i> {{ $tratamiento }}</li>
                            @empty
                                <li class="fvu-lista__vacia">No hay registros</li>
                            @endforelse
                        </ul>
                    </div>

                    <div class="tab-pane fade" id="discap" role="tabpanel" aria-labelledby="discap-tab">
                        <div class="fvu-tabla-envoltura">
                            <table class="fvu-tabla">
                                <thead>
                                    <tr>
                                        <th>Tipo</th>
                                        <th>Nombre</th>
                                        <th>Fecha</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($documentosFvu as $documento)
                                        <tr>
                                            <td class="fvu-tabla__fuerte">{{ $documento['tipo'] ?? '-' }}</td>
                                            <td>{{ $documento['nombre'] ?? '-' }}</td>
                                            <td class="fvu-tabla__nowrap">{{ !empty($documento['fecha']) ? \Carbon\Carbon::parse($documento['fecha'])->format('d-m-Y') : '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3">Sin documentos</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="fvu-panel-principal">
            {{-- Indicadores de salud --}}
            <section class="fvu-zone-vitals" aria-labelledby="fvu_titulo_salud">
                <div class="fvu-seccion__encabezado">
                    <div>
                        <h3 class="fvu-seccion__titulo" id="fvu_titulo_salud">Estado de salud</h3>
                        <p class="fvu-seccion__bajada">Indicadores clave de {{ $nombreMascotaFvu }}</p>
                    </div>
                </div>

                <div class="fvu-indicadores">
                    @foreach ($tarjetasVitalesFvu as $tarjetaVital)
                        <div class="fvu-vital-item fvu-indicador fvu-tono--morado">
                            <div class="fvu-indicador__superior">
                                <span class="fvu-icono fvu-icono--imagen"><img src="{{ asset('images/iconos/' . $tarjetaVital['icono']) }}" alt="" aria-hidden="true"></span>
                                @if (!empty($tarjetaVital['mostrar_ver']))
                                    @if (!empty($tarjetaVital['ver_modal']))
                                        <button type="button" class="fvu-card-ver-btn fvu-ver-modal" data-toggle="modal"
                                            data-target="{{ $tarjetaVital['ver_modal'] }}"
                                            aria-label="Ver {{ $tarjetaVital['titulo'] }}">Ver <i class="fas fa-arrow-right" aria-hidden="true"></i></button>
                                    @elseif (!empty($tarjetaVital['ver_url']))
                                        <a href="{{ $tarjetaVital['ver_url'] }}" class="fvu-card-ver-btn"
                                            aria-label="Ver {{ $tarjetaVital['titulo'] }}">Ver <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
                                    @elseif (!empty($tarjetaVital['ver_mas_info']))
                                        <button type="button" class="fvu-card-ver-btn fvu-ver-mas-info"
                                            data-fvu-tab="{{ $tarjetaVital['ver_target'] }}"
                                            aria-label="Ver {{ $tarjetaVital['titulo'] }}">Ver <i class="fas fa-arrow-right" aria-hidden="true"></i></button>
                                    @else
                                        <button type="button" class="fvu-card-ver-btn fvu-ver-collapse"
                                            data-target="{{ $tarjetaVital['ver_target'] }}"
                                            aria-label="Ver {{ $tarjetaVital['titulo'] }}">Ver <i class="fas fa-arrow-right" aria-hidden="true"></i></button>
                                    @endif
                                @endif
                            </div>
                            <div>
                                <h5 class="fvu-vital-title">{{ $tarjetaVital['titulo'] }}</h5>
                                <span class="fvu-vital-value {{ !empty($tarjetaVital['alerta']) ? 'fvu-vital-value--alerta' : '' }}">{{ $tarjetaVital['valor'] }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- Resumen de antecedentes --}}
            <section class="fvu-zone-summaries" aria-labelledby="fvu_titulo_resumen">
                <div class="fvu-seccion__encabezado">
                    <div>
                        <h3 class="fvu-seccion__titulo" id="fvu_titulo_resumen">Resumen clínico</h3>
                        <p class="fvu-seccion__bajada">Lo más relevante de sus antecedentes</p>
                    </div>
                </div>

                <div class="fvu-resumenes">
                    <article class="fvu-resumen fvu-tono--morado">
                        <div class="fvu-resumen__cabecera">
                            <span class="fvu-icono fvu-icono--imagen"><img src="{{ asset('images/iconos/tto-curso.png') }}" alt="" aria-hidden="true"></span>
                            <h5 class="fvu-resumen__titulo">Tratamientos en curso</h5>
                            @if ($tratamientosLista->isNotEmpty())
                                <span class="fvu-contador">{{ $tratamientosLista->count() }}</span>
                            @endif
                        </div>
                        @if ($tratamientosLista->isNotEmpty())
                            <ul class="fvu-resumen__lista">
                                @foreach ($tratamientosLista->take(3) as $tratamiento)
                                    <li>{{ $tratamiento }}</li>
                                @endforeach
                                @if ($tratamientosLista->count() > 3)
                                    <li class="fvu-resumen__mas">y {{ $tratamientosLista->count() - 3 }} más</li>
                                @endif
                            </ul>
                            <button type="button" class="fvu-card-ver-btn fvu-ver-mas-morado"
                                data-fvu-tab="#seccion_ultimo_tratamiento">Ver detalle <i class="fas fa-arrow-right" aria-hidden="true"></i></button>
                        @else
                            <p class="fvu-vacio"><i class="feather icon-inbox" aria-hidden="true"></i> No hay registros</p>
                        @endif
                    </article>

                    <article class="fvu-resumen fvu-tono--morado">
                        <div class="fvu-resumen__cabecera">
                            <span class="fvu-icono fvu-icono--imagen"><img src="{{ asset('images/iconos/meds-cronicos.png') }}" alt="" aria-hidden="true"></span>
                            <h5 class="fvu-resumen__titulo">Enfermedades crónicas</h5>
                            @if ($tieneCronico && $cronicosRegistradosFvu->isNotEmpty())
                                <span class="fvu-contador">{{ $cronicosRegistradosFvu->count() }}</span>
                            @endif
                        </div>
                        @if ($tieneCronico)
                            <ul class="fvu-resumen__lista">
                                @foreach ($cronicosRegistradosFvu as $cronico)
                                    <li>{{ $cronico['nombre'] }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="fvu-card-ver-btn fvu-ver-mas-info"
                                data-fvu-tab="#seccion_enfer_cronicas">Ver detalle <i class="fas fa-arrow-right" aria-hidden="true"></i></button>
                        @else
                            <p class="fvu-vacio"><i class="feather icon-inbox" aria-hidden="true"></i> No hay registros</p>
                        @endif
                    </article>

                    <article class="fvu-resumen fvu-tono--morado">
                        <div class="fvu-resumen__cabecera">
                            <span class="fvu-icono fvu-icono--imagen"><img src="{{ asset('images/iconos/ant-qx.png') }}" alt="" aria-hidden="true"></span>
                            <h5 class="fvu-resumen__titulo">Cirugías recientes</h5>
                            @if ($cirugiasLista->isNotEmpty())
                                <span class="fvu-contador">{{ $cirugiasLista->count() }}</span>
                            @endif
                        </div>
                        @if ($cirugiasLista->isNotEmpty())
                            <ul class="fvu-resumen__lista">
                                @foreach ($cirugiasLista->take(3) as $cirugia)
                                    <li>{{ $cirugia }}</li>
                                @endforeach
                                @if ($cirugiasLista->count() > 3)
                                    <li class="fvu-resumen__mas">y {{ $cirugiasLista->count() - 3 }} más</li>
                                @endif
                            </ul>
                            <button type="button" class="fvu-card-ver-btn fvu-ver-mas-info"
                                data-fvu-tab="#seccion_ultimas_cirugia">Ver detalle <i class="fas fa-arrow-right" aria-hidden="true"></i></button>
                        @else
                            <p class="fvu-vacio"><i class="feather icon-inbox" aria-hidden="true"></i> No hay registros</p>
                        @endif
                    </article>

                    <article class="fvu-resumen fvu-tono--morado">
                        <div class="fvu-resumen__cabecera">
                            <span class="fvu-icono fvu-icono--imagen"><img src="{{ asset('images/iconos/prot-ort.png') }}" alt="" aria-hidden="true"></span>
                            <h5 class="fvu-resumen__titulo">Documentos clínicos</h5>
                            @if ($cantidadDocumentos > 0)
                                <span class="fvu-contador">{{ $cantidadDocumentos }}</span>
                            @endif
                        </div>
                        @if ($cantidadDocumentos > 0)
                            <ul class="fvu-resumen__lista">
                                <li>{{ $cantidadDocumentos }} documento(s) registrado(s)</li>
                            </ul>
                            <button type="button" class="fvu-card-ver-btn fvu-ver-mas-info"
                                data-fvu-tab="#discap">Ver detalle <i class="fas fa-arrow-right" aria-hidden="true"></i></button>
                        @else
                            <p class="fvu-vacio"><i class="feather icon-inbox" aria-hidden="true"></i> No hay registros</p>
                        @endif
                    </article>
                </div>
            </section>
        </div>

        {{-- Historial por secciones --}}
        <section class="fvu-historial-full" aria-labelledby="fvu_titulo_historial">
            <div class="fvu-seccion__encabezado">
                <div>
                    <h3 class="fvu-seccion__titulo" id="fvu_titulo_historial">Historial veterinario</h3>
                    <p class="fvu-seccion__bajada">Seleccione una sección para ver el detalle</p>
                </div>
            </div>

            <div class="fvu-acordeones">
                <div class="fvu-acordeon fvu-tono--morado">
                    <div id="histo_medico">
                        <button class="fvu-acordeon__boton collapsed" type="button" data-toggle="collapse" data-target="#histo_medico_c" aria-expanded="false" aria-controls="histo_medico_c">
                            <span class="fvu-icono"><i class="fas fa-stethoscope" aria-hidden="true"></i></span>
                            <span class="fvu-acordeon__texto">
                                <strong>Historial Veterinario</strong>
                                <small>Atenciones, profesionales y diagnósticos</small>
                            </span>
                            @if ($fichasFvu->isNotEmpty())
                                <span class="fvu-contador">{{ $fichasFvu->count() }}</span>
                            @endif
                            <span class="fvu-acordeon__flecha"><i class="feather icon-chevron-down" aria-hidden="true"></i></span>
                        </button>
                    </div>
                    <div id="histo_medico_c" class="collapse" aria-labelledby="histo_medico" data-parent="#histo_medico">
                        <div class="fvu-acordeon__cuerpo">
                            <div class="fvu-tabla-envoltura">
                                <table class="fvu-tabla">
                                    <thead>
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Profesional</th>
                                            <th>Diagnóstico</th>
                                            <th>Ficha</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($fichasFvu as $ficha)
                                            @php
                                                $profesionalFicha = trim(
                                                    data_get($ficha, 'Profesional.nombres', '') . ' ' .
                                                    data_get($ficha, 'Profesional.apellido_uno', '') . ' ' .
                                                    data_get($ficha, 'Profesional.apellido_dos', '')
                                                );
                                                $fechaFicha = data_get($ficha, 'created_at');
                                                $diagnosticoFicha = data_get($ficha, 'hipotesis_diagnostico')
                                                    ?: data_get($ficha, 'diagnostico_ce10')
                                                    ?: data_get($ficha, 'motivo_consulta')
                                                    ?: data_get($ficha, 'observaciones')
                                                    ?: '-';
                                            @endphp
                                            <tr>
                                                <td class="fvu-tabla__nowrap"><span class="badge badge-secondary">{{ $fechaFicha ? \Carbon\Carbon::parse($fechaFicha)->format('d-m-Y') : '-' }}</span></td>
                                                <td class="fvu-tabla__fuerte">{{ $profesionalFicha !== '' ? $profesionalFicha : '-' }}</td>
                                                <td>{{ $diagnosticoFicha }}</td>
                                                <td><span class="fvu-etiqueta-ficha">#{{ data_get($ficha, 'id', '-') }}</span></td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4">No existen registros</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="fvu-acordeon fvu-tono--morado">
                    <div id="vacunas">
                        <button class="fvu-acordeon__boton collapsed" type="button" data-toggle="collapse" data-target="#vacunas_c" aria-expanded="false" aria-controls="vacunas_c">
                            <span class="fvu-icono"><i class="fas fa-syringe" aria-hidden="true"></i></span>
                            <span class="fvu-acordeon__texto">
                                <strong>Registro de Vacunas</strong>
                                <small>Dosis aplicadas y próximas fechas</small>
                            </span>
                            <span class="fvu-acordeon__flecha"><i class="feather icon-chevron-down" aria-hidden="true"></i></span>
                        </button>
                    </div>
                    <div id="vacunas_c" class="collapse" aria-labelledby="vacunas" data-parent="#vacunas">
                        <div class="fvu-acordeon__cuerpo">
                            <div class="fvu-tabla-envoltura">
                                <table class="fvu-tabla">
                                    <thead>
                                        <tr>
                                            <th>Edad</th>
                                            <th>Fecha dosis</th>
                                            <th>Vacuna</th>
                                            <th>Próx. dosis</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($vacunasFvu as $vacuna)
                                            <tr>
                                                <td class="align-middle">{{ $vacuna['edad'] ?? '-' }}</td>
                                                <td class="align-middle"><span class="badge badge-secondary">{{ !empty($vacuna['fecha_dosis']) ? \Carbon\Carbon::parse($vacuna['fecha_dosis'])->format('d-m-Y') : '-' }}</span></td>
                                                <td class="align-middle">{{ $vacuna['vacuna'] ?? '-' }}</td>
                                                <td class="align-middle"><span class="badge badge-info">{{ !empty($vacuna['proxima_dosis']) ? \Carbon\Carbon::parse($vacuna['proxima_dosis'])->format('d-m-Y') : '-' }}</span></td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4">Sin registros</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="fvu-acordeon fvu-tono--morado">
                    <div id="desparasitacion">
                        <button class="fvu-acordeon__boton collapsed" type="button" data-toggle="collapse" data-target="#desparasitacion_c" aria-expanded="false" aria-controls="desparasitacion_c">
                            <span class="fvu-icono"><i class="fas fa-bug" aria-hidden="true"></i></span>
                            <span class="fvu-acordeon__texto">
                                <strong>Registro de Desparasitación</strong>
                                <small>Antiparasitarios aplicados y próximas dosis</small>
                            </span>
                            <span class="fvu-acordeon__flecha"><i class="feather icon-chevron-down" aria-hidden="true"></i></span>
                        </button>
                    </div>
                    <div id="desparasitacion_c" class="collapse" aria-labelledby="desparasitacion" data-parent="#desparasitacion">
                        <div class="fvu-acordeon__cuerpo">
                            <div class="fvu-tabla-envoltura">
                                <table class="fvu-tabla">
                                    <thead>
                                        <tr>
                                            <th>Fecha dosis</th>
                                            <th>Antiparasitario</th>
                                            <th>Tipo</th>
                                            <th>Próx. dosis</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($desparasitacionesFvu as $registro)
                                            <tr>
                                                <td class="align-middle"><span class="badge badge-secondary">{{ !empty($registro['fecha_dosis']) ? \Carbon\Carbon::parse($registro['fecha_dosis'])->format('d-m-Y') : '-' }}</span></td>
                                                <td class="align-middle">{{ $registro['antiparasitario'] ?? '-' }}</td>
                                                <td class="align-middle">{{ $registro['tipo'] ?? '-' }}</td>
                                                <td class="align-middle"><span class="badge badge-info">{{ !empty($registro['proxima_dosis']) ? \Carbon\Carbon::parse($registro['proxima_dosis'])->format('d-m-Y') : '-' }}</span></td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4">Sin registros</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="fvu-acordeon fvu-tono--morado">
                    <div id="odonto_felino">
                        <button class="fvu-acordeon__boton collapsed" type="button" data-toggle="collapse" data-target="#odonto_felino_c" aria-expanded="false" aria-controls="odonto_felino_c">
                            <span class="fvu-icono"><i class="fas fa-tooth" aria-hidden="true"></i></span>
                            <span class="fvu-acordeon__texto">
                                <strong>Historial Odontológico</strong>
                                <small>Odontograma y estado de cada pieza dental</small>
                            </span>
                            <span class="fvu-acordeon__flecha"><i class="feather icon-chevron-down" aria-hidden="true"></i></span>
                        </button>
                    </div>
                    <div id="odonto_felino_c" class="collapse" aria-labelledby="odonto_felino" data-parent="#odonto_felino">
                        <div class="fvu-acordeon__cuerpo">
                            @if ($mostrarOdontogramaCanino || $mostrarOdontogramaFelino)
                                @include('general.secciones_ficha.partials.odontograma_mascota')
                            @else
                                <div class="fvu-aviso">
                                    <i class="fas fa-info-circle" aria-hidden="true"></i>
                                    El odontograma de mascota se muestra cuando la especie corresponde a perro o gato.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="fvu-acordeon fvu-tono--morado">
                    <div id="documentos">
                        <button class="fvu-acordeon__boton collapsed" type="button" data-toggle="collapse" data-target="#documentos_c" aria-expanded="false" aria-controls="documentos_c">
                            <span class="fvu-icono"><i class="fas fa-file-alt" aria-hidden="true"></i></span>
                            <span class="fvu-acordeon__texto">
                                <strong>Documentación</strong>
                                <small>Consentimientos y presupuestos veterinarios</small>
                            </span>
                            @if ($cantidadDocumentos > 0)
                                <span class="fvu-contador">{{ $cantidadDocumentos }}</span>
                            @endif
                            <span class="fvu-acordeon__flecha"><i class="feather icon-chevron-down" aria-hidden="true"></i></span>
                        </button>
                    </div>
                    <div id="documentos_c" class="collapse" aria-labelledby="documentos" data-parent="#documentos">
                        <div class="fvu-acordeon__cuerpo">
                            <div class="fvu-tabla-envoltura">
                                <table class="fvu-tabla">
                                    <thead>
                                        <tr>
                                            <th>Tipo</th>
                                            <th>Nombre</th>
                                            <th>Fecha</th>
                                            <th>Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($documentosFvu as $documento)
                                            <tr>
                                                <td class="fvu-tabla__fuerte">{{ $documento['tipo'] ?? '-' }}</td>
                                                <td>{{ $documento['nombre'] ?? '-' }}</td>
                                                <td class="fvu-tabla__nowrap">{{ !empty($documento['fecha']) ? \Carbon\Carbon::parse($documento['fecha'])->format('d-m-Y') : '-' }}</td>
                                                <td>{{ $documento['estado'] ?? '-' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4">Sin documentos</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                @if ($galeriaFvu->isNotEmpty())
                    <div class="fvu-acordeon fvu-tono--morado">
                        <div id="galeria">
                            <button class="fvu-acordeon__boton collapsed" type="button" data-toggle="collapse" data-target="#galeria_c" aria-expanded="false" aria-controls="galeria_c">
                                <span class="fvu-icono"><i class="fas fa-images" aria-hidden="true"></i></span>
                                <span class="fvu-acordeon__texto">
                                    <strong>Fotos y Galería</strong>
                                    <small>Imágenes registradas de {{ $nombreMascotaFvu }}</small>
                                </span>
                                <span class="fvu-contador">{{ $galeriaFvu->count() + 1 }}</span>
                                <span class="fvu-acordeon__flecha"><i class="feather icon-chevron-down" aria-hidden="true"></i></span>
                            </button>
                        </div>
                        <div id="galeria_c" class="collapse" aria-labelledby="galeria" data-parent="#galeria">
                            <div class="fvu-acordeon__cuerpo">
                                <div class="fvu-galeria">
                                    <a class="fvu-galeria__foto" href="{{ $fotoMascota }}" target="_blank" rel="noopener">
                                        <img src="{{ $fotoMascota }}" alt="Foto de perfil de {{ $nombreMascotaFvu }}" loading="lazy">
                                    </a>
                                    @foreach ($galeriaFvu as $foto)
                                        <a class="fvu-galeria__foto" href="{{ $foto }}" target="_blank" rel="noopener">
                                            <img src="{{ $foto }}" alt="Galería de {{ $nombreMascotaFvu }}" loading="lazy">
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </section>
    </div>
</div>

<div id="modal_contacto_emergencia_fvu" class="modal fade fvu-modal" tabindex="-1" role="dialog"
    aria-labelledby="modal_contacto_emergencia_fvu_titulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header modal-header-purple">
                <h5 class="modal-title" id="modal_contacto_emergencia_fvu_titulo">
                    <i class="feather icon-phone-call" aria-hidden="true"></i> Contacto de emergencia
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar" onclick="$(this).closest('.modal').modal('hide');">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="fvu-modal__persona">
                    <span class="fvu-modal__avatar"><i class="feather icon-user" aria-hidden="true"></i></span>
                    <div>
                        <span class="fvu-modal__rol">Contacto / responsable</span>
                        <h5 class="fvu-modal__nombre">{{ $contactoNombreFvu }}</h5>
                    </div>
                </div>
                <ul class="fvu-modal__lista">
                    <li>
                        <span class="fvu-icono fvu-tono--morado"><i class="fas fa-id-card" aria-hidden="true"></i></span>
                        <div class="fvu-modal__dato">
                            <small>RUT</small>
                            <span>{{ $contactoRutFvu }}</span>
                        </div>
                    </li>
                    <li>
                        <span class="fvu-icono fvu-tono--verde"><i class="fas fa-phone-alt" aria-hidden="true"></i></span>
                        <div class="fvu-modal__dato">
                            <small>Teléfono</small>
                            <span>{{ $contactoTelefonoFvu }}</span>
                        </div>
                        @if ($contactoTelefonoFvu !== 'Sin registro')
                            <a class="fvu-modal__accion" href="tel:{{ preg_replace('/[^0-9+]/', '', (string) $contactoTelefonoFvu) }}">
                                <i class="feather icon-phone" aria-hidden="true"></i> Llamar
                            </a>
                        @endif
                    </li>
                    <li>
                        <span class="fvu-icono fvu-tono--azul"><i class="fas fa-envelope" aria-hidden="true"></i></span>
                        <div class="fvu-modal__dato">
                            <small>Email</small>
                            <span>{{ $contactoEmailFvu }}</span>
                        </div>
                        @if ($contactoEmailFvu !== 'Sin registro')
                            <a class="fvu-modal__accion" href="mailto:{{ $contactoEmailFvu }}">
                                <i class="feather icon-mail" aria-hidden="true"></i> Escribir
                            </a>
                        @endif
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="modal fade fvu-modal" id="modal_fvu_vacunas" tabindex="-1" role="dialog" aria-labelledby="modal_fvu_vacunas_titulo" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header modal-header-purple">
                <h5 class="modal-title" id="modal_fvu_vacunas_titulo">
                    <i class="fas fa-syringe" aria-hidden="true"></i> Registro de vacunas — {{ $mascota->nombre ?? 'Mascota' }}
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar" onclick="$(this).closest('.modal').modal('hide');">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="fvu-tabla-envoltura">
                    <table class="fvu-tabla">
                        <thead>
                            <tr>
                                <th>Edad</th>
                                <th>Fecha dosis</th>
                                <th>Vacuna</th>
                                <th>Próx. dosis</th>
                            </tr>
                        </thead>
                        <tbody id="modal_fvu_vacunas_body">
                            @forelse ($vacunasFvu as $vacuna)
                                <tr>
                                    <td>{{ $vacuna['edad'] ?? '-' }}</td>
                                    <td>{{ $fmtFechaFvu($vacuna['fecha_dosis'] ?? null) }}</td>
                                    <td>{{ $vacuna['vacuna'] ?? '-' }}</td>
                                    <td>{{ $fmtFechaFvu($vacuna['proxima_dosis'] ?? null) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">Sin registros</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade fvu-modal" id="modal_fvu_desparasitaciones" tabindex="-1" role="dialog" aria-labelledby="modal_fvu_desparasitaciones_titulo" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header modal-header-purple">
                <h5 class="modal-title" id="modal_fvu_desparasitaciones_titulo">
                    Registro de desparasitación — {{ $mascota->nombre ?? 'Mascota' }}
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar" onclick="$(this).closest('.modal').modal('hide');">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="fvu-tabla-envoltura">
                    <table class="fvu-tabla">
                        <thead>
                            <tr>
                                <th>Fecha dosis</th>
                                <th>Antiparasitario</th>
                                <th>Tipo</th>
                                <th>Próx. dosis</th>
                            </tr>
                        </thead>
                        <tbody id="modal_fvu_desparasitaciones_body">
                            @forelse ($desparasitacionesFvu as $registro)
                                <tr>
                                    <td>{{ $fmtFechaFvu($registro['fecha_dosis'] ?? null) }}</td>
                                    <td>{{ $registro['antiparasitario'] ?? '-' }}</td>
                                    <td>{{ $registro['tipo'] ?? '-' }}</td>
                                    <td>{{ $fmtFechaFvu($registro['proxima_dosis'] ?? null) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">Sin registros</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@once
@push('page-scripts')
<script>
    (function ($) {
        function fvuScrollToElement($element) {
            if (!$element || !$element.length) {
                return;
            }

            $('html, body').animate({
                scrollTop: Math.max($element.offset().top - 90, 0)
            }, 280);
        }

        $(document).on('click', '.fvu-ver-mas-info', function () {
            var tabSelector = $(this).data('fvu-tab');
            var $panel = $('#cabecera_info');

            $panel.collapse('show');

            if (tabSelector) {
                $(tabSelector + '-tab').tab('show');
            }

            fvuScrollToElement($panel);
        });

        $(document).on('click', '.fvu-ver-collapse', function () {
            var target = $(this).data('target');
            var $target = $(target);

            if (!$target.length) {
                return;
            }

            $target.collapse('show');
            fvuScrollToElement($target);
        });

        $(document).on('click', '.fvu-ver-modal', function (e) {
            e.preventDefault();
            var target = $(this).attr('data-target');
            if (!target) {
                return;
            }
            var $modal = $(target);
            if ($modal.length && $modal.closest('.tab-pane').length) {
                $modal.appendTo('body');
            }
            $modal.modal('show');
        });

        function fechaCortaFvu(valor) {
            if (!valor) {
                return '-';
            }
            var partes = String(valor).split('T')[0].split('-');
            if (partes.length === 3) {
                return partes[2] + '-' + partes[1] + '-' + partes[0];
            }
            return valor;
        }

        function renderTablaVacunasFvu(vacunas) {
            var $body = $('#modal_fvu_vacunas_body').empty();
            if (!vacunas || !vacunas.length) {
                $body.append('<tr><td colspan="4" class="text-center text-muted py-3">Sin registros</td></tr>');
                return;
            }
            vacunas.forEach(function (item) {
                $body.append(
                    '<tr><td>' + (item.edad || '-') + '</td>' +
                    '<td>' + fechaCortaFvu(item.fecha_dosis) + '</td>' +
                    '<td>' + (item.vacuna || '-') + '</td>' +
                    '<td>' + fechaCortaFvu(item.proxima_dosis) + '</td></tr>'
                );
            });
        }

        function renderTablaDesparasitacionesFvu(registros) {
            var $body = $('#modal_fvu_desparasitaciones_body').empty();
            if (!registros || !registros.length) {
                $body.append('<tr><td colspan="4" class="text-center text-muted py-3">Sin registros</td></tr>');
                return;
            }
            registros.forEach(function (item) {
                $body.append(
                    '<tr><td>' + fechaCortaFvu(item.fecha_dosis) + '</td>' +
                    '<td>' + (item.antiparasitario || '-') + '</td>' +
                    '<td>' + (item.tipo || '-') + '</td>' +
                    '<td>' + fechaCortaFvu(item.proxima_dosis) + '</td></tr>'
                );
            });
        }

        function actualizarTarjetasSanitariasFvu(vacunas, desparasitaciones) {
            vacunas = vacunas || [];
            desparasitaciones = desparasitaciones || [];
            renderTablaVacunasFvu(vacunas);
            renderTablaDesparasitacionesFvu(desparasitaciones);

            var $shell = $('.fvu-vet-shell').first();
            $shell.find('.fvu-vital-item').each(function () {
                var $titulo = $(this).find('.fvu-vital-title');
                var $valor = $(this).find('.fvu-vital-value');
                var $ver = $(this).find('.fvu-card-ver-btn');
                if ($titulo.text().trim() === 'Vacunas') {
                    $valor.text(vacunas.length > 0 ? (vacunas.length + ' registro(s)') : 'Sin registro');
                    $ver.toggle(vacunas.length > 0);
                }
                if ($titulo.text().trim() === 'Desparasitaciones') {
                    $valor.text(desparasitaciones.length > 0 ? (desparasitaciones.length + ' registro(s)') : 'Sin registro');
                    $ver.toggle(desparasitaciones.length > 0);
                }
            });

            $('#vacunas_c tbody').empty();
            if (!vacunas.length) {
                $('#vacunas_c tbody').append('<tr><td colspan="4">Sin registros</td></tr>');
            } else {
                vacunas.forEach(function (item) {
                    $('#vacunas_c tbody').append(
                        '<tr><td class="align-middle">' + (item.edad || '-') + '</td>' +
                        '<td class="align-middle"><span class="badge badge-secondary">' + fechaCortaFvu(item.fecha_dosis) + '</span></td>' +
                        '<td class="align-middle">' + (item.vacuna || '-') + '</td>' +
                        '<td class="align-middle text-center"><span class="badge badge-info">' + fechaCortaFvu(item.proxima_dosis) + '</span></td></tr>'
                    );
                });
            }

            $('#desparasitacion_c tbody').empty();
            if (!desparasitaciones.length) {
                $('#desparasitacion_c tbody').append('<tr><td colspan="4">Sin registros</td></tr>');
            } else {
                desparasitaciones.forEach(function (item) {
                    $('#desparasitacion_c tbody').append(
                        '<tr><td class="align-middle"><span class="badge badge-secondary">' + fechaCortaFvu(item.fecha_dosis) + '</span></td>' +
                        '<td class="align-middle">' + (item.antiparasitario || '-') + '</td>' +
                        '<td class="align-middle">' + (item.tipo || '-') + '</td>' +
                        '<td class="align-middle text-center"><span class="badge badge-info">' + fechaCortaFvu(item.proxima_dosis) + '</span></td></tr>'
                    );
                });
            }
        }

        function recargarRegistrosSanitariosFvu() {
            var $shell = $('.fvu-vet-shell').first();
            var url = $shell.data('fvu-registros-url');
            if (!url) {
                return;
            }
            $.get(url).done(function (resp) {
                if (Number(resp.estado) !== 1) {
                    return;
                }
                actualizarTarjetasSanitariasFvu(resp.vacunas || [], resp.desparasitaciones || []);
            });
        }

        $('a[data-toggle="tab"][href="#fvu"], #fvu-tab').on('shown.bs.tab', recargarRegistrosSanitariosFvu);
        $(document).on('fvu:sanitarios-actualizados', function () {
            recargarRegistrosSanitariosFvu();
        });
    })(jQuery);
</script>
@endpush
@endonce

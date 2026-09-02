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
            'valor' => $cantidadVacunas > 0 ? $cantidadVacunas . ' registro(s)' : 'Sin registro',
            'valor_clase' => 'text-info',
            'mostrar_ver' => $cantidadVacunas > 0,
            'ver_modal' => '#modal_fvu_vacunas',
            'ver_target' => '#vacunas_c',
        ],
        [
            'titulo' => 'Alergias',
            'icono' => 'alergias.png',
            'valor' => $cantidadAlergias > 0 ? 'SI' : 'NO',
            'valor_clase' => $cantidadAlergias > 0 ? 'text-danger' : 'text-info',
            'mostrar_ver' => $cantidadAlergias > 0,
            'ver_url' => null,
            'ver_target' => '#seccion_alergias',
            'ver_mas_info' => true,
        ],
        [
            'titulo' => 'Desparasitaciones',
            'icono' => 'transfusion.jpg',
            'valor' => $cantidadDesparasitaciones > 0 ? $cantidadDesparasitaciones . ' registro(s)' : 'Sin registro',
            'valor_clase' => 'text-info',
            'mostrar_ver' => $cantidadDesparasitaciones > 0,
            'ver_modal' => '#modal_fvu_desparasitaciones',
            'ver_target' => '#desparasitacion_c',
        ],
        [
            'titulo' => 'Condición crónica',
            'icono' => 'enfermedad-cronica.png',
            'valor' => $tieneCronico ? 'SI' : 'NO',
            'valor_clase' => 'text-info',
            'mostrar_ver' => $tieneCronico,
            'ver_url' => null,
            'ver_target' => '#seccion_enfer_cronicas',
            'ver_mas_info' => true,
        ],
        [
            'titulo' => 'Esterilizado/a',
            'icono' => 'esterilizacion.png',
            'valor' => $mascota->esterilizado ? 'SI' : 'NO',
            'valor_clase' => 'text-info',
            'mostrar_ver' => false,
        ],
        [
            'titulo' => 'Peso',
            'icono' => 'peso.png',
            'valor' => $pesoMascota,
            'valor_clase' => 'text-info',
            'mostrar_ver' => false,
        ],
    ];

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

<div class="fvu-vet-shell {{ $fvuModoEmbebido ? 'fvu-vet-shell--embedded' : 'user-profile user-card mt-0' }}" style="background-color: #ecf0f5!important;" data-mascota-id="{{ $mascota->id ?? '' }}" data-fvu-registros-url="{{ !empty($mascota->id) ? route('paciente.mascotas.registros_sanitarios', ['mascotaId' => $mascota->id]) : '' }}">
    <div class="col-md-12 py-0 px-0 shadow-none fvu-shell-inner">
        @if($fvuMostrarTitulo)
        <div class="row mx-1 mt-2">
            <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
                <div class="fvu-title-block">
                    <span class="fvu-title-icon"><i class="fas fa-paw"></i></span>
                    <div>
                        <h4 class="mb-0">Ficha Veterinaria Única</h4>
                        <small>Resumen clínico y antecedentes de {{ $mascota->nombre ?? 'la mascota' }}</small>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <div class="row mx-0 px-2 px-md-3 {{ $fvuModoEmbebido ? 'fvu-dashboard-row--compact' : '' }}">
            <div class="col-12 px-0">
                <div class="fvu-dashboard-grid">
                    <div class="fvu-zone-pet">
                        <div class="card rounded-xl fvu-pet-card" id="enf-cron">
                            <input type="hidden" name="id_paciente" id="id_paciente" value="{{ $mascota->id }}">
                            <div class="row px-2 py-1">
                                <div class="col-sm-12 col-md-12">
                                    <div class="media">
                                        <img class="img-radius img-fluid wid-70 mr-3 align-self-center" id="profile-image"
                                            src="{{ $fotoMascota }}"
                                            onerror="this.onerror=null;this.src='{{ asset('images/iconos/usuario_profesional.svg') }}';"
                                            alt="Foto de {{ $mascota->nombre ?? 'la mascota' }}">
                                        <div class="media-body">
                                            <div class="row">
                                                <div class="col-12 mb-3">
                                                    <h6 class="f-16">
                                                        <span class="text-c-blue">{{ $mascota->nombre ?? 'Mascota' }}</span><br>
                                                        <small>N° Microchip ({{ $microchip }})</small>
                                                    </h6>
                                                </div>

                                                <div class="col-6 mb-2 fvu-pet-attribute">
                                                    <h6 class="f-16">
                                                        <span class="text-c-blue">{{ $edadMascota !== null ? $edadMascota . ' Años' : 'Sin registro' }}</span><br>
                                                        <small>{{ !empty($mascota->fecha_nacimiento) ? '(' . \Carbon\Carbon::parse($mascota->fecha_nacimiento)->format('d-m-Y') . ')' : '' }}</small>
                                                    </h6>
                                                </div>
                                                <div class="col-6 mb-2 fvu-pet-attribute">
                                                    <h6 class="f-16"><span class="text-c-blue">{{ $sexoMascota }}</span></h6>
                                                    <p>Sexo</p>
                                                </div>
                                                <div class="col-6 mb-2 fvu-pet-attribute">
                                                    <h6 class="f-16"><span class="text-c-blue">{{ $especieMascota }}</span></h6>
                                                    <p>Especie</p>
                                                </div>
                                                <div class="col-6 mb-2 fvu-pet-attribute">
                                                    <h6 class="f-16"><span class="text-c-blue">{{ $razaMascota }}</span></h6>
                                                    <p>Raza</p>
                                                </div>
                                                <div class="col-6 mb-2 fvu-pet-attribute">
                                                    <h6 class="f-16"><span class="text-c-blue">{{ $tamanoMascota }}</span></h6>
                                                    <p>Tamaño</p>
                                                </div>
                                                <div class="col-6 mb-2 fvu-pet-attribute">
                                                    <h6 class="f-16"><span class="text-c-blue">{{ $colorMascota }}</span></h6>
                                                    <p>Color</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="fvu-zone-vitals">
                        <div class="fvu-vital-grid">
                            @foreach ($tarjetasVitalesFvu as $tarjetaVital)
                                <div class="fvu-vital-item">
                                    <div class="card mb-2">
                                        <div class="card-body px-2 py-1">
                                            <div class="media align-items-center">
                                                <img src="{{ asset('images/iconos/' . $tarjetaVital['icono']) }}"
                                                    class="wid-35 rounded-xl mr-3 align-self-center"
                                                    alt="{{ $tarjetaVital['titulo'] }}">
                                                <div class="media-body min-width-0">
                                                    <h5 class="mt-0 mb-1 pt-1 fvu-vital-title">{{ $tarjetaVital['titulo'] }}</h5>
                                                    <div class="fvu-vital-count-row">
                                                        <h5 class="mt-0 mb-0 fvu-vital-value {{ $tarjetaVital['valor_clase'] ?? 'text-info' }}">
                                                            {{ $tarjetaVital['valor'] }}
                                                        </h5>
                                                        @if (!empty($tarjetaVital['mostrar_ver']))
                                                            @if (!empty($tarjetaVital['ver_modal']))
                                                                <button type="button" class="fvu-card-ver-btn fvu-ver-modal" data-toggle="modal"
                                                                    data-target="{{ $tarjetaVital['ver_modal'] }}">ver</button>
                                                            @elseif (!empty($tarjetaVital['ver_url']))
                                                                <a href="{{ $tarjetaVital['ver_url'] }}" class="fvu-card-ver-btn">ver</a>
                                                            @elseif (!empty($tarjetaVital['ver_mas_info']))
                                                                <button type="button" class="fvu-card-ver-btn fvu-ver-mas-info"
                                                                    data-fvu-tab="{{ $tarjetaVital['ver_target'] }}">ver</button>
                                                            @else
                                                                <button type="button" class="fvu-card-ver-btn fvu-ver-collapse"
                                                                    data-target="{{ $tarjetaVital['ver_target'] }}">ver</button>
                                                            @endif
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="fvu-zone-summaries">
                        <div class="fvu-resumen-cards-grid">
                            <div class="fvu-summary-card fvu-summary-card--info">
                                <div class="fvu-summary-card__icon"><img src="{{ asset('images/iconos/tto-curso.png') }}" alt="Tratamientos en curso"></div>
                                <div class="fvu-summary-card__body">
                                    <h5>Tratamientos en curso</h5>
                                    <ul>
                                        @forelse ($tratamientosLista as $tratamiento)
                                            <li>{{ $tratamiento }}</li>
                                        @empty
                                            <li class="fvu-empty">No hay registros</li>
                                        @endforelse
                                    </ul>
                                </div>
                                @if ($tratamientosLista->isNotEmpty())
                                    <button type="button" class="fvu-card-ver-btn fvu-ver-mas-info"
                                        data-fvu-tab="#seccion_ultimo_tratamiento">ver</button>
                                @endif
                            </div>

                            <div class="fvu-summary-card fvu-summary-card--danger">
                                <div class="fvu-summary-card__icon"><img src="{{ asset('images/iconos/meds-cronicos.png') }}" alt="Enfermedades crónicas"></div>
                                <div class="fvu-summary-card__body">
                                    <h5>Enfermedades crónicas</h5>
                                    <ul>
                                        @if ($tieneCronico)
                                            @foreach ($cronicosRegistradosFvu as $cronico)
                                                <li>{{ $cronico['nombre'] }}</li>
                                            @endforeach
                                        @else
                                            <li class="fvu-empty">No hay registros</li>
                                        @endif
                                    </ul>
                                </div>
                                @if ($tieneCronico)
                                    <button type="button" class="fvu-card-ver-btn fvu-ver-mas-info"
                                        data-fvu-tab="#seccion_enfer_cronicas">ver</button>
                                @endif
                            </div>

                            <div class="fvu-summary-card fvu-summary-card--teal">
                                <div class="fvu-summary-card__icon"><img src="{{ asset('images/iconos/ant-qx.png') }}" alt="Cirugías recientes"></div>
                                <div class="fvu-summary-card__body">
                                    <h5>Cirugías recientes</h5>
                                    <ul>
                                        @forelse ($cirugiasLista as $cirugia)
                                            <li>{{ $cirugia }}</li>
                                        @empty
                                            <li class="fvu-empty">No hay registros</li>
                                        @endforelse
                                    </ul>
                                </div>
                                @if ($cirugiasLista->isNotEmpty())
                                    <button type="button" class="fvu-card-ver-btn fvu-ver-mas-info"
                                        data-fvu-tab="#seccion_ultimas_cirugia">ver</button>
                                @endif
                            </div>

                            <div class="fvu-summary-card fvu-summary-card--purple">
                                <div class="fvu-summary-card__icon"><img src="{{ asset('images/iconos/prot-ort.png') }}" alt="Documentos clínicos"></div>
                                <div class="fvu-summary-card__body">
                                    <h5>Documentos clínicos</h5>
                                    <ul>
                                        @if ($cantidadDocumentos > 0)
                                            <li>{{ $cantidadDocumentos }} documento(s) registrado(s)</li>
                                        @else
                                            <li class="fvu-empty">No hay registros</li>
                                        @endif
                                    </ul>
                                </div>
                                @if ($cantidadDocumentos > 0)
                                    <button type="button" class="fvu-card-ver-btn fvu-ver-mas-info"
                                        data-fvu-tab="#discap">ver</button>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="fvu-zone-actions">
                        <button type="button"
                            class="btn btn-danger-light-c fvu-action-btn"
                            data-toggle="modal" data-target="#modal_contacto_emergencia_fvu">
                            <i class="feather icon-users mr-1"></i>
                            Tutor y contacto de emergencia
                        </button>
                        <button class="btn btn-purple-light-c fvu-action-btn collapsed" type="button"
                            data-toggle="collapse" data-target="#cabecera_info"
                            aria-expanded="false" aria-controls="cabecera_info">
                            <i class="feather icon-plus-circle mr-1"></i>
                            Ver más información
                        </button>
                    </div>

                <div class="fvu-more-panel">
                    <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
                        <div class="card rounded-xl">
                            <div class="card-header-fmu border-none" style="border:0px!important;" id="enf-cron-mascota"></div>
                            <div id="cabecera_info" class="collapse" aria-labelledby="enf-cron-mascota" data-parent="#cabecera_info">
                                <div class="card-body pt-2" style="padding-top: 0px!important;">
                                    <div class="row">
                                        <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 pb-0">
                                            <ul class="nav nav-tabs profile-tabs nav-fill mt-1 mb-3" id="myTabMascota" role="tablist">
                                                <li class="nav-item">
                                                    <a class="nav-link-aten text-reset active" id="seccion_ident_contacto-tab" data-toggle="tab" href="#seccion_ident_contacto" role="tab" aria-controls="seccion_ident_contacto" aria-selected="true">Info. Del Responsable</a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link-aten text-reset" id="seccion_enfer_cronicas-tab" data-toggle="tab" href="#seccion_enfer_cronicas" role="tab" aria-controls="seccion_enfer_cronicas" aria-selected="false">Enfermedades Crónicas</a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link-aten text-reset" id="seccion_alergias-tab" data-toggle="tab" href="#seccion_alergias" role="tab" aria-controls="seccion_alergias" aria-selected="false">Alergias</a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link-aten text-reset" id="seccion_ultimas_cirugia-tab" data-toggle="tab" href="#seccion_ultimas_cirugia" role="tab" aria-controls="seccion_ultimas_cirugia" aria-selected="false">Últimas Cirugias</a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link-aten text-reset" id="seccion_ultimo_tratamiento-tab" data-toggle="tab" href="#seccion_ultimo_tratamiento" role="tab" aria-controls="seccion_ultimo_tratamiento" aria-selected="false">Últimos Tratamientos</a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link-aten text-reset" id="discap-tab" data-toggle="tab" href="#discap" role="tab" aria-controls="discap" aria-selected="false">Documentos</a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12 pb-2">
                                            <div class="tab-content" id="at-oftalmo-mascota">
                                                <div class="tab-pane fade show active" id="seccion_ident_contacto" role="tabpanel" aria-labelledby="seccion_ident_contacto-tab">
                                                    <div class="row">
                                                        <div class="col-sm-6 col-md-6 col-lg-6 col-xl-3 col-xxl-3">
                                                            <div class="media">
                                                                <img src="{{ asset('images/iconos/persona-info.png') }}" class="wid-35 rounded-circle align-self-start mr-2" alt="Responsable">
                                                                <div class="media-body">
                                                                    <h6 class="mt-0 mb-1 pt-1">Responsable</h6>
                                                                    <h6 class="mt-0 text-c-blue">{{ $nombreResponsable ?: 'Sin registro' }}</h6>
                                                                    <small class="mt-0 text-c-blue">{{ $responsableFvu->rut ?? '' }}</small>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-6 col-md-6 col-lg-6 col-xl-3 col-xxl-3">
                                                            <div class="media">
                                                                <img src="{{ asset('images/iconos/tel-info.png') }}" class="wid-35 rounded-circle align-self-start mr-2" alt="Telefono">
                                                                <div class="media-body">
                                                                    <h6 class="mt-0 mb-1 pt-1">Teléfono</h6>
                                                                    <h6 class="mt-0 text-c-blue">{{ trim($telefonoResponsable, ' /') ?: 'Sin registro' }}</h6>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-6 col-md-6 col-lg-6 col-xl-3 col-xxl-3">
                                                            <div class="media">
                                                                <img src="{{ asset('images/iconos/email-info.png') }}" class="wid-35 rounded-circle align-self-start mr-2" alt="Email">
                                                                <div class="media-body">
                                                                    <h6 class="mt-0 mb-1 pt-1">Email</h6>
                                                                    <h6 class="mt-0 text-c-blue">{{ $responsableFvu->email ?? 'Sin registro' }}</h6>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-sm-6 col-md-6 col-lg-6 col-xl-3 col-xxl-3">
                                                            <div class="media">
                                                                <img src="{{ asset('images/iconos/direccion-info.png') }}" class="wid-35 rounded-circle align-self-start mr-2" alt="Mascota">
                                                                <div class="media-body">
                                                                    <h6 class="mt-0 mb-1 pt-1">Mascota</h6>
                                                                    <h6 class="mt-0 text-c-blue">{{ $especieMascota }} / {{ $razaMascota }}</h6>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="tab-pane fade" id="seccion_enfer_cronicas" role="tabpanel" aria-labelledby="seccion_enfer_cronicas-tab">
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <table class="display table table-striped table-xs table-bordered dt-responsive nowrap pb-4" style="width:100%">
                                                                <thead>
                                                                    <tr>
                                                                        <th>NOMBRE</th>
                                                                        <th>COMENTARIO</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <tr>
                                                                        <td>Enfermedad crónica</td>
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
                                                </div>

                                                <div class="tab-pane fade" id="seccion_alergias" role="tabpanel" aria-labelledby="seccion_alergias-tab">
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <table class="display table table-bordered table-striped table-xs dt-responsive nowrap pb-4" style="width:100%">
                                                                <thead>
                                                                    <tr>
                                                                        <th>NOMBRE</th>
                                                                        <th>COMENTARIO</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <tr>
                                                                        <td colspan="2" class="text-center text-muted">Sin registros de alergias</td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="tab-pane fade" id="seccion_ultimas_cirugia" role="tabpanel" aria-labelledby="seccion_ultimas_cirugia-tab">
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <table class="display table table-bordered table-striped table-xs dt-responsive nowrap pb-4" style="width:100%">
                                                                <thead>
                                                                    <tr>
                                                                        <th>PROCEDIMIENTO</th>
                                                                        <th>DETALLE</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @forelse ($cirugiasLista as $cirugia)
                                                                        <tr>
                                                                            <td>Cirugía registrada</td>
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
                                                </div>

                                                <div class="tab-pane fade" id="seccion_ultimo_tratamiento" role="tabpanel" aria-labelledby="seccion_ultimo_tratamiento-tab">
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <ul>
                                                                @forelse ($tratamientosLista as $tratamiento)
                                                                    <li>{{ $tratamiento }}</li>
                                                                @empty
                                                                    <li>No hay registros</li>
                                                                @endforelse
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="tab-pane fade" id="discap" role="tabpanel" aria-labelledby="discap-tab">
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <table class="display table-bordered table table-striped table-xs dt-responsive nowrap pb-4" style="width:100%">
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
                                                                            <td>{{ $documento['tipo'] ?? '-' }}</td>
                                                                            <td>{{ $documento['nombre'] ?? '-' }}</td>
                                                                            <td>{{ !empty($documento['fecha']) ? \Carbon\Carbon::parse($documento['fecha'])->format('d-m-Y') : '-' }}</td>
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
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="fvu-historial-full">
                        <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 mt-2">
                            <h5 class="f-20 text-c-blue mb-3">Historial veterinario</h5>
                        </div>

                        <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
                            <div class="card-a">
                                <div class="card-header-a" id="histo_medico">
                                    <button class="accor-closed btn pt-1 pb-0 pl-1 btn-block text-left card-act-open collapsed" type="button" data-toggle="collapse" data-target="#histo_medico_c" aria-expanded="false" aria-controls="histo_medico_c">
                                        Historial Veterinario
                                    </button>
                                </div>
                                <div id="histo_medico_c" class="collapse" aria-labelledby="histo_medico" data-parent="#histo_medico">
                                    <div class="card-body-aten-a">
                                        <div class="row mt-3">
                                            <div class="col-sm-12 pb-4">
                                                <table class="display table table-striped table-xs dt-responsive nowrap pb-4" style="width:100%">
                                                    <thead>
                                                        <tr>
                                                            <th class="align-middle">Fecha</th>
                                                            <th class="align-middle">Profesional</th>
                                                            <th class="align-middle">Diagnóstico</th>
                                                            <th class="align-middle">Ficha</th>
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
                                                                <td class="align-middle">{{ $fechaFicha ? \Carbon\Carbon::parse($fechaFicha)->format('d-m-Y') : '-' }}</td>
                                                                <td class="align-middle">{{ $profesionalFicha !== '' ? $profesionalFicha : '-' }}</td>
                                                                <td class="align-middle">{{ $diagnosticoFicha }}</td>
                                                                <td class="align-middle">#{{ data_get($ficha, 'id', '-') }}</td>
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
                            </div>
                        </div>

                        <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
                            <div class="card-a">
                                <div class="card-header-a" id="vacunas">
                                    <button class="accor-closed btn pt-1 pb-0 pl-1 btn-block text-left card-act-open collapsed" type="button" data-toggle="collapse" data-target="#vacunas_c" aria-expanded="false" aria-controls="vacunas_c">
                                        Registro de Vacunas
                                    </button>
                                </div>
                                <div id="vacunas_c" class="collapse" aria-labelledby="vacunas" data-parent="#vacunas">
                                    <div class="card-body-aten-a">
                                        <div class="table-responsive">
                                            <table class="display table table-striped dt-responsive nowrap table-sm" style="width:100%">
                                                <thead>
                                                    <tr>
                                                        <th class="align-middle">Edad</th>
                                                        <th class="align-middle">Fecha dosis</th>
                                                        <th class="align-middle">Vacuna</th>
                                                        <th class="align-middle">Próx.Dosis</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse ($vacunasFvu as $vacuna)
                                                        <tr>
                                                            <td class="align-middle">{{ $vacuna['edad'] ?? '-' }}</td>
                                                            <td class="align-middle"><span class="badge badge-secondary">{{ !empty($vacuna['fecha_dosis']) ? \Carbon\Carbon::parse($vacuna['fecha_dosis'])->format('d-m-Y') : '-' }}</span></td>
                                                            <td class="align-middle">{{ $vacuna['vacuna'] ?? '-' }}</td>
                                                            <td class="align-middle text-center"><span class="badge badge-info">{{ !empty($vacuna['proxima_dosis']) ? \Carbon\Carbon::parse($vacuna['proxima_dosis'])->format('d-m-Y') : '-' }}</span></td>
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
                        </div>

                        <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
                            <div class="card-a">
                                <div class="card-header-a" id="desparasitacion">
                                    <button class="accor-closed btn pt-1 pb-0 pl-1 btn-block text-left card-act-open collapsed" type="button" data-toggle="collapse" data-target="#desparasitacion_c" aria-expanded="false" aria-controls="desparasitacion_c">
                                        Registro de Desparasitación
                                    </button>
                                </div>
                                <div id="desparasitacion_c" class="collapse" aria-labelledby="desparasitacion" data-parent="#desparasitacion">
                                    <div class="card-body-aten-a">
                                        <div class="table-responsive">
                                            <table class="display table table-striped dt-responsive nowrap table-sm" style="width:100%">
                                                <thead>
                                                    <tr>
                                                        <th class="align-middle">Fecha dosis</th>
                                                        <th class="align-middle">Antiparasitario</th>
                                                        <th class="align-middle">Tipo</th>
                                                        <th class="align-middle">Próx.Dosis</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse ($desparasitacionesFvu as $registro)
                                                        <tr>
                                                            <td class="align-middle"><span class="badge badge-secondary">{{ !empty($registro['fecha_dosis']) ? \Carbon\Carbon::parse($registro['fecha_dosis'])->format('d-m-Y') : '-' }}</span></td>
                                                            <td class="align-middle">{{ $registro['antiparasitario'] ?? '-' }}</td>
                                                            <td class="align-middle">{{ $registro['tipo'] ?? '-' }}</td>
                                                            <td class="align-middle text-center"><span class="badge badge-info">{{ !empty($registro['proxima_dosis']) ? \Carbon\Carbon::parse($registro['proxima_dosis'])->format('d-m-Y') : '-' }}</span></td>
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
                        </div>

                        <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
                            <div class="card-a">
                                <div class="card-header-a" id="odonto_felino">
                                    <button class="accor-closed btn pt-1 pb-0 pl-1 btn-block text-left card-act-open collapsed" type="button" data-toggle="collapse" data-target="#odonto_felino_c" aria-expanded="false" aria-controls="odonto_felino_c">
                                        Historial Odontológico
                                    </button>
                                </div>
                                <div id="odonto_felino_c" class="collapse" aria-labelledby="odonto_felino" data-parent="#odonto_felino">
                                    <div class="card-body-aten-a">
                                        @if ($mostrarOdontogramaCanino || $mostrarOdontogramaFelino)
                                            @include('general.secciones_ficha.partials.odontograma_mascota')
                                        @else
                                            <div class="alert alert-info mb-0">
                                                El odontograma de mascota se muestra cuando la especie corresponde a perro o gato.
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
                            <div class="card-a">
                                <div class="card-header-a" id="documentos">
                                    <button class="accor-closed btn pt-1 pb-0 pl-1 btn-block text-left card-act-open collapsed" type="button" data-toggle="collapse" data-target="#documentos_c" aria-expanded="false" aria-controls="documentos_c">
                                        Documentación
                                    </button>
                                </div>
                                <div id="documentos_c" class="collapse" aria-labelledby="documentos" data-parent="#documentos">
                                    <div class="card-body-aten-a">
                                        <div class="table-responsive">
                                            <table class="display table table-striped dt-responsive nowrap table-sm" style="width:100%">
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
                                                            <td>{{ $documento['tipo'] ?? '-' }}</td>
                                                            <td>{{ $documento['nombre'] ?? '-' }}</td>
                                                            <td>{{ !empty($documento['fecha']) ? \Carbon\Carbon::parse($documento['fecha'])->format('d-m-Y') : '-' }}</td>
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
                        </div>

                        @if ($galeriaFvu->isNotEmpty())
                            <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                <div class="card-a">
                                    <div class="card-header-a" id="galeria">
                                        <button class="accor-closed btn pt-1 pb-0 pl-1 btn-block text-left card-act-open collapsed" type="button" data-toggle="collapse" data-target="#galeria_c" aria-expanded="false" aria-controls="galeria_c">
                                            Fotos y Galería
                                        </button>
                                    </div>
                                    <div id="galeria_c" class="collapse" aria-labelledby="galeria" data-parent="#galeria">
                                        <div class="card-body-aten-a">
                                            <div class="d-flex flex-wrap">
                                                <img class="img-thumbnail mr-2 mb-2" src="{{ $fotoMascota }}" alt="Foto perfil" style="width: 110px; height: 110px; object-fit: cover;">
                                                @foreach ($galeriaFvu as $foto)
                                                    <img class="img-thumbnail mr-2 mb-2" src="{{ $foto }}" alt="Galería mascota" style="width: 110px; height: 110px; object-fit: cover;">
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="{{ asset('css/ficha_medica_unica.css') }}">
<style type="text/css">
    .auth-wrapper
    {
        background-color: #f3f3f3!important;
    }

    .fvu-vet-shell {
        --fvu-primary: #168f86;
        --fvu-purple: #6c389a;
        --fvu-border: #dfe7ee;
        --fvu-muted: #667085;
        background: linear-gradient(180deg, #eef4f7 0%, #f7f9fb 100%) !important;
        padding-bottom: 24px;
    }

    .fvu-shell-inner {
        max-width: 100%;
        width: 100%;
    }

    .fvu-vet-shell--embedded {
        margin: 0 !important;
        padding-top: 12px;
        padding-bottom: 20px;
    }

    #fvu.tab-pane .fvu-vet-shell--embedded {
        margin-left: 0 !important;
        margin-right: 0 !important;
        background: #ecf0f5 !important;
    }

    .fvu-vet-shell--compact {
        margin-top: 0 !important;
    }

    .fvu-dashboard-row--compact {
        margin-top: 0 !important;
        padding-top: 4px !important;
    }

    .fvu-title-block {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        gap: 12px;
        margin: 0 0 14px;
        padding: 12px 16px;
        background: #fff;
        border: 1px solid var(--fvu-border);
        border-left: 4px solid #1a6b5a;
        border-radius: 0 10px 10px 0;
    }

    .fvu-title-block h4 {
        font-size: 18px;
        font-weight: 700;
        color: #1a6b5a;
        letter-spacing: 0.01em;
    }

    .fvu-title-block small { color: var(--fvu-muted); font-size: 13px; }

    .fvu-title-icon {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 18px;
        background: #1a6b5a;
        box-shadow: none;
        flex-shrink: 0;
    }

    .fvu-pet-card,
    .fvu-vital-grid .card {
        border: 1px solid rgba(216,226,234,.9) !important;
        border-radius: 14px !important;
        box-shadow: 0 5px 15px rgba(37,55,72,.08) !important;
        transition: transform .18s ease, box-shadow .18s ease;
    }

    .fvu-pet-card:hover,
    .fvu-vital-grid .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 9px 22px rgba(37,55,72,.12) !important;
    }

    .fvu-pet-card { overflow: hidden; border-top: 4px solid var(--fvu-primary) !important; }
    .fvu-pet-card .media { flex-direction: column; align-items: center; }
    .fvu-pet-card #profile-image {
        width: 84px !important; height: 84px !important; object-fit: cover;
        margin: 4px auto 14px !important;
        border: 4px solid #e4f7f5; box-shadow: 0 4px 12px rgba(0,0,0,.12);
    }
    .fvu-pet-card .media-body { width: 100%; }
    .fvu-pet-card .media-body > .row > .col-12:first-child { text-align: center; margin-bottom: 8px !important; }
    .fvu-pet-card p { margin-bottom: 0; color: var(--fvu-muted); font-size: 12px; }
    .fvu-pet-card .fvu-pet-attribute {
        padding-top: 6px;
        border-top: 1px solid #eef2f5;
    }

    .fvu-dashboard-grid {
        display: grid;
        width: 100%;
        grid-template-columns: minmax(240px, 22%) minmax(0, 1.15fr) minmax(0, 0.85fr);
        grid-template-areas:
            "pet vitals summaries"
            "actions actions actions"
            "details details details"
            "history history history";
        align-items: stretch;
        gap: 14px 16px;
        margin: 0;
    }

    .fvu-zone-pet { grid-area: pet; min-width: 0; }
    .fvu-zone-vitals { grid-area: vitals; min-width: 0; display: flex; }
    .fvu-zone-summaries { grid-area: summaries; min-width: 0; display: flex; }
    .fvu-zone-actions { grid-area: actions; min-width: 0; }
    .fvu-dashboard-grid > .fvu-more-panel { grid-area: details; min-width: 0; }
    .fvu-dashboard-grid > .fvu-historial-full { grid-area: history; min-width: 0; }

    .fvu-dashboard-grid .fvu-pet-card {
        height: 100%;
        margin-bottom: 0;
    }

    .fvu-dashboard-grid .fvu-vital-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
        margin: 0;
        width: 100%;
        flex: 1;
    }

    .fvu-dashboard-grid .fvu-vital-grid > .fvu-vital-item {
        width: 100%;
        max-width: none;
        padding: 0;
        display: flex;
    }

    .fvu-dashboard-grid .fvu-vital-grid .card {
        margin: 0 !important;
        width: 100%;
    }

    .fvu-zone-summaries .fvu-resumen-cards-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        grid-template-rows: repeat(2, minmax(0, 1fr));
        gap: 10px;
        width: 100%;
        flex: 1;
    }

    .fvu-zone-actions {
        display: flex;
        justify-content: center;
        align-items: stretch;
        flex-wrap: wrap;
        gap: 12px;
        padding: 4px 0 2px;
    }

    .fvu-action-btn {
        flex: 1 1 280px;
        max-width: 420px;
        min-height: 48px;
        border-radius: 12px !important;
        font-weight: 600;
        font-size: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 14px rgba(37, 55, 72, .08);
        transition: transform .18s ease, box-shadow .18s ease;
    }

    .fvu-action-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 20px rgba(37, 55, 72, .12);
    }

    .fvu-action-btn.btn-danger-light-c {
        background: #fff5f5 !important;
        border: 1px solid #f5c2c7 !important;
    }

    .fvu-action-btn.btn-purple-light-c {
        border: 1px dashed #b98ad1 !important;
    }

    .fvu-vital-grid > .fvu-vital-item { display: flex; }
    .fvu-vital-grid .card { width: 100%; min-height: 72px; margin-bottom: 0 !important; }
    .fvu-vital-grid .card-body { display: flex; align-items: center; padding: 10px 12px !important; }
    .fvu-vital-title { font-size: 13px; font-weight: 600; color: #344563; }
    .fvu-vital-value { font-size: 13px; font-weight: 700; line-height: 1.2; }
    .fvu-vital-count-row {
        display: inline-flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 2px;
    }
    .fvu-card-ver-btn {
        flex-shrink: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 42px;
        height: 24px;
        padding: 0 10px;
        border: 1px solid #e8b4b8;
        border-radius: 6px;
        background: #fff;
        color: #c0392b;
        font-size: 11px;
        font-weight: 700;
        line-height: 1;
        text-transform: lowercase;
        text-decoration: none;
        cursor: pointer;
        transition: background .15s ease, border-color .15s ease;
    }
    .fvu-card-ver-btn:hover,
    .fvu-card-ver-btn:focus {
        background: #fff5f5;
        border-color: #d9888f;
        color: #a93226;
        text-decoration: none;
        outline: none;
    }
    .fvu-summary-card .fvu-card-ver-btn { align-self: center; margin-left: auto; }
    .fvu-vital-grid h5 { font-size: 13px; margin-bottom: 0; }
    .fvu-vital-grid img { width: 36px !important; height: 36px; object-fit: contain; flex-shrink: 0; }

    .fvu-more-panel { margin-top: 2px; margin-bottom: 12px; }
    .fvu-more-panel > div > .card { margin-bottom: 0; background: transparent; box-shadow: none; border: 0; }
    .fvu-more-panel #cabecera_info.show {
        background: #fff; border: 1px solid var(--fvu-border); border-radius: 14px;
        box-shadow: 0 6px 18px rgba(37,55,72,.08); overflow: hidden;
    }

    .fvu-zone-summaries .fvu-summary-card {
        background: #fff;
        border: 1px solid var(--fvu-border);
        border-radius: 14px;
        padding: 12px 14px;
        box-shadow: 0 6px 18px rgba(38, 59, 80, .07);
        display: flex;
        flex-direction: row;
        align-items: flex-start;
        gap: 10px;
        min-height: 0;
        height: 100%;
        transition: transform .18s ease, box-shadow .18s ease;
    }
    .fvu-summary-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 24px rgba(38, 59, 80, .11);
    }
    .fvu-summary-card--info { border-top: 3px solid #24b7ae; }
    .fvu-summary-card--danger { border-top: 3px solid #ff6268; }
    .fvu-summary-card--teal { border-top: 3px solid #168f86; }
    .fvu-summary-card--purple { border-top: 3px solid #6c389a; }
    .fvu-summary-card__icon {
        flex-shrink: 0;
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: #f4f8fb;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .fvu-summary-card__icon img { width: 28px; height: 28px; object-fit: contain; }
    .fvu-summary-card__body { flex: 1; min-width: 0; }
    .fvu-summary-card__body h5 {
        margin: 0 0 4px;
        font-size: 13px;
        font-weight: 700;
        color: #263b50;
        line-height: 1.25;
    }
    .fvu-summary-card__body ul {
        list-style: none;
        padding: 0;
        margin: 0;
        font-size: 12px;
        color: var(--fvu-muted);
        line-height: 1.35;
    }
    .fvu-summary-card__body li + li { margin-top: 4px; }
    .fvu-summary-card .fvu-empty { font-style: italic; opacity: .85; }

    .fvu-historial-full > div:first-child h5 {
        display: flex; align-items: center; gap: 8px; margin-top: 8px;
    }
    .fvu-historial-full > div:first-child h5:before {
        content: ''; width: 5px; height: 24px; border-radius: 3px; background: var(--fvu-primary);
    }
    .fvu-historial-full .card-a {
        margin-bottom: 10px; overflow: hidden; border: 1px solid var(--fvu-border);
        border-radius: 11px; background: #fff; box-shadow: 0 3px 9px rgba(37,55,72,.07);
    }
    .fvu-historial-full .card-header-a button {
        min-height: 42px; padding: 9px 14px !important; font-weight: 600; color: #3c4858;
        background: #fff; border: 0;
    }
    .fvu-historial-full .card-header-a button:hover { background: #f0fbfa; color: var(--fvu-primary); }

    .fvu-historial-full {
        width: 100%;
        max-width: 100%;
        margin-right: 0;
        margin-left: 0;
    }

    .fvu-historial-full > [class*="col-"],
    .fvu-historial-full .card-a {
        width: 100%;
        max-width: 100%;
    }

    .odontograma-felino-wrap {
        margin-top: 6px;
    }

    .odontograma-felino-titulo,
    .odontograma-felino-subtitulo {
        color: #343a40;
        font-weight: 600;
    }

    .odontograma-felino-lienzo {
        background: #d8e2ee;
        border-radius: 8px;
        padding: 18px 14px;
        position: relative;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 18px 24px;
    }

    .odontograma-felino-cuadrante {
        display: grid;
        grid-template-columns: repeat(9, minmax(42px, 1fr));
        gap: 8px;
        position: relative;
        z-index: 2;
    }

    .odontograma-pieza {
        display: flex;
        flex-direction: column;
        align-items: center;
        cursor: pointer;
        margin: 0;
    }

    .odontograma-pieza input {
        display: none;
    }

    .odontograma-caja {
        width: 30px;
        height: 30px;
        border: 3px solid #1b9ed8;
        border-radius: 4px;
        background: #f3f7fb;
        box-shadow: inset 0 0 0 1px #9db6c8;
    }

    .odontograma-pieza input:checked + .odontograma-caja {
        background: #1b9ed8;
        box-shadow: inset 0 0 0 2px #f3f7fb;
    }

    .odontograma-numero {
        font-size: 12px;
        font-weight: 700;
        color: #3d4348;
        margin-top: 4px;
    }

    .odontograma-eje {
        position: absolute;
        border-color: #525a63;
        border-style: dashed;
        z-index: 1;
    }

    .odontograma-eje-horizontal {
        left: 14px;
        right: 14px;
        top: 50%;
        border-width: 0 0 2px 0;
    }

    .odontograma-eje-vertical {
        top: 14px;
        bottom: 14px;
        left: 50%;
        border-width: 0 0 0 2px;
    }

    .odontograma-felino-resumen {
        margin-top: 16px;
    }

    .odontograma-felino-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        border-top: 2px solid #d24b3e;
        border-left: 2px solid #d24b3e;
    }

    .odontograma-felino-grid > div {
        border-right: 2px solid #d24b3e;
        border-bottom: 2px solid #d24b3e;
        padding: 14px;
        font-size: 22px;
        line-height: 1.3;
    }

    @media (max-width: 1200px) {
        .fvu-dashboard-grid .fvu-vital-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 992px) {
        .fvu-dashboard-grid {
            grid-template-columns: minmax(240px, 36%) minmax(0, 1fr);
            grid-template-areas:
                "pet vitals"
                "summaries summaries"
                "actions actions"
                "details details"
                "history history";
        }

        .fvu-zone-summaries .fvu-resumen-cards-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
            grid-template-rows: auto;
        }

        .odontograma-felino-lienzo {
            grid-template-columns: 1fr;
        }

        .odontograma-eje-vertical {
            display: none;
        }

        .odontograma-eje-horizontal {
            top: 50%;
        }

        .fvu-title-block {
            margin-left: 0;
            margin-right: 0;
        }
    }

    @media (max-width: 768px) {
        .fvu-dashboard-grid {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .fvu-dashboard-grid .fvu-vital-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .fvu-zone-summaries .fvu-resumen-cards-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .fvu-zone-actions {
            flex-direction: column;
            align-items: stretch;
        }

        .fvu-action-btn {
            max-width: none;
            width: 100%;
        }

        .odontograma-felino-cuadrante {
            grid-template-columns: repeat(5, minmax(42px, 1fr));
        }

        .odontograma-felino-grid {
            grid-template-columns: 1fr;
        }

        .odontograma-felino-grid > div {
            font-size: 18px;
        }

        .fvu-pet-card #profile-image { width: 72px !important; height: 72px !important; }
        .fvu-vital-grid .card { min-height: 74px; }
    }

    @media (max-width: 480px) {
        .fvu-zone-summaries .fvu-resumen-cards-grid { grid-template-columns: 1fr; }
        .fvu-dashboard-grid .fvu-vital-grid { grid-template-columns: 1fr; }
        .fvu-title-block small { display: none; }
        .fvu-pet-card .fvu-pet-attribute { padding-left: 10px; padding-right: 10px; }
    }
</style>

<div id="modal_contacto_emergencia_fvu" class="modal fade" tabindex="-1" role="dialog"
    aria-labelledby="modal_contacto_emergencia_fvu_titulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 rounded-xl overflow-hidden">
            <div class="modal-header bg-info">
                <h5 class="modal-title text-white" id="modal_contacto_emergencia_fvu_titulo">
                    <i class="feather icon-phone-call mr-2"></i> Contacto de emergencia
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="media mb-3">
                    <div class="wid-45 hei-45 rounded-circle bg-light-info d-flex align-items-center justify-content-center mr-3">
                        <i class="feather icon-user text-info f-22"></i>
                    </div>
                    <div class="media-body">
                        <small class="text-muted">Contacto / responsable</small>
                        <h5 class="mb-0">{{ $contactoNombreFvu }}</h5>
                    </div>
                </div>
                <div class="border rounded-lg px-3">
                    <div class="py-2 border-bottom"><strong>RUT:</strong> {{ $contactoRutFvu }}</div>
                    <div class="py-2 border-bottom">
                        <strong>Teléfono:</strong>
                        @if ($contactoTelefonoFvu !== 'Sin registro')
                            <a href="tel:{{ preg_replace('/[^0-9+]/', '', (string) $contactoTelefonoFvu) }}">{{ $contactoTelefonoFvu }}</a>
                        @else
                            Sin registro
                        @endif
                    </div>
                    <div class="py-2">
                        <strong>Email:</strong>
                        @if ($contactoEmailFvu !== 'Sin registro')
                            <a href="mailto:{{ $contactoEmailFvu }}">{{ $contactoEmailFvu }}</a>
                        @else
                            Sin registro
                        @endif
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal_fvu_vacunas" tabindex="-1" role="dialog" aria-labelledby="modal_fvu_vacunas_titulo" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="modal_fvu_vacunas_titulo">Registro de vacunas — {{ $mascota->nombre ?? 'Mascota' }}</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-sm mb-0">
                        <thead class="thead-light">
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
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal_fvu_desparasitaciones" tabindex="-1" role="dialog" aria-labelledby="modal_fvu_desparasitaciones_titulo" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="modal_fvu_desparasitaciones_titulo">Registro de desparasitación — {{ $mascota->nombre ?? 'Mascota' }}</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-sm mb-0">
                        <thead class="thead-light">
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
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
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

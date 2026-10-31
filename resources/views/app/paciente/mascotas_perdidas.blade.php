@extends('template.usuario.template')

@php
    // Regiones para los filtros y el reporte; las comunas se cargan según la región elegida
    $regiones = \App\Models\Region::orderBy('id')->get(['id', 'nombre']);

    // Mascotas del tutor (sin fallecidas) para el modal de publicar mascota perdida
    $pacienteActual = \App\Models\Paciente::where('id_usuario', auth()->id())->first();
    $mascotasTutor = $pacienteActual
        ? \App\Models\Mascota::where('id_responsable', $pacienteActual->id)->seleccionables()->orderBy('nombre')->get()
            ->map(fn ($m) => ['id' => $m->id, 'nombre' => $m->nombre, 'foto_url' => $m->foto_url, 'fallecida' => $m->fallecida])->values()
        : collect();

    // Avisos de ejemplo mientras se programa el guardado real de reportes.
    // foto = foto de perfil (si no hay, se muestra el ícono de mascota); fotos = las que subió el tutor
    $avisos = [
        ['id' => 1, 'tutor' => 'Camila R.', 'nombre' => 'Luna', 'especie' => 'Perro', 'raza' => 'Mestiza', 'estado' => 'perdida', 'comuna' => 'Providencia', 'direccion' => 'Av. Ricardo Lyon 1200, esquina Pocuro', 'recompensa' => 50000, 'fecha' => '27/10/2026', 'detalle' => 'Collar rojo, muy mansa. Se perdió cerca del parque Inés de Suárez.', 'foto' => 'images/promociones/promo3.jpg', 'enfoque' => '50% 20%', 'fotos' => ['images/promociones/promo3.jpg', 'images/promociones/promo1.jpg']],
        ['id' => 2, 'tutor' => 'Felipe M.', 'nombre' => 'Michi', 'especie' => 'Gato', 'raza' => 'Siamés', 'estado' => 'perdida', 'comuna' => 'Ñuñoa', 'direccion' => 'Calle Suecia 2850, depto. 304', 'recompensa' => null, 'fecha' => '25/10/2026', 'detalle' => 'Ojos azules, tiene chip. Asustadizo con extraños.', 'foto' => null, 'fotos' => []],
        ['id' => 3, 'tutor' => 'Javiera S.', 'nombre' => 'Sin nombre', 'especie' => 'Perro', 'raza' => 'Labrador', 'estado' => 'encontrada', 'comuna' => 'Maipú', 'direccion' => 'Av. Pajaritos 3400, frente al metro Las Parcelas', 'recompensa' => null, 'fecha' => '24/10/2026', 'detalle' => 'Encontrado en Av. Pajaritos. Está a resguardo, buscamos a su familia.', 'foto' => 'images/promociones/promo1.jpg', 'enfoque' => '68% 72%', 'fotos' => ['images/promociones/promo1.jpg']],
    ];

    // Mis avisos publicados y lo que me respondieron otros tutores de la red (ejemplo)
    $misAvisos = [
        [
            'id' => 10, 'nombre' => 'Toby', 'especie' => 'Perro', 'raza' => 'Beagle', 'estado' => 'perdida', 'fecha' => '26/10/2026',
            'direccion' => 'Av. Grecia 1500, Ñuñoa', 'foto' => 'images/promociones/promo3.jpg', 'enfoque' => '50% 20%',
            'respuestas' => [
                ['tutor' => 'Ignacio P.', 'tipo' => 'la_tiene', 'fecha' => '28/10/2026 18:40', 'lugar' => 'Plaza Ñuñoa, Av. Irarrázaval 3450', 'mensaje' => 'Lo encontré solito en la plaza, está conmigo y está bien. Tiene collar azul.', 'foto' => 'images/promociones/promo3.jpg', 'telefono' => '+56 9 8765 4321'],
                ['tutor' => 'María José L.', 'tipo' => 'la_vio', 'fecha' => '27/10/2026 09:15', 'lugar' => 'Av. Grecia con Marathon', 'mensaje' => 'Lo vi cruzando hacia el estadio, iba en dirección al poniente.', 'foto' => null, 'telefono' => null],
            ],
        ],
    ];
    $totalRespuestas = collect($misAvisos)->sum(fn ($a) => count($a['respuestas']));
@endphp

@section('content')
<div class="pcoded-main-container">
    <div class="pcoded-content">
        <div class="perdidas-intro">
            <div>
                <h3>Mascotas perdidas</h3>
                <p>Revisa los avisos cercanos o reporta a tu mascota para que otros tutores te ayuden a encontrarla.</p>
            </div>
            <button type="button" class="btn btn-info" onclick="return abrirModalMascotaPerdida(event);">
                <i class="feather icon-plus mr-1"></i> Publicar mi mascota perdida
            </button>
        </div>

        <!--Pestañas: avisos de la red y mis avisos con sus respuestas-->
        <ul class="nav nav-pills pill-moderno pill-moderno-white" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-toggle="pill" href="#panel_avisos_red" role="tab">
                    <i class="feather icon-users mr-1"></i> Avisos de la red
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="pill" href="#panel_mis_avisos" role="tab">
                    <i class="feather icon-inbox mr-1"></i> Mis avisos
                    @if($totalRespuestas)
                        <span class="badge badge-pill badge-danger ml-1">{{ $totalRespuestas }}</span>
                    @endif
                </a>
            </li>
        </ul>

        <div class="tab-content p-0 bg-transparent shadow-none">
        <div class="tab-pane fade show active" id="panel_avisos_red" role="tabpanel">

        <!--Filtros-->
        <div class="perdidas-filtros">
            <div class="perdidas-filtros-cabecera">
                <span class="perdidas-filtros-titulo"><i class="feather icon-filter"></i> Filtrar avisos</span>
                <button type="button" class="btn btn-info btn-sm btn-cerca-de-mi" id="btn_cerca_de_mi">
                    <i class="feather icon-map-pin mr-1"></i> Cerca de mí
                </button>
            </div>
            <div class="perdidas-ubicacion-estado" id="ubicacion_estado" aria-live="polite"></div>
            <div class="form-row">
                <div class="form-group col-md-3 mb-md-0">
                    <label class="floating-label-activo-sm" for="filtro_region">Región</label>
                    <select id="filtro_region" class="form-control form-control-sm selector-region" data-comuna="#filtro_comuna">
                        <option value="">Todas</option>
                        @foreach($regiones as $region)
                            <option value="{{ $region->id }}">{{ $region->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-3 mb-md-0">
                    <label class="floating-label-activo-sm" for="filtro_comuna">Comuna</label>
                    <select id="filtro_comuna" class="form-control form-control-sm">
                        <option value="">Todas</option>
                    </select>
                </div>
                <div class="form-group col-md-3 mb-md-0">
                    <label class="floating-label-activo-sm" for="filtro_especie">Especie</label>
                    <select id="filtro_especie" class="form-control form-control-sm">
                        <option value="">Todas</option>
                        <option value="Perro">Perro</option>
                        <option value="Gato">Gato</option>
                        <option value="Otra">Otra</option>
                    </select>
                </div>
                <div class="form-group col-md-3 mb-0">
                    <label class="floating-label-activo-sm" for="filtro_estado">Estado</label>
                    <select id="filtro_estado" class="form-control form-control-sm">
                        <option value="">Todos</option>
                        <option value="perdida">Perdidas</option>
                        <option value="encontrada">Encontradas</option>
                    </select>
                </div>
            </div>
        </div>

        <!--Avisos-->
        <div class="perdidas-grilla" id="perdidas_grilla">
            @foreach($avisos as $aviso)
                @php $fotosAviso = array_map(fn ($ruta) => asset($ruta), $aviso['fotos']); @endphp
                <article class="aviso-perdida {{ $aviso['estado'] }}" data-estado="{{ $aviso['estado'] }}" data-especie="{{ $aviso['especie'] }}" data-comuna="{{ $aviso['comuna'] }}">
                    <div class="aviso-perdida-cabecera">
                        <span class="aviso-perdida-estado">{{ $aviso['estado'] == 'perdida' ? 'Perdida' : 'Encontrada' }}</span>
                        <div class="aviso-perdida-avatar">
                            @if($aviso['foto'])
                                <img src="{{ asset($aviso['foto']) }}" alt="Foto de {{ $aviso['nombre'] }}" style="object-position: {{ $aviso['enfoque'] ?? 'center' }};">
                            @else
                                <img src="{{ asset('images/iconos/mascotas.svg') }}" alt="Sin foto" class="sin-foto">
                            @endif
                        </div>
                    </div>
                    <div class="aviso-perdida-cuerpo">
                        <h5>{{ $aviso['nombre'] }}</h5>
                        <span class="aviso-perdida-especie">{{ $aviso['especie'] }} · {{ $aviso['raza'] }}</span>
                        @if(!empty($aviso['recompensa']))
                            <span class="aviso-perdida-recompensa">
                                <i class="feather icon-award"></i> Recompensa ${{ number_format($aviso['recompensa'], 0, ',', '.') }}
                            </span>
                        @endif
                        <div class="aviso-perdida-datos">
                            <div class="aviso-perdida-dato">
                                <small>Comuna</small>
                                <span><i class="feather icon-map-pin"></i>{{ $aviso['comuna'] }}</span>
                            </div>
                            <div class="aviso-perdida-dato">
                                <small>{{ $aviso['estado'] == 'perdida' ? 'Se perdió' : 'Encontrada' }}</small>
                                <span><i class="feather icon-calendar"></i>{{ $aviso['fecha'] }}</span>
                            </div>
                        </div>
                        <div class="aviso-perdida-direccion">
                            <i class="feather icon-navigation"></i>
                            <div>
                                <small>{{ $aviso['estado'] == 'perdida' ? 'Dirección donde se perdió' : 'Dirección donde se encontró' }}</small>
                                <span>{{ $aviso['direccion'] }}</span>
                            </div>
                        </div>
                        <p class="aviso-perdida-detalle">{{ $aviso['detalle'] }}</p>
                        <div class="aviso-perdida-acciones">
                            @if(count($fotosAviso))
                                <button type="button" class="btn btn-ver-fotos" data-nombre="{{ $aviso['nombre'] }}" data-fotos='@json($fotosAviso)'>
                                    <i class="feather icon-image mr-1"></i> Fotos ({{ count($fotosAviso) }})
                                </button>
                            @endif
                            <button type="button" class="btn btn-info btn-contactar-aviso"
                                data-id="{{ $aviso['id'] }}" data-estado="{{ $aviso['estado'] }}" data-nombre="{{ $aviso['nombre'] }}"
                                data-tutor="{{ $aviso['tutor'] }}" data-foto="{{ $aviso['foto'] ? asset($aviso['foto']) : asset('images/iconos/mascotas.svg') }}">
                                <i class="feather icon-message-circle mr-1"></i> {{ $aviso['estado'] == 'perdida' ? 'La he visto' : 'Es mi mascota' }}
                            </button>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
        <div class="perdidas-vacio" id="perdidas_vacio">
            <i class="feather icon-search mr-1"></i> No hay avisos con esos filtros.
        </div>
        </div>

        <!--Mis avisos: respuestas que mandaron otros tutores-->
        <div class="tab-pane fade" id="panel_mis_avisos" role="tabpanel">
            <p class="perdidas-privacidad">
                <i class="feather icon-shield"></i>
                Solo tutores registrados en la red pueden responder tus avisos. Tu teléfono no se muestra hasta que tú decidas compartirlo.
            </p>

            @forelse($misAvisos as $miAviso)
                <section class="mi-aviso">
                    <div class="mi-aviso-cabecera">
                        <img src="{{ asset($miAviso['foto']) }}" alt="Foto de {{ $miAviso['nombre'] }}" style="object-position: {{ $miAviso['enfoque'] ?? 'center' }};">
                        <div class="mi-aviso-texto">
                            <h5>{{ $miAviso['nombre'] }} <span class="aviso-perdida-especie">· {{ $miAviso['especie'] }} {{ $miAviso['raza'] }}</span></h5>
                            <small><i class="feather icon-map-pin"></i> {{ $miAviso['direccion'] }} · Se perdió el {{ $miAviso['fecha'] }}</small>
                        </div>
                        <button type="button" class="btn btn-info btn-sm btn-aviso-encontrada" data-nombre="{{ $miAviso['nombre'] }}">
                            <i class="feather icon-check mr-1"></i> Ya la encontré
                        </button>
                    </div>

                    <h6 class="mi-aviso-subtitulo">Respuestas recibidas ({{ count($miAviso['respuestas']) }})</h6>

                    @forelse($miAviso['respuestas'] as $respuesta)
                        <article class="respuesta-aviso {{ $respuesta['tipo'] }}">
                            <div class="respuesta-aviso-cabecera">
                                <span class="respuesta-aviso-avatar">{{ mb_substr($respuesta['tutor'], 0, 1) }}</span>
                                <div>
                                    <strong>{{ $respuesta['tutor'] }}</strong>
                                    <small>{{ $respuesta['fecha'] }}</small>
                                </div>
                                <span class="respuesta-aviso-tipo">
                                    {{ $respuesta['tipo'] == 'la_tiene' ? 'La tiene con él/ella' : 'La vio' }}
                                </span>
                            </div>
                            <div class="respuesta-aviso-lugar">
                                <i class="feather icon-navigation"></i> {{ $respuesta['lugar'] }}
                            </div>
                            <p class="respuesta-aviso-mensaje">{{ $respuesta['mensaje'] }}</p>
                            <div class="respuesta-aviso-acciones">
                                @if($respuesta['foto'])
                                    <button type="button" class="btn btn-ver-fotos btn-sm" data-nombre="{{ $miAviso['nombre'] }}" data-fotos='@json([asset($respuesta['foto'])])'>
                                        <i class="feather icon-image mr-1"></i> Ver foto
                                    </button>
                                @endif
                                <a class="btn btn-ver-fotos btn-sm" target="_blank" rel="noopener"
                                    href="https://www.google.com/maps/search/?api=1&query={{ urlencode($respuesta['lugar']) }}">
                                    <i class="feather icon-map mr-1"></i> Ver en el mapa
                                </a>
                                @if($respuesta['telefono'])
                                    <a class="btn btn-info btn-sm" href="https://wa.me/{{ preg_replace('/\D/', '', $respuesta['telefono']) }}" target="_blank" rel="noopener">
                                        <i class="feather icon-phone mr-1"></i> Contactar {{ $respuesta['telefono'] }}
                                    </a>
                                @else
                                    <button type="button" class="btn btn-info btn-sm btn-compartir-contacto" data-tutor="{{ $respuesta['tutor'] }}">
                                        <i class="feather icon-share-2 mr-1"></i> Compartir mi contacto
                                    </button>
                                @endif
                            </div>
                        </article>
                    @empty
                        <div class="perdidas-vacio d-block">Aún nadie ha respondido este aviso.</div>
                    @endforelse
                </section>
            @empty
                <div class="perdidas-vacio d-block">No tienes avisos publicados.</div>
            @endforelse
        </div>
        </div>
    </div>
</div>

<!--Modal para avisar al tutor: la vi, la tengo o es mi mascota-->
<div class="modal fade" id="modalContactoAviso" tabindex="-1" role="dialog" aria-labelledby="tituloContactoAviso" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
        <form class="modal-content" id="formContactoAviso">
            <div class="modal-header modal-header-purple">
                <h5 class="modal-title text-white" id="tituloContactoAviso">Avisar al tutor</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="aviso_id" id="contacto_aviso_id">
                <div class="contacto-aviso-resumen">
                    <img id="contacto_foto" src="{{ asset('images/iconos/mascotas.svg') }}" alt="">
                    <div>
                        <strong id="contacto_titulo"></strong>
                        <small>Tu mensaje le llega a <span id="contacto_tutor"></span> dentro de la red.</small>
                    </div>
                </div>

                <!-- Aviso de mascota perdida: la vi o la tengo -->
                <div id="contacto_bloque_perdida">
                    <div class="contacto-tipos">
                        <label class="contacto-tipo">
                            <input type="radio" name="tipo" value="la_vio" checked>
                            <span><i class="feather icon-eye"></i> La vi</span>
                        </label>
                        <label class="contacto-tipo">
                            <input type="radio" name="tipo" value="la_tiene">
                            <span><i class="feather icon-home"></i> La tengo conmigo</span>
                        </label>
                    </div>
                </div>

                <!-- Aviso de mascota encontrada: es mía -->
                <div id="contacto_bloque_encontrada" class="d-none">
                    <div class="form-group">
                        <label class="floating-label-activo-sm" for="contacto_mi_mascota">¿Cuál de tus mascotas es?</label>
                        <select id="contacto_mi_mascota" name="mascota_id" class="form-control form-control-sm">
                            <option value="">No la tengo registrada</option>
                            @foreach($mascotasTutor as $mascota)
                                <option value="{{ $mascota['id'] }}">{{ $mascota['nombre'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="floating-label-activo-sm" for="contacto_sena"><span class="text-danger">*</span> Una seña que solo tú conozcas</label>
                        <input type="text" id="contacto_sena" name="sena" class="form-control form-control-sm" placeholder="Ej.: cicatriz en la oreja, responde a un nombre, chip">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-12" id="contacto_grupo_lugar">
                        <label class="floating-label-activo-sm" for="contacto_lugar"><span class="text-danger">*</span> <span id="contacto_lugar_texto">¿Dónde la viste?</span></label>
                        <select id="contacto_lugar" name="lugar" class="form-control form-control-sm"></select>
                        <input type="hidden" id="contacto_latitud" name="latitud">
                        <input type="hidden" id="contacto_longitud" name="longitud">
                        <button type="button" class="contacto-usar-ubicacion" id="contacto_usar_ubicacion">
                            <i class="feather icon-map-pin"></i> Usar mi ubicación actual
                        </button>
                    </div>
                    <div class="form-group col-6" id="contacto_grupo_fecha">
                        <label class="floating-label-activo-sm" for="contacto_fecha">Fecha</label>
                        <input type="date" id="contacto_fecha" name="fecha" class="form-control form-control-sm" max="{{ date('Y-m-d') }}" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="form-group col-6" id="contacto_grupo_hora">
                        <label class="floating-label-activo-sm" for="contacto_hora">Hora aproximada</label>
                        <input type="time" id="contacto_hora" name="hora" class="form-control form-control-sm">
                    </div>
                    <div class="form-group col-12">
                        <label class="floating-label-activo-sm" for="contacto_mensaje"><span class="text-danger">*</span> Mensaje para el tutor</label>
                        <textarea id="contacto_mensaje" name="mensaje" rows="3" class="form-control form-control-sm"></textarea>
                    </div>
                    <div class="col-12 mb-3">
                        <div class="titulo-item text-dark">Fotos</div>
                        <p class="small text-muted mb-2">Si alcanzaste a sacarle fotos, súbelas para que el tutor la reconozca.</p>
                        <input type="hidden" id="contacto_fotos_json" name="fotos" value="[]">
                        <div class="dropzone" id="dropzone-contacto-aviso"></div>
                    </div>
                </div>

                <!-- Compartir teléfono: mismo bloque con switch que la recompensa -->
                <div class="perdida-recompensa mb-0" id="contacto_bloque_telefono">
                    <div class="perdida-recompensa-cabecera">
                        <span class="perdida-recompensa-icono contacto-icono-telefono"><i class="feather icon-phone"></i></span>
                        <label class="perdida-recompensa-texto" for="contacto_compartir_telefono">
                            <strong>Compartir mi teléfono</strong>
                            <small>Si no lo activas, el tutor solo verá tu nombre y tu mensaje, y podrá pedirte el contacto desde la red.</small>
                        </label>
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="contacto_compartir_telefono" name="compartir_telefono" value="1">
                            <label class="custom-control-label" for="contacto_compartir_telefono"><span class="sr-only">Compartir mi teléfono</span></label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-dark" data-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-info"><i class="feather icon-send"></i> Enviar aviso</button>
            </div>
        </form>
    </div>
</div>

<!--Modal galería de fotos del aviso-->
<div class="modal fade" id="modalFotosPerdida" tabindex="-1" role="dialog" aria-labelledby="tituloFotosPerdida" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header modal-header-purple">
                <h5 class="modal-title text-white" id="tituloFotosPerdida">Fotos</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div id="carruselFotosPerdida" class="carousel slide" data-interval="false">
                    <div class="carousel-inner"></div>
                    <a class="carousel-control-prev" href="#carruselFotosPerdida" role="button" data-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="sr-only">Anterior</span>
                    </a>
                    <a class="carousel-control-next" href="#carruselFotosPerdida" role="button" data-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="sr-only">Siguiente</span>
                    </a>
                </div>
                <div class="galeria-miniaturas"></div>
            </div>
        </div>
    </div>
</div>

@include('app.paciente.partials.mascota_perdida.modal')
@endsection

@section('page-script')
<script>
    $(function () {
        // Comunas según la región elegida (misma búsqueda que usa el registro)
        function cargarComunas(selectRegion, alTerminar) {
            var comuna = $($(selectRegion).data('comuna'));
            var primera = comuna.find('option:first').clone();
            comuna.empty().append(primera);
            if (!selectRegion.value) { filtrarAvisos(); return; }
            $.get('{{ route('home.buscar_ciudad_region') }}', { region: selectRegion.value }, function (datos) {
                $.each(JSON.parse(datos), function (i, ciudad) {
                    comuna.append($('<option>', { value: ciudad.nombre, text: ciudad.nombre }));
                });
                if (alTerminar) { alTerminar(comuna); }
            });
            filtrarAvisos();
        }

        $('.selector-region').on('change', function () {
            cargarComunas(this);
        });

        // Cerca de mí: toma la ubicación del navegador y deja elegidas la región y la comuna
        function normalizar(texto) {
            return (texto || '').toString().toLowerCase()
                .normalize('NFD').replace(/[̀-ͯ]/g, '')
                .replace(/\bregion\b|\bde\b|\bdel\b|\bla\b|\bthe\b/g, '')
                .replace(/[^a-z0-9]/g, '');
        }

        // Busca en un select la opción cuyo nombre coincide con alguno de los textos;
        // primero el nombre exacto y, si no hay, uno que lo contenga
        function buscarOpcion(select, candidatos) {
            var encontrada = null;
            $.each([true, false], function (j, exacto) {
                $.each(candidatos, function (i, candidato) {
                    var buscado = normalizar(candidato);
                    if (!buscado) { return; }
                    select.find('option').each(function () {
                        var nombre = normalizar($(this).text());
                        if (!this.value || !nombre) { return; }
                        if (nombre === buscado || (!exacto && (nombre.indexOf(buscado) !== -1 || buscado.indexOf(nombre) !== -1))) {
                            encontrada = this.value;
                            return false;
                        }
                    });
                    if (encontrada) { return false; }
                });
                if (encontrada) { return false; }
            });
            return encontrada;
        }

        function mostrarEstadoUbicacion(mensaje, esError) {
            $('#ubicacion_estado').toggleClass('error', !!esError).text(mensaje).show();
        }

        $('#btn_cerca_de_mi').on('click', function () {
            var boton = $(this);

            if (!navigator.geolocation) {
                mostrarEstadoUbicacion('Tu navegador no permite obtener la ubicación. Elige la región y comuna a mano.', true);
                return;
            }

            boton.prop('disabled', true).addClass('buscando');
            mostrarEstadoUbicacion('Buscando tu ubicación...');

            navigator.geolocation.getCurrentPosition(function (posicion) {
                $.getJSON('https://nominatim.openstreetmap.org/reverse', {
                    format: 'json',
                    lat: posicion.coords.latitude,
                    lon: posicion.coords.longitude,
                    'accept-language': 'es',
                    zoom: 14
                }).done(function (datos) {
                    var direccion = (datos && datos.address) || {};
                    var selectRegion = $('#filtro_region');
                    var region = buscarOpcion(selectRegion, [direccion.state, direccion.region]);

                    if (!region) {
                        boton.prop('disabled', false).removeClass('buscando');
                        mostrarEstadoUbicacion('No pudimos reconocer tu región. Elígela a mano.', true);
                        return;
                    }

                    selectRegion.val(region);
                    cargarComunas(selectRegion[0], function (selectComuna) {
                        var comuna = buscarOpcion(selectComuna, [
                            direccion.municipality, direccion.city, direccion.town, direccion.village,
                            direccion.city_district, direccion.suburb, direccion.county
                        ]);
                        boton.prop('disabled', false).removeClass('buscando');

                        if (comuna) {
                            selectComuna.val(comuna);
                            mostrarEstadoUbicacion('Mostrando avisos cerca de ti: ' + comuna + ', ' + selectRegion.find('option:selected').text() + '.');
                        } else {
                            mostrarEstadoUbicacion('Encontramos tu región, pero no la comuna. Elígela en la lista.', true);
                        }
                        filtrarAvisos();
                    });
                }).fail(function () {
                    boton.prop('disabled', false).removeClass('buscando');
                    mostrarEstadoUbicacion('No pudimos consultar tu ubicación en este momento. Intenta de nuevo.', true);
                });
            }, function (error) {
                boton.prop('disabled', false).removeClass('buscando');
                var mensaje = 'No fue posible obtener tu ubicación.';
                if (error && error.code === 1) {
                    mensaje = 'El navegador bloqueó el permiso de ubicación. Permítelo para este sitio o elige la región y comuna a mano.';
                } else if (error && error.code === 3) {
                    mensaje = 'La ubicación tardó demasiado. Intenta de nuevo.';
                }
                mostrarEstadoUbicacion(mensaje, true);
            }, { enableHighAccuracy: true, timeout: 10000, maximumAge: 60000 });
        });

        // Filtro de avisos en pantalla
        function filtrarAvisos() {
            var estado = $('#filtro_estado').val();
            var especie = $('#filtro_especie').val();
            var comuna = $('#filtro_comuna').val();
            var visibles = 0;
            $('#perdidas_grilla .aviso-perdida').each(function () {
                var tarjeta = $(this);
                var mostrar = (!estado || tarjeta.data('estado') === estado)
                    && (!especie || tarjeta.data('especie') === especie)
                    && (!comuna || tarjeta.data('comuna') === comuna);
                tarjeta.toggle(mostrar);
                if (mostrar) { visibles++; }
            });
            $('#perdidas_vacio').toggle(visibles === 0);
        }

        $('#filtro_estado, #filtro_especie, #filtro_comuna').on('change', filtrarAvisos);

        // Galería: arma el carrusel con las fotos que subió el tutor
        var carrusel = $('#carruselFotosPerdida');
        var miniaturas = $('#modalFotosPerdida .galeria-miniaturas');

        $('.btn-ver-fotos[data-fotos]').on('click', function () {
            var fotos = $(this).data('fotos') || [];
            var nombre = $(this).data('nombre');
            var interior = carrusel.find('.carousel-inner').empty();
            miniaturas.empty();

            $.each(fotos, function (i, ruta) {
                interior.append(
                    $('<div>', { class: 'carousel-item' + (i === 0 ? ' active' : '') })
                        .append($('<img>', { src: ruta, alt: 'Foto ' + (i + 1) + ' de ' + nombre }))
                );
                miniaturas.append(
                    $('<img>', { src: ruta, alt: '', class: i === 0 ? 'activa' : '', 'data-indice': i })
                );
            });

            // Con una sola foto no hacen falta flechas ni miniaturas
            carrusel.find('.carousel-control-prev, .carousel-control-next').toggle(fotos.length > 1);
            miniaturas.toggle(fotos.length > 1);

            $('#tituloFotosPerdida').text('Fotos de ' + nombre);
            $('#modalFotosPerdida').modal('show');
        });

        miniaturas.on('click', 'img', function () {
            carrusel.carousel($(this).data('indice'));
        });

        carrusel.on('slid.bs.carousel', function (e) {
            miniaturas.find('img').removeClass('activa').eq($(e.relatedTarget).index()).addClass('activa');
        });

        // Contacto entre tutores: "La he visto" en avisos de perdida y "Es mi mascota" en avisos de encontrada
        var avisoActual = null;
        var dropzoneContacto = null;
        var fotosContacto = [];

        $('.btn-contactar-aviso').on('click', function () {
            avisoActual = $(this).data();
            var esPerdida = avisoActual.estado === 'perdida';
            var nombre = avisoActual.nombre;

            $('#formContactoAviso')[0].reset();
            $('#contacto_lugar').empty().trigger('change');
            $('#contacto_latitud, #contacto_longitud').val('');
            $('#contacto_lugar_texto').text('¿Dónde la viste?');
            $('#contacto_aviso_id').val(avisoActual.id);
            $('#contacto_foto').attr('src', avisoActual.foto);
            $('#contacto_tutor').text(avisoActual.tutor);
            $('#tituloContactoAviso').text(esPerdida ? 'Avisar que la viste' : 'Es mi mascota');
            $('#contacto_titulo').text(esPerdida ? '¿Viste a ' + nombre + '?' : '¿Es tu mascota?');

            $('#contacto_bloque_perdida').toggleClass('d-none', !esPerdida);
            $('#contacto_bloque_encontrada').toggleClass('d-none', esPerdida);
            // Si es la mascota encontrada, el lugar ya lo sabe quien la tiene
            $('#contacto_grupo_lugar, #contacto_grupo_fecha, #contacto_grupo_hora').toggleClass('d-none', !esPerdida);
            $('#contacto_mensaje').attr('placeholder', esPerdida
                ? 'Cuéntale cómo estaba, hacia dónde iba o cómo puede ubicarte.'
                : 'Cuéntale por qué crees que es tuya y cuándo se perdió.');
            $('#contacto_fecha').val('{{ date('Y-m-d') }}').trigger('change');

            $('#modalContactoAviso').modal('show');
        });

        $('#modalContactoAviso').on('shown.bs.modal', function () {
            iniciarBuscadorLugarContacto();
            if (!dropzoneContacto) { iniciarDropzoneContacto(); }
        });

        $('#modalContactoAviso').on('hidden.bs.modal', function () {
            if (dropzoneContacto) { dropzoneContacto.removeAllFiles(true); }
            fotosContacto = [];
            $('#contacto_fotos_json').val('[]');
        });

        // Al elegir "La tengo conmigo" el lugar pasa a ser dónde la encontró
        $('input[name="tipo"]').on('change', function () {
            var laTiene = this.value === 'la_tiene' && this.checked;
            $('#contacto_lugar_texto').text(laTiene ? '¿Dónde la encontraste?' : '¿Dónde la viste?');
        });

        // Buscador de direcciones: el mismo de publicar mascota perdida
        function iniciarBuscadorLugarContacto() {
            var lugar = $('#contacto_lugar');
            if (!$.fn.select2 || lugar.hasClass('select2-hidden-accessible')) return;

            lugar.select2({
                width: '100%',
                dropdownParent: $('#modalContactoAviso'),
                dropdownCssClass: 'perdida-direccion-dropdown',
                placeholder: 'Ej. Av. Providencia 1234, Santiago',
                minimumInputLength: 3,
                language: {
                    inputTooShort: function () { return 'Escribe al menos 3 letras'; },
                    searching: function () { return 'Buscando direcciones...'; },
                    noResults: function () { return 'No encontramos esa dirección'; },
                    errorLoading: function () { return 'No se pudieron cargar las sugerencias'; }
                },
                ajax: {
                    url: URL_BUSCADOR_DIRECCIONES + '/api/',
                    dataType: 'json',
                    delay: 350,
                    data: function (params) {
                        return { q: params.term, limit: 6, bbox: LIMITES_CHILE };
                    },
                    processResults: function (data, params) {
                        var numero = extraerNumeroDireccion(params.term);
                        var vistas = {};
                        var resultados = (data && data.features ? data.features : [])
                            .map(function (item) { return armarSugerenciaDireccion(item, numero); })
                            .filter(function (item) {
                                if (vistas[item.text]) return false;
                                vistas[item.text] = true;
                                return true;
                            });
                        return { results: resultados };
                    }
                },
                templateResult: function (item) {
                    if (!item.titulo) return item.text;
                    var fila = $('<div></div>');
                    $('<span class="perdida-sugerencia-titulo"></span>').text(item.titulo).appendTo(fila);
                    if (item.detalle) {
                        $('<span class="perdida-sugerencia-detalle"></span>').text(item.detalle).appendTo(fila);
                    }
                    return fila;
                },
                templateSelection: function (item) {
                    return item.text || 'Ej. Av. Providencia 1234, Santiago';
                }
            });

            lugar.on('select2:select', function (e) {
                var punto = e.params.data;
                $('#contacto_latitud').val(punto.lat || '');
                $('#contacto_longitud').val(punto.lng || '');
            });
        }

        // Ubicación actual: se busca la dirección del punto y queda elegida en el buscador
        $('#contacto_usar_ubicacion').on('click', function () {
            var boton = $(this);
            if (!navigator.geolocation) {
                swal({ title: 'Ubicación', text: 'Tu navegador no permite obtener la ubicación. Escribe el lugar.', icon: 'warning' });
                return;
            }
            boton.prop('disabled', true).addClass('buscando');
            navigator.geolocation.getCurrentPosition(function (posicion) {
                var lat = posicion.coords.latitude;
                var lng = posicion.coords.longitude;
                $.getJSON(URL_BUSCADOR_DIRECCIONES + '/reverse', { lat: lat, lon: lng, limit: 1 })
                    .done(function (data) {
                        if (!data || !data.features || !data.features.length) return;
                        var punto = armarSugerenciaDireccion(data.features[0]);
                        $('#contacto_lugar').empty().append(new Option(punto.text, punto.id, true, true)).trigger('change');
                        $('#contacto_latitud').val(lat);
                        $('#contacto_longitud').val(lng);
                    })
                    .always(function () { boton.prop('disabled', false).removeClass('buscando'); });
            }, function () {
                boton.prop('disabled', false).removeClass('buscando');
                swal({ title: 'Ubicación', text: 'No pudimos obtener tu ubicación. Escribe el lugar.', icon: 'warning' });
            }, { enableHighAccuracy: true, timeout: 10000 });
        });

        // Fotos: misma zona de carga que publicar mascota perdida
        function iniciarDropzoneContacto() {
            var elemento = document.querySelector('#dropzone-contacto-aviso');
            if (!elemento || typeof Dropzone === 'undefined') return;

            dropzoneContacto = new Dropzone(elemento, {
                url: rutaImagenCarga,
                method: 'post',
                headers: { 'X-CSRF-TOKEN': CSRF_TOKEN },
                acceptedFiles: 'image/jpeg,image/png,image/jpg,.jpeg,.jpg,.png,image/*',
                maxFilesize: 6,
                maxFiles: 4,
                addRemoveLinks: true,
                paramName: 'file',
                dictDefaultMessage: 'Arrastra las fotos aquí o haz clic para elegirlas',
                dictRemoveFile: 'Quitar',
                dictInvalidFileType: 'No puedes subir archivos de este tipo.',
                dictFileTooBig: 'El archivo es demasiado grande. Máx. 6 MiB.',
                dictMaxFilesExceeded: 'Puedes subir hasta 4 fotos.',
                success: function (file, response) {
                    var data = parsearRespuestaImagen(response) || response;
                    if (!data || !data.img) return;
                    file.rutaSubida = obtenerRutaImagenSubida(data.img);
                    fotosContacto.push(file.rutaSubida);
                    $('#contacto_fotos_json').val(JSON.stringify(fotosContacto));
                },
                removedfile: function (file) {
                    fotosContacto = fotosContacto.filter(function (ruta) { return ruta !== file.rutaSubida; });
                    $('#contacto_fotos_json').val(JSON.stringify(fotosContacto));
                    if (file.previewElement) { file.previewElement.remove(); }
                },
                error: function (file, message, xhr) {
                    mostrarErrorCargaImagen(typeof message === 'string' ? message : 'No se pudo subir la imagen.', xhr);
                }
            });
        }

        // Envío del aviso al tutor (pendiente de conectar con el guardado y la notificación)
        $('#formContactoAviso').on('submit', function (e) {
            e.preventDefault();
            var esPerdida = avisoActual && avisoActual.estado === 'perdida';
            var faltantes = [];
            if (esPerdida && !$.trim($('#contacto_lugar option:selected').text())) faltantes.push($('#contacto_lugar_texto').text().replace(/[¿?]/g, ''));
            if (!esPerdida && !$.trim($('#contacto_sena').val())) faltantes.push('Una seña que solo tú conozcas');
            if (!$.trim($('#contacto_mensaje').val())) faltantes.push('Mensaje');

            if (faltantes.length) {
                swal({ title: 'Faltan datos', text: 'Completa: ' + faltantes.join(', ') + '.', icon: 'warning' });
                return;
            }

            $('#modalContactoAviso').modal('hide');
            swal({
                title: 'Aviso enviado',
                text: avisoActual.tutor + ' recibirá una notificación y verá tu mensaje en "Mis avisos". Te avisaremos cuando responda.',
                icon: 'success'
            });
        });

        // Respuestas recibidas: compartir contacto y cerrar el aviso
        $('.btn-compartir-contacto').on('click', function () {
            var tutor = $(this).data('tutor');
            swal({
                title: 'Compartir tu contacto',
                text: tutor + ' podrá ver tu teléfono para coordinar el encuentro. ¿Continuar?',
                icon: 'info',
                buttons: ['Cancelar', 'Compartir']
            }).then(function (acepta) {
                if (acepta) {
                    swal({ title: 'Contacto compartido', text: 'Le avisamos a ' + tutor + '.', icon: 'success' });
                }
            });
        });

        $('.btn-aviso-encontrada').on('click', function () {
            var nombre = $(this).data('nombre');
            swal({
                title: '¡Qué buena noticia!',
                text: 'Se cerrará el aviso de ' + nombre + ' y dejará de mostrarse en la red. ¿Continuar?',
                icon: 'success',
                buttons: ['Cancelar', 'Cerrar aviso']
            }).then(function (acepta) {
                if (acepta) {
                    swal({ title: 'Aviso cerrado', text: 'Gracias a los tutores que ayudaron. Les avisaremos que ' + nombre + ' volvió a casa.', icon: 'success' });
                }
            });
        });
    });
</script>
<script>
    // Datos y funciones que pide el modal compartido de mascota perdida
    Dropzone.autoDiscover = false;
    var mascotasIniciales = @json($mascotasTutor);
    var rutaImagenCarga = @json(route('paciente.imagen.carga'));

    function obtenerMascotaPorId(id) {
        return mascotasIniciales.find(function (mascota) { return String(mascota.id) === String(id); }) || null;
    }

    function obtenerImagenMascota(mascota) {
        return (mascota && mascota.foto_url) || '{{ asset('images/iconos/mascotas.svg') }}';
    }

    function mascotaEstaFallecida(mascota) {
        return !!(mascota && (mascota.fallecida === true || mascota.fallecida === 1 || mascota.fallecida === '1'));
    }

    // En esta pantalla no hay lista de mascotas que refrescar
    function actualizarMascotasEnLista() {}

    function parsearRespuestaImagen(respuesta) {
        if (!respuesta) return null;
        if (typeof respuesta === 'string') {
            try { return JSON.parse(respuesta); } catch (e) { return null; }
        }
        return respuesta;
    }

    function obtenerRutaImagenSubida(img) {
        if (!img) return '';
        return img.ruta || img.nombre_img || '';
    }

    function mostrarErrorCargaImagen(mensaje, xhr) {
        var texto = mensaje || 'No se pudo subir la imagen.';
        if (xhr && xhr.responseJSON && xhr.responseJSON.message) {
            texto = xhr.responseJSON.message;
        } else if (xhr && xhr.responseJSON && xhr.responseJSON.errors && xhr.responseJSON.errors.file) {
            texto = xhr.responseJSON.errors.file.join('\n');
        }
        if (typeof swal === 'function') {
            swal({ title: 'Error al subir imagen', text: texto, icon: 'error' });
        }
    }

    @include('app.paciente.partials.mascota_perdida.script')
</script>
@endsection

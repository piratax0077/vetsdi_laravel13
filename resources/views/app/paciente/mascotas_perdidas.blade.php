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
        ['nombre' => 'Luna', 'especie' => 'Perro', 'raza' => 'Mestiza', 'estado' => 'perdida', 'comuna' => 'Providencia', 'direccion' => 'Av. Ricardo Lyon 1200, esquina Pocuro', 'recompensa' => 50000, 'fecha' => '27/10/2026', 'detalle' => 'Collar rojo, muy mansa. Se perdió cerca del parque Inés de Suárez.', 'foto' => 'images/promociones/promo3.jpg', 'enfoque' => '50% 20%', 'fotos' => ['images/promociones/promo3.jpg', 'images/promociones/promo1.jpg']],
        ['nombre' => 'Michi', 'especie' => 'Gato', 'raza' => 'Siamés', 'estado' => 'perdida', 'comuna' => 'Ñuñoa', 'direccion' => 'Calle Suecia 2850, depto. 304', 'recompensa' => null, 'fecha' => '25/10/2026', 'detalle' => 'Ojos azules, tiene chip. Asustadizo con extraños.', 'foto' => null, 'fotos' => []],
        ['nombre' => 'Sin nombre', 'especie' => 'Perro', 'raza' => 'Labrador', 'estado' => 'encontrada', 'comuna' => 'Maipú', 'direccion' => 'Av. Pajaritos 3400, frente al metro Las Parcelas', 'recompensa' => null, 'fecha' => '24/10/2026', 'detalle' => 'Encontrado en Av. Pajaritos. Está a resguardo, buscamos a su familia.', 'foto' => 'images/promociones/promo1.jpg', 'enfoque' => '68% 72%', 'fotos' => ['images/promociones/promo1.jpg']],
    ];
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

        <!--Filtros-->
        <div class="perdidas-filtros">
            <div class="perdidas-filtros-cabecera">
                <span class="perdidas-filtros-titulo"><i class="feather icon-filter"></i> Filtrar avisos</span>
                <button type="button" class="btn btn-info btn-cerca-de-mi" id="btn_cerca_de_mi">
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
                            <button type="button" class="btn btn-info">
                                <i class="feather icon-phone mr-1"></i> {{ $aviso['estado'] == 'perdida' ? 'La he visto' : 'Es mi mascota' }}
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
</div>

<!--Modal galería de fotos del aviso-->
<div class="modal fade" id="modalFotosPerdida" tabindex="-1" role="dialog" aria-labelledby="tituloFotosPerdida" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info">
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

        $('.btn-ver-fotos').on('click', function () {
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

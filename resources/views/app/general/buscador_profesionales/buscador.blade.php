@extends('template.templateBuscadorProfesionales')

@section('content')
    <!--Container Completo-->
    <div class="pcoded-main-container">
        <div class="pcoded-content">
            <!--Header-->
            <div class="row">
                <div class="col-12">
                    <h6 class="font-weight-bold f-26">{{ !empty($es_veterinaria_busqueda) ? 'Reservar hora veterinaria' : 'Reservar hora médica' }}</h6>
                    <p>{{ !empty($es_veterinaria_busqueda) ? 'Busque por servicio, por veterinario o por telemedicina y agende la hora en pocos pasos.' : 'Busque por especialidad, por profesional o por videoconsulta y agende la hora en pocos pasos.' }}</p>
                </div>
            </div>
            <!--Cierre: Header-->

            <!--Reserva de hora por pasos: todo ocurre dentro de esta tarjeta-->
            <div class="row">
                <div class="col-sm-12">
                    <div class="card" id="vista_reserva_veterinaria">
                        <div class="card-body">
                            <input type="hidden" name="select_tipo_agenda" id="select_tipo_agenda" value="1,2">

                            @if(!empty($es_veterinaria_busqueda))
                                @php
                                    // Misma foto que muestra el menú lateral de la mascota
                                    $foto_mascota_reserva = $mascota->foto_url ?? asset('images/iconos/mascotas.svg');
                                @endphp
                                <!--Para quién es la hora; al elegir veterinario su nombre queda a la derecha-->
                                <div class="reserva-vet-profesional">
                                    <img class="reserva-vet-profesional-icono" src="{{ $foto_mascota_reserva }}" alt="Foto de la mascota">
                                    <div class="reserva-vet-profesional-datos">
                                        <span class="reserva-vet-profesional-etiqueta">Reservando hora para</span>
                                        <span class="reserva-vet-profesional-nombre">{{ !empty($mascota) ? (\Illuminate\Support\Str::ucfirst(trim($mascota->nombre ?? '')) ?: '-') : 'Su mascota' }}</span>
                                    </div>
                                    <span class="reserva-vet-profesional-especialidad" id="reserva_hora_veterinario"></span>
                                </div>
                            @endif

                            <!--Indicador de pasos-->
                            <ol class="reserva-vet-pasos" id="reserva_hora_pasos">
                                <li class="reserva-vet-paso activo" data-paso="1">
                                    <button type="button" class="reserva-vet-paso-boton" onclick="irPasoReservaHora(1);" aria-current="step">
                                        <span class="reserva-vet-paso-numero">1</span>
                                        <span class="reserva-vet-paso-texto">Búsqueda</span>
                                    </button>
                                </li>
                                <li class="reserva-vet-paso" data-paso="2">
                                    <button type="button" class="reserva-vet-paso-boton" onclick="irPasoReservaHora(2);" disabled>
                                        <span class="reserva-vet-paso-numero">2</span>
                                        <span class="reserva-vet-paso-texto">{{ !empty($es_veterinaria_busqueda) ? 'Veterinario' : 'Profesional' }}</span>
                                    </button>
                                </li>
                                <li class="reserva-vet-paso" data-paso="3">
                                    <button type="button" class="reserva-vet-paso-boton" onclick="irPasoReservaHora(3);" disabled>
                                        <span class="reserva-vet-paso-numero">3</span>
                                        <span class="reserva-vet-paso-texto">Fecha y hora</span>
                                    </button>
                                </li>
                                <li class="reserva-vet-paso" data-paso="4">
                                    <button type="button" class="reserva-vet-paso-boton" onclick="irPasoReservaHora(4);" disabled>
                                        <span class="reserva-vet-paso-numero">4</span>
                                        <span class="reserva-vet-paso-texto">Confirmar</span>
                                    </button>
                                </li>
                            </ol>

                            <!--Paso 1: cómo buscar-->
                            <div class="reserva-vet-panel activo" data-paso="1">
                                <div class="reserva-vet-panel-encabezado">
                                    <h6>¿Cómo quiere buscar su hora?</h6>
                                    <p class="small text-muted mb-0">Elija una forma de búsqueda y complete los datos.</p>
                                </div>

                                <!--Las tres formas de buscar son pestañas con el aspecto de las tarjetas de lugar-->
                                <ul class="nav reserva-vet-lugares" id="Buscadores" role="tablist">
                                    <li class="nav-item reserva-vet-lugar">
                                        <button type="button" class="nav-link reserva-vet-lugar-caja active" onclick="$('#select_tipo_agenda').val(1);$('#div_resultado_busqueda').html('')" id="buscar_especialidad-tab" data-toggle="tab" data-target="#buscar_especialidad" role="tab" aria-controls="buscar_especialidad" aria-selected="true">
                                            <span class="reserva-vet-lugar-icono"><i class="feather icon-heart"></i></span>
                                            <span class="reserva-vet-lugar-datos">
                                                <span class="reserva-vet-lugar-nombre">{{ !empty($es_veterinaria_busqueda) ? 'Servicio veterinario' : 'Especialidad' }}</span>
                                                <span class="reserva-vet-lugar-direccion">{{ !empty($es_veterinaria_busqueda) ? 'Por área y motivo de consulta' : 'Por profesión y especialidad' }}</span>
                                            </span>
                                            <span class="reserva-vet-lugar-marca" aria-hidden="true"><i class="feather icon-check"></i></span>
                                        </button>
                                    </li>
                                    <li class="nav-item reserva-vet-lugar">
                                        <button type="button" class="nav-link reserva-vet-lugar-caja" onclick="$('#select_tipo_agenda').val(1);$('#div_resultado_busqueda').html('');setTimeout(function(){ $('#buscar_profesional_dato_profesional').trigger('focus'); }, 150)" id="buscar_profesional-tab" data-toggle="tab" data-target="#buscar_profesional" role="tab" aria-controls="buscar_profesional" aria-selected="false">
                                            <span class="reserva-vet-lugar-icono"><i class="feather icon-user"></i></span>
                                            <span class="reserva-vet-lugar-datos">
                                                <span class="reserva-vet-lugar-nombre">{{ !empty($es_veterinaria_busqueda) ? 'Veterinario/a' : 'Profesional' }}</span>
                                                <span class="reserva-vet-lugar-direccion">Por nombre o RUT</span>
                                            </span>
                                            <span class="reserva-vet-lugar-marca" aria-hidden="true"><i class="feather icon-check"></i></span>
                                        </button>
                                    </li>
                                    <li class="nav-item reserva-vet-lugar">
                                        <button type="button" class="nav-link reserva-vet-lugar-caja" onclick="$('#select_tipo_agenda').val(3);$('#div_resultado_busqueda').html('')" id="buscar_videoconsulta-tab" data-toggle="tab" data-target="#buscar_videoconsulta" role="tab" aria-controls="buscar_videoconsulta" aria-selected="false">
                                            <span class="reserva-vet-lugar-icono"><i class="feather icon-video"></i></span>
                                            <span class="reserva-vet-lugar-datos">
                                                <span class="reserva-vet-lugar-nombre">{{ !empty($es_veterinaria_busqueda) ? 'Telemedicina veterinaria' : 'Videoconsulta' }}</span>
                                                <span class="reserva-vet-lugar-direccion">Atención a distancia</span>
                                            </span>
                                            <span class="reserva-vet-lugar-marca" aria-hidden="true"><i class="feather icon-check"></i></span>
                                        </button>
                                    </li>
                                </ul>

                                <div class="tab-content" id="BuscadoresContent">

                                    <!--Hora por especialidad-->
                                    <div class="tab-pane fade show active" id="buscar_especialidad" role="tabpanel" aria-labelledby="buscar_especialidad-tab">
                                        {{--  <form>  --}}
                                            <p class="small text-muted mb-4">{{ !empty($es_veterinaria_busqueda) ? 'Seleccione el área y la prestación veterinaria que necesita su mascota.' : 'Ingrese los datos solicitados para buscar horas por especialidad.' }}</p>
                                            <div class="form-row">
                                                <div class="col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label class="floating-label-activo-sm">{{ !empty($es_veterinaria_busqueda) ? 'Área' : 'Profesión' }}</label>
                                                        <select class="form-control form-control-sm" name="buscar_especialidad_profesion" id="buscar_especialidad_profesion" onchange="buscar_tipo_especialidad(this);">
                                                            <option value="">Seleccione</option>

                                                            @if(!empty($es_veterinaria_busqueda))
                                                                @foreach(($catalogo_veterinario_busqueda ?? []) as $area)
                                                                    <option value="{{ $area['slug'] }}">{{ $area['nombre'] }}</option>
                                                                @endforeach
                                                            @elseif(isset($profesiones))
                                                                @foreach($profesiones as $pro_key => $pro)
                                                                @php
                                                                    $selected_id1 = '';
                                                                    if((int)$filtros['id_profesion']==$pro->id && (int)$filtros['id_profesion']>0)
                                                                    $selected_id1 = 'selected';
                                                                @endphp
                                                                    <option value="{{ $pro->id }}" {{$selected_id1}}>{{ $pro->nombre }}</option>
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label class="floating-label-activo-sm" id="label_buscar_especialidad_especialidad">{{ !empty($es_veterinaria_busqueda) ? 'Procedimiento / Exámenes' : 'Especialidad' }}</label>
                                                        <select class="form-control form-control-sm" name="buscar_especialidad_especialidad" id="buscar_especialidad_especialidad">
                                                            <option value="">Seleccione</option>
                                                            @if(!empty($es_veterinaria_busqueda))
                                                            @elseif(isset($especialidades))
                                                                @foreach($especialidades as $esp_key => $esp)
                                                                @php
                                                                    $selected_id2 = '';
                                                                    if($filtros['id_especialidad']==$esp->id && $filtros['id_especialidad']!=0)
                                                                    $selected_id2 = 'selected';
                                                                @endphp
                                                                    <option value="{{ $esp->id }}" {{$selected_id2}}>{{ $esp->nombre }}</option>
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label class="floating-label-activo-sm">Región</label>
                                                        <select class="form-control form-control-sm" name="buscar_especialidad_region" id="buscar_especialidad_region" onchange="buscar_ciudad(this);">
                                                            <option value="">Seleccione</option>
                                                            @foreach($regiones as $reg_key => $reg)
                                                                <option value="{{ $reg->id }}">{{ $reg->nombre }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label class="floating-label-activo-sm">Comuna</label>
                                                        <select class="form-control form-control-sm" name="buscar_especialidad_comuna" id="buscar_especialidad_comuna">
                                                            <option value="">Seleccione</option>
                                                            @foreach($ciudades as $ciu_key => $ciu)
                                                                <option value="{{ $ciu->id }}">{{ $ciu->nombre }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>

                                                {{-- <div class="col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <div class="switch switch-success d-inline m-r-10">
                                                            <input type="checkbox" id="buscar_especialidad_hora24" value="1">
                                                            <label for="buscar_especialidad_hora24" class="cr"></label>
                                                        </div>
                                                        <label><strong>Buscar horas para las próx. 24 hrs</strong></label>
                                                    </div>
                                                </div> --}}
                                            </div>
                                            <div class="reserva-vet-acciones">
                                                <button class="btn btn-info" type="button" id="btn_buscar_especialidad" onclick="buscar_profesional_especialidad();"><i class="feather icon-search"></i> Buscar horas</button>
                                            </div>
                                        {{--  </form>  --}}
                                    </div>

                                    <!--Hora por profesional-->
                                    <div class="tab-pane fade" id="buscar_profesional" role="tabpanel" aria-labelledby="buscar_profesional-tab">
                                        <form onsubmit="event.preventDefault(); buscar_profesional_profesional();">
                                            <p class="small text-muted mb-4">{{ !empty($es_veterinaria_busqueda) ? 'Ingrese nombre o RUT del veterinario/a. Región y comuna son opcionales para acotar la búsqueda.' : 'Ingrese los datos solicitados para buscar horas por profesional.' }}</p>
                                            <div class="form-row">
                                                <div class="col-sm-12 col-md-12">
                                                    <div class="form-group">
                                                        <label class="floating-label-activo-sm">{{ !empty($es_veterinaria_busqueda) ? 'Nombre o RUT del veterinario/a' : 'Nombre o RUT del profesional' }}</label>
                                                        <input type="text" class="form-control form-control-sm" name="buscar_profesional_dato_profesional" id="buscar_profesional_dato_profesional">
                                                    </div>
                                                </div>
                                                <div class="col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label class="floating-label-activo-sm">Región</label>
                                                        <select class="form-control form-control-sm" name="buscar_profesional_region" id="buscar_profesional_region" onchange="buscar_ciudad(this);">
                                                            <option value="">Seleccione</option>
                                                            @foreach($regiones as $reg_key => $reg)
                                                                <option value="{{ $reg->id }}">{{ $reg->nombre }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label class="floating-label-activo-sm">Comuna</label>
                                                        <select class="form-control form-control-sm" name="buscar_profesional_comuna" id="buscar_profesional_comuna">
                                                            <option value="">Seleccione</option>
                                                            @foreach($ciudades as $ciu_key => $ciu)
                                                                <option value="{{ $ciu->id }}">{{ $ciu->nombre }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>

                                                {{-- <div class="col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <div class="switch switch-success d-inline m-r-10">
                                                            <input type="checkbox" id="buscar_profesional_hora24" value="1">
                                                            <label for="buscar_profesional_hora24" class="cr"></label>
                                                        </div>
                                                        <label><strong>Buscar horas para las próx. 24 hrs</strong></label>
                                                    </div>
                                                </div> --}}
                                            </div>
                                            <div class="reserva-vet-acciones">
                                                <button class="btn btn-info" type="button" onclick="buscar_profesional_profesional();"><i class="feather icon-search"></i> Buscar horas</button>
                                            </div>
                                        </form>
                                    </div>

                                    <!--Hora por videoconsulta-->
                                    <div class="tab-pane fade" id="buscar_videoconsulta" role="tabpanel" aria-labelledby="buscar_videoconsulta-tab">
                                        {{--  <form>  --}}
                                            <p class="small text-muted mb-4">{{ !empty($es_veterinaria_busqueda) ? 'Seleccione el área y la prestación para buscar horas de telemedicina veterinaria.' : 'Ingrese los datos solicitados para buscar horas por especialidad.' }}</p>
                                            <div class="form-row">
                                                <div class="col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label class="floating-label-activo-sm">{{ !empty($es_veterinaria_busqueda) ? 'Área' : 'Profesión' }}</label>
                                                        <select class="form-control form-control-sm" name="buscar_videoconsulta_profesion" id="buscar_videoconsulta_profesion" onchange="buscar_tipo_especialidad(this);">
                                                            <option value="">Seleccione</option>

                                                            @if(!empty($es_veterinaria_busqueda))
                                                                @foreach(($catalogo_veterinario_busqueda ?? []) as $area)
                                                                    <option value="{{ $area['slug'] }}">{{ $area['nombre'] }}</option>
                                                                @endforeach
                                                            @elseif(isset($profesiones))
                                                                @foreach($profesiones as $pro_key => $pro)
                                                                @php
                                                                    $selected_id1 = '';
                                                                    if((int)$filtros['id_profesion']==$pro->id && (int)$filtros['id_profesion']>0)
                                                                    $selected_id1 = 'selected';
                                                                @endphp
                                                                    <option value="{{ $pro->id }}" {{$selected_id1}}>{{ $pro->nombre }}</option>
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label class="floating-label-activo-sm" id="label_buscar_videoconsulta_especialidad">{{ !empty($es_veterinaria_busqueda) ? 'Prestación veterinaria' : 'Especialidad' }}</label>
                                                        <select class="form-control form-control-sm" name="buscar_videoconsulta_especialidad" id="buscar_videoconsulta_especialidad">
                                                            <option value="">Seleccione</option>
                                                            @if(!empty($es_veterinaria_busqueda))
                                                            @elseif(isset($especialidades))
                                                                @foreach($especialidades as $esp_key => $esp)
                                                                @php
                                                                    $selected_id2 = '';
                                                                    if($filtros['id_especialidad']==$esp->id && $filtros['id_especialidad']!=0)
                                                                    $selected_id2 = 'selected';
                                                                @endphp
                                                                    <option value="{{ $esp->id }}" {{$selected_id2}}>{{ $esp->nombre }}</option>
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                    </div>
                                                </div>
                                                {{-- <div class="col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label class="floating-label-activo-sm">Previsión</label>
                                                        <select class="form-control form-control-sm" name="prevision" id="buscar_videoconsulta_prevision">
                                                            <option value="">Seleccione</option>
                                                        </select>
                                                    </div>
                                                </div> --}}

                                                {{-- <div class="col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <div class="switch switch-success d-inline m-r-10">
                                                            <input type="checkbox" id="buscar_videoconsulta_hora24" value="1">
                                                            <label for="buscar_videoconsulta_hora24" class="cr"></label>
                                                        </div>
                                                        <label><strong>Buscar horas para las próx. 24 hrs</strong></label>
                                                    </div>
                                                </div> --}}
                                            </div>
                                            <div class="reserva-vet-acciones">
                                                <button class="btn btn-info" type="button" id="btn_buscar_especialidad_video_consulta" onclick="buscar_profesional_especialidad_video_consulta();"><i class="feather icon-search"></i> Buscar horas</button>
                                            </div>
                                        {{--  </form>  --}}
                                    </div>
                                </div>
                            </div>

                            <!--Paso 2: resultados de la búsqueda-->
                            <div class="reserva-vet-panel" data-paso="2">
                                <div class="reserva-vet-panel-encabezado">
                                    <h6>{{ !empty($es_veterinaria_busqueda) ? 'Elija un veterinario' : 'Elija un profesional' }}</h6>
                                    <p class="small text-muted mb-0">Cada tarjeta muestra la próxima hora disponible. Puede revisar la ficha antes de reservar.</p>
                                </div>

                                <!--Las tarjetas se llenan al buscar-->
                                <div class="reserva-vet-veterinarios" id="div_resultado_busqueda"></div>

                                <div class="reserva-vet-acciones">
                                    <button type="button" class="btn btn-outline-secondary reserva-vet-volver" onclick="irPasoReservaHora(1);">
                                        <i class="feather icon-chevron-left"></i> Cambiar búsqueda
                                    </button>
                                </div>
                            </div>

                            <!--Pasos 3 y 4: fecha y hora, y confirmación-->
                            @include("app.general.buscador_profesionales.modals.reservar_hora")
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--Cierre: Container Completo-->

    <!--Modals buscador -->
    @include("app.general.buscador_profesionales.modals.ficha_profesional")

@endsection


@section('page-script')
    <script>
     window.esVeterinariaBusqueda = @json(!empty($es_veterinaria_busqueda));
     window.catalogoVeterinarioBusqueda = @json($catalogo_veterinario_busqueda ?? []);
     if (window.esVeterinariaBusqueda) {
        $('#label_buscar_especialidad_especialidad, #label_buscar_videoconsulta_especialidad').text('Motivo de consulta');
     }
     window.idMascotaReserva = @json(
        !empty($mascota)
            ? (string) $mascota->id
            : (((isset($mascotas) ? $mascotas->count() : 0) === 1) ? (string) $mascotas->first()->id : '')
     );

     // Reserva por pasos: 1 búsqueda, 2 veterinario, 3 fecha y hora, 4 confirmar.
     // Acá solo se decide qué paso se ve; buscar, cargar horas y agendar siguen en la plantilla.
     const TOTAL_PASOS_RESERVA_HORA = 4;

     const reservaHoraPasos = {
        actual: 1,
        alcanzado: 1,
     };

     // Del paso 2 en adelante hacen falta resultados en pantalla
     function pasoDisponibleReservaHora(paso) {
        if (paso > reservaHoraPasos.alcanzado) {
            return false;
        }

        return paso === 1 || $('#div_resultado_busqueda').children().length > 0;
     }

     function irPasoReservaHora(paso) {
        if (paso < 1 || paso > TOTAL_PASOS_RESERVA_HORA || !pasoDisponibleReservaHora(paso)) {
            return;
        }

        // A confirmar solo se entra eligiendo una hora, no desde el indicador
        if (paso < TOTAL_PASOS_RESERVA_HORA) {
            reservaHoraPasos.alcanzado = Math.min(reservaHoraPasos.alcanzado, TOTAL_PASOS_RESERVA_HORA - 1);
        }

        reservaHoraPasos.actual = paso;
        $('#vista_reserva_veterinaria .reserva-vet-panel').removeClass('activo')
            .filter('[data-paso="' + paso + '"]').addClass('activo');
        actualizarPasosReservaHora();

        // Si la tarjeta quedó a mitad de pantalla (pasa en celular) se vuelve a su comienzo
        const tarjeta = document.getElementById('vista_reserva_veterinaria');
        if (tarjeta && tarjeta.scrollIntoView && tarjeta.getBoundingClientRect().top < 0) {
            const sinAnimacion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            tarjeta.scrollIntoView({ behavior: sinAnimacion ? 'auto' : 'smooth', block: 'start' });
        }
     }

     // Marca el indicador según el paso actual y hasta dónde se puede volver
     function actualizarPasosReservaHora() {
        if (!pasoDisponibleReservaHora(reservaHoraPasos.actual)) {
            reservaHoraPasos.actual = 1;
            $('#vista_reserva_veterinaria .reserva-vet-panel').removeClass('activo')
                .filter('[data-paso="1"]').addClass('activo');
        }

        $('#reserva_hora_pasos .reserva-vet-paso').each(function() {
            const paso = Number($(this).data('paso'));
            const disponible = pasoDisponibleReservaHora(paso);
            const completado = disponible && paso !== reservaHoraPasos.actual && paso < reservaHoraPasos.alcanzado;

            $(this).toggleClass('activo', paso === reservaHoraPasos.actual).toggleClass('completado', completado);
            $(this).find('.reserva-vet-paso-numero').html(completado ? '<i class="feather icon-check"></i>' : paso);
            $(this).find('.reserva-vet-paso-boton')
                .prop('disabled', !disponible)
                .attr('aria-current', paso === reservaHoraPasos.actual ? 'step' : null);
        });
     }

     // Llegó una búsqueda: se parte de nuevo desde los resultados
     function mostrarResultadosReservaHora() {
        reservaHoraPasos.alcanzado = 2;
        $('#reserva_hora_veterinario').text('');
        irPasoReservaHora(2);
     }

     // Se eligió un veterinario: su nombre queda arriba y se pasa al calendario
     function abrirFechaHoraReservaHora(idProfesional) {
        const nombre = $('#div_resultado_busqueda .reserva-vet-veterinario[data-profesional="' + idProfesional + '"]')
            .first().find('.reserva-vet-lugar-nombre').text().trim();

        $('#reserva_hora_veterinario').text(nombre ? 'Con ' + nombre : '');
        $('#reserva_resumen_veterinario').text(nombre || '-');

        // Mientras cargan sus lugares no se muestra lo del veterinario anterior
        $('#modal_reserva_fecha').prop('disabled', true);
        $('#modal_reserva_dias_atencion, #modal_reserva_fecha_seleccionada').text('');

        reservaHoraPasos.alcanzado = 3;
        irPasoReservaHora(3);
     }

     // "2026-10-12" queda como "Lunes 12 de octubre de 2026"
     function textoFechaReservaHora(fecha) {
        const dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
        const meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
        const dia = moment(fecha, 'YYYY-MM-DD');

        if (!dia.isValid()) {
            return fecha || '-';
        }

        return dias[dia.day()] + ' ' + dia.date() + ' de ' + meses[dia.month()] + ' de ' + dia.year();
     }

     // Ya hay hora elegida y datos del tutor: se arma el resumen y se pasa a confirmar
     function abrirConfirmacionReservaHora(fecha, hora) {
        $('#modal_reserva_hora_lista_horas .reserva-vet-hora').each(function() {
            const elegida = $(this).attr('data-hora') === hora;
            $(this).toggleClass('active', elegida).attr('aria-pressed', elegida ? 'true' : 'false');
        });

        $('#reserva_resumen_fecha').text(textoFechaReservaHora(fecha));
        $('#reserva_resumen_hora').text(moment(hora, 'HH:mm:ss').format('HH:mm') + ' hrs.');
        $('#reserva_resumen_lugar').text($('#modal_reserva_hora_lugar_atencion option:selected').text() || '-');

        reservaHoraPasos.alcanzado = TOTAL_PASOS_RESERVA_HORA;
        irPasoReservaHora(TOTAL_PASOS_RESERVA_HORA);
     }

     // La hora quedó agendada: se vuelve a la búsqueda. Para reservar otra hay que
     // elegir de nuevo al veterinario, así las horas se cargan actualizadas.
     function cerrarReservaHora() {
        $('#reserva_hora_veterinario').text('');
        reservaHoraPasos.alcanzado = 2;
        irPasoReservaHora(1);
     }

     // Al cambiar la forma de búsqueda se borran los resultados y el indicador debe reflejarlo
     $('#Buscadores').on('click', '.nav-link', actualizarPasosReservaHora);

     @if( $filtros['id_profesion'] !=0 && $filtros['id_especialidad'] !=0 )
        buscar_profesional_especialidad();
     @endif
    </script>
@endsection

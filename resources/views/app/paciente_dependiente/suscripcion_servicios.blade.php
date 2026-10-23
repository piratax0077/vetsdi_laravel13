@extends('template.usuario.template')

@section('page-styles')
    <link rel="stylesheet" href="{{ asset('css/suscripcion_servicios.css') }}?t={{ time() }}">
@endsection

@section('content')
    {{-- Pantalla de carga al entrar a la sección --}}
    <div class="servicios-cargando" id="serviciosCargando" role="status" aria-live="polite">
        <div class="servicios-cargando-contenido">
            <div class="servicios-cargando-pin">
                <span class="servicios-cargando-onda"></span>
                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                    <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5a2.5 2.5 0 1 1 0-5 2.5 2.5 0 0 1 0 5z"/>
                </svg>
                <span class="servicios-cargando-sombra"></span>
            </div>
            <p>Buscando servicios cercanos</p>
            <span class="servicios-cargando-puntos"><i></i><i></i><i></i></span>
        </div>
    </div>

    <div class="pcoded-main-container">
        <div class="pcoded-content">
            <div class="page-header">
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item">
                                    <a href="{{ ROUTE('paciente.home') }}" data-toggle="tooltip" data-placement="top" title="Volver a mi escritorio">
                                        <i class="feather icon-home"></i>
                                    </a>
                                </li>
                                <li class="breadcrumb-item"><a href="#">Suscripciones y servicios cercanos</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="servicios-cercanos">
            {{-- Buscador principal --}}
            <div class="row mt-n5 mx-1">
                <div class="col-sm-12 mt-n5">
                    <section class="servicios-tarjeta servicios-buscador">
                        <div class="servicios-cabecera">
                            <span class="servicios-icono"><i class="fas fa-map-marked-alt"></i></span>
                            <div class="servicios-cabecera-texto">
                                <h4>Encuentra servicios para tu mascota</h4>
                                <p>Centros veterinarios, farmacias, pet shops, peluquerías y hoteles adheridos cerca de ti.</p>
                            </div>
                        </div>

                        <div class="form-row servicios-filtros">
                            <div class="form-group col-md-4">
                                <label class="floating-label-activo-sm">Mascota</label>
                                <select class="custom-select" id="selectedPet">
                                    <option value="">Seleccione mascota</option>
                                    @foreach ($mascotas as $mascota)
                                        <option value="{{ $mascota->id }}"
                                            data-foto="{{ $mascota->foto_url ?? asset('images/iconos/mascotas.svg') }}"
                                            {{ optional($mascotaActiva)->id === $mascota->id ? 'selected' : '' }}>
                                            {{ $mascota->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-4">
                                <label class="floating-label-activo-sm">¿Qué buscas?</label>
                                <select class="custom-select" id="searchCategory">
                                    <option value="">Seleccione servicio</option>
                                    <option value="centro_veterinario">Clínica veterinaria</option>
                                    <option value="peluqueria">Peluquería</option>
                                    <option value="alimentos" {{ request('servicio') === 'alimentos' ? 'selected' : '' }}>Alimentos</option>
                                    <option value="farmacia" {{ request('servicio') === 'farmacia' ? 'selected' : '' }}>Farmacia veterinaria</option>
                                    <option value="pet_shop">Pet shop</option>
                                    <option value="hotel_mascotas">Hotel de mascotas</option>
                                </select>
                            </div>
                            <div class="form-group col-md-4">
                                <label class="floating-label-activo-sm">Ubicación manual</label>
                                <select class="custom-select" id="manualLocation">
                                    <option value="">Seleccione ciudad o comuna</option>
                                    <option value="vina_del_mar">Viña del Mar</option>
                                    <option value="valparaiso">Valparaíso</option>
                                    <option value="quilpue">Quilpué</option>
                                    <option value="villa_alemana">Villa Alemana</option>
                                    <option value="los_andes">Los Andes</option>
                                    <option value="santiago_centro">Santiago Centro</option>
                                    <option value="las_condes">Las Condes</option>
                                </select>
                            </div>
                        </div>

                        <div class="servicios-buscador-pie">
                            <div class="servicios-estado-ubicacion">
                                <i class="fas fa-map-marker-alt"></i>
                                <span id="locationStatus">Seleccione una ubicación manual o use la geolocalización automática para buscar servicios cercanos.</span>
                            </div>
                            <div class="servicios-acciones">
                                <button type="button" class="btn btn-outline-info servicios-btn-secundario" id="btnUseGeolocation">
                                    <i class="fas fa-location-arrow"></i> Mi ubicación
                                </button>
                                <button type="button" class="btn btn-info servicios-btn-principal" id="btnBuscarServicio">
                                    <i class="feather icon-search"></i> Buscar
                                </button>
                            </div>
                        </div>
                    </section>
                </div>
            </div>

            {{-- Resultados: aparecen recién al presionar Buscar --}}
            <div class="row mx-1 d-none" id="serviciosResultados">
                <div class="col-12">
                    <section class="servicios-tarjeta servicios-aparece">
                        <div class="servicios-cabecera">
                            <span class="servicios-icono"><i class="fas fa-paw"></i></span>
                            <div class="servicios-cabecera-texto">
                                <h4>Lugares adheridos a nuestra comunidad</h4>
                                <p id="searchSummary">Buscando lugares cercanos...</p>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-7 mb-4 mb-lg-0">
                                <div class="servicios-mapa">
                                    <div id="map"></div>
                                </div>
                            </div>
                            <div class="col-lg-5">
                                <aside class="servicios-ficha" id="fichaLugar" aria-live="polite">
                                    <div class="servicios-ficha-vacia" id="fichaVacia">
                                        <div class="servicios-vacio">
                                            <i class="fas fa-hand-pointer"></i>
                                            <p>Elige un lugar</p>
                                            <small>Presiona un marcador en el mapa o una tarjeta de la lista para ver su detalle.</small>
                                        </div>
                                    </div>

                                    <div class="servicios-ficha-contenido d-none" id="fichaContenido">
                                        <div class="servicios-ficha-cabecera">
                                            <div class="servicios-ficha-logo" id="fichaLogo">
                                                <img id="fichaLogoImagen" src="{{ asset('images/iconos/servicio_adherido.svg') }}" alt="Logotipo del lugar">
                                            </div>
                                            <div class="servicios-ficha-titulo">
                                                <span class="servicios-etiqueta" id="fichaTipo">-</span>
                                                <h5 id="fichaInstitucion">-</h5>
                                                <p class="servicios-ficha-sucursal" id="fichaSucursal">-</p>
                                            </div>
                                        </div>

                                        <div class="servicios-ficha-cuerpo">
                                            <div class="servicios-estados" id="fichaEstados"></div>

                                            <div class="servicios-bloque">
                                                <p class="servicios-direccion">
                                                    <i class="feather icon-map-pin"></i>
                                                    <span id="fichaDireccion">-</span>
                                                </p>
                                                <div class="reserva-vet-lugar-contacto mt-3 mb-0" id="fichaContactos"></div>
                                            </div>

                                            <div id="fichaUrgencia"></div>

                                            <div class="servicios-bloque">
                                                <span class="servicios-etiqueta"><i class="feather icon-clock"></i> Horario</span>
                                                <div class="servicios-horario" id="fichaHorario"></div>
                                            </div>

                                            <div class="servicios-bloque mb-0">
                                                <span class="servicios-etiqueta"><i class="feather icon-grid"></i> También encuentras aquí</span>
                                                <div class="servicios-chips" id="fichaServicios"></div>
                                            </div>

                                            {{-- Otras sucursales en el país: oculto por ahora, se sigue llenando desde dibujarRed() --}}
                                            <div class="servicios-bloque mb-0 d-none" id="fichaRed"></div>
                                        </div>

                                        <div class="servicios-ficha-pie">
                                            <button type="button" class="btn btn-info btn-block servicios-btn-principal" id="btnAccionLugar">
                                                <i class="feather icon-calendar"></i> <span>Agendar hora</span>
                                            </button>
                                        </div>
                                    </div>
                                </aside>
                            </div>
                        </div>

                        <div class="servicios-resultados">
                            <div class="servicios-subtitulo">
                                <i class="feather icon-list"></i> <span id="tituloLista">Resultados de la búsqueda</span>
                            </div>
                            <ul class="reserva-vet-lugares servicios-lista mb-0" id="listaLugares"></ul>
                        </div>
                    </section>
                </div>
            </div>

            {{-- Lo que se puede hacer en el lugar elegido, según el servicio buscado --}}
            <div class="row mx-1 d-none" id="servicioAccion">
                <div class="col-12">
                    <section class="servicios-tarjeta servicios-agenda servicios-aparece" id="tarjetaAccion">
                        <div class="servicios-cabecera">
                            <span class="servicios-icono servicios-icono-reserva" id="accionIcono"><i class="feather icon-calendar"></i></span>
                            <div class="servicios-cabecera-texto">
                                <h4 id="accionTitulo">-</h4>
                                <p id="accionSubtitulo">-</p>
                            </div>
                        </div>

                        <div class="reserva-vet-profesional">
                            <img class="reserva-vet-profesional-icono" id="accionMascotaFoto" src="{{ asset('images/iconos/mascotas.svg') }}" alt="Foto de la mascota">
                            <div class="reserva-vet-profesional-datos">
                                <span class="reserva-vet-profesional-etiqueta" id="accionMascotaEtiqueta">Reservando para</span>
                                <span class="reserva-vet-profesional-nombre" id="accionMascotaNombre">Su mascota</span>
                            </div>
                            <span class="reserva-vet-profesional-especialidad" id="accionLugarNombre"></span>
                        </div>

                        {{-- Agenda: clínica, peluquería y hotel --}}
                        <div class="d-none" id="flujoAgenda">
                            <ol class="reserva-vet-pasos" id="agendaPasos"></ol>

                            {{-- Clínica: motivo de la consulta --}}
                            <div class="reserva-vet-panel" data-panel="motivo">
                                <div class="reserva-vet-panel-encabezado">
                                    <h6>¿Qué necesita su mascota?</h6>
                                    <p class="small text-muted mb-0">Elija el área y la prestación. Para un control use "Consulta veterinaria".</p>
                                </div>
                                <div class="form-row">
                                    <div class="col-sm-12 col-md-6">
                                        <div class="form-group">
                                            <label class="floating-label-activo-sm" for="vetArea">Área</label>
                                            <select class="form-control form-control-sm" id="vetArea">
                                                <option value="">Seleccione</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-12 col-md-6">
                                        <div class="form-group">
                                            <label class="floating-label-activo-sm" for="vetPrestacion" id="vetPrestacionEtiqueta">Prestación</label>
                                            <select class="form-control form-control-sm" id="vetPrestacion">
                                                <option value="">Seleccione</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="reserva-vet-acciones">
                                    <button type="button" class="btn btn-info" data-agenda="siguiente">Siguiente <i class="feather icon-chevron-right"></i></button>
                                </div>
                            </div>

                            {{-- Clínica: veterinario --}}
                            <div class="reserva-vet-panel" data-panel="veterinario">
                                <div class="reserva-vet-panel-encabezado">
                                    <h6>Elija un veterinario</h6>
                                    <p class="small text-muted mb-0">Cada tarjeta muestra la próxima hora disponible en esta sucursal.</p>
                                </div>
                                <ul class="reserva-vet-lugares mb-0" id="vetProfesionales"></ul>
                                <div class="reserva-vet-acciones">
                                    <button type="button" class="btn btn-outline-secondary reserva-vet-volver" data-agenda="anterior"><i class="feather icon-chevron-left"></i> Anterior</button>
                                    <button type="button" class="btn btn-info" data-agenda="siguiente">Siguiente <i class="feather icon-chevron-right"></i></button>
                                </div>
                            </div>

                            {{-- Peluquería: servicio y tamaño --}}
                            <div class="reserva-vet-panel" data-panel="servicio">
                                <div class="reserva-vet-panel-encabezado">
                                    <h6>¿Qué servicio quiere?</h6>
                                    <p class="small text-muted mb-0">El valor se ajusta según el tamaño de su mascota.</p>
                                </div>
                                <div class="form-row">
                                    <div class="col-sm-12 col-md-6">
                                        <div class="form-group">
                                            <label class="floating-label-activo-sm" for="peluqueriaTamano">Tamaño de la mascota</label>
                                            <select class="form-control form-control-sm" id="peluqueriaTamano">
                                                <option value="1">Pequeño (hasta 10 kg)</option>
                                                <option value="1.25">Mediano (10 a 25 kg)</option>
                                                <option value="1.5">Grande (más de 25 kg)</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <ul class="reserva-vet-lugares servicios-opciones-imagen mb-0" id="peluqueriaServicios"></ul>
                                <div class="servicios-interruptor">
                                    <div class="switch switch-success d-inline m-r-10">
                                        <input type="checkbox" id="peluqueriaTraslado">
                                        <label for="peluqueriaTraslado" class="cr"></label>
                                    </div>
                                    <label for="peluqueriaTraslado">Retiro y entrega a domicilio de la mascota <span>(+$4.990)</span></label>
                                </div>
                                <div class="reserva-vet-acciones">
                                    <button type="button" class="btn btn-info" data-agenda="siguiente">Siguiente <i class="feather icon-chevron-right"></i></button>
                                </div>
                            </div>

                            {{-- Peluquería: quién atiende. Solo si el lugar lo habilita y tiene más de una persona --}}
                            <div class="reserva-vet-panel" data-panel="peluquero">
                                <div class="reserva-vet-panel-encabezado">
                                    <h6>¿Quién quiere que atienda a su mascota?</h6>
                                    <p class="small text-muted mb-0" id="peluqueroSubtitulo">Si tiene preferencia por alguien del equipo, elíjalo. Si no, tome la primera hora disponible.</p>
                                </div>
                                <ul class="reserva-vet-lugares mb-0" id="peluqueriaEquipo"></ul>
                                <div class="reserva-vet-acciones">
                                    <button type="button" class="btn btn-outline-secondary reserva-vet-volver" data-agenda="anterior"><i class="feather icon-chevron-left"></i> Anterior</button>
                                    <button type="button" class="btn btn-info" data-agenda="siguiente">Siguiente <i class="feather icon-chevron-right"></i></button>
                                </div>
                            </div>

                            {{-- Hotel: fechas y alojamiento --}}
                            <div class="reserva-vet-panel" data-panel="estadia">
                                <div class="reserva-vet-panel-encabezado">
                                    <h6>¿Cuándo se queda su mascota?</h6>
                                    <p class="small text-muted mb-0">Indique el ingreso, la salida y el tipo de alojamiento.</p>
                                </div>
                                <div class="form-row">
                                    <div class="col-sm-12 col-md-6">
                                        <div class="form-group">
                                            <label class="floating-label-activo-sm" for="hotelIngreso">Fecha de ingreso</label>
                                            <input type="date" class="form-control form-control-sm" id="hotelIngreso" min="{{ date('Y-m-d') }}">
                                        </div>
                                    </div>
                                    <div class="col-sm-12 col-md-6">
                                        <div class="form-group">
                                            <label class="floating-label-activo-sm" for="hotelSalida">Fecha de salida</label>
                                            <input type="date" class="form-control form-control-sm" id="hotelSalida" min="{{ date('Y-m-d') }}">
                                        </div>
                                    </div>
                                </div>
                                <ul class="reserva-vet-lugares servicios-opciones-imagen mb-0" id="hotelAlojamientos"></ul>
                                <div class="reserva-vet-acciones">
                                    <button type="button" class="btn btn-info" data-agenda="siguiente">Siguiente <i class="feather icon-chevron-right"></i></button>
                                </div>
                            </div>

                            {{-- Clínica y peluquería: día y hora --}}
                            <div class="reserva-vet-panel" data-panel="fecha">
                                <div class="reserva-vet-panel-encabezado">
                                    <h6>¿Qué día y a qué hora?</h6>
                                    <p class="small text-muted mb-0">Los días marcados tienen atención en los próximos 60 días. Al elegir una hora pasará al pago.</p>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6 mb-3 mb-lg-0">
                                        {{-- flatpickr se dibuja en línea; el input queda oculto --}}
                                        <div class="reserva-vet-calendario">
                                            <input type="text" class="d-none" id="agendaCalendario" value="" tabindex="-1" aria-hidden="true">
                                        </div>
                                        <div class="reserva-vet-leyenda" aria-hidden="true">
                                            <span><i class="reserva-vet-leyenda-disponible"></i>Disponible</span>
                                            <span><i class="reserva-vet-leyenda-elegido"></i>Elegido</span>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="reserva-vet-horas-caja" id="agendaHorasCaja">
                                            <div class="reserva-vet-horas-cabecera">
                                                <div class="reserva-vet-horas-titulo">Horas disponibles</div>
                                                <span class="reserva-vet-horas-total d-none" id="agendaTotalHoras"></span>
                                            </div>
                                            <div class="reserva-vet-horas-fecha" id="agendaFechaTexto">Primero elija una fecha.</div>
                                            <div class="reserva-vet-horas" id="agendaHoras" aria-live="polite"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="reserva-vet-acciones">
                                    <button type="button" class="btn btn-outline-secondary reserva-vet-volver" data-agenda="anterior"><i class="feather icon-chevron-left"></i> Anterior</button>
                                </div>
                            </div>

                            {{-- Pago: en línea con Webpay, o en el lugar solo si el administrador lo permite --}}
                            <div class="reserva-vet-panel" data-panel="pago">
                                <div class="reserva-vet-panel-encabezado">
                                    <h6>¿Cómo quiere pagar?</h6>
                                    <p class="small text-muted mb-0">Elija el medio de pago. En el siguiente paso podrá revisar todo antes de confirmar.</p>
                                </div>
                                <div class="row">
                                    <div class="col-lg-7 mb-3 mb-lg-0">
                                        <ul class="reserva-vet-lugares servicios-medios-pago mb-0">
                                            <li class="reserva-vet-lugar">
                                                <input class="reserva-vet-lugar-radio" type="radio" name="medioPago" id="pagoWebpay" value="webpay">
                                                <label class="reserva-vet-lugar-caja mb-0" for="pagoWebpay">
                                                    <span class="reserva-vet-lugar-icono"><i class="feather icon-credit-card"></i></span>
                                                    <span class="reserva-vet-lugar-datos">
                                                        <span class="reserva-vet-lugar-nombre">Pagar en línea con Webpay</span>
                                                        <span class="reserva-vet-lugar-direccion">Será redirigido a la página segura de Transbank</span>
                                                        <span class="servicios-tarjetas">
                                                            <span>Crédito</span>
                                                            <span>Débito</span>
                                                            <span>Prepago</span>
                                                        </span>
                                                    </span>
                                                    <span class="reserva-vet-lugar-marca" aria-hidden="true"><i class="feather icon-check"></i></span>
                                                </label>
                                            </li>
                                            <li class="reserva-vet-lugar" id="opcionPagoLugar">
                                                <input class="reserva-vet-lugar-radio" type="radio" name="medioPago" id="pagoLugar" value="lugar">
                                                <label class="reserva-vet-lugar-caja mb-0" for="pagoLugar">
                                                    <span class="reserva-vet-lugar-icono"><i class="fas fa-cash-register"></i></span>
                                                    <span class="reserva-vet-lugar-datos">
                                                        <span class="reserva-vet-lugar-nombre">Pagar en el lugar</span>
                                                        <span class="reserva-vet-lugar-direccion">El día de la atención, en efectivo o con tarjeta</span>
                                                    </span>
                                                    <span class="reserva-vet-lugar-marca" aria-hidden="true"><i class="feather icon-check"></i></span>
                                                </label>
                                            </li>
                                        </ul>
                                        <p class="servicios-nota d-none" id="avisoSoloEnLinea">
                                            <i class="feather icon-info"></i>
                                            <span>Este lugar recibe el pago solo en línea para asegurar su hora.</span>
                                        </p>
                                    </div>
                                    <div class="col-lg-5">
                                        <div class="servicios-pago">
                                            <span class="reserva-vet-etiqueta">Total a pagar</span>
                                            <div class="servicios-pago-total" id="pagoTotal">-</div>
                                            <small class="text-muted d-block" id="pagoDetalle">-</small>
                                            <div class="servicios-pago-seguro">
                                                <i class="feather icon-lock"></i>
                                                <span>Pago protegido por Transbank. No guardamos los datos de su tarjeta.</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="reserva-vet-acciones">
                                    <button type="button" class="btn btn-outline-secondary reserva-vet-volver" data-agenda="anterior"><i class="feather icon-chevron-left"></i> Anterior</button>
                                    <button type="button" class="btn btn-info" data-agenda="siguiente">Siguiente <i class="feather icon-chevron-right"></i></button>
                                </div>
                            </div>

                            {{-- Resumen para confirmar --}}
                            <div class="reserva-vet-panel" data-panel="confirmar">
                                <div class="reserva-vet-panel-encabezado">
                                    <h6>Revise su reserva</h6>
                                    <p class="small text-muted mb-0">Si todo está correcto, confirme para agendar.</p>
                                </div>
                                <div class="row">
                                    <div class="col-lg-7 mb-3 mb-lg-0">
                                        <div class="reserva-vet-resumen">
                                            <div class="reserva-vet-resumen-cita">
                                                <span class="reserva-vet-resumen-cita-icono"><i class="feather icon-calendar"></i></span>
                                                <div class="reserva-vet-resumen-cita-datos">
                                                    <span class="reserva-vet-resumen-fecha" id="resumenFecha">-</span>
                                                    <span class="reserva-vet-resumen-hora">
                                                        <i class="feather icon-clock"></i>
                                                        <span id="resumenHora">-</span>
                                                    </span>
                                                </div>
                                            </div>
                                            <ul class="reserva-vet-detalle" id="resumenDetalle"></ul>
                                        </div>
                                    </div>
                                    <div class="col-lg-5">
                                        <div class="servicios-pago">
                                            <span class="reserva-vet-etiqueta">Total</span>
                                            <div class="servicios-pago-total" id="resumenTotal">-</div>
                                            <small class="text-muted d-block" id="resumenNota">-</small>
                                            <div class="form-group mt-4 mb-0">
                                                <label class="floating-label-activo-sm" for="resumenComentario">Comentario para el lugar (opcional)</label>
                                                <textarea class="form-control form-control-sm" id="resumenComentario" rows="3" placeholder="Ej. se pone nervioso con otros perros"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="reserva-vet-acciones">
                                    <button type="button" class="btn btn-outline-secondary reserva-vet-volver" data-agenda="anterior"><i class="feather icon-chevron-left"></i> Anterior</button>
                                    <button type="button" class="btn btn-info" id="btnConfirmarAgenda"><i class="feather icon-check-circle"></i> Confirmar reserva</button>
                                </div>
                            </div>

                            {{-- Reserva lista --}}
                            <div class="reserva-vet-panel" data-panel="listo">
                                <div class="servicios-exito">
                                    <span class="servicios-exito-icono"><i class="feather icon-check"></i></span>
                                    <h5 id="exitoTitulo">¡Reserva agendada!</h5>
                                    <p id="exitoTexto">-</p>
                                    <button type="button" class="btn btn-outline-info" id="btnAgendarOtra"><i class="feather icon-plus"></i> Agendar otra</button>
                                </div>
                            </div>
                        </div>

                        {{-- Tienda: alimentos, farmacia y pet shop --}}
                        <div class="d-none" id="flujoTienda">
                            <div class="row" id="tiendaCompra">
                                <div class="col-lg-7 mb-4 mb-lg-0">
                                    <span class="reserva-vet-etiqueta" id="tiendaEtiquetaBuscar">¿Qué alimento necesita?</span>
                                    <div class="servicios-buscar-producto">
                                        <i class="feather icon-search"></i>
                                        <input type="text" class="form-control" id="tiendaBuscar" autocomplete="off"
                                            placeholder="Escriba el nombre o la marca" role="combobox" aria-autocomplete="list"
                                            aria-expanded="false" aria-controls="tiendaSugerencias">
                                        <ul class="servicios-sugerencias d-none" id="tiendaSugerencias" role="listbox"></ul>
                                    </div>
                                    <div class="servicios-atajos" id="tiendaAtajos"></div>
                                    <div id="tiendaProducto"></div>
                                </div>

                                <div class="col-lg-5">
                                    <div class="servicios-pedido">
                                        <div class="reserva-vet-horas-cabecera">
                                            <div class="reserva-vet-horas-titulo">Su pedido</div>
                                            <span class="reserva-vet-horas-total" id="tiendaCantidad">0 productos</span>
                                        </div>
                                        <ul class="servicios-pedido-lista" id="tiendaItems"></ul>

                                        <span class="reserva-vet-etiqueta mt-3">¿Cómo lo quiere recibir?</span>
                                        <ul class="reserva-vet-lugares servicios-entregas mb-0">
                                            <li class="reserva-vet-lugar">
                                                <input class="reserva-vet-lugar-radio" type="radio" name="tiendaEntrega" id="entregaAhora" value="ahora" checked>
                                                <label class="reserva-vet-lugar-caja mb-0" for="entregaAhora">
                                                    <span class="reserva-vet-lugar-icono"><i class="fas fa-shipping-fast"></i></span>
                                                    <span class="reserva-vet-lugar-datos">
                                                        <span class="reserva-vet-lugar-nombre">Despacho ahora</span>
                                                        <span class="reserva-vet-lugar-direccion" id="entregaAhoraTexto">Seguimiento en tiempo real</span>
                                                    </span>
                                                    <span class="reserva-vet-lugar-marca" aria-hidden="true"><i class="feather icon-check"></i></span>
                                                </label>
                                            </li>
                                            <li class="reserva-vet-lugar">
                                                <input class="reserva-vet-lugar-radio" type="radio" name="tiendaEntrega" id="entregaProgramada" value="programada">
                                                <label class="reserva-vet-lugar-caja mb-0" for="entregaProgramada">
                                                    <span class="reserva-vet-lugar-icono"><i class="feather icon-calendar"></i></span>
                                                    <span class="reserva-vet-lugar-datos">
                                                        <span class="reserva-vet-lugar-nombre">Programar despacho</span>
                                                        <span class="reserva-vet-lugar-direccion">Usted elige el día y la franja horaria</span>
                                                    </span>
                                                    <span class="reserva-vet-lugar-marca" aria-hidden="true"><i class="feather icon-check"></i></span>
                                                </label>
                                            </li>
                                            <li class="reserva-vet-lugar">
                                                <input class="reserva-vet-lugar-radio" type="radio" name="tiendaEntrega" id="entregaRetiro" value="retiro">
                                                <label class="reserva-vet-lugar-caja mb-0" for="entregaRetiro">
                                                    <span class="reserva-vet-lugar-icono"><i class="feather icon-package"></i></span>
                                                    <span class="reserva-vet-lugar-datos">
                                                        <span class="reserva-vet-lugar-nombre">Retiro en tienda</span>
                                                        <span class="reserva-vet-lugar-direccion">Listo en 20 minutos, sin costo de despacho</span>
                                                    </span>
                                                    <span class="reserva-vet-lugar-marca" aria-hidden="true"><i class="feather icon-check"></i></span>
                                                </label>
                                            </li>
                                        </ul>

                                        <div class="form-row mt-4 d-none" id="tiendaProgramar">
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label class="floating-label-activo-sm" for="tiendaFecha">Día de despacho</label>
                                                    <input type="date" class="form-control form-control-sm" id="tiendaFecha" min="{{ date('Y-m-d') }}">
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label class="floating-label-activo-sm" for="tiendaFranja">Franja horaria</label>
                                                    <select class="form-control form-control-sm" id="tiendaFranja">
                                                        <option value="">Seleccione</option>
                                                        <option value="10:00 y 13:00">10:00 a 13:00</option>
                                                        <option value="13:00 y 16:00">13:00 a 16:00</option>
                                                        <option value="16:00 y 19:00">16:00 a 19:00</option>
                                                        <option value="19:00 y 21:00">19:00 a 21:00</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group mt-4" id="tiendaDireccionGrupo">
                                            <label class="floating-label-activo-sm" for="tiendaDireccion">Dirección de despacho</label>
                                            <input type="text" class="form-control form-control-sm" id="tiendaDireccion" placeholder="Calle, número y comuna">
                                        </div>

                                        <div class="custom-control custom-checkbox mb-3">
                                            <input type="checkbox" class="custom-control-input" id="tiendaMensual">
                                            <label class="custom-control-label" for="tiendaMensual">Repetir este pedido cada mes</label>
                                        </div>

                                        <ul class="servicios-totales">
                                            <li><span>Subtotal</span><strong id="tiendaSubtotal">$0</strong></li>
                                            <li><span>Despacho</span><strong id="tiendaDespacho">$0</strong></li>
                                            <li class="servicios-totales-final"><span>Total</span><strong id="tiendaTotal">$0</strong></li>
                                        </ul>

                                        <button type="button" class="btn btn-info btn-block servicios-btn-principal" id="tiendaConfirmar">
                                            <i class="feather icon-check-circle"></i> Pedir ahora
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="d-none" id="tiendaSeguimiento"></div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page-script')
<script>
    const DEFAULT_PET_PHOTO = @json(asset('images/iconos/mascotas.svg'));
    // Si el lugar no tiene logotipo (o no carga) se muestra este
    const LOGO_RESPALDO = @json(asset('images/iconos/servicio_adherido.svg'));
    const CATALOGO_VETERINARIO = @json($catalogoServiciosVeterinarios ?? []);
    const RECETAS = @json($prescripcionesData ?? []);

    // Radio de búsqueda en km
    const RADIO_BUSQUEDA = 25;

    const SERVICIOS = {
        centro_veterinario: { nombre: 'Clínica veterinaria', icono: 'fas fa-clinic-medical', accion: 'Agendar hora', accionIcono: 'feather icon-calendar', flujo: 'agenda' },
        peluqueria: { nombre: 'Peluquería', icono: 'fas fa-cut', accion: 'Agendar peluquería', accionIcono: 'fas fa-cut', flujo: 'agenda' },
        hotel_mascotas: { nombre: 'Hotel de mascotas', icono: 'fas fa-hotel', accion: 'Reservar estadía', accionIcono: 'fas fa-hotel', flujo: 'agenda' },
        alimentos: { nombre: 'Alimentos', icono: 'fas fa-bone', accion: 'Pedir alimento', accionIcono: 'feather icon-shopping-cart', flujo: 'tienda' },
        farmacia: { nombre: 'Farmacia veterinaria', icono: 'fas fa-prescription-bottle-alt', accion: 'Pedir a farmacia', accionIcono: 'feather icon-shopping-cart', flujo: 'tienda' },
        pet_shop: { nombre: 'Pet shop', icono: 'fas fa-store', accion: 'Comprar productos', accionIcono: 'feather icon-shopping-cart', flujo: 'tienda' }
    };

    const UBICACIONES = {
        vina_del_mar: { nombre: 'Viña del Mar', coords: [-33.0245, -71.5518] },
        valparaiso: { nombre: 'Valparaíso', coords: [-33.0472, -71.6127] },
        quilpue: { nombre: 'Quilpué', coords: [-33.0475, -71.4425] },
        villa_alemana: { nombre: 'Villa Alemana', coords: [-33.0422, -71.3733] },
        los_andes: { nombre: 'Los Andes', coords: [-32.8337, -70.5983] },
        santiago_centro: { nombre: 'Santiago Centro', coords: [-33.4489, -70.6693] },
        las_condes: { nombre: 'Las Condes', coords: [-33.4080, -70.5670] }
    };

    // Horarios por día de la semana (0 domingo ... 6 sábado)
    const HORARIOS = {
        clinica: { 0: [], 1: [['09:00', '20:00']], 2: [['09:00', '20:00']], 3: [['09:00', '20:00']], 4: [['09:00', '20:00']], 5: [['09:00', '20:00']], 6: [['09:30', '14:00']] },
        continuo: { 0: [['00:00', '24:00']], 1: [['00:00', '24:00']], 2: [['00:00', '24:00']], 3: [['00:00', '24:00']], 4: [['00:00', '24:00']], 5: [['00:00', '24:00']], 6: [['00:00', '24:00']] },
        peluqueria: { 0: [], 1: [['10:00', '13:30'], ['15:00', '19:00']], 2: [['10:00', '13:30'], ['15:00', '19:00']], 3: [['10:00', '13:30'], ['15:00', '19:00']], 4: [['10:00', '13:30'], ['15:00', '19:00']], 5: [['10:00', '13:30'], ['15:00', '19:00']], 6: [['10:00', '15:00']] },
        tienda: { 0: [['10:00', '18:00']], 1: [['09:00', '21:00']], 2: [['09:00', '21:00']], 3: [['09:00', '21:00']], 4: [['09:00', '21:00']], 5: [['09:00', '21:00']], 6: [['09:00', '21:00']] },
        hotel: { 0: [['10:00', '13:00']], 1: [['08:00', '20:00']], 2: [['08:00', '20:00']], 3: [['08:00', '20:00']], 4: [['08:00', '20:00']], 5: [['08:00', '20:00']], 6: [['09:00', '18:00']] }
    };

    // Instituciones adheridas con su red de sucursales en el país (datos de prueba)
    const INSTITUCIONES = {
        costa_azul: {
            nombre: 'Clínica Veterinaria Costa Azul',
            red: [
                { nombre: 'Casa matriz', ciudad: 'Viña del Mar', lugar: 1 },
                { nombre: 'Sucursal Reñaca', ciudad: 'Viña del Mar', lugar: 2 },
                { nombre: 'Sucursal Concepción', ciudad: 'Concepción' },
                { nombre: 'Sucursal La Serena', ciudad: 'La Serena' }
            ]
        },
        perro_loco: {
            nombre: 'Veterinaria Perro Loco',
            logo: @json(asset('images/otroslogos/logo-local.png')),
            red: [
                { nombre: 'Casa matriz', ciudad: 'Valparaíso', lugar: 3 },
                { nombre: 'Sucursal Quilpué', ciudad: 'Quilpué', lugar: 4 },
                { nombre: 'Sucursal Ñuñoa', ciudad: 'Santiago' },
                { nombre: 'Sucursal Rancagua', ciudad: 'Rancagua' },
                { nombre: 'Sucursal Talca', ciudad: 'Talca' }
            ]
        },
        metropolitano: {
            nombre: 'Hospital Veterinario Metropolitano',
            red: [
                { nombre: 'Sede Las Condes', ciudad: 'Las Condes', lugar: 5 },
                { nombre: 'Sede Santiago Centro', ciudad: 'Santiago Centro', lugar: 6 },
                { nombre: 'Sede Maipú', ciudad: 'Maipú' }
            ]
        },
        clinivet: {
            nombre: 'Clinivet Andes',
            red: [
                { nombre: 'Casa matriz', ciudad: 'Los Andes', lugar: 7 },
                { nombre: 'Sucursal San Felipe', ciudad: 'San Felipe' }
            ]
        },
        patitas: {
            nombre: 'Peluquería Canina Patitas Limpias',
            red: [
                { nombre: 'Sucursal 15 Norte', ciudad: 'Viña del Mar', lugar: 8 },
                { nombre: 'Sucursal Pedro Montt', ciudad: 'Valparaíso', lugar: 9 },
                { nombre: 'Sucursal Providencia', ciudad: 'Santiago' },
                { nombre: 'Sucursal Concepción', ciudad: 'Concepción' },
                { nombre: 'Sucursal Temuco', ciudad: 'Temuco' },
                { nombre: 'Sucursal Puerto Montt', ciudad: 'Puerto Montt' }
            ]
        },
        mascota_feliz: {
            nombre: 'Spa Mascota Feliz',
            red: [
                { nombre: 'Sucursal Las Condes', ciudad: 'Las Condes', lugar: 10 },
                { nombre: 'Sucursal Vitacura', ciudad: 'Vitacura' }
            ]
        },
        pelos_colas: {
            nombre: 'Peluquería Pelos y Colas',
            red: [
                { nombre: 'Local único', ciudad: 'Villa Alemana', lugar: 11 }
            ]
        },
        pet_market: {
            nombre: 'Pet Market Pacífico',
            red: [
                { nombre: 'Local Av. Valparaíso', ciudad: 'Viña del Mar', lugar: 12 },
                { nombre: 'Local Av. Brasil', ciudad: 'Valparaíso', lugar: 13 },
                { nombre: 'Local Alameda', ciudad: 'Santiago Centro', lugar: 14 },
                { nombre: 'Local Antofagasta', ciudad: 'Antofagasta' },
                { nombre: 'Local Iquique', ciudad: 'Iquique' }
            ]
        },
        vetfarma: {
            nombre: 'Farmacia Veterinaria VetFarma',
            red: [
                { nombre: 'Local Quillota', ciudad: 'Viña del Mar', lugar: 15 },
                { nombre: 'Local Apoquindo', ciudad: 'Las Condes', lugar: 16 },
                { nombre: 'Local Concepción', ciudad: 'Concepción' }
            ]
        },
        mundo_mascota: {
            nombre: 'Mundo Mascota Interior',
            red: [
                { nombre: 'Local Quilpué', ciudad: 'Quilpué', lugar: 17 }
            ]
        },
        agro_andes: {
            nombre: 'Agro y Mascotas Los Andes',
            red: [
                { nombre: 'Local Santa Rosa', ciudad: 'Los Andes', lugar: 18 }
            ]
        },
        huellitas: {
            nombre: 'Hotel Canino Huellitas',
            red: [
                { nombre: 'Parcela Villa Alemana', ciudad: 'Villa Alemana', lugar: 19 },
                { nombre: 'Parcela Peñalolén', ciudad: 'Peñalolén', lugar: 20 },
                { nombre: 'Parcela Pucón', ciudad: 'Pucón' }
            ]
        }
    };

    // Ajustes que define el administrador de cada lugar:
    // pagoEnLugar: si es false solo se paga en línea.
    // eligePeluquero: deja que el tutor elija quién atiende (solo se ve si el equipo tiene más de una persona)
    const LUGARES = [
        { id: 1, institucion: 'costa_azul', sucursal: 'Casa matriz Viña del Mar', tipoSede: 'Casa matriz', pagoEnLugar: true, direccion: 'Av. Libertad 1150, Viña del Mar', telefono: '+56 32 268 1100', coords: [-33.0135, -71.5490], servicios: ['centro_veterinario', 'farmacia', 'hotel_mascotas'], horario: 'continuo', urgencia: true, calificacion: 4.8, resenas: 214,
            equipo: [{ nombre: 'Dra. Camila Rojas', cargo: 'Medicina general' }, { nombre: 'Dr. Matías Fuentes', cargo: 'Cirugía' }, { nombre: 'Dra. Valentina Soto', cargo: 'Medicina felina' }] },
        { id: 2, institucion: 'costa_azul', sucursal: 'Sucursal Reñaca', tipoSede: 'Sucursal', pagoEnLugar: false, eligePeluquero: false, direccion: 'Av. Borgoño 14920, Reñaca', telefono: '+56 32 268 1120', coords: [-32.9678, -71.5452], servicios: ['centro_veterinario', 'peluqueria'], horario: 'clinica', urgencia: false, calificacion: 4.6, resenas: 88,
            equipo: [{ nombre: 'Dr. Ignacio Pérez', cargo: 'Medicina general' }, { nombre: 'Dra. Fernanda Muñoz', cargo: 'Dermatología' }] },
        { id: 3, institucion: 'perro_loco', sucursal: 'Casa matriz Valparaíso', tipoSede: 'Casa matriz', pagoEnLugar: true, direccion: 'Av. Argentina 820, Valparaíso', telefono: '+56 32 221 4455', coords: [-33.0455, -71.6105], servicios: ['centro_veterinario', 'farmacia'], horario: 'clinica', urgencia: false, calificacion: 4.7, resenas: 156,
            equipo: [{ nombre: 'Dr. Tomás Araya', cargo: 'Medicina general' }, { nombre: 'Dra. Josefa Contreras', cargo: 'Traumatología' }] },
        { id: 4, institucion: 'perro_loco', sucursal: 'Sucursal Quilpué', tipoSede: 'Sucursal', pagoEnLugar: true, eligePeluquero: false, direccion: 'Los Carrera 1020, Quilpué', telefono: '+56 32 291 7788', coords: [-33.0478, -71.4402], servicios: ['centro_veterinario', 'peluqueria'], horario: 'clinica', urgencia: false, calificacion: 4.5, resenas: 61,
            equipo: [{ nombre: 'Dra. Antonia Vega', cargo: 'Medicina general' }] },
        { id: 5, institucion: 'metropolitano', sucursal: 'Sede Las Condes', tipoSede: 'Casa matriz', pagoEnLugar: false, direccion: 'Av. Apoquindo 5120, Las Condes', telefono: '+56 2 2940 5500', coords: [-33.4145, -70.5786], servicios: ['centro_veterinario', 'farmacia'], horario: 'continuo', urgencia: true, calificacion: 4.9, resenas: 532,
            equipo: [{ nombre: 'Dr. Felipe Navarro', cargo: 'Cardiología' }, { nombre: 'Dra. Isidora Castro', cargo: 'Medicina interna' }, { nombre: 'Dr. Benjamín Lagos', cargo: 'Urgencias' }] },
        { id: 6, institucion: 'metropolitano', sucursal: 'Sede Santiago Centro', tipoSede: 'Sucursal', pagoEnLugar: false, direccion: 'San Diego 440, Santiago', telefono: '+56 2 2940 5520', coords: [-33.4521, -70.6508], servicios: ['centro_veterinario'], horario: 'clinica', urgencia: false, calificacion: 4.6, resenas: 240,
            equipo: [{ nombre: 'Dra. Martina Reyes', cargo: 'Medicina general' }, { nombre: 'Dr. Vicente Morales', cargo: 'Odontología' }] },
        { id: 7, institucion: 'clinivet', sucursal: 'Casa matriz Los Andes', tipoSede: 'Casa matriz', pagoEnLugar: true, eligePeluquero: true, direccion: 'Esmeralda 381, Los Andes', telefono: '+56 34 242 1010', coords: [-32.8318, -70.5968], servicios: ['centro_veterinario', 'farmacia', 'peluqueria'], horario: 'clinica', urgencia: false, calificacion: 4.4, resenas: 47,
            equipo: [{ nombre: 'Dr. Cristóbal Herrera', cargo: 'Medicina general' }, { nombre: 'Dra. Paula Silva', cargo: 'Animales exóticos' }] },
        { id: 8, institucion: 'patitas', sucursal: 'Sucursal 15 Norte', tipoSede: 'Casa matriz', pagoEnLugar: true, eligePeluquero: true, direccion: '15 Norte 961, Viña del Mar', telefono: '+56 9 8765 4321', coords: [-33.0098, -71.5486], servicios: ['peluqueria', 'pet_shop'], horario: 'peluqueria', calificacion: 4.9, resenas: 302 },
        { id: 9, institucion: 'patitas', sucursal: 'Sucursal Pedro Montt', tipoSede: 'Sucursal', pagoEnLugar: false, eligePeluquero: false, direccion: 'Av. Pedro Montt 2050, Valparaíso', telefono: '+56 9 8765 4300', coords: [-33.0490, -71.6200], servicios: ['peluqueria'], horario: 'peluqueria', calificacion: 4.7, resenas: 119 },
        { id: 10, institucion: 'mascota_feliz', sucursal: 'Sucursal Las Condes', tipoSede: 'Casa matriz', pagoEnLugar: false, eligePeluquero: true, direccion: 'Av. Las Condes 9800, Las Condes', telefono: '+56 9 7654 3210', coords: [-33.4005, -70.5560], servicios: ['peluqueria', 'pet_shop'], horario: 'peluqueria', calificacion: 4.8, resenas: 178 },
        { id: 11, institucion: 'pelos_colas', sucursal: 'Local Villa Alemana', tipoSede: 'Casa matriz', pagoEnLugar: true, eligePeluquero: true, direccion: 'Av. Valparaíso 680, Villa Alemana', telefono: '+56 9 6543 2109', coords: [-33.0435, -71.3745], servicios: ['peluqueria'], horario: 'peluqueria', calificacion: 4.3, resenas: 36 },
        { id: 12, institucion: 'pet_market', sucursal: 'Local Av. Valparaíso', tipoSede: 'Casa matriz', pagoEnLugar: true, direccion: 'Av. Valparaíso 520, Viña del Mar', telefono: '+56 32 297 3300', coords: [-33.0242, -71.5560], servicios: ['alimentos', 'pet_shop'], horario: 'tienda', calificacion: 4.6, resenas: 410 },
        { id: 13, institucion: 'pet_market', sucursal: 'Local Av. Brasil', tipoSede: 'Sucursal', pagoEnLugar: false, direccion: 'Av. Brasil 1450, Valparaíso', telefono: '+56 32 297 3310', coords: [-33.0447, -71.6150], servicios: ['alimentos', 'pet_shop'], horario: 'tienda', calificacion: 4.5, resenas: 198 },
        { id: 14, institucion: 'pet_market', sucursal: 'Local Alameda', tipoSede: 'Sucursal', pagoEnLugar: false, direccion: 'Av. Libertador Bernardo O\'Higgins 1200, Santiago', telefono: '+56 2 2697 3320', coords: [-33.4440, -70.6550], servicios: ['alimentos', 'pet_shop'], horario: 'tienda', calificacion: 4.4, resenas: 264 },
        { id: 15, institucion: 'vetfarma', sucursal: 'Local Quillota', tipoSede: 'Casa matriz', pagoEnLugar: false, direccion: 'Quillota 300, Viña del Mar', telefono: '+56 32 248 0090', coords: [-33.0200, -71.5545], servicios: ['farmacia', 'alimentos'], horario: 'tienda', calificacion: 4.7, resenas: 143 },
        { id: 16, institucion: 'vetfarma', sucursal: 'Local Apoquindo', tipoSede: 'Sucursal', pagoEnLugar: false, direccion: 'Av. Apoquindo 4400, Las Condes', telefono: '+56 2 2248 0091', coords: [-33.4110, -70.5880], servicios: ['farmacia'], horario: 'tienda', calificacion: 4.8, resenas: 97 },
        { id: 17, institucion: 'mundo_mascota', sucursal: 'Local Quilpué', tipoSede: 'Casa matriz', pagoEnLugar: true, eligePeluquero: false, direccion: 'Diego Portales 775, Quilpué', telefono: '+56 32 291 1122', coords: [-33.0468, -71.4425], servicios: ['alimentos', 'pet_shop', 'peluqueria'], horario: 'tienda', calificacion: 4.2, resenas: 58 },
        { id: 18, institucion: 'agro_andes', sucursal: 'Local Santa Rosa', tipoSede: 'Casa matriz', pagoEnLugar: false, direccion: 'Santa Rosa 250, Los Andes', telefono: '+56 34 242 5566', coords: [-32.8350, -70.5990], servicios: ['alimentos', 'pet_shop', 'farmacia'], horario: 'tienda', calificacion: 4.1, resenas: 29 },
        { id: 19, institucion: 'huellitas', sucursal: 'Parcela Villa Alemana', tipoSede: 'Casa matriz', pagoEnLugar: true, eligePeluquero: true, direccion: 'Camino Troncal 4500, Villa Alemana', telefono: '+56 9 5432 1098', coords: [-33.0510, -71.3920], servicios: ['hotel_mascotas', 'peluqueria'], horario: 'hotel', calificacion: 4.9, resenas: 87 },
        { id: 20, institucion: 'huellitas', sucursal: 'Parcela Peñalolén', tipoSede: 'Sucursal', pagoEnLugar: false, direccion: 'Av. José Arrieta 9900, Peñalolén', telefono: '+56 9 5432 1000', coords: [-33.4800, -70.5400], servicios: ['hotel_mascotas'], horario: 'hotel', calificacion: 4.7, resenas: 54 }
    ];

    // Equipo de peluquería por lugar (id del lugar); lo mantiene el administrador
    const PELUQUEROS = {
        2: [{ nombre: 'Javiera Torres', cargo: 'Cortes de raza' }, { nombre: 'Martín Rivas', cargo: 'Baños y deslanados' }],
        4: [{ nombre: 'Daniela Pino', cargo: 'Baños y cortes' }],
        7: [{ nombre: 'Catalina Fuentes', cargo: 'Especialista en cortes de raza' }, { nombre: 'Diego Salinas', cargo: 'Baños y deslanados' }, { nombre: 'Paula Moya', cargo: 'Gatos y razas pequeñas' }],
        8: [{ nombre: 'Fernanda Ríos', cargo: 'Cortes a tijera' }, { nombre: 'Matías Olivares', cargo: 'Razas grandes y deslanados' }],
        9: [{ nombre: 'Constanza León', cargo: 'Baños y cortes' }, { nombre: 'Ignacio Bravo', cargo: 'Razas grandes' }],
        10: [{ nombre: 'Antonia Reyes', cargo: 'Spa y cortes de raza' }, { nombre: 'Sebastián Vidal', cargo: 'Razas grandes' }, { nombre: 'Valentina Cruz', cargo: 'Gatos' }],
        11: [{ nombre: 'Rocío Muñoz', cargo: 'Baños y cortes' }],
        17: [{ nombre: 'Camila Soto', cargo: 'Baños y cortes' }],
        19: [{ nombre: 'Tomás Herrera', cargo: 'Baños para huéspedes del hotel' }, { nombre: 'Francisca Díaz', cargo: 'Cortes de raza' }]
    };

    const PRODUCTOS = [
        { id: 'a1', tipo: 'alimentos', nombre: 'Pro Plan Adulto Razas Medianas', detalle: 'Saco 15 kg · Perro', precio: 59990, icono: 'fas fa-bone' },
        { id: 'a2', tipo: 'alimentos', nombre: 'Royal Canin Medium Adult', detalle: 'Saco 15 kg · Perro', precio: 64990, icono: 'fas fa-bone' },
        { id: 'a3', tipo: 'alimentos', nombre: 'Acana Adult Dog', detalle: 'Saco 11,4 kg · Perro', precio: 74990, icono: 'fas fa-bone' },
        { id: 'a4', tipo: 'alimentos', nombre: 'Bravery Pollo Adulto', detalle: 'Saco 12 kg · Perro', precio: 54990, icono: 'fas fa-bone' },
        { id: 'a5', tipo: 'alimentos', nombre: 'Fit Formula Cachorro', detalle: 'Saco 10 kg · Perro', precio: 29990, icono: 'fas fa-bone' },
        { id: 'a6', tipo: 'alimentos', nombre: 'Royal Canin Indoor', detalle: 'Saco 7,5 kg · Gato', precio: 52990, icono: 'fas fa-fish' },
        { id: 'a7', tipo: 'alimentos', nombre: 'Pro Plan Gato Esterilizado', detalle: 'Saco 7,5 kg · Gato', precio: 44990, icono: 'fas fa-fish' },
        { id: 'a8', tipo: 'alimentos', nombre: 'Hill\'s c/d Urinary Care', detalle: 'Dieta veterinaria 4 kg · Gato', precio: 39990, icono: 'fas fa-fish' },
        { id: 'a9', tipo: 'alimentos', nombre: 'Royal Canin Renal', detalle: 'Dieta veterinaria 2 kg · Perro', precio: 28990, icono: 'fas fa-bone' },
        { id: 'f1', tipo: 'farmacia', nombre: 'Bravecto 20 a 40 kg', detalle: 'Antiparasitario externo · 1 comprimido', precio: 34990, icono: 'fas fa-pills' },
        { id: 'f2', tipo: 'farmacia', nombre: 'NexGard Spectra 15 a 30 kg', detalle: 'Antiparasitario interno y externo · 1 comprimido', precio: 27990, icono: 'fas fa-pills' },
        { id: 'f3', tipo: 'farmacia', nombre: 'Drontal Plus Perro', detalle: 'Antiparasitario interno · 2 comprimidos', precio: 8990, icono: 'fas fa-pills' },
        { id: 'f4', tipo: 'farmacia', nombre: 'Meloxivet 1,5 mg/ml', detalle: 'Antiinflamatorio · Suspensión oral 10 ml', precio: 12990, icono: 'fas fa-prescription-bottle-alt', receta: true },
        { id: 'f5', tipo: 'farmacia', nombre: 'Amoxicilina + Ác. clavulánico 250 mg', detalle: 'Antibiótico · 10 comprimidos', precio: 15990, icono: 'fas fa-prescription-bottle-alt', receta: true },
        { id: 'f6', tipo: 'farmacia', nombre: 'Revolution Plus Gato', detalle: 'Pipeta 2,5 a 5 kg', precio: 22990, icono: 'fas fa-syringe' },
        { id: 'p1', tipo: 'pet_shop', nombre: 'Arena sanitaria aglomerante', detalle: 'Bolsa 10 kg', precio: 9990, icono: 'fas fa-box' },
        { id: 'p2', tipo: 'pet_shop', nombre: 'Collar reflectante ajustable', detalle: 'Talla M', precio: 7990, icono: 'fas fa-dog' },
        { id: 'p3', tipo: 'pet_shop', nombre: 'Cama ortopédica', detalle: 'Tamaño mediano', precio: 39990, icono: 'fas fa-couch' },
        { id: 'p4', tipo: 'pet_shop', nombre: 'Kong Classic', detalle: 'Juguete rellenable · Talla M', precio: 14990, icono: 'fas fa-baseball-ball' },
        { id: 'p5', tipo: 'pet_shop', nombre: 'Shampoo dermatológico', detalle: 'Frasco 250 ml', precio: 8990, icono: 'fas fa-pump-soap' }
    ];

    // La imagen de cada servicio la sube el administrador del lugar desde su perfil.
    // Mientras no tenga una se muestra IMAGEN_SERVICIO_RESPALDO
    const IMAGEN_SERVICIO_RESPALDO = @json(asset('images/iconos/servicio_sin_imagen.svg'));

    const SERVICIOS_PELUQUERIA = [
        { id: 'bano', nombre: 'Baño completo', detalle: 'Baño, secado y perfume · 60 min', precio: 15990, duracion: 60, imagen: null },
        { id: 'corte', nombre: 'Baño y corte', detalle: 'Corte de raza o a gusto · 90 min', precio: 22990, duracion: 90, imagen: null },
        { id: 'deslanado', nombre: 'Deslanado', detalle: 'Retiro de pelo muerto · 90 min', precio: 24990, duracion: 90, imagen: null },
        { id: 'unas', nombre: 'Corte de uñas', detalle: 'Incluye limpieza de oídos · 30 min', precio: 5990, duracion: 30, imagen: null }
    ];

    const ALOJAMIENTOS = [
        { id: 'estandar', nombre: 'Habitación estándar', detalle: 'Paseos dos veces al día', precio: 18000, imagen: null },
        { id: 'suite', nombre: 'Suite con cámara', detalle: 'Puede verlo en línea desde su celular', precio: 25000, imagen: null },
        { id: 'felino', nombre: 'Zona felina tranquila', detalle: 'Espacio separado de los perros', precio: 15000, imagen: null }
    ];

    // Valor de referencia por área de la clínica
    const VALORES_VETERINARIOS = {
        consulta_veterinaria: 25000,
        consulta_especialista: 38000,
        procedimientos_examenes: 32000,
        vacunacion: 18000
    };

    const FLUJOS = {
        centro_veterinario: [
            { panel: 'motivo', texto: 'Motivo' },
            { panel: 'veterinario', texto: 'Veterinario' },
            { panel: 'fecha', texto: 'Fecha y hora' },
            { panel: 'pago', texto: 'Pago' },
            { panel: 'confirmar', texto: 'Confirmar' }
        ],
        peluqueria: [
            { panel: 'servicio', texto: 'Servicio' },
            { panel: 'fecha', texto: 'Fecha y hora' },
            { panel: 'pago', texto: 'Pago' },
            { panel: 'confirmar', texto: 'Confirmar' }
        ],
        hotel_mascotas: [
            { panel: 'estadia', texto: 'Estadía' },
            { panel: 'pago', texto: 'Pago' },
            { panel: 'confirmar', texto: 'Confirmar' }
        ]
    };

    const MEDIOS_PAGO = {
        webpay: { nombre: 'Webpay (crédito, débito o prepago)', icono: 'feather icon-credit-card' },
        lugar: { nombre: 'Pago en el lugar', icono: 'fas fa-cash-register' }
    };

    const DIAS = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
    const DIAS_CORTOS = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
    const MESES = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];

    const busqueda = {
        ubicacion: null,
        ubicacionNombre: '',
        origen: '',
        servicio: '',
        resultados: [],
        lejanos: [],
        seleccionado: null,
        marcadores: {},
        marcadorUsuario: null
    };

    let agenda = {};
    let pedido = [];
    let productoElegido = null;
    let temporizadorSeguimiento = null;
    let calendarioAgenda = null;

    // flatpickr no trae el español cargado en la plantilla
    const CALENDARIO_ESPANOL = {
        weekdays: {
            shorthand: ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'],
            longhand: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado']
        },
        months: {
            shorthand: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
            longhand: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre']
        },
        ordinal: function () {
            return 'º';
        },
        firstDayOfWeek: 1,
        rangeSeparator: ' a ',
        time_24hr: true,
        yearAriaLabel: 'Año',
        monthAriaLabel: 'Mes'
    };

    /* ---------- Utilidades ---------- */

    function mostrarMensaje(titulo, texto, icono) {
        swal({ title: titulo, text: texto, icon: icono, buttons: 'Aceptar' });
    }

    function escapar(texto) {
        return $('<div>').text(texto == null ? '' : String(texto)).html();
    }

    function formatoPeso(valor) {
        return '$' + Math.round(valor).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    function distanciaKm(a, b) {
        const rad = function (v) { return v * Math.PI / 180; };
        const dLat = rad(b[0] - a[0]);
        const dLon = rad(b[1] - a[1]);
        const h = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
            Math.cos(rad(a[0])) * Math.cos(rad(b[0])) * Math.sin(dLon / 2) * Math.sin(dLon / 2);
        return 6371 * 2 * Math.atan2(Math.sqrt(h), Math.sqrt(1 - h));
    }

    function formatoDistancia(km) {
        if (km == null || isNaN(km)) {
            return '-';
        }
        return km < 1 ? Math.round(km * 1000) + ' m' : km.toFixed(1).replace('.', ',') + ' km';
    }

    // Número estable a partir de un texto, para simular cupos y stock sin que cambien al recargar
    function semilla(texto) {
        let h = 0;
        for (let i = 0; i < texto.length; i++) {
            h = (h * 31 + texto.charCodeAt(i)) | 0;
        }
        return Math.abs(h);
    }

    function aMinutos(hora) {
        const partes = hora.split(':');
        return parseInt(partes[0], 10) * 60 + parseInt(partes[1], 10);
    }

    function aHora(minutos) {
        const h = Math.floor(minutos / 60);
        const m = minutos % 60;
        return (h < 10 ? '0' : '') + h + ':' + (m < 10 ? '0' : '') + m;
    }

    function fechaIso(fecha) {
        const m = fecha.getMonth() + 1;
        const d = fecha.getDate();
        return fecha.getFullYear() + '-' + (m < 10 ? '0' : '') + m + '-' + (d < 10 ? '0' : '') + d;
    }

    function desdeIso(iso) {
        const p = iso.split('-');
        return new Date(parseInt(p[0], 10), parseInt(p[1], 10) - 1, parseInt(p[2], 10));
    }

    function textoFecha(iso) {
        const f = desdeIso(iso);
        return DIAS[f.getDay()] + ' ' + f.getDate() + ' de ' + MESES[f.getMonth()];
    }

    function mascotaActual() {
        const $opcion = $('#selectedPet option:selected');
        if (!$opcion.val()) {
            return null;
        }
        return {
            id: $opcion.val(),
            nombre: $.trim($opcion.text()),
            foto: $opcion.data('foto') || DEFAULT_PET_PHOTO
        };
    }

    function desplazarA(elemento) {
        if (!elemento || !elemento.scrollIntoView) {
            return;
        }
        const sinAnimacion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        elemento.scrollIntoView({ behavior: sinAnimacion ? 'auto' : 'smooth', block: 'start' });
    }

    /* ---------- Horario y estado del lugar ---------- */

    function estadoLugar(lugar) {
        const horario = HORARIOS[lugar.horario];
        const ahora = new Date();
        const minutos = ahora.getHours() * 60 + ahora.getMinutes();
        const hoy = horario[ahora.getDay()] || [];

        if (lugar.horario === 'continuo') {
            return { abierto: true, texto: 'Abierto las 24 horas' };
        }

        for (let i = 0; i < hoy.length; i++) {
            if (minutos >= aMinutos(hoy[i][0]) && minutos < aMinutos(hoy[i][1])) {
                return { abierto: true, texto: 'Abierto · cierra a las ' + hoy[i][1] };
            }
        }

        // Próxima apertura: hoy más tarde o en los días siguientes
        for (let d = 0; d < 7; d++) {
            const dia = (ahora.getDay() + d) % 7;
            const rangos = horario[dia] || [];
            for (let r = 0; r < rangos.length; r++) {
                if (d > 0 || aMinutos(rangos[r][0]) > minutos) {
                    const cuando = d === 0 ? 'hoy' : (d === 1 ? 'mañana' : 'el ' + DIAS[dia].toLowerCase());
                    return { abierto: false, texto: 'Cerrado · abre ' + cuando + ' a las ' + rangos[r][0] };
                }
            }
        }

        return { abierto: false, texto: 'Cerrado' };
    }

    // Agrupa los días con el mismo horario: "Lunes a viernes 09:00 - 20:00"
    function filasHorario(lugar) {
        const horario = HORARIOS[lugar.horario];
        const hoy = new Date().getDay();
        const grupos = [];

        [1, 2, 3, 4, 5, 6, 0].forEach(function (dia) {
            const clave = JSON.stringify(horario[dia] || []);
            const ultimo = grupos[grupos.length - 1];
            if (ultimo && ultimo.clave === clave) {
                ultimo.dias.push(dia);
            } else {
                grupos.push({ clave: clave, dias: [dia], rangos: horario[dia] || [] });
            }
        });

        return grupos.map(function (grupo) {
            const nombre = grupo.dias.length > 1
                ? DIAS[grupo.dias[0]] + ' a ' + DIAS[grupo.dias[grupo.dias.length - 1]].toLowerCase()
                : DIAS[grupo.dias[0]];
            const rangos = grupo.rangos.length
                ? grupo.rangos.map(function (r) {
                    return '<span class="reserva-vet-horario-rango">' + (r[0] === '00:00' && r[1] === '24:00' ? '24 horas' : r[0] + ' - ' + r[1]) + '</span>';
                }).join('')
                : '<span class="reserva-vet-horario-rango servicios-rango-cerrado">Cerrado</span>';
            const esHoy = grupo.dias.indexOf(hoy) !== -1;

            return '<div class="reserva-vet-horario' + (esHoy ? ' servicios-horario-hoy' : '') + '">' +
                '<span class="reserva-vet-horario-dias">' + nombre + (esHoy ? ' <small>(hoy)</small>' : '') + '</span>' +
                '<span class="reserva-vet-horario-rangos">' + rangos + '</span>' +
            '</div>';
        }).join('');
    }

    /* ---------- Mapa ---------- */

    function iconoMarcador(elegido) {
        const servicio = SERVICIOS[busqueda.servicio] || SERVICIOS.centro_veterinario;
        return L.divIcon({
            className: 'servicios-marcador-contenedor',
            html: '<span class="servicios-marcador' + (elegido ? ' elegido' : '') + '"><i class="' + servicio.icono + '"></i></span>',
            iconSize: [40, 40],
            iconAnchor: [20, 45],
            tooltipAnchor: [0, -42]
        });
    }

    function limpiarMarcadores() {
        Object.keys(busqueda.marcadores).forEach(function (id) {
            map.removeLayer(busqueda.marcadores[id]);
        });
        busqueda.marcadores = {};
    }

    function dibujarMarcadores() {
        limpiarMarcadores();

        busqueda.resultados.forEach(function (lugar) {
            const marcador = L.marker(lugar.coords, { icon: iconoMarcador(false), riseOnHover: true })
                .addTo(map)
                .bindTooltip('<strong>' + escapar(INSTITUCIONES[lugar.institucion].nombre) + '</strong><br>' + escapar(lugar.sucursal), { direction: 'top' });

            marcador.on('click', function () {
                seleccionarLugar(lugar.id, false);
            });

            busqueda.marcadores[lugar.id] = marcador;
        });
    }

    function ponerUbicacionUsuario(coords) {
        if (busqueda.marcadorUsuario) {
            busqueda.marcadorUsuario.setLatLng(coords);
            return;
        }

        busqueda.marcadorUsuario = L.marker(coords, {
            icon: L.divIcon({
                className: 'servicios-marcador-contenedor',
                html: '<span class="servicios-marcador-usuario"></span>',
                iconSize: [22, 22],
                iconAnchor: [11, 11]
            }),
            interactive: false,
            zIndexOffset: -100
        }).addTo(map);
    }

    function encuadrarMapa() {
        const puntos = busqueda.resultados.map(function (l) { return l.coords; });
        if (busqueda.ubicacion) {
            puntos.push(busqueda.ubicacion);
        }

        if (puntos.length > 1) {
            map.fitBounds(puntos, { padding: [50, 50], maxZoom: 14 });
        } else if (puntos.length === 1) {
            map.setView(puntos[0], 13);
        }
    }

    /* ---------- Búsqueda ---------- */

    function actualizarEstadoUbicacion() {
        if (!busqueda.ubicacion) {
            $('#locationStatus').text('Seleccione una ubicación manual o use la geolocalización automática para buscar servicios cercanos.');
            return;
        }
        const origen = busqueda.origen === 'auto' ? 'Ubicación automática' : 'Ubicación manual';
        $('#locationStatus').text(origen + ': ' + busqueda.ubicacionNombre);
    }

    // Si cambia algo del buscador se vuelve a la pantalla limpia hasta que se busque otra vez
    function ocultarResultados() {
        $('#serviciosResultados, #servicioAccion').addClass('d-none');
        detenerSeguimiento();
        busqueda.seleccionado = null;
    }

    function buscar() {
        const servicio = $('#searchCategory').val();

        if (!servicio) {
            mostrarMensaje('Búsqueda de servicios', 'Seleccione qué servicio busca.', 'error');
            return;
        }

        if (!busqueda.ubicacion) {
            mostrarMensaje('Búsqueda de servicios', 'Seleccione una ubicación manual o use su ubicación.', 'error');
            return;
        }

        busqueda.servicio = servicio;
        busqueda.seleccionado = null;

        const encontrados = LUGARES
            .filter(function (lugar) { return lugar.servicios.indexOf(servicio) !== -1; })
            .map(function (lugar) {
                return $.extend({}, lugar, { distancia: distanciaKm(busqueda.ubicacion, lugar.coords) });
            })
            .sort(function (a, b) { return a.distancia - b.distancia; });

        busqueda.resultados = encontrados.filter(function (l) { return l.distancia <= RADIO_BUSQUEDA; });
        busqueda.lejanos = encontrados.filter(function (l) { return l.distancia > RADIO_BUSQUEDA; });

        $('#servicioAccion').addClass('d-none');
        detenerSeguimiento();
        $('#serviciosResultados').removeClass('d-none');
        mostrarFichaVacia();
        $('#searchSummary').text('Buscando lugares cercanos...');
        $('#tituloLista').text('Resultados de la búsqueda');
        $('#listaLugares').html(esqueletoLista());

        const $boton = $('#btnBuscarServicio').prop('disabled', true);

        // El mapa estaba oculto: se recalcula su tamaño antes de dibujar
        setTimeout(function () {
            map.invalidateSize();
            limpiarMarcadores();
            ponerUbicacionUsuario(busqueda.ubicacion);
            map.setView(busqueda.ubicacion, 12);
        }, 30);

        setTimeout(function () {
            $boton.prop('disabled', false);
            mostrarResultados();
        }, 650);
    }

    function esqueletoLista() {
        let html = '';
        for (let i = 0; i < 3; i++) {
            html += '<li class="reserva-vet-lugar"><div class="reserva-vet-lugar-esqueleto"><span class="reserva-vet-lugar-icono"></span><span class="reserva-vet-lugar-datos"><span></span><span></span></span></div></li>';
        }
        return html;
    }

    function mostrarResultados() {
        const servicio = SERVICIOS[busqueda.servicio];
        const total = busqueda.resultados.length;

        dibujarMarcadores();
        encuadrarMapa();

        if (!total) {
            $('#searchSummary').text('No hay lugares de ' + servicio.nombre.toLowerCase() + ' adheridos a menos de ' + RADIO_BUSQUEDA + ' km de ' + busqueda.ubicacionNombre + '.');
            $('#tituloLista').text('Sin resultados en la zona');
            $('#listaLugares').html(listaVacia());
            return;
        }

        $('#searchSummary').text(total + (total === 1 ? ' lugar adherido' : ' lugares adheridos') + ' de ' + servicio.nombre.toLowerCase() + ' cerca de ' + busqueda.ubicacionNombre + '.');
        $('#tituloLista').text('Más cercanos primero');
        dibujarLista();
    }

    function listaVacia() {
        let html = '<li class="servicios-lista-vacia"><div class="servicios-vacio">' +
            '<i class="fas fa-search-location"></i>' +
            '<p>No encontramos lugares en esta zona</p>';

        if (busqueda.lejanos.length) {
            const cercano = busqueda.lejanos[0];
            html += '<small>El más cercano es ' + escapar(INSTITUCIONES[cercano.institucion].nombre) + ' en ' +
                escapar(cercano.direccion) + ', a ' + formatoDistancia(cercano.distancia) + '.</small>' +
                '<button type="button" class="btn btn-outline-info btn-sm mt-3" id="btnAmpliarBusqueda"><i class="feather icon-maximize-2"></i> Ver lugares más lejanos</button>';
        } else {
            html += '<small>Pruebe con otra ubicación o con otro servicio.</small>';
        }

        return html + '</div></li>';
    }

    function dibujarLista() {
        let html = '';

        busqueda.resultados.forEach(function (lugar) {
            const estado = estadoLugar(lugar);
            const activo = busqueda.seleccionado && busqueda.seleccionado.id === lugar.id;

            html += '<li class="reserva-vet-lugar">' +
                '<button type="button" class="reserva-vet-lugar-caja' + (activo ? ' active' : '') + '" data-lugar="' + lugar.id + '">' +
                    '<span class="reserva-vet-lugar-icono"><i class="' + SERVICIOS[busqueda.servicio].icono + '"></i></span>' +
                    '<span class="reserva-vet-lugar-datos">' +
                        '<span class="reserva-vet-lugar-nombre">' + escapar(INSTITUCIONES[lugar.institucion].nombre) + '</span>' +
                        '<span class="reserva-vet-lugar-direccion">' + escapar(lugar.sucursal) + ' · ' + formatoDistancia(lugar.distancia) + '</span>' +
                        '<span class="servicios-lista-estado">' +
                            '<span class="servicios-estado ' + (estado.abierto ? 'servicios-estado-abierto' : 'servicios-estado-cerrado') + '">' + (estado.abierto ? 'Abierto' : 'Cerrado') + '</span>' +
                            (lugar.urgencia && busqueda.servicio === 'centro_veterinario' ? '<span class="servicios-estado servicios-estado-urgencia"><i class="fas fa-ambulance"></i> Urgencias</span>' : '') +
                            '<span class="servicios-calificacion"><i class="fas fa-star"></i> ' + lugar.calificacion.toFixed(1).replace('.', ',') + '</span>' +
                        '</span>' +
                    '</span>' +
                    '<span class="reserva-vet-lugar-marca" aria-hidden="true"><i class="feather icon-check"></i></span>' +
                '</button>' +
            '</li>';
        });

        $('#listaLugares').html(html);
    }

    /* ---------- Ficha del lugar ---------- */

    function mostrarFichaVacia() {
        $('#fichaContenido').addClass('d-none');
        $('#fichaVacia').removeClass('d-none');
    }

    function seleccionarLugar(id, centrar) {
        const lugar = busqueda.resultados.find(function (l) { return String(l.id) === String(id); });
        if (!lugar) {
            return;
        }

        const anterior = busqueda.seleccionado;
        busqueda.seleccionado = lugar;

        if (anterior && busqueda.marcadores[anterior.id]) {
            busqueda.marcadores[anterior.id].setIcon(iconoMarcador(false)).setZIndexOffset(0);
        }
        busqueda.marcadores[lugar.id].setIcon(iconoMarcador(true)).setZIndexOffset(1000);

        if (centrar) {
            map.setView(lugar.coords, Math.max(map.getZoom(), 14));
        } else {
            map.panTo(lugar.coords);
        }

        $('#listaLugares .reserva-vet-lugar-caja').removeClass('active')
            .filter('[data-lugar="' + lugar.id + '"]').addClass('active');

        dibujarFicha(lugar);
        prepararAccion(lugar);

        // Si la ficha quedó fuera de la vista (se eligió desde la lista o en celular) se sube hasta ella.
        // En escritorio se muestra desde el comienzo de la tarjeta para ver mapa y ficha juntos
        const ficha = document.getElementById('fichaLugar');
        const posicion = ficha.getBoundingClientRect();
        if (posicion.top < 0 || posicion.top > window.innerHeight - 200) {
            desplazarA(window.innerWidth < 992 ? ficha : document.getElementById('serviciosResultados'));
        }
    }

    function dibujarFicha(lugar) {
        const institucion = INSTITUCIONES[lugar.institucion];
        const servicio = SERVICIOS[busqueda.servicio];
        const estado = estadoLugar(lugar);
        const destino = lugar.coords[0] + ',' + lugar.coords[1];

        $('#fichaLogo').toggleClass('servicios-ficha-logo-respaldo', !institucion.logo);
        $('#fichaLogoImagen')
            .off('error')
            .one('error', function () {
                $('#fichaLogo').addClass('servicios-ficha-logo-respaldo');
                $(this).attr('src', LOGO_RESPALDO);
            })
            .attr({ src: institucion.logo || LOGO_RESPALDO, alt: 'Logotipo de ' + institucion.nombre });
        $('#fichaTipo').text(servicio.nombre + ' · ' + lugar.tipoSede);
        $('#fichaInstitucion').text(institucion.nombre);
        $('#fichaSucursal').text(lugar.sucursal);
        $('#fichaDireccion').text(lugar.direccion);

        let estados = '<span class="servicios-estado ' + (estado.abierto ? 'servicios-estado-abierto' : 'servicios-estado-cerrado') + '"><i class="feather icon-clock"></i> ' + estado.texto + '</span>';
        if (busqueda.servicio === 'centro_veterinario') {
            estados += lugar.urgencia
                ? '<span class="servicios-estado servicios-estado-urgencia"><i class="fas fa-ambulance"></i> Urgencias 24 h</span>'
                : '<span class="servicios-estado servicios-estado-neutro"><i class="fas fa-ambulance"></i> Sin urgencias</span>';
        }
        estados += '<span class="servicios-estado servicios-estado-neutro"><i class="feather icon-navigation"></i> ' + formatoDistancia(lugar.distancia) + '</span>';
        estados += '<span class="servicios-estado servicios-estado-neutro"><i class="fas fa-star servicios-estrella"></i> ' + lugar.calificacion.toFixed(1).replace('.', ',') + ' (' + lugar.resenas + ')</span>';
        $('#fichaEstados').html(estados);

        $('#fichaContactos').html(
            '<a class="reserva-vet-contacto" href="tel:' + lugar.telefono.replace(/\s/g, '') + '"><i class="feather icon-phone"></i> ' + escapar(lugar.telefono) + '</a>' +
            '<a class="reserva-vet-contacto" href="https://www.google.com/maps/dir/?api=1&destination=' + destino + '" target="_blank" rel="noopener"><i class="feather icon-navigation"></i> Cómo llegar</a>'
        );

        // En una clínica con urgencias se avisa antes de agendar
        if (busqueda.servicio === 'centro_veterinario' && lugar.urgencia) {
            $('#fichaUrgencia').html(
                '<div class="servicios-urgencia servicios-bloque">' +
                    '<i class="fas fa-ambulance"></i>' +
                    '<div><strong>Atiende urgencias las 24 horas</strong><span>Si su mascota está grave, no agende: llame o diríjase directamente.</span></div>' +
                '</div>'
            );
        } else {
            $('#fichaUrgencia').empty();
        }

        $('#fichaHorario').html(filasHorario(lugar));

        $('#fichaServicios').html(lugar.servicios.map(function (s) {
            return '<span class="badge ' + (s === busqueda.servicio ? 'badge-info' : 'badge-purple-light') + ' mr-1 mb-1"><i class="' + SERVICIOS[s].icono + ' mr-1"></i>' + SERVICIOS[s].nombre + '</span>';
        }).join(''));

        dibujarRed(lugar);

        $('#btnAccionLugar').html('<i class="' + servicio.accionIcono + '"></i> <span>' + servicio.accion + '</span>');

        $('#fichaVacia').addClass('d-none');
        $('#fichaContenido').removeClass('d-none');
        $('#fichaLugar .servicios-ficha-cuerpo').scrollTop(0);

        // Destello corto para que se note que cambió el lugar
        const ficha = document.getElementById('fichaLugar');
        ficha.classList.remove('servicios-ficha-cambio');
        void ficha.offsetWidth;
        ficha.classList.add('servicios-ficha-cambio');
    }

    // Otras sucursales de la misma institución, en pequeño
    function dibujarRed(lugar) {
        const otras = INSTITUCIONES[lugar.institucion].red.filter(function (s) { return s.lugar !== lugar.id; });

        if (!otras.length) {
            $('#fichaRed').html('<span class="servicios-etiqueta"><i class="feather icon-share-2"></i> Sucursales en el país</span><small class="text-muted">Esta institución tiene un solo local.</small>');
            return;
        }

        let html = '<span class="servicios-etiqueta"><i class="feather icon-share-2"></i> Otras sucursales en el país (' + otras.length + ')</span><ul class="servicios-red">';

        otras.forEach(function (sucursal, indice) {
            const enResultados = sucursal.lugar && busqueda.resultados.some(function (l) { return l.id === sucursal.lugar; });
            html += '<li class="' + (indice > 2 ? 'd-none servicios-red-extra' : '') + '">' +
                '<i class="feather icon-map-pin"></i> <span>' + escapar(sucursal.nombre) + ' · ' + escapar(sucursal.ciudad) + '</span>' +
                (enResultados ? ' <button type="button" class="servicios-red-ver" data-lugar="' + sucursal.lugar + '">Ver en el mapa</button>' : '') +
            '</li>';
        });

        html += '</ul>';
        if (otras.length > 3) {
            html += '<button type="button" class="servicios-red-mas" id="btnVerRed">Ver ' + (otras.length - 3) + ' más</button>';
        }

        $('#fichaRed').html(html);
    }

    /* ---------- Tarjeta de acción ---------- */

    function prepararAccion(lugar) {
        const servicio = SERVICIOS[busqueda.servicio];
        const institucion = INSTITUCIONES[lugar.institucion];

        detenerSeguimiento();

        $('#accionIcono').html('<i class="' + servicio.accionIcono + '"></i>')
            .toggleClass('servicios-icono-reserva', servicio.flujo === 'agenda');
        $('#accionTitulo').text(servicio.accion);
        $('#accionLugarNombre').text(institucion.nombre + ' · ' + lugar.sucursal);
        actualizarMascotaAccion();

        if (servicio.flujo === 'agenda') {
            const subtitulos = {
                centro_veterinario: 'Elija el motivo, el veterinario y la hora que le acomode.',
                peluqueria: 'Elija el servicio, el día y la hora para su mascota.',
                hotel_mascotas: 'Indique las fechas de la estadía y el tipo de alojamiento.'
            };
            $('#flujoTienda').addClass('d-none');
            $('#flujoAgenda').removeClass('d-none');
            $('#accionSubtitulo').text(subtitulos[busqueda.servicio]);
            iniciarAgenda(lugar);
        } else {
            $('#flujoAgenda').addClass('d-none');
            $('#flujoTienda').removeClass('d-none');
            $('#accionSubtitulo').text('Busque el producto, agréguelo al pedido y elija cómo recibirlo.');
            iniciarTienda(lugar);
        }

        // Queda lista pero oculta: aparece al presionar el botón de la ficha
        $('#servicioAccion').addClass('d-none');
    }

    function abrirAccion() {
        const $boton = $('#btnAccionLugar');
        const original = $boton.html();

        if ($boton.prop('disabled') || !busqueda.seleccionado) {
            return;
        }

        $boton.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> <span>Abriendo...</span>');

        setTimeout(function () {
            $boton.prop('disabled', false).html(original);

            // Se reinicia la animación de entrada aunque la tarjeta ya estuviera abierta
            const tarjeta = document.getElementById('tarjetaAccion');
            tarjeta.classList.remove('servicios-aparece');
            $('#servicioAccion').removeClass('d-none');
            void tarjeta.offsetWidth;
            tarjeta.classList.add('servicios-aparece');

            desplazarA(tarjeta);
        }, 450);
    }

    function actualizarMascotaAccion() {
        const mascota = mascotaActual();
        const esTienda = SERVICIOS[busqueda.servicio] && SERVICIOS[busqueda.servicio].flujo === 'tienda';

        $('#accionMascotaFoto').attr('src', mascota ? mascota.foto : DEFAULT_PET_PHOTO);
        $('#accionMascotaEtiqueta').text(esTienda ? 'Pedido para' : 'Reservando para');
        $('#accionMascotaNombre').text(mascota ? mascota.nombre : 'Seleccione la mascota en el buscador');
    }

    /* ---------- Agenda por pasos ---------- */

    // En peluquería se suma el paso "Peluquero/a" solo si el lugar lo habilita y hay más de una persona
    function pasosAgenda(lugar) {
        const pasos = FLUJOS[busqueda.servicio].slice();
        if (busqueda.servicio === 'peluqueria' && lugar.eligePeluquero && (PELUQUEROS[lugar.id] || []).length > 1) {
            pasos.splice(1, 0, { panel: 'peluquero', texto: 'Peluquero/a' });
        }
        return pasos;
    }

    // Personas que pueden atender según el servicio buscado
    function equipoAgenda() {
        return busqueda.servicio === 'peluqueria' ? (PELUQUEROS[agenda.lugar.id] || []) : (agenda.lugar.equipo || []);
    }

    function inicialesDe(nombre) {
        return nombre.replace(/^Dra?\.\s*/, '').split(' ').map(function (p) { return p.charAt(0); }).join('').substring(0, 2);
    }

    function iniciarAgenda(lugar) {
        agenda = {
            lugar: lugar,
            flujo: pasosAgenda(lugar),
            actual: 0,
            alcanzado: 0,
            area: '',
            prestacion: '',
            profesional: null,
            servicio: null,
            fecha: '',
            salida: '',
            hora: '',
            fechaHora: '',
            medioPago: ''
        };

        $('#resumenComentario').val('');
        $('input[name="medioPago"]').prop('checked', false);

        if (busqueda.servicio === 'centro_veterinario') {
            llenarAreas();
            dibujarProfesionales();
        } else if (busqueda.servicio === 'peluqueria') {
            $('#peluqueriaTraslado').prop('checked', false);
            $('#peluqueriaEquipo').empty();
            dibujarServiciosPeluqueria();
        } else {
            $('#hotelIngreso, #hotelSalida').val('').trigger('change');
            dibujarAlojamientos();
        }

        irPanel(0);
    }

    function dibujarPasos() {
        let html = '';

        agenda.flujo.forEach(function (paso, indice) {
            const completado = indice < agenda.alcanzado && indice !== agenda.actual;
            const disponible = indice <= agenda.alcanzado;
            html += '<li class="reserva-vet-paso' + (indice === agenda.actual ? ' activo' : '') + (completado ? ' completado' : '') + '">' +
                '<button type="button" class="reserva-vet-paso-boton" data-ir="' + indice + '"' + (disponible ? '' : ' disabled') + (indice === agenda.actual ? ' aria-current="step"' : '') + '>' +
                    '<span class="reserva-vet-paso-numero">' + (completado ? '<i class="feather icon-check"></i>' : indice + 1) + '</span>' +
                    '<span class="reserva-vet-paso-texto">' + paso.texto + '</span>' +
                '</button>' +
            '</li>';
        });

        $('#agendaPasos').html(html).removeClass('d-none');
    }

    function irPanel(indice) {
        if (indice < 0 || indice >= agenda.flujo.length || indice > agenda.alcanzado) {
            return;
        }

        agenda.actual = indice;
        const panel = agenda.flujo[indice].panel;

        if (panel === 'peluquero') {
            dibujarPeluqueros();
        }
        if (panel === 'fecha') {
            dibujarCalendario();
        }
        if (panel === 'pago') {
            dibujarPago();
        }
        if (panel === 'confirmar') {
            dibujarResumen();
        }

        $('#flujoAgenda .reserva-vet-panel').removeClass('activo')
            .filter('[data-panel="' + panel + '"]').addClass('activo');
        dibujarPasos();
    }

    function avanzar() {
        const error = validarPanel(agenda.flujo[agenda.actual].panel);

        if (error) {
            mostrarMensaje('Falta un dato', error, 'warning');
            return;
        }

        agenda.alcanzado = Math.max(agenda.alcanzado, agenda.actual + 1);
        irPanel(agenda.actual + 1);
        desplazarA(document.getElementById('tarjetaAccion'));
    }

    function validarPanel(panel) {
        if (panel === 'motivo') {
            agenda.area = $('#vetArea').val();
            agenda.prestacion = $('#vetPrestacion').val();
            if (!agenda.area || !agenda.prestacion) {
                return 'Seleccione el área y la prestación.';
            }
        }

        if (panel === 'veterinario' && !agenda.profesional) {
            return 'Seleccione un veterinario o la opción de primera hora disponible.';
        }

        if (panel === 'peluquero' && !agenda.profesional) {
            return 'Seleccione quién atenderá a su mascota o la opción sin preferencia.';
        }

        if (panel === 'pago' && !agenda.medioPago) {
            return 'Seleccione cómo quiere pagar.';
        }

        if (panel === 'servicio' && !agenda.servicio) {
            return 'Seleccione el servicio de peluquería.';
        }

        if (panel === 'estadia') {
            const ingreso = $('#hotelIngreso').val();
            const salida = $('#hotelSalida').val();
            if (!ingreso || !salida) {
                return 'Indique la fecha de ingreso y la de salida.';
            }
            if (desdeIso(salida) <= desdeIso(ingreso)) {
                return 'La salida debe ser al menos un día después del ingreso.';
            }
            if (!agenda.servicio) {
                return 'Seleccione el tipo de alojamiento.';
            }
            agenda.fecha = ingreso;
            agenda.salida = salida;
        }

        return '';
    }

    function llenarAreas() {
        const $area = $('#vetArea').html('<option value="">Seleccione</option>');
        CATALOGO_VETERINARIO.forEach(function (area) {
            $area.append($('<option>').val(area.slug).text(area.nombre));
        });
        $('#vetPrestacion').html('<option value="">Seleccione</option>');
        $('#vetPrestacionEtiqueta').text('Prestación');
    }

    function llenarPrestaciones() {
        const area = CATALOGO_VETERINARIO.find(function (a) { return a.slug === $('#vetArea').val(); });
        const $prestacion = $('#vetPrestacion').html('<option value="">Seleccione</option>');

        $('#vetPrestacionEtiqueta').text(area && area.label_item ? area.label_item : 'Prestación');
        if (!area) {
            return;
        }
        (area.items || []).forEach(function (item) {
            $prestacion.append($('<option>').val(item).text(item));
        });
    }

    function dibujarProfesionales() {
        let html = tarjetaOpcion({
            grupo: 'profesional',
            valor: 'cualquiera',
            icono: 'fas fa-bolt',
            nombre: 'Primera hora disponible',
            detalle: 'Con cualquier veterinario del lugar'
        });

        (agenda.lugar.equipo || []).forEach(function (persona, indice) {
            const proxima = proximaHora(agenda.lugar, 30, persona.nombre);
            html += tarjetaOpcion({
                grupo: 'profesional',
                valor: String(indice),
                iniciales: inicialesDe(persona.nombre),
                nombre: persona.nombre,
                detalle: persona.cargo + (proxima ? ' · Próxima hora ' + proxima : '')
            });
        });

        $('#vetProfesionales').html(html);
    }

    // La próxima hora de cada persona depende de la duración del servicio elegido
    function dibujarPeluqueros() {
        const duracion = duracionAgenda();
        let html = tarjetaOpcion({
            grupo: 'peluquero',
            valor: 'cualquiera',
            icono: 'fas fa-bolt',
            nombre: 'Sin preferencia',
            detalle: 'Primera hora disponible con cualquiera del equipo'
        });

        equipoAgenda().forEach(function (persona, indice) {
            const proxima = proximaHora(agenda.lugar, duracion, persona.nombre);
            html += tarjetaOpcion({
                grupo: 'peluquero',
                valor: String(indice),
                iniciales: inicialesDe(persona.nombre),
                nombre: persona.nombre,
                detalle: persona.cargo + (proxima ? ' · Próxima hora ' + proxima : '')
            });
        });

        $('#peluqueriaEquipo').html(html);
        $('#peluqueroSubtitulo').text('Para ' + agenda.servicio.nombre.toLowerCase() + '. Si prefiere a alguien del equipo, elíjalo; si no, tome la primera hora disponible.');

        if (agenda.profesional) {
            const valor = agenda.profesional.cualquiera ? 'cualquiera' : String(agenda.profesional.indice);
            $('#peluqueriaEquipo input[value="' + valor + '"]').prop('checked', true);
        }
    }

    function dibujarServiciosPeluqueria() {
        const factor = parseFloat($('#peluqueriaTamano').val()) || 1;
        let html = '';

        SERVICIOS_PELUQUERIA.forEach(function (servicio) {
            const precio = servicio.id === 'unas' ? servicio.precio : servicio.precio * factor;
            html += tarjetaOpcion({
                grupo: 'peluqueria',
                valor: servicio.id,
                imagen: servicio.imagen || IMAGEN_SERVICIO_RESPALDO,
                nombre: servicio.nombre,
                detalle: servicio.detalle,
                precio: formatoPeso(precio)
            });
        });

        $('#peluqueriaServicios').html(html);
        if (agenda.servicio) {
            $('#peluqueriaServicios input[value="' + agenda.servicio.id + '"]').prop('checked', true);
        }
    }

    function dibujarAlojamientos() {
        let html = '';
        ALOJAMIENTOS.forEach(function (alojamiento) {
            html += tarjetaOpcion({
                grupo: 'alojamiento',
                valor: alojamiento.id,
                imagen: alojamiento.imagen || IMAGEN_SERVICIO_RESPALDO,
                nombre: alojamiento.nombre,
                detalle: alojamiento.detalle,
                precio: formatoPeso(alojamiento.precio) + ' / noche'
            });
        });
        $('#hotelAlojamientos').html(html);
    }

    // Tarjeta seleccionable con el mismo aspecto que los lugares del agendamiento.
    // A la izquierda va la imagen del servicio, las iniciales del veterinario o un ícono
    function tarjetaOpcion(opcion) {
        const id = 'opcion_' + opcion.grupo + '_' + opcion.valor;
        let visual = '<span class="reserva-vet-lugar-icono"><i class="' + opcion.icono + '"></i></span>';

        if (opcion.imagen) {
            visual = '<span class="servicios-opcion-imagen">' +
                '<img src="' + escapar(opcion.imagen) + '" alt="Imagen de ' + escapar(opcion.nombre) + '" loading="lazy"' +
                    ' onerror="this.onerror=null;this.src=IMAGEN_SERVICIO_RESPALDO;">' +
            '</span>';
        } else if (opcion.iniciales) {
            visual = '<span class="reserva-vet-lugar-icono servicios-iniciales">' + escapar(opcion.iniciales) + '</span>';
        }

        const precio = opcion.precio ? '<span class="servicios-precio">' + opcion.precio + '</span>' : '';

        return '<li class="reserva-vet-lugar">' +
            '<input class="reserva-vet-lugar-radio" type="radio" name="opcion_' + opcion.grupo + '" id="' + id + '" value="' + opcion.valor + '">' +
            '<label class="reserva-vet-lugar-caja mb-0' + (opcion.imagen ? ' servicios-opcion-con-imagen' : '') + '" for="' + id + '">' +
                visual +
                '<span class="reserva-vet-lugar-datos">' +
                    '<span class="reserva-vet-lugar-nombre">' + escapar(opcion.nombre) + '</span>' +
                    '<span class="reserva-vet-lugar-direccion">' + escapar(opcion.detalle) + '</span>' +
                    (opcion.imagen ? precio : '') +
                '</span>' +
                (opcion.imagen ? '' : precio) +
                '<span class="reserva-vet-lugar-marca" aria-hidden="true"><i class="feather icon-check"></i></span>' +
            '</label>' +
        '</li>';
    }

    function duracionAgenda() {
        return busqueda.servicio === 'peluqueria' && agenda.servicio ? agenda.servicio.duracion : 30;
    }

    // Horas simuladas: cada 30 minutos dentro del horario, con algunas ya tomadas
    function horasDelDia(lugar, iso, duracion, clave) {
        const fecha = desdeIso(iso);
        const ahora = new Date();
        const minimo = fechaIso(ahora) === iso ? ahora.getHours() * 60 + ahora.getMinutes() + 30 : 0;
        const horas = [];
        let rangos = HORARIOS[lugar.horario][fecha.getDay()] || [];

        // En los lugares de 24 horas la agenda de controles va de 09:00 a 21:00
        if (lugar.horario === 'continuo') {
            rangos = [['09:00', '21:00']];
        }

        rangos.forEach(function (rango) {
            for (let m = aMinutos(rango[0]); m + duracion <= aMinutos(rango[1]); m += 30) {
                if (m < minimo || semilla(lugar.id + iso + m + (clave || '')) % 3 === 0) {
                    continue;
                }
                horas.push(aHora(m));
            }
        });

        return horas;
    }

    function proximaHora(lugar, duracion, clave) {
        const hoy = new Date();
        for (let d = 0; d < 14; d++) {
            const dia = new Date(hoy.getFullYear(), hoy.getMonth(), hoy.getDate() + d);
            const horas = horasDelDia(lugar, fechaIso(dia), duracion, clave);
            if (horas.length) {
                const cuando = d === 0 ? 'hoy' : (d === 1 ? 'mañana' : DIAS_CORTOS[dia.getDay()].toLowerCase() + ' ' + dia.getDate());
                return cuando + ' ' + horas[0];
            }
        }
        return '';
    }

    function claveProfesional() {
        return agenda.profesional && agenda.profesional.nombre ? agenda.profesional.nombre : '';
    }

    // flatpickr lo trae selector_fechas.js; si todavía no llega se carga acá
    function conCalendario(listo) {
        if (window.flatpickr) {
            listo();
            return;
        }

        if (!document.querySelector('link[href*="flatpickr"]')) {
            const estilos = document.createElement('link');
            estilos.rel = 'stylesheet';
            estilos.href = @json(asset('css/flatpickr/flatpickr.min.css'));
            document.head.insertBefore(estilos, document.head.firstChild);
        }

        const script = document.createElement('script');
        script.src = @json(asset('js/flatpickr/flatpickr.min.js'));
        script.onload = listo;
        document.head.appendChild(script);
    }

    // Mismo calendario de la reserva de hora: solo se pueden elegir los días que tienen horas libres
    function dibujarCalendario() {
        const lugar = agenda.lugar;
        const duracion = duracionAgenda();
        const clave = claveProfesional();
        const tieneHoras = function (fecha) {
            return horasDelDia(lugar, fechaIso(fecha), duracion, clave).length > 0;
        };
        const fechaPrevia = agenda.fecha && tieneHoras(desdeIso(agenda.fecha)) ? agenda.fecha : null;

        conCalendario(function () {
            if (calendarioAgenda) {
                calendarioAgenda.destroy();
            }

            calendarioAgenda = flatpickr('#agendaCalendario', {
                inline: true,
                dateFormat: 'Y-m-d',
                minDate: 'today',
                maxDate: new Date().fp_incr(60),
                defaultDate: fechaPrevia,
                disable: [function (fecha) { return !tieneHoras(fecha); }],
                locale: CALENDARIO_ESPANOL,
                onChange: function (fechas, texto) {
                    elegirDia(texto);
                }
            });

            elegirDia(fechaPrevia);
        });
    }

    function elegirDia(iso) {
        if (!iso) {
            agenda.fecha = '';
            $('#agendaFechaTexto').text('Primero elija una fecha.');
            $('#agendaTotalHoras').addClass('d-none').text('');
            $('#agendaHoras').html(avisoHoras('Las horas aparecerán aquí cuando elija un día en el calendario.'));
            return;
        }

        agenda.fecha = iso;
        const horas = horasDelDia(agenda.lugar, iso, duracionAgenda(), claveProfesional());
        const manana = horas.filter(function (h) { return aMinutos(h) < 12 * 60; });
        const tarde = horas.filter(function (h) { return aMinutos(h) >= 12 * 60; });
        let html = '';

        if (manana.length) {
            html += bloqueHoras('Mañana', 'feather icon-sunrise', manana);
        }
        if (tarde.length) {
            html += bloqueHoras('Tarde', 'feather icon-sunset', tarde);
        }

        $('#agendaFechaTexto').text(textoFecha(iso));
        $('#agendaTotalHoras').text(horas.length + (horas.length === 1 ? ' hora' : ' horas')).removeClass('d-none');
        $('#agendaHoras').html(html || avisoHoras('No quedan horas para este día.'));

        // En celular las horas quedan bajo el calendario: se acercan a la vista
        const caja = document.getElementById('agendaHorasCaja');
        if (window.innerWidth < 992 && caja && caja.scrollIntoView) {
            const sinAnimacion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            caja.scrollIntoView({ behavior: sinAnimacion ? 'auto' : 'smooth', block: 'nearest' });
        }
    }

    function bloqueHoras(titulo, icono, horas) {
        return '<div class="reserva-vet-horas-bloque">' +
            '<div class="reserva-vet-horas-bloque-titulo"><i class="' + icono + '"></i>' + titulo + '</div>' +
            '<div class="reserva-vet-horas-grilla">' +
                horas.map(function (h) {
                    const elegida = agenda.hora === h && agenda.fechaHora === agenda.fecha;
                    return '<button type="button" class="reserva-vet-hora' + (elegida ? ' active' : '') + '" data-hora="' + h + '" aria-pressed="' + (elegida ? 'true' : 'false') + '">' + h + '</button>';
                }).join('') +
            '</div>' +
        '</div>';
    }

    function avisoHoras(texto) {
        return '<div class="reserva-vet-horas-vacio"><i class="feather icon-clock"></i><span>' + texto + '</span></div>';
    }

    function elegirHora(hora) {
        agenda.hora = hora;
        agenda.fechaHora = agenda.fecha;

        // Con "primera hora disponible" se asigna un veterinario del lugar
        if (agenda.profesional && agenda.profesional.cualquiera) {
            const equipo = equipoAgenda();
            const deTurno = busqueda.servicio === 'peluqueria' ? 'Peluquero/a de turno' : 'Veterinario de turno';
            agenda.profesional.asignado = equipo.length ? equipo[semilla(agenda.fecha + hora) % equipo.length].nombre : deTurno;
        }

        $('#agendaHoras .reserva-vet-hora').removeClass('active').attr('aria-pressed', 'false')
            .filter('[data-hora="' + hora + '"]').addClass('active').attr('aria-pressed', 'true');

        agenda.alcanzado = Math.max(agenda.alcanzado, agenda.actual + 1);
        setTimeout(function () { irPanel(agenda.actual + 1); }, 180);
    }

    function filaDetalle(icono, titulo, valor) {
        return '<li><span class="reserva-vet-detalle-icono"><i class="' + icono + '"></i></span>' +
            '<div><span class="reserva-vet-detalle-titulo">' + titulo + '</span>' +
            '<span class="reserva-vet-detalle-valor">' + escapar(valor) + '</span></div></li>';
    }

    function nochesEstadia() {
        return Math.round((desdeIso(agenda.salida) - desdeIso(agenda.fecha)) / 86400000);
    }

    // Valor de la reserva y una línea que explica de dónde sale
    function totalAgenda() {
        if (busqueda.servicio === 'hotel_mascotas') {
            const noches = nochesEstadia();
            return {
                total: noches * agenda.servicio.precio,
                detalle: agenda.servicio.nombre + ' · ' + noches + (noches === 1 ? ' noche' : ' noches')
            };
        }

        if (busqueda.servicio === 'centro_veterinario') {
            return { total: VALORES_VETERINARIOS[agenda.area] || 25000, detalle: agenda.prestacion };
        }

        const factor = agenda.servicio.id === 'unas' ? 1 : (parseFloat($('#peluqueriaTamano').val()) || 1);
        const traslado = $('#peluqueriaTraslado').is(':checked');
        return {
            total: agenda.servicio.precio * factor + (traslado ? 4990 : 0),
            detalle: agenda.servicio.nombre + (traslado ? ' + retiro y entrega a domicilio' : '')
        };
    }

    // El pago en el lugar aparece solo si el administrador del lugar lo habilitó
    function dibujarPago() {
        const valor = totalAgenda();
        const permiteLugar = Boolean(agenda.lugar.pagoEnLugar);

        if (!permiteLugar && agenda.medioPago === 'lugar') {
            agenda.medioPago = '';
        }

        $('#opcionPagoLugar').toggleClass('d-none', !permiteLugar);
        $('#avisoSoloEnLinea').toggleClass('d-none', permiteLugar);
        $('input[name="medioPago"]').prop('checked', false)
            .filter('[value="' + agenda.medioPago + '"]').prop('checked', true);

        $('#pagoTotal').text(formatoPeso(valor.total));
        $('#pagoDetalle').text(valor.detalle);
    }

    function dibujarResumen() {
        const lugar = agenda.lugar;
        const mascota = mascotaActual();
        const valor = totalAgenda();
        const enLinea = agenda.medioPago === 'webpay';
        let detalle = filaDetalle('fas fa-paw', 'Mascota', mascota ? mascota.nombre : 'Sin seleccionar');

        if (busqueda.servicio === 'hotel_mascotas') {
            const noches = nochesEstadia();
            $('#resumenFecha').text(textoFecha(agenda.fecha) + ' al ' + textoFecha(agenda.salida).toLowerCase());
            $('#resumenHora').text(noches + (noches === 1 ? ' noche' : ' noches') + ' · ingreso desde las 10:00');
            detalle += filaDetalle('fas fa-bed', 'Alojamiento', agenda.servicio.nombre);
        } else {
            $('#resumenFecha').text(textoFecha(agenda.fecha));
            $('#resumenHora').text(agenda.hora + ' hrs.');

            if (busqueda.servicio === 'centro_veterinario') {
                detalle += filaDetalle('feather icon-user', 'Veterinario', agenda.profesional.cualquiera ? agenda.profesional.asignado : agenda.profesional.nombre);
                detalle += filaDetalle('feather icon-heart', 'Prestación', agenda.prestacion);
            } else {
                detalle += filaDetalle('fas fa-cut', 'Servicio', agenda.servicio.nombre + ' · ' + $('#peluqueriaTamano option:selected').text());
                if (agenda.profesional) {
                    detalle += filaDetalle('feather icon-user', 'Lo atiende', agenda.profesional.cualquiera ? agenda.profesional.asignado : agenda.profesional.nombre);
                }
                if ($('#peluqueriaTraslado').is(':checked')) {
                    detalle += filaDetalle('fas fa-car', 'Traslado', 'Retiro y entrega a domicilio de la mascota');
                }
            }
        }

        detalle += filaDetalle('feather icon-map-pin', 'Lugar', INSTITUCIONES[lugar.institucion].nombre + ' · ' + lugar.sucursal + ' — ' + lugar.direccion);
        detalle += filaDetalle(MEDIOS_PAGO[agenda.medioPago].icono, 'Medio de pago', MEDIOS_PAGO[agenda.medioPago].nombre);

        $('#resumenDetalle').html(detalle);
        $('#resumenTotal').text(formatoPeso(valor.total));
        $('#resumenNota').text(enLinea
            ? 'Al confirmar pasará a Webpay para pagar. La hora queda reservada cuando el pago se apruebe.'
            : 'Se paga en el lugar el día de la atención.');
        $('#btnConfirmarAgenda').prop('disabled', false).html(enLinea
            ? '<i class="feather icon-credit-card"></i> Ir a pagar ' + formatoPeso(valor.total)
            : '<i class="feather icon-check-circle"></i> Confirmar reserva');
    }

    function confirmarAgenda() {
        const mascota = mascotaActual();
        if (!mascota) {
            mostrarMensaje('Falta la mascota', 'Seleccione la mascota en el buscador de arriba antes de confirmar.', 'warning');
            return;
        }

        if (agenda.medioPago !== 'webpay') {
            mostrarReservaLista(mascota, false);
            return;
        }

        // Simula el paso por la pasarela; acá irá la redirección real a Webpay
        $('#btnConfirmarAgenda').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Conectando con Webpay...');
        setTimeout(function () {
            mostrarReservaLista(mascota, true);
        }, 1600);
    }

    function mostrarReservaLista(mascota, pagado) {
        const lugar = agenda.lugar;
        const institucion = INSTITUCIONES[lugar.institucion].nombre;
        const total = formatoPeso(totalAgenda().total);
        let titulo = '¡Hora agendada!';
        const atiende = agenda.profesional ? (agenda.profesional.cualquiera ? agenda.profesional.asignado : agenda.profesional.nombre) : '';
        let texto = mascota.nombre + ' tiene hora el ' + textoFecha(agenda.fecha).toLowerCase() + ' a las ' + agenda.hora +
            (atiende ? ' con ' + atiende : '') + ' en ' + institucion + ' (' + lugar.sucursal + ').';

        if (busqueda.servicio === 'hotel_mascotas') {
            titulo = '¡Estadía reservada!';
            texto = mascota.nombre + ' tiene reserva desde el ' + textoFecha(agenda.fecha).toLowerCase() + ' hasta el ' + textoFecha(agenda.salida).toLowerCase() + ' en ' + institucion + '.';
        }

        texto += pagado
            ? ' Pago de ' + total + ' aprobado por Webpay (código de autorización ' + (100000 + semilla(agenda.fecha + agenda.hora) % 899999) + ').'
            : ' Pagará ' + total + ' en el lugar.';

        $('#exitoTitulo').text(titulo);
        $('#exitoTexto').text(texto + ' Le enviaremos un recordatorio por correo.');
        $('#agendaPasos').addClass('d-none');
        $('#flujoAgenda .reserva-vet-panel').removeClass('activo').filter('[data-panel="listo"]').addClass('activo');
    }

    /* ---------- Tienda: alimentos, farmacia y pet shop ---------- */

    function stockProducto(lugar, producto) {
        const valor = semilla(lugar.id + producto.id) % 7;
        if (valor === 0) {
            return { hay: false, texto: 'Sin stock en este local' };
        }
        if (valor === 1) {
            return { hay: true, pocas: true, texto: 'Últimas unidades' };
        }
        return { hay: true, texto: 'Disponible' };
    }

    function productosDelServicio() {
        return PRODUCTOS.filter(function (p) { return p.tipo === busqueda.servicio; });
    }

    function iniciarTienda(lugar) {
        const etiquetas = {
            alimentos: '¿Qué alimento necesita?',
            farmacia: '¿Qué medicamento necesita?',
            pet_shop: '¿Qué producto busca?'
        };
        const ejemplos = {
            alimentos: 'Escriba el nombre o la marca, ej. Pro Plan',
            farmacia: 'Escriba el medicamento, ej. Bravecto',
            pet_shop: 'Escriba el producto, ej. arena'
        };
        const minutos = 25 + Math.round(lugar.distancia * 3);

        pedido = [];
        productoElegido = null;
        $('#tiendaEtiquetaBuscar').text(etiquetas[busqueda.servicio]);
        $('#tiendaBuscar').val('').attr('placeholder', ejemplos[busqueda.servicio]);
        cerrarSugerencias();
        $('#entregaAhora').prop('checked', true);
        $('#tiendaFecha').val('').trigger('change');
        $('#tiendaFranja').val('');
        $('#tiendaMensual').prop('checked', false);
        $('#tiendaCompra').removeClass('d-none');
        $('#tiendaSeguimiento').addClass('d-none').empty();
        $('#entregaAhoraTexto').text('Llega en ' + minutos + ' a ' + (minutos + 15) + ' min · seguimiento en tiempo real');

        // Atajos con lo que hay en stock en este local
        const atajos = productosDelServicio().filter(function (p) { return stockProducto(lugar, p).hay; }).slice(0, 4);
        $('#tiendaAtajos').html(atajos.length
            ? '<span>Lo más pedido aquí:</span>' + atajos.map(function (p) {
                return '<button type="button" class="servicios-atajo" data-producto="' + p.id + '">' + escapar(p.nombre) + '</button>';
            }).join('')
            : '');

        dibujarProductoElegido();
        cambiarEntrega();
    }

    function abrirSugerencias() {
        const lugar = busqueda.seleccionado;
        const escrito = $.trim($('#tiendaBuscar').val());
        const texto = escrito.toLowerCase();

        if (texto.length < 2 || (productoElegido && escrito === productoElegido.nombre)) {
            cerrarSugerencias();
            return;
        }

        const coincidencias = productosDelServicio().filter(function (p) {
            return (p.nombre + ' ' + p.detalle).toLowerCase().indexOf(texto) !== -1;
        });

        let html = '';
        if (!coincidencias.length) {
            html = '<li class="servicios-sugerencias-vacio"><i class="feather icon-alert-circle"></i> No encontramos "' + escapar(escrito) + '" en este local. Pruebe con otra marca o nombre.</li>';
        }

        coincidencias.forEach(function (p) {
            const stock = stockProducto(lugar, p);
            html += '<li role="option">' +
                '<button type="button" class="servicios-sugerencia" data-producto="' + p.id + '">' +
                    '<span class="servicios-sugerencia-icono"><i class="' + p.icono + '"></i></span>' +
                    '<span class="servicios-sugerencia-datos"><strong>' + resaltar(p.nombre, texto) + '</strong><small>' + escapar(p.detalle) + '</small></span>' +
                    '<span class="servicios-sugerencia-precio">' + formatoPeso(p.precio) +
                        '<small class="' + (stock.hay ? (stock.pocas ? 'text-warning' : 'text-success') : 'text-danger') + '">' + stock.texto + '</small>' +
                    '</span>' +
                '</button>' +
            '</li>';
        });

        $('#tiendaSugerencias').html(html).removeClass('d-none');
        $('#tiendaBuscar').attr('aria-expanded', 'true');
    }

    function resaltar(nombre, texto) {
        const posicion = nombre.toLowerCase().indexOf(texto);
        if (posicion === -1) {
            return escapar(nombre);
        }
        return escapar(nombre.substring(0, posicion)) + '<mark>' + escapar(nombre.substr(posicion, texto.length)) + '</mark>' + escapar(nombre.substring(posicion + texto.length));
    }

    function cerrarSugerencias() {
        $('#tiendaSugerencias').addClass('d-none').empty();
        $('#tiendaBuscar').attr('aria-expanded', 'false');
    }

    function elegirProducto(id) {
        productoElegido = PRODUCTOS.find(function (p) { return p.id === id; }) || null;
        $('#tiendaBuscar').val(productoElegido ? productoElegido.nombre : '');
        cerrarSugerencias();
        dibujarProductoElegido();
    }

    // Otro lugar de la búsqueda que sí tenga el producto
    function alternativaCercana(producto) {
        return busqueda.resultados.find(function (l) {
            return l.id !== busqueda.seleccionado.id && stockProducto(l, producto).hay;
        }) || null;
    }

    function dibujarProductoElegido() {
        const $contenedor = $('#tiendaProducto');

        if (!productoElegido) {
            $contenedor.html(
                '<div class="servicios-vacio servicios-producto-vacio">' +
                    '<i class="feather icon-package"></i>' +
                    '<p>Busque un producto</p>' +
                    '<small>Escriba al menos dos letras y elija una opción de la lista para agregarla al pedido.</small>' +
                '</div>'
            );
            return;
        }

        const stock = stockProducto(busqueda.seleccionado, productoElegido);
        const mascota = mascotaActual();
        const deshabilitado = stock.hay ? '' : ' disabled';
        let notas = '';

        if (productoElegido.receta) {
            const receta = mascota ? RECETAS.find(function (r) { return String(r.mascota_id) === String(mascota.id); }) : null;
            notas += '<div class="servicios-nota servicios-nota-receta"><i class="fas fa-file-prescription"></i><span>' +
                (receta
                    ? 'Requiere receta. Se adjuntará la receta del ' + escapar(receta.fecha) + ' de ' + escapar(mascota.nombre) + '.'
                    : 'Requiere receta veterinaria vigente. El local la pedirá antes de despachar.') +
            '</span></div>';
        }

        if (!stock.hay) {
            const otro = alternativaCercana(productoElegido);
            notas += '<div class="servicios-nota servicios-nota-sin-stock"><i class="feather icon-alert-triangle"></i><span>No queda stock en este local.' +
                (otro ? ' Lo tiene <button type="button" class="servicios-red-ver" data-lugar="' + otro.id + '">' + escapar(INSTITUCIONES[otro.institucion].nombre) + ' · ' + escapar(otro.sucursal) + '</button> a ' + formatoDistancia(otro.distancia) + '.' : '') +
            '</span></div>';
        }

        $contenedor.html(
            '<div class="servicios-producto">' +
                '<span class="servicios-producto-icono"><i class="' + productoElegido.icono + '"></i></span>' +
                '<div class="servicios-producto-datos">' +
                    '<strong>' + escapar(productoElegido.nombre) + '</strong>' +
                    '<small>' + escapar(productoElegido.detalle) + '</small>' +
                    '<span class="servicios-estado ' + (stock.hay ? (stock.pocas ? 'servicios-estado-pocas' : 'servicios-estado-abierto') : 'servicios-estado-cerrado') + '">' + stock.texto + '</span>' +
                '</div>' +
                '<div class="servicios-producto-compra">' +
                    '<span class="servicios-producto-precio">' + formatoPeso(productoElegido.precio) + '</span>' +
                    '<div class="servicios-cantidad">' +
                        '<button type="button" data-cantidad="-1" aria-label="Quitar uno"' + deshabilitado + '><i class="feather icon-minus"></i></button>' +
                        '<input type="number" id="tiendaCantidadProducto" value="1" min="1" max="20" aria-label="Cantidad"' + deshabilitado + '>' +
                        '<button type="button" data-cantidad="1" aria-label="Agregar uno"' + deshabilitado + '><i class="feather icon-plus"></i></button>' +
                    '</div>' +
                    '<button type="button" class="btn btn-info servicios-btn-principal" id="btnAgregarPedido"' + deshabilitado + '><i class="feather icon-plus"></i> Agregar</button>' +
                '</div>' +
            '</div>' + notas
        );
    }

    function agregarAlPedido() {
        if (!productoElegido) {
            return;
        }

        const cantidad = Math.max(1, Math.min(20, parseInt($('#tiendaCantidadProducto').val(), 10) || 1));
        const existente = pedido.find(function (item) { return item.producto.id === productoElegido.id; });
        let indice = pedido.length;

        if (existente) {
            existente.cantidad = Math.min(20, existente.cantidad + cantidad);
            indice = pedido.indexOf(existente);
        } else {
            pedido.push({ producto: productoElegido, cantidad: cantidad });
        }

        productoElegido = null;
        $('#tiendaBuscar').val('');
        dibujarProductoElegido();
        dibujarPedido();
        $('#tiendaItems li').eq(indice).addClass('servicios-pedido-nuevo');
    }

    function costoDespacho() {
        const entrega = $('input[name="tiendaEntrega"]:checked').val();
        if (entrega === 'retiro' || !pedido.length) {
            return 0;
        }
        const distancia = busqueda.seleccionado ? busqueda.seleccionado.distancia : 0;
        return (entrega === 'ahora' ? 2990 : 1990) + Math.round(distancia * 30) * 10;
    }

    function dibujarPedido() {
        const cantidad = pedido.reduce(function (s, i) { return s + i.cantidad; }, 0);
        const subtotal = pedido.reduce(function (s, i) { return s + i.cantidad * i.producto.precio; }, 0);
        const despacho = costoDespacho();

        $('#tiendaCantidad').text(cantidad + (cantidad === 1 ? ' producto' : ' productos'));

        if (!pedido.length) {
            $('#tiendaItems').html('<li class="servicios-pedido-vacio"><i class="feather icon-shopping-cart"></i> Aún no agrega productos.</li>');
        } else {
            $('#tiendaItems').html(pedido.map(function (item, indice) {
                return '<li>' +
                    '<span class="servicios-pedido-cantidad">' + item.cantidad + '×</span>' +
                    '<span class="servicios-pedido-nombre">' + escapar(item.producto.nombre) + '<small>' + escapar(item.producto.detalle) + '</small></span>' +
                    '<strong>' + formatoPeso(item.cantidad * item.producto.precio) + '</strong>' +
                    '<button type="button" class="btn btn-danger btn-icon servicios-quitar" data-indice="' + indice + '" title="Quitar"><i class="feather icon-x"></i></button>' +
                '</li>';
            }).join(''));
        }

        $('#tiendaSubtotal').text(formatoPeso(subtotal));
        $('#tiendaDespacho').text($('#entregaRetiro').is(':checked') ? 'Gratis' : formatoPeso(despacho));
        $('#tiendaTotal').text(formatoPeso(subtotal + despacho));
    }

    function cambiarEntrega() {
        const entrega = $('input[name="tiendaEntrega"]:checked').val();
        const textos = { ahora: 'Pedir ahora', programada: 'Programar despacho', retiro: 'Confirmar retiro' };

        $('#tiendaProgramar').toggleClass('d-none', entrega !== 'programada');
        $('#tiendaDireccionGrupo').toggleClass('d-none', entrega === 'retiro');
        $('#tiendaConfirmar').html('<i class="feather icon-check-circle"></i> ' + textos[entrega]);
        dibujarPedido();
    }

    function confirmarPedido() {
        const entrega = $('input[name="tiendaEntrega"]:checked').val();

        if (!pedido.length) {
            mostrarMensaje('Pedido vacío', 'Agregue al menos un producto al pedido.', 'warning');
            return;
        }
        if (entrega !== 'retiro' && !$.trim($('#tiendaDireccion').val())) {
            mostrarMensaje('Falta la dirección', 'Indique la dirección donde quiere recibir el pedido.', 'warning');
            return;
        }
        if (entrega === 'programada' && (!$('#tiendaFecha').val() || !$('#tiendaFranja').val())) {
            mostrarMensaje('Falta programar', 'Elija el día y la franja horaria del despacho.', 'warning');
            return;
        }

        $('#tiendaCompra').addClass('d-none');
        $('#tiendaSeguimiento').removeClass('d-none');

        if (entrega === 'ahora') {
            iniciarSeguimiento();
        } else {
            mostrarPedidoListo(entrega);
        }
        desplazarA(document.getElementById('tarjetaAccion'));
    }

    function numeroPedido() {
        return 'VS-' + (10000 + semilla(String(Date.now())) % 89999);
    }

    function resumenPedidoHtml() {
        return '<ul class="servicios-pedido-lista servicios-pedido-resumen">' + pedido.map(function (item) {
            return '<li><span class="servicios-pedido-cantidad">' + item.cantidad + '×</span><span class="servicios-pedido-nombre">' + escapar(item.producto.nombre) + '</span><strong>' + formatoPeso(item.cantidad * item.producto.precio) + '</strong></li>';
        }).join('') + '</ul>' +
        ($('#tiendaMensual').is(':checked') ? '<p class="servicios-nota"><i class="feather icon-repeat"></i><span>Este pedido se repetirá cada mes. Puede pausarlo cuando quiera.</span></p>' : '');
    }

    function mostrarPedidoListo(entrega) {
        const lugar = busqueda.seleccionado;
        const programada = entrega === 'programada';
        const texto = programada
            ? 'Llegará el ' + textoFecha($('#tiendaFecha').val()).toLowerCase() + ', entre las ' + $('#tiendaFranja').val() + ' hrs., a ' + $.trim($('#tiendaDireccion').val()) + '.'
            : 'Puede retirarlo en ' + lugar.direccion + ' en 20 minutos más. Muestre el número de pedido en caja.';

        $('#tiendaSeguimiento').html(
            '<div class="servicios-exito">' +
                '<span class="servicios-exito-icono"><i class="feather ' + (programada ? 'icon-calendar' : 'icon-package') + '"></i></span>' +
                '<h5>' + (programada ? 'Despacho programado' : 'Pedido listo para retiro') + ' · ' + numeroPedido() + '</h5>' +
                '<p>' + escapar(texto) + '</p>' +
                resumenPedidoHtml() +
                '<button type="button" class="btn btn-outline-info" id="btnNuevoPedido"><i class="feather icon-plus"></i> Hacer otro pedido</button>' +
            '</div>'
        );
    }

    // Seguimiento simulado: avanza solo para mostrar cómo se vería en tiempo real
    function iniciarSeguimiento() {
        const lugar = busqueda.seleccionado;
        const llegada = new Date(Date.now() + (35 + Math.round(lugar.distancia * 3)) * 60000);
        const etapas = [
            { icono: 'feather icon-check', titulo: 'Pedido recibido', texto: 'El local confirmó su pedido.' },
            { icono: 'feather icon-package', titulo: 'Preparando', texto: 'Están armando su pedido en ' + lugar.sucursal + '.' },
            { icono: 'fas fa-motorcycle', titulo: 'En camino', texto: 'El repartidor va hacia ' + $.trim($('#tiendaDireccion').val()) + '.' },
            { icono: 'feather icon-home', titulo: 'Entregado', texto: '¡Que lo disfrute!' }
        ];

        $('#tiendaSeguimiento').html(
            '<div class="servicios-seguimiento">' +
                '<div class="servicios-seguimiento-cabecera">' +
                    '<div><span class="reserva-vet-etiqueta">Pedido ' + numeroPedido() + '</span><h5 id="seguimientoTitulo">Pedido recibido</h5></div>' +
                    '<div class="servicios-seguimiento-llegada"><span>Llega aprox.</span><strong>' + aHora(llegada.getHours() * 60 + llegada.getMinutes()) + '</strong></div>' +
                '</div>' +
                '<div class="servicios-progreso"><span id="seguimientoBarra"></span></div>' +
                '<ol class="servicios-etapas">' + etapas.map(function (e, i) {
                    return '<li data-etapa="' + i + '"><span class="servicios-etapa-icono"><i class="' + e.icono + '"></i></span><div><strong>' + e.titulo + '</strong><small>' + escapar(e.texto) + '</small></div><time></time></li>';
                }).join('') + '</ol>' +
                resumenPedidoHtml() +
                '<button type="button" class="btn btn-outline-info" id="btnNuevoPedido"><i class="feather icon-plus"></i> Hacer otro pedido</button>' +
            '</div>'
        );

        let etapa = 0;
        const marcar = function () {
            const ahora = new Date();
            const ultima = etapa === etapas.length - 1;

            $('#tiendaSeguimiento [data-etapa]').each(function () {
                const i = Number($(this).data('etapa'));
                $(this).toggleClass('hecha', i < etapa || (ultima && i === etapa)).toggleClass('actual', i === etapa && !ultima);
            });
            $('#tiendaSeguimiento [data-etapa="' + etapa + '"] time').text(aHora(ahora.getHours() * 60 + ahora.getMinutes()));
            $('#seguimientoTitulo').text(etapas[etapa].titulo);
            $('#seguimientoBarra').css('width', (etapa / (etapas.length - 1) * 100) + '%');

            if (ultima) {
                temporizadorSeguimiento = null;
                return;
            }
            etapa++;
            temporizadorSeguimiento = setTimeout(marcar, 4000);
        };
        marcar();
    }

    function detenerSeguimiento() {
        if (temporizadorSeguimiento) {
            clearTimeout(temporizadorSeguimiento);
            temporizadorSeguimiento = null;
        }
    }

    /* ---------- Ubicación ---------- */

    function fijarUbicacion(coords, nombre, origen) {
        busqueda.ubicacion = coords;
        busqueda.ubicacionNombre = nombre;
        busqueda.origen = origen;
        actualizarEstadoUbicacion();
    }

    function pedirGeolocalizacion() {
        if (!navigator.geolocation) {
            mostrarMensaje('Geolocalización', 'Su navegador no soporta geolocalización.', 'error');
            return;
        }

        if (!window.isSecureContext) {
            mostrarMensaje('Geolocalización', 'La geolocalización del navegador requiere una conexión segura (https). Use la ubicación manual.', 'error');
            return;
        }

        const $boton = $('#btnUseGeolocation').prop('disabled', true);

        navigator.geolocation.getCurrentPosition(function (posicion) {
            $boton.prop('disabled', false);
            $('#manualLocation').val('');
            fijarUbicacion([posicion.coords.latitude, posicion.coords.longitude], 'su posición actual', 'auto');
            ocultarResultados();
        }, function (error) {
            $boton.prop('disabled', false);
            let mensaje = 'No fue posible obtener su ubicación.';
            if (error && error.code === 1) {
                mensaje = 'El navegador bloqueó el permiso de ubicación. Permita el acceso para este sitio o use la ubicación manual.';
            } else if (error && error.code === 3) {
                mensaje = 'La solicitud de ubicación tardó demasiado. Intente nuevamente o use la ubicación manual.';
            }
            mostrarMensaje('Geolocalización', mensaje, 'error');
        });
    }

    /* ---------- Eventos ---------- */

    $(function () {
        if (typeof map !== 'undefined') {
            map.setView(UBICACIONES.vina_del_mar.coords, 11);
        }
        actualizarEstadoUbicacion();

        $('#btnBuscarServicio').on('click', buscar);
        $('#btnUseGeolocation').on('click', pedirGeolocalizacion);

        $('#manualLocation').on('change', function () {
            const ubicacion = UBICACIONES[$(this).val()];
            if (ubicacion) {
                fijarUbicacion(ubicacion.coords, ubicacion.nombre, 'manual');
            }
            ocultarResultados();
        });

        $('#searchCategory').on('change', ocultarResultados);

        $('#selectedPet').on('change', function () {
            actualizarMascotaAccion();
            if (productoElegido) {
                dibujarProductoElegido();
            }
        });

        // Lista de resultados y sucursales de la ficha
        $('#listaLugares').on('click', '[data-lugar]', function () {
            seleccionarLugar($(this).data('lugar'), true);
        });

        $(document).on('click', '.servicios-red-ver', function () {
            seleccionarLugar($(this).data('lugar'), true);
        });

        $('#fichaRed').on('click', '#btnVerRed', function () {
            $('#fichaRed .servicios-red-extra').removeClass('d-none');
            $(this).remove();
        });

        $('#listaLugares').on('click', '#btnAmpliarBusqueda', function () {
            busqueda.resultados = busqueda.resultados.concat(busqueda.lejanos);
            busqueda.lejanos = [];
            $('#searchSummary').text(busqueda.resultados.length + ' lugares adheridos, ordenados por distancia desde ' + busqueda.ubicacionNombre + '.');
            $('#tituloLista').text('Más cercanos primero');
            dibujarMarcadores();
            encuadrarMapa();
            dibujarLista();
        });

        $('#btnAccionLugar').on('click', abrirAccion);

        // Agenda
        $('#flujoAgenda').on('click', '[data-agenda="siguiente"]', avanzar);
        $('#flujoAgenda').on('click', '[data-agenda="anterior"]', function () {
            irPanel(agenda.actual - 1);
        });
        $('#agendaPasos').on('click', '[data-ir]', function () {
            irPanel(Number($(this).data('ir')));
        });

        $('#vetArea').on('change', llenarPrestaciones);

        // Veterinario o peluquero/a elegido; las horas libres cambian según la persona
        $('#vetProfesionales, #peluqueriaEquipo').on('change', 'input', function () {
            const valor = $(this).val();
            agenda.profesional = valor === 'cualquiera'
                ? { cualquiera: true }
                : $.extend({ indice: Number(valor) }, equipoAgenda()[Number(valor)]);
            agenda.hora = '';
        });

        $('#peluqueriaServicios').on('change', 'input', function () {
            const valor = $(this).val();
            agenda.servicio = SERVICIOS_PELUQUERIA.find(function (s) { return s.id === valor; });
            agenda.hora = '';
        });

        $('#peluqueriaTamano').on('change', dibujarServiciosPeluqueria);

        $('#hotelAlojamientos').on('change', 'input', function () {
            const valor = $(this).val();
            agenda.servicio = ALOJAMIENTOS.find(function (a) { return a.id === valor; });
        });

        $('input[name="medioPago"]').on('change', function () {
            agenda.medioPago = $(this).val();
        });

        $('#agendaHoras').on('click', '.reserva-vet-hora', function () {
            elegirHora($(this).data('hora'));
        });

        $('#btnConfirmarAgenda').on('click', confirmarAgenda);

        $('#btnAgendarOtra').on('click', function () {
            iniciarAgenda(busqueda.seleccionado);
        });

        // Tienda
        $('#tiendaBuscar').on('input focus', abrirSugerencias);

        $('#tiendaBuscar').on('keydown', function (e) {
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                $('#tiendaSugerencias .servicios-sugerencia').first().trigger('focus');
            } else if (e.key === 'Escape') {
                cerrarSugerencias();
            }
        });

        $('#tiendaSugerencias').on('keydown', '.servicios-sugerencia', function (e) {
            const $item = $(this).closest('li');
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                $item.next().find('.servicios-sugerencia').trigger('focus');
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                const $anterior = $item.prev().find('.servicios-sugerencia');
                ($anterior.length ? $anterior : $('#tiendaBuscar')).trigger('focus');
            } else if (e.key === 'Escape') {
                cerrarSugerencias();
                $('#tiendaBuscar').trigger('focus');
            }
        });

        $('#tiendaSugerencias').on('click', '.servicios-sugerencia', function () {
            elegirProducto($(this).data('producto'));
        });

        $('#tiendaAtajos').on('click', '.servicios-atajo', function () {
            elegirProducto($(this).data('producto'));
        });

        $(document).on('click', function (e) {
            if (!$(e.target).closest('.servicios-buscar-producto').length) {
                cerrarSugerencias();
            }
        });

        $('#tiendaProducto').on('click', '[data-cantidad]', function () {
            const $cantidad = $('#tiendaCantidadProducto');
            const valor = (parseInt($cantidad.val(), 10) || 1) + Number($(this).data('cantidad'));
            $cantidad.val(Math.max(1, Math.min(20, valor)));
        });

        $('#tiendaProducto').on('click', '#btnAgregarPedido', agregarAlPedido);

        $('#tiendaItems').on('click', '.servicios-quitar', function () {
            pedido.splice(Number($(this).data('indice')), 1);
            dibujarPedido();
        });

        $('input[name="tiendaEntrega"]').on('change', cambiarEntrega);
        $('#tiendaConfirmar').on('click', confirmarPedido);

        $('#tiendaSeguimiento').on('click', '#btnNuevoPedido', function () {
            detenerSeguimiento();
            iniciarTienda(busqueda.seleccionado);
        });
    });
</script>
<script>
    // Se quita la pantalla de carga cuando termina de cargar la página
    window.addEventListener('load', function () {
        var cargando = document.getElementById('serviciosCargando');
        if (!cargando) {
            return;
        }
        cargando.classList.add('oculto');
        setTimeout(function () {
            if (cargando.parentNode) {
                cargando.parentNode.removeChild(cargando);
            }
        }, 600);
    });
</script>
@endsection

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
                                        <option value="{{ $mascota->id }}" {{ optional($mascotaActiva)->id === $mascota->id ? 'selected' : '' }}>
                                            {{ $mascota->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-4">
                                <label class="floating-label-activo-sm">¿Qué buscas?</label>
                                <select class="custom-select" id="searchCategory">
                                    <option value="">Seleccione servicio</option>
                                    <option value="alimentos" {{ request('servicio') === 'alimentos' ? 'selected' : '' }}>Alimentos</option>
                                    <option value="centro_veterinario">Centro Veterinario</option>
                                    <option value="pet_shop">Pet Shop</option>
                                    <option value="farmacia" {{ request('servicio') === 'farmacia' ? 'selected' : '' }}>Farmacia Veterinaria</option>
                                    <option value="peluqueria">Peluquería</option>
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

            @if(($comerciosIntegrados ?? collect())->isNotEmpty())
            <div class="row mx-1">
                <div class="col-12">
                    <section class="servicios-tarjeta">
                        <div class="servicios-cabecera">
                            <span class="servicios-icono"><i class="fas fa-store"></i></span>
                            <div class="servicios-cabecera-texto">
                                <h4>Central de alimentos y farmacias integrada</h4>
                                <p>Los productos del comercio aparecen automáticamente al buscar alimentos o farmacia.</p>
                            </div>
                        </div>
                        <div class="row">
                            @foreach($comerciosIntegrados as $comercio)
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <article class="servicios-comercio">
                                        <span class="servicios-comercio-avatar"><i class="fas fa-store-alt"></i></span>
                                        <div class="servicios-comercio-datos">
                                            <strong>{{ $comercio->nombre }}</strong>
                                            <span class="servicios-chip">{{ ucfirst(str_replace('_',' ',$comercio->tipo ?: 'tienda')) }}</span>
                                            @if($comercio->direccion || $comercio->comuna)
                                                <small><i class="feather icon-map-pin"></i> {{ $comercio->direccion }} {{ $comercio->comuna }}</small>
                                            @endif
                                            @if($comercio->telefono)
                                                <small><i class="feather icon-phone"></i> {{ $comercio->telefono }}</small>
                                            @endif
                                        </div>
                                    </article>
                                </div>
                            @endforeach
                        </div>
                    </section>
                </div>
            </div>
            @endif

            {{-- Mapa y ficha del lugar --}}
            <div class="row mx-1">
                <div class="col-md-12">
                    <section class="servicios-tarjeta">
                        <div class="servicios-cabecera servicios-cabecera-mapa">
                            <span class="servicios-icono"><i class="fas fa-paw"></i></span>
                            <div class="servicios-cabecera-texto">
                                <h4>Lugares adheridos a nuestra comunidad</h4>
                                <p id="searchSummary">Aún no se ha realizado una búsqueda.</p>
                            </div>
                            <div class="servicios-leyenda">
                                <span><i class="servicios-punto servicios-punto-usuario"></i> Tu ubicación</span>
                                <span><i class="servicios-punto servicios-punto-lugar"></i> Establecimiento</span>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-7 mb-4 mb-lg-0">
                                <div class="servicios-mapa">
                                    <div id="map"></div>
                                </div>
                            </div>
                            <div class="col-lg-5">
                                <aside class="servicios-ficha">
                                    <div class="servicios-ficha-cabecera">
                                        <div class="servicios-ficha-logo">
                                            <img id="selectedPlaceLogo" src="{{ asset('images/otroslogos/logo-local.png') }}" alt="Logo del lugar">
                                        </div>
                                        <div class="servicios-ficha-titulo">
                                            <span class="servicios-etiqueta">Institución</span>
                                            <h5 id="selectedInstitutionName">Sin selección</h5>
                                        </div>
                                    </div>

                                    <div class="servicios-ficha-cuerpo">
                                        <div class="servicios-bloque">
                                            <span class="servicios-etiqueta">Sucursal</span>
                                            <p class="servicios-sucursal" id="selectedPlaceName">Seleccione un marcador en el mapa</p>
                                            <p class="servicios-direccion">
                                                <i class="feather icon-map-pin"></i>
                                                <span id="selectedPlaceAddress">La información del lugar aparecerá aquí.</span>
                                            </p>
                                        </div>

                                        <div class="servicios-metricas">
                                            <div class="servicios-metrica">
                                                <i class="feather icon-home"></i>
                                                <div>
                                                    <span class="servicios-etiqueta">Tipo sede</span>
                                                    <p id="selectedPlaceType">-</p>
                                                </div>
                                            </div>
                                            <div class="servicios-metrica">
                                                <i class="feather icon-navigation"></i>
                                                <div>
                                                    <span class="servicios-etiqueta">Distancia aprox.</span>
                                                    <p id="selectedPlaceDistance">-</p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="servicios-bloque">
                                            <span class="servicios-etiqueta"><i class="feather icon-clock"></i> Horario</span>
                                            <div id="selectedPlaceSchedule" class="servicios-horario">-</div>
                                        </div>

                                        <div class="servicios-bloque">
                                            <span class="servicios-etiqueta"><i class="feather icon-grid"></i> Servicios disponibles</span>
                                            <div id="selectedPlaceServices" class="servicios-chips">-</div>
                                        </div>

                                        <div class="servicios-bloque mb-0">
                                            <span class="servicios-etiqueta"><i class="feather icon-git-branch"></i> Sucursales de la institución</span>
                                            <div id="selectedInstitutionBranches" class="servicios-sucursales">-</div>
                                        </div>
                                    </div>
                                </aside>
                            </div>
                        </div>

                        <div class="servicios-resultados">
                            <div class="servicios-subtitulo">
                                <i class="feather icon-list"></i> Resultados de la búsqueda
                            </div>
                            <div id="searchResultsList" class="text-muted">
                                <div class="servicios-vacio">
                                    <i class="fas fa-search-location"></i>
                                    <p>Sin resultados todavía.</p>
                                    <small>Elige un servicio y una ubicación, luego presiona Buscar.</small>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>

            {{-- Suscripción mensual (alimentos, farmacia y pet shop) --}}
            <div class="row mx-1 d-none" id="subscriptionSection">
                <div class="col-md-12">
                    <section class="servicios-tarjeta">
                        <div class="servicios-cabecera">
                            <span class="servicios-icono"><i class="fas fa-sync-alt"></i></span>
                            <div class="servicios-cabecera-texto">
                                <h4>Suscripción mensual</h4>
                                <p id="subscriptionHelpText">Indique los productos que desea registrar para recibir de forma mensual.</p>
                            </div>
                        </div>

                        <div class="form-row servicios-formulario">
                            <div class="form-group col-md-12 d-none" id="prescriptionSelectorGroup">
                                <div class="servicios-receta">
                                    <label class="floating-label-activo-sm mb-0">Relacionar con receta veterinaria</label>
                                    <select class="form-control form-control-sm" id="prescriptionSelector">
                                        <option value="">Agregar otro producto de farmacia</option>
                                    </select>
                                    <small class="text-muted"><i class="feather icon-info"></i> Sólo se muestran recetas de la mascota seleccionada.</small>
                                    <div class="custom-control custom-checkbox mt-2 d-none" id="attachPrescriptionGroup">
                                        <input type="checkbox" class="custom-control-input" id="attachPrescription" checked>
                                        <label class="custom-control-label" for="attachPrescription">Adjuntar esta receta al pedido</label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group col-md-9">
                                <label class="floating-label-activo-sm mb-0" id="subscriptionItemLabel">Producto</label>
                                <input type="text" id="subscriptionItemName" class="form-control form-control-sm ui-autocomplete-input" autocomplete="off" placeholder="Escribe al menos 2 letras para buscar">
                                <input type="hidden" id="subscriptionItemId" value="">
                                <input type="hidden" id="subscriptionPrescriptionId" value="">
                            </div>
                            <div class="form-group col-md-3">
                                <label class="floating-label-activo-sm mb-0">Cantidad</label>
                                <input class="form-control form-control-sm" type="number" id="subscriptionQuantity" min="1">
                            </div>
                            <div class="form-group col-md-9">
                                <label class="floating-label-activo-sm mb-0">Presentación</label>
                                <select class="form-control form-control-sm" id="subscriptionPresentation">
                                    <option value="">Seleccione</option>
                                </select>
                            </div>
                            <div class="form-group col-md-3">
                                <button type="button" class="btn btn-info btn-block servicios-btn-principal" id="addSubscriptionItem">
                                    <i class="feather icon-plus"></i> Añadir
                                </button>
                            </div>
                        </div>

                        <div class="table-responsive servicios-tabla">
                            <table class="table table-sm mb-0">
                                <thead>
                                    <tr class="text-center">
                                        <th class="align-middle">Producto</th>
                                        <th class="align-middle">Cantidad</th>
                                        <th class="align-middle">Presentación</th>
                                        <th class="align-middle">Quitar</th>
                                    </tr>
                                </thead>
                                <tbody id="subscriptionItemsTable">
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No hay elementos agregados.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="servicios-pie">
                            <button type="button" class="btn btn-info servicios-btn-principal" id="saveSubscription">
                                <i class="feather icon-save"></i> Guardar suscripción
                            </button>
                        </div>
                    </section>
                </div>
            </div>

            {{-- Reserva (peluquería y hotel) --}}
            <div class="row mx-1 d-none" id="bookingSection">
                <div class="col-md-12">
                    <section class="servicios-tarjeta">
                        <div class="servicios-cabecera">
                            <span class="servicios-icono servicios-icono-reserva"><i class="feather icon-calendar"></i></span>
                            <div class="servicios-cabecera-texto">
                                <h4>Reserva de hora</h4>
                                <p id="bookingMessage">Este servicio se gestiona mediante sistema de reserva.</p>
                            </div>
                        </div>
                        <div class="form-row servicios-formulario">
                            <div class="form-group col-md-4">
                                <label class="floating-label-activo-sm">Sucursal</label>
                                <select class="form-control form-control-sm" id="bookingBranch">
                                    <option value="">Seleccione</option>
                                </select>
                            </div>
                            <div class="form-group col-md-4">
                                <label class="floating-label-activo-sm">Fecha</label>
                                <input type="date" class="form-control form-control-sm" id="bookingDate">
                            </div>
                            <div class="form-group col-md-4">
                                <label class="floating-label-activo-sm">Horario disponible</label>
                                <select class="form-control form-control-sm" id="bookingTime">
                                    <option value="">Seleccione</option>
                                    <option value="10:00">10:00</option>
                                    <option value="11:00">11:00</option>
                                    <option value="12:00">12:00</option>
                                    <option value="15:00">15:00</option>
                                    <option value="16:00">16:00</option>
                                </select>
                            </div>
                        </div>
                        <div class="servicios-pie">
                            <button type="button" class="btn btn-info servicios-btn-principal" id="submitBooking">
                                <i class="feather icon-check-circle"></i> Solicitar reserva
                            </button>
                        </div>
                    </section>
                </div>
            </div>

            {{-- Centro veterinario, sin formulario por ahora --}}
            <div class="row mx-1 d-none" id="pendingSection">
                <div class="col-md-12">
                    <section class="servicios-tarjeta">
                        <div class="servicios-vacio">
                            <i class="fas fa-clinic-medical"></i>
                            <p>Centro Veterinario</p>
                            <small>Centro Veterinario quedará sin formulario inferior por ahora. Puede buscar lugares en el mapa y revisar la información del establecimiento.</small>
                        </div>
                    </section>
                </div>
            </div>

            {{-- Historial de solicitudes --}}
            <div class="row mx-1" id="historySection">
                <div class="col-md-12">
                    <section class="servicios-tarjeta">
                        <div class="servicios-cabecera">
                            <span class="servicios-icono"><i class="feather icon-clock"></i></span>
                            <div class="servicios-cabecera-texto">
                                <h4>Solicitudes guardadas para la mascota</h4>
                                <p>Suscripciones y reservas registradas según el servicio seleccionado.</p>
                            </div>
                        </div>
                        <div id="serviceHistoryContent" class="text-muted">Seleccione una mascota y un servicio para ver registros.</div>
                    </section>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page-script')
<script>
    const PETS = @json($petsData ?? []);
    const PRESCRIPTIONS = @json($prescripcionesData ?? []);
    const DEFAULT_LOGO = @json(asset('images/otroslogos/logo-local.png'));

    const SERVICE_LAYOUTS = {
        alimentos: 'subscription',
        farmacia: 'subscription',
        pet_shop: 'subscription',
        peluqueria: 'booking',
        hotel_mascotas: 'booking',
        centro_veterinario: 'pending'
    };

    const SERVICE_LABELS = {
        alimentos: 'Alimentos',
        farmacia: 'Farmacia Veterinaria',
        pet_shop: 'Pet Shop',
        peluqueria: 'Peluquería',
        hotel_mascotas: 'Hotel de Mascotas',
        centro_veterinario: 'Centro Veterinario'
    };

    const SUBSCRIPTION_CONTENT = {
        alimentos: {
            helpText: 'Indique los alimentos que desea registrar para recibir de forma mensual.',
            itemLabel: 'Alimento'
        },
        farmacia: {
            helpText: 'Indique los productos de farmacia que desea registrar para recibir de forma mensual.',
            itemLabel: 'Producto'
        },
        pet_shop: {
            helpText: 'Indique los productos de pet shop que desea registrar para recibir de forma mensual.',
            itemLabel: 'Producto'
        }
    };

    const MANUAL_LOCATIONS = {
        vina_del_mar: { label: 'Viña del Mar', coords: [-33.0245, -71.5518] },
        valparaiso: { label: 'Valparaíso', coords: [-33.0472, -71.6127] },
        quilpue: { label: 'Quilpué', coords: [-33.0475, -71.4425] },
        villa_alemana: { label: 'Villa Alemana', coords: [-33.0422, -71.3733] },
        los_andes: { label: 'Los Andes', coords: [-32.8337, -70.5983] },
        santiago_centro: { label: 'Santiago Centro', coords: [-33.4489, -70.6693] },
        las_condes: { label: 'Las Condes', coords: [-33.4080, -70.5670] }
    };

    const ESTABLISHMENTS = [
        {
            id: 1,
            institutionName: 'SDI Centro Veterinario Costa',
            branchName: 'Casa Matriz Viña del Mar',
            branchType: 'Casa matriz',
            city: 'Viña del Mar',
            address: 'Av. Libertad 2450, Viña del Mar',
            coords: [-33.0172, -71.5502],
            services: ['centro_veterinario', 'farmacia', 'pet_shop'],
            logo: DEFAULT_LOGO,
            schedule: [
                'Lunes a Viernes: 09:00 - 20:00',
                'Sábado: 09:30 - 14:00',
                'Domingo: Cerrado'
            ],
            branches: ['Casa Matriz Viña del Mar', 'Sucursal Reñaca', 'Sucursal 15 Norte'],
            isSdi: true
        },
        {
            id: 2,
            institutionName: 'SDI Centro Veterinario Costa',
            branchName: 'Sucursal Reñaca',
            branchType: 'Sucursal',
            city: 'Viña del Mar',
            address: 'Av. Borgoño 14920, Reñaca, Viña del Mar',
            coords: [-32.9678, -71.5452],
            services: ['centro_veterinario', 'peluqueria', 'hotel_mascotas'],
            logo: DEFAULT_LOGO,
            schedule: [
                'Lunes a Viernes: 10:00 - 19:30',
                'Sábado: 10:00 - 15:00',
                'Domingo: Cerrado'
            ],
            branches: ['Casa Matriz Viña del Mar', 'Sucursal Reñaca', 'Sucursal 15 Norte'],
            isSdi: true
        },
        {
            id: 8,
            institutionName: 'SDI Centro Veterinario Costa',
            branchName: 'Sucursal 15 Norte',
            branchType: 'Sucursal',
            city: 'Viña del Mar',
            address: '15 Norte 961, Viña del Mar',
            coords: [-33.0098, -71.5486],
            services: ['centro_veterinario', 'pet_shop', 'farmacia', 'alimentos'],
            logo: DEFAULT_LOGO,
            schedule: [
                'Lunes a Viernes: 09:30 - 19:00',
                'Sábado: 10:00 - 14:30',
                'Domingo: Cerrado'
            ],
            branches: ['Casa Matriz Viña del Mar', 'Sucursal Reñaca', 'Sucursal 15 Norte'],
            isSdi: true
        },
        {
            id: 3,
            institutionName: 'SDI Pet Market Pacífico',
            branchName: 'Sucursal Valparaíso',
            branchType: 'Casa matriz',
            city: 'Valparaíso',
            address: 'Av. Argentina 1120, Valparaíso',
            coords: [-33.0421, -71.6120],
            services: ['pet_shop', 'alimentos', 'farmacia'],
            logo: DEFAULT_LOGO,
            schedule: [
                'Lunes a Viernes: 09:30 - 19:30',
                'Sábado: 10:00 - 16:00',
                'Domingo: Cerrado'
            ],
            branches: ['Sucursal Valparaíso'],
            isSdi: true
        },
        {
            id: 4,
            institutionName: 'SDI Mundo Mascota Interior',
            branchName: 'Sucursal Quilpué',
            branchType: 'Casa matriz',
            city: 'Quilpué',
            address: 'Diego Portales 775, Quilpué',
            coords: [-33.0468, -71.4410],
            services: ['pet_shop', 'alimentos', 'peluqueria'],
            logo: DEFAULT_LOGO,
            schedule: [
                'Lunes a Viernes: 09:00 - 19:00',
                'Sábado: 09:30 - 15:00',
                'Domingo: Cerrado'
            ],
            branches: ['Sucursal Quilpué'],
            isSdi: true
        },
        {
            id: 5,
            institutionName: 'SDI Clinivet Andes',
            branchName: 'Sucursal Los Andes',
            branchType: 'Casa matriz',
            city: 'Los Andes',
            address: 'Esmeralda 381, Los Andes, Región de Valparaíso',
            coords: [-32.8337, -70.5983],
            services: ['centro_veterinario', 'farmacia', 'pet_shop', 'peluqueria', 'hotel_mascotas'],
            logo: DEFAULT_LOGO,
            schedule: [
                'Lunes a Viernes: 10:00 - 20:30',
                'Sábado: 10:00 - 15:00',
                'Domingo: Cerrado'
            ],
            branches: ['Sucursal Los Andes'],
            isSdi: true
        },
        {
            id: 6,
            institutionName: 'SDI Veterinaria Metropolitana',
            branchName: 'Sucursal Las Condes',
            branchType: 'Casa matriz',
            city: 'Las Condes',
            address: 'Av. Apoquindo 5120, Las Condes, Santiago',
            coords: [-33.4145, -70.5786],
            services: ['centro_veterinario', 'farmacia'],
            logo: DEFAULT_LOGO,
            schedule: [
                'Lunes a Viernes: 08:30 - 20:00',
                'Sábado: 09:00 - 14:00',
                'Domingo: Cerrado'
            ],
            branches: ['Sucursal Las Condes', 'Sucursal Santiago Centro'],
            isSdi: true
        },
        {
            id: 7,
            institutionName: 'SDI Veterinaria Metropolitana',
            branchName: 'Sucursal Santiago Centro',
            branchType: 'Sucursal',
            city: 'Santiago Centro',
            address: 'San Diego 440, Santiago Centro',
            coords: [-33.4521, -70.6508],
            services: ['centro_veterinario', 'farmacia', 'pet_shop'],
            logo: DEFAULT_LOGO,
            schedule: [
                'Lunes a Viernes: 09:00 - 19:30',
                'Sábado: 09:30 - 13:30',
                'Domingo: Cerrado'
            ],
            branches: ['Sucursal Las Condes', 'Sucursal Santiago Centro'],
            isSdi: true
        }
    ];

    const searchState = {
        currentLocation: null,
        currentLocationLabel: '',
        locationSource: '',
        filteredPlaces: [],
        selectedPlace: null,
        markers: [],
        userLocationMarker: null,
    };

    let subscriptionItems = [];
    let subscriptionAutocompleteResults = [];

    function showMessage(title, text, icon) {
        swal({
            title: title,
            text: text,
            icon: icon,
            buttons: 'Aceptar',
        });
    }

    function getSelectedPet() {
        const petId = $('#selectedPet').val();
        return PETS.find(function (pet) {
            return String(pet.id) === String(petId);
        }) || null;
    }

    function resetSubscriptionInputs() {
        $('#subscriptionItemName').val('').prop('readonly', false);
        $('#subscriptionItemId').val('');
        $('#subscriptionPrescriptionId').val('');
        $('#prescriptionSelector').val('');
        $('#attachPrescription').prop('checked', true);
        $('#attachPrescriptionGroup').addClass('d-none');
        $('#subscriptionQuantity').val('');
        $('#subscriptionPresentation').html('<option value="">Seleccione</option>');
    }

    function renderPrescriptionOptions() {
        const pet = getSelectedPet();
        const $selector = $('#prescriptionSelector');
        $selector.html('<option value="">Agregar otro producto de farmacia</option>');

        if (!pet || $('#searchCategory').val() !== 'farmacia') {
            return;
        }

        PRESCRIPTIONS.filter(function (receta) {
            return String(receta.mascota_id) === String(pet.id);
        }).forEach(function (receta) {
            const detalle = receta.producto + ' · ' + receta.presentacion + (receta.fecha ? ' · ' + receta.fecha : '');
            $('<option>')
                .val(receta.id)
                .text(detalle)
                .data('receta', receta)
                .appendTo($selector);
        });
    }

    function useSelectedPrescription() {
        const $option = $('#prescriptionSelector option:selected');
        const receta = $option.data('receta');
        if (!receta) {
            $('#subscriptionPrescriptionId').val('');
            $('#attachPrescriptionGroup').addClass('d-none');
            return;
        }

        $('#subscriptionPrescriptionId').val(receta.id);
        $('#attachPrescription').prop('checked', true);
        $('#attachPrescriptionGroup').removeClass('d-none');
        $('#subscriptionItemId').val('receta:' + receta.id);
        $('#subscriptionItemName').val(receta.producto).prop('readonly', true);
        $('#subscriptionQuantity').val(receta.cantidad || 1);
        $('#subscriptionPresentation').html(
            $('<option>').val('receta:' + receta.id).text(receta.presentacion).prop('selected', true)
        );
    }

    function resetPlaceCard() {
        $('#selectedPlaceLogo').attr('src', DEFAULT_LOGO);
        $('#selectedInstitutionName').text('Sin selección');
        $('#selectedPlaceName').text('Seleccione un marcador en el mapa');
        $('#selectedPlaceAddress').text('La información del lugar aparecerá aquí.');
        $('#selectedPlaceType').text('-');
        $('#selectedPlaceDistance').text('-');
        $('#selectedPlaceSchedule').html('-');
        $('#selectedPlaceServices').html('-');
        $('#selectedInstitutionBranches').html('-');
    }

    function hideServiceSections() {
        $('#subscriptionSection, #bookingSection, #pendingSection').addClass('d-none');
    }

    function renderSubscriptionTable() {
        const $table = $('#subscriptionItemsTable');
        $table.empty();

        if (!subscriptionItems.length) {
            $table.html('<tr><td colspan="4" class="text-center text-muted">No hay elementos agregados.</td></tr>');
            return;
        }

        subscriptionItems.forEach(function (item, index) {
            $table.append(
                '<tr>' +
                    '<td>' + item.name + (item.attach_prescription ? ' <span class="badge badge-info ml-1">Receta adjunta</span>' : '') + '</td>' +
                    '<td class="text-center">' + item.quantity + '</td>' +
                    '<td>' + item.presentation + '</td>' +
                    '<td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger remove-subscription-item" data-index="' + index + '">Quitar</button></td>' +
                '</tr>'
            );
        });
    }

    function renderHistory() {
        const pet = getSelectedPet();
        const service = $('#searchCategory').val();
        const layout = SERVICE_LAYOUTS[service] || null;
        const $container = $('#serviceHistoryContent');

        if (!pet) {
            $container.html('<div class="text-muted">Seleccione una mascota para ver sus registros.</div>');
            return;
        }

        if (!service || !layout) {
            $container.html('<div class="text-muted">Seleccione un servicio para ver sus registros.</div>');
            return;
        }

        let records = [];
        if (layout === 'subscription') {
            records = (pet.suscripciones || []).filter(function (item) {
                return item.servicio === service;
            });
        } else if (layout === 'booking') {
            records = (pet.reservas || []).filter(function (item) {
                return item.servicio === service;
            });
        }

        if (!records.length) {
            $container.html('<div class="text-muted">No hay registros guardados para este servicio.</div>');
            return;
        }

        let html = '';
        records.slice().reverse().forEach(function (record) {
            html += '<div class="border rounded p-3 mb-3">';
            html += '<div class="font-weight-bold text-dark">' + (SERVICE_LABELS[record.servicio] || record.servicio) + '</div>';
            html += '<div class="small text-muted mb-2">Estado: ' + (record.estado || 'pendiente') + ' | Fecha registro: ' + (record.created_at || '-') + '</div>';
            html += '<div><strong>Lugar:</strong> ' + (record.lugar_nombre || '-') + '</div>';

            if (layout === 'subscription' && Array.isArray(record.items)) {
                html += '<div class="mt-2"><strong>Productos:</strong></div>';
                html += '<ul class="mb-0 pl-3">';
                record.items.forEach(function (item) {
                    html += '<li>' + item.name + ' | Cantidad: ' + item.quantity + ' | Presentación: ' + item.presentation + '</li>';
                });
                html += '</ul>';
            }

            if (layout === 'booking') {
                html += '<div class="mt-2"><strong>Fecha:</strong> ' + (record.fecha || '-') + ' <strong>Hora:</strong> ' + (record.hora || '-') + '</div>';
            }

            html += '</div>';
        });

        $container.html(html);
    }

    function formatDistance(km) {
        if (km === null || typeof km === 'undefined' || Number.isNaN(km)) {
            return '-';
        }

        if (km < 1) {
            return Math.round(km * 1000) + ' m';
        }

        return km.toFixed(1) + ' km';
    }

    function haversineDistance(coordsA, coordsB) {
        if (!coordsA || !coordsB) {
            return null;
        }

        const toRad = function(value) {
            return value * Math.PI / 180;
        };

        const lat1 = coordsA[0];
        const lon1 = coordsA[1];
        const lat2 = coordsB[0];
        const lon2 = coordsB[1];
        const earthRadiusKm = 6371;
        const dLat = toRad(lat2 - lat1);
        const dLon = toRad(lon2 - lon1);
        const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
            Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) *
            Math.sin(dLon / 2) * Math.sin(dLon / 2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

        return earthRadiusKm * c;
    }

    function clearMarkers() {
        searchState.markers.forEach(function (markerItem) {
            map.removeLayer(markerItem.marker);
        });
        searchState.markers = [];
    }

    function updateLocationStatus() {
        if (!searchState.currentLocation) {
            $('#locationStatus').text('Seleccione una ubicación manual o use la geolocalización automática para buscar servicios cercanos.');
            return;
        }

        const sourceLabel = searchState.locationSource === 'auto' ? 'Ubicación automática' : 'Ubicación manual';
        $('#locationStatus').text(sourceLabel + ': ' + searchState.currentLocationLabel);
    }

    function setCurrentLocation(coords, label, source) {
        searchState.currentLocation = coords;
        searchState.currentLocationLabel = label;
        searchState.locationSource = source;
        updateLocationStatus();

        if (searchState.userLocationMarker) {
            searchState.userLocationMarker.setLatLng(coords);
        } else {
            searchState.userLocationMarker = L.circleMarker(coords, {
                radius: 8,
                color: '#0fb5c0',
                fillColor: '#0fb5c0',
                fillOpacity: 0.9
            }).addTo(map);
        }

        searchState.userLocationMarker.bindPopup('Tu ubicación de referencia: ' + label);
    }

    function fitMapToResults() {
        const bounds = [];

        if (searchState.currentLocation) {
            bounds.push(searchState.currentLocation);
        }

        searchState.filteredPlaces.forEach(function (place) {
            bounds.push(place.coords);
        });

        if (bounds.length === 0) {
            map.setView([-33.0245, -71.5518], 11);
            return;
        }

        if (bounds.length === 1) {
            map.setView(bounds[0], 13);
            return;
        }

        map.fitBounds(bounds, { padding: [40, 40] });
    }

    function renderPlaceCard(place) {
        if (!place) {
            resetPlaceCard();
            return;
        }

        $('#selectedPlaceLogo').attr('src', place.logo || DEFAULT_LOGO);
        $('#selectedInstitutionName').text(place.institutionName);
        $('#selectedPlaceName').text(place.branchName);
        $('#selectedPlaceAddress').text(place.address);
        $('#selectedPlaceType').text(place.branchType);
        $('#selectedPlaceDistance').text(formatDistance(place.distanceKm));
        $('#selectedPlaceSchedule').html(place.schedule.map(function (line) {
            return '<div>' + line + '</div>';
        }).join(''));
        $('#selectedPlaceServices').html(place.services.map(function (service) {
            return '<span class="badge badge-purple-light mr-1 mb-1">' + (SERVICE_LABELS[service] || service) + '</span>';
        }).join(''));
        $('#selectedInstitutionBranches').html(place.branches.map(function (branch) {
            return '<div>' + branch + '</div>';
        }).join(''));
    }

    function renderBookingBranches(selectedService) {
        const $branch = $('#bookingBranch');
        $branch.html('<option value="">Seleccione</option>');

        if (searchState.selectedPlace && searchState.selectedPlace.services.includes(selectedService)) {
            $branch.append('<option value="' + searchState.selectedPlace.branchName + '" selected>' + searchState.selectedPlace.branchName + '</option>');
            return;
        }

        searchState.filteredPlaces.forEach(function (place) {
            $branch.append('<option value="' + place.branchName + '">' + place.branchName + '</option>');
        });
    }

    function renderSearchResultsList() {
        const $container = $('#searchResultsList');

        if (!searchState.filteredPlaces.length) {
            $container.html('<div class="text-muted">No se encontraron establecimientos SDI para este servicio y ubicación.</div>');
            return;
        }

        let html = '<div class="row">';
        searchState.filteredPlaces.forEach(function (place) {
            html += '<div class="col-md-6 mb-3">';
            html += '  <div class="border rounded p-3 h-100">';
            html += '      <div class="font-weight-bold text-dark">' + place.branchName + '</div>';
            html += '      <div class="small text-muted mb-2">' + place.institutionName + '</div>';
            html += '      <div class="small mb-2">' + place.address + '</div>';
            html += '      <div class="small text-muted mb-2">Distancia aprox.: ' + formatDistance(place.distanceKm) + '</div>';
            html += '      <button type="button" class="btn btn-sm btn-outline-info select-search-result" data-place-id="' + place.id + '">Ver en mapa</button>';
            html += '  </div>';
            html += '</div>';
        });
        html += '</div>';

        $container.html(html);
    }

    function renderMarkers() {
        clearMarkers();

        searchState.filteredPlaces.forEach(function (place) {
            const markerInstance = L.marker(place.coords)
                .addTo(map)
                .bindPopup('<strong>' + place.branchName + '</strong><br>' + place.address);

            markerInstance.on('click', function () {
                selectPlace(place.id, true);
            });

            searchState.markers.push({
                id: place.id,
                marker: markerInstance
            });
        });
    }

    function selectPlace(placeId, openPopup) {
        const place = searchState.filteredPlaces.find(function (item) {
            return String(item.id) === String(placeId);
        }) || null;

        searchState.selectedPlace = place;
        renderPlaceCard(place);

        const selectedService = $('#searchCategory').val();
        renderServiceSection(selectedService);

        if (!place) {
            return;
        }

        map.setView(place.coords, 14);
        const markerItem = searchState.markers.find(function (item) {
            return String(item.id) === String(place.id);
        });

        if (openPopup && markerItem) {
            markerItem.marker.openPopup();
        }
    }

    function renderSearchSummary(selectedService) {
        if (!selectedService) {
            $('#searchSummary').text('Aún no se ha realizado una búsqueda.');
            return;
        }

        if (!searchState.currentLocation) {
            $('#searchSummary').text('Seleccione una ubicación para buscar establecimientos SDI.');
            return;
        }

        const serviceLabel = SERVICE_LABELS[selectedService] || selectedService;
        $('#searchSummary').text(
            searchState.filteredPlaces.length + ' establecimiento(s) SDI encontrados para ' +
            serviceLabel + ' cerca de ' + searchState.currentLocationLabel + '.'
        );
    }

    function renderServiceSection(selectedService) {
        const layout = SERVICE_LAYOUTS[selectedService] || null;

        hideServiceSections();

        if (layout === 'subscription') {
            const subscriptionContent = SUBSCRIPTION_CONTENT[selectedService] || SUBSCRIPTION_CONTENT.alimentos;
            $('#subscriptionHelpText').text(subscriptionContent.helpText);
            $('#subscriptionItemLabel').text(subscriptionContent.itemLabel);
            $('#subscriptionSection').removeClass('d-none');
        }

        $('#prescriptionSelectorGroup').toggleClass('d-none', selectedService !== 'farmacia');
        renderPrescriptionOptions();

        if (layout === 'booking') {
            const serviceLabel = SERVICE_LABELS[selectedService] || 'Este servicio';
            $('#bookingMessage').text(serviceLabel + ' se gestiona mediante sistema de reserva.');
            renderBookingBranches(selectedService);
            $('#bookingSection').removeClass('d-none');
        }

        if (layout === 'pending') {
            $('#pendingSection').removeClass('d-none');
        }

        renderHistory();
    }

    function runSearch() {
        const selectedService = $('#searchCategory').val();

        if (!selectedService) {
            showMessage('Búsqueda de servicios', 'Seleccione un servicio para buscar establecimientos SDI.', 'error');
            return;
        }

        if (!searchState.currentLocation) {
            showMessage('Búsqueda de servicios', 'Seleccione una ubicación manual o use la geolocalización automática.', 'error');
            return;
        }

        searchState.filteredPlaces = ESTABLISHMENTS
            .filter(function (place) {
                return place.isSdi && place.services.includes(selectedService);
            })
            .map(function (place) {
                const distanceKm = haversineDistance(searchState.currentLocation, place.coords);
                return Object.assign({}, place, {
                    distanceKm: distanceKm
                });
            })
            .sort(function (a, b) {
                return a.distanceKm - b.distanceKm;
            });

        searchState.selectedPlace = searchState.filteredPlaces.length ? searchState.filteredPlaces[0] : null;

        renderMarkers();
        renderSearchSummary(selectedService);
        renderSearchResultsList();
        fitMapToResults();
        renderPlaceCard(searchState.selectedPlace);
        renderServiceSection(selectedService);

        if (searchState.selectedPlace) {
            selectPlace(searchState.selectedPlace.id, true);
        } else {
            resetPlaceCard();
        }
    }

    function handleManualLocationChange() {
        const key = $('#manualLocation').val();
        if (!key || !MANUAL_LOCATIONS[key]) {
            return;
        }

        setCurrentLocation(MANUAL_LOCATIONS[key].coords, MANUAL_LOCATIONS[key].label, 'manual');
        map.setView(MANUAL_LOCATIONS[key].coords, 12);
    }

    function requestGeolocation() {
        if (!navigator.geolocation) {
            showMessage('Geolocalización', 'Tu navegador no soporta geolocalización.', 'error');
            return;
        }

        if (!window.isSecureContext) {
            showMessage(
                'Geolocalización',
                'La geolocalización del navegador requiere una conexión segura. Abre esta página en https://vet-sdi.test o usa la ubicación manual.',
                'error'
            );
            return;
        }

        navigator.geolocation.getCurrentPosition(function(position) {
            const coords = [position.coords.latitude, position.coords.longitude];
            const label = 'Lat ' + position.coords.latitude.toFixed(4) + ', Lon ' + position.coords.longitude.toFixed(4);

            setCurrentLocation(coords, label, 'auto');
            map.setView(coords, 13);
        }, function(error) {
            let message = 'No fue posible obtener tu ubicación.';

            if (error && error.code === 1) {
                message = 'El navegador bloqueó el permiso de ubicación. Debes permitir acceso a la ubicación para este sitio o usar la ubicación manual.';
            } else if (error && error.code === 2) {
                message = 'No se pudo determinar tu ubicación actual. Revisa tu conexión o usa la ubicación manual.';
            } else if (error && error.code === 3) {
                message = 'La solicitud de ubicación tardó demasiado. Intenta nuevamente o usa la ubicación manual.';
            }

            showMessage('Geolocalización', message, 'error');
        });
    }

    function initializeLeafletMap() {
        if (typeof map === 'undefined' || !document.getElementById('map')) {
            return;
        }

        map.setView(MANUAL_LOCATIONS.vina_del_mar.coords, 11);
        resetPlaceCard();
    }

    function loadSubscriptionPresentations(productId) {
        $('#subscriptionPresentation').html('<option value="">Cargando...</option>');

        $.ajax({
            url: "{{ route('listar.presentacion') }}",
            type: 'GET',
            dataType: 'json',
            data: {
                medicamento: productId
            }
        }).done(function (response) {
            const $presentation = $('#subscriptionPresentation');
            $presentation.html('<option value="">Seleccione</option>');

            if (!response || !response.length) {
                $presentation.html('<option value="sin_presentacion" selected>Sin presentación registrada</option>');
                return;
            }

            response.forEach(function (item) {
                $presentation.append('<option value="' + item.id + '">' + item.descripcion_presentacion + '</option>');
            });
        }).fail(function () {
            $('#subscriptionPresentation').html('<option value="">No fue posible cargar las presentaciones</option>');
        });
    }

    function initializeSubscriptionAutocomplete() {
        $('#subscriptionItemName').autocomplete({
            minLength: 2,
            source: function (request, response) {
                $.ajax({
                    url: "{{ route('paciente.mascotas.suscripcion_servicios.productos') }}",
                    type: 'GET',
                    dataType: 'json',
                    data: {
                        search: request.term,
                        service: $('#serviceType').val()
                    },
                    success: function (data) {
                        subscriptionAutocompleteResults = Array.isArray(data) ? data : [];
                        response(data);
                    }
                });
            },
            focus: function (event, ui) {
                $('#subscriptionItemName').val(ui.item.label);
                return false;
            },
            select: function (event, ui) {
                $('#subscriptionItemName').val(ui.item.label);
                $('#subscriptionItemId').val(ui.item.value);
                loadSubscriptionPresentations(ui.item.value);
                return false;
            },
            change: function (event, ui) {
                if (ui.item) {
                    $('#subscriptionItemName').val(ui.item.label);
                    $('#subscriptionItemId').val(ui.item.value);
                    loadSubscriptionPresentations(ui.item.value);
                    return false;
                }

                const currentValue = $('#subscriptionItemName').val().trim().toLowerCase();
                const matchedItem = subscriptionAutocompleteResults.find(function (item) {
                    return String(item.label).trim().toLowerCase() === currentValue ||
                        String(item.name || '').trim().toLowerCase() === currentValue;
                });

                if (matchedItem) {
                    $('#subscriptionItemName').val(matchedItem.label);
                    $('#subscriptionItemId').val(matchedItem.value);
                    loadSubscriptionPresentations(matchedItem.value);
                    return false;
                }

                $('#subscriptionItemId').val('');
                $('#subscriptionPresentation').html('<option value="">Seleccione</option>');
            }
        });
    }

    function addSubscriptionItem() {
        const productId = $('#subscriptionItemId').val();
        const productName = $('#subscriptionItemName').val().trim();
        const quantity = $('#subscriptionQuantity').val();
        const presentationId = $('#subscriptionPresentation').val();
        const presentation = $('#subscriptionPresentation option:selected').text();
        const prescriptionId = $('#subscriptionPrescriptionId').val();
        const attachPrescription = Boolean(prescriptionId) && $('#attachPrescription').is(':checked');

        if (!productId || !productName) {
            showMessage('Suscripción', 'Seleccione un producto válido.', 'error');
            return;
        }

        if (!quantity || parseInt(quantity, 10) <= 0) {
            showMessage('Suscripción', 'Ingrese una cantidad válida.', 'error');
            return;
        }

        if (!presentationId) {
            showMessage('Suscripción', 'Seleccione una presentación.', 'error');
            return;
        }

        subscriptionItems.push({
            productId: productId,
            name: productName,
            quantity: parseInt(quantity, 10),
            presentationId: presentationId,
            presentation: presentation,
            prescription_id: prescriptionId || null,
            attach_prescription: attachPrescription
        });

        renderSubscriptionTable();
        resetSubscriptionInputs();
    }

    function saveSubscription() {
        const pet = getSelectedPet();
        const service = $('#searchCategory').val();
        const place = searchState.selectedPlace;

        if (!pet) {
            showMessage('Suscripción', 'Seleccione una mascota.', 'error');
            return;
        }

        if (!place) {
            showMessage('Suscripción', 'Seleccione un establecimiento en el mapa.', 'error');
            return;
        }

        if (!SERVICE_LAYOUTS[service] || SERVICE_LAYOUTS[service] !== 'subscription') {
            showMessage('Suscripción', 'Seleccione un servicio con formulario de suscripción.', 'error');
            return;
        }

        if (!subscriptionItems.length) {
            showMessage('Suscripción', 'Debe agregar al menos un producto.', 'error');
            return;
        }

        $.ajax({
            url: "{{ route('paciente.mascotas.suscripcion_servicios.guardar') }}",
            type: 'POST',
            dataType: 'json',
            data: {
                _token: "{{ csrf_token() }}",
                id_mascota: pet.id,
                servicio: service,
                lugar_nombre: place.branchName,
                lugar_direccion: place.address,
                items: subscriptionItems
            }
        }).done(function (response) {
            if (response.estado !== 1) {
                showMessage('Suscripción', response.msj || 'No fue posible guardar la suscripción.', 'error');
                return;
            }

            pet.suscripciones = response.suscripciones || pet.suscripciones;
            subscriptionItems = [];
            renderSubscriptionTable();
            renderHistory();

            showMessage('Suscripción registrada', response.msj, 'success');
        }).fail(function (xhr) {
            const response = xhr.responseJSON || {};
            showMessage('Suscripción', response.msj || 'No fue posible guardar la suscripción.', 'error');
        });
    }

    function saveBooking() {
        const pet = getSelectedPet();
        const service = $('#searchCategory').val();
        const branch = $('#bookingBranch').val();
        const date = $('#bookingDate').val();
        const time = $('#bookingTime').val();
        const place = searchState.filteredPlaces.find(function (item) {
            return item.branchName === branch;
        }) || searchState.selectedPlace;

        if (!pet) {
            showMessage('Reserva', 'Seleccione una mascota.', 'error');
            return;
        }

        if (!place) {
            showMessage('Reserva', 'Seleccione un establecimiento en el mapa.', 'error');
            return;
        }

        if (!SERVICE_LAYOUTS[service] || SERVICE_LAYOUTS[service] !== 'booking') {
            showMessage('Reserva', 'Seleccione un servicio con sistema de reserva.', 'error');
            return;
        }

        if (!branch || !date || !time) {
            showMessage('Reserva', 'Complete sucursal, fecha y horario.', 'error');
            return;
        }

        $.ajax({
            url: "{{ route('paciente.mascotas.suscripcion_servicios.reserva') }}",
            type: 'POST',
            dataType: 'json',
            data: {
                _token: "{{ csrf_token() }}",
                id_mascota: pet.id,
                servicio: service,
                lugar_nombre: place.branchName,
                lugar_direccion: place.address,
                fecha: date,
                hora: time
            }
        }).done(function (response) {
            if (response.estado !== 1) {
                showMessage('Reserva', response.msj || 'No fue posible registrar la reserva.', 'error');
                return;
            }

            pet.reservas = response.reservas || pet.reservas;
            renderHistory();

            showMessage('Reserva registrada', response.msj, 'success');
        }).fail(function (xhr) {
            const response = xhr.responseJSON || {};
            showMessage('Reserva', response.msj || 'No fue posible registrar la reserva.', 'error');
        });
    }

    $(function () {
        initializeLeafletMap();
        initializeSubscriptionAutocomplete();
        renderSubscriptionTable();
        renderHistory();
        updateLocationStatus();
        renderServiceSection($('#searchCategory').val());

        $('#btnBuscarServicio').on('click', function () {
            runSearch();
        });

        $('#btnUseGeolocation').on('click', function () {
            requestGeolocation();
        });

        $('#manualLocation').on('change', function () {
            handleManualLocationChange();
        });

        $('#searchCategory, #selectedPet').on('change', function () {
            $('#subscriptionItemName').prop('readonly', false);
            resetSubscriptionInputs();
            renderServiceSection($('#searchCategory').val());
        });

        $('#prescriptionSelector').on('change', function () {
            const selectedPrescription = $(this).val();
            resetSubscriptionInputs();
            $('#prescriptionSelector').val(selectedPrescription);
            useSelectedPrescription();
        });

        $('#subscriptionItemName').on('input', function () {
            $('#subscriptionPrescriptionId').val('');
            $('#subscriptionItemId').val('');
            $('#subscriptionPresentation').html('<option value="">Seleccione</option>');
        });

        $('#addSubscriptionItem').on('click', function () {
            addSubscriptionItem();
        });

        $(document).on('click', '.remove-subscription-item', function () {
            const index = $(this).data('index');
            subscriptionItems.splice(index, 1);
            renderSubscriptionTable();
        });

        $(document).on('click', '.select-search-result', function () {
            selectPlace($(this).data('place-id'), true);
        });

        $('#saveSubscription').on('click', function () {
            saveSubscription();
        });

        $('#submitBooking').on('click', function () {
            saveBooking();
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

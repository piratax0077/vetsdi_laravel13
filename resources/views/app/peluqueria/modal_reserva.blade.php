{{-- Modal para agendar una hora en 3 pasos; lo abre el botón "Agendar hora" o un horario elegido en el calendario (peluqueria-agenda.js) --}}
<div id="modal_agendar_hora" class="modal fade" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="titulo_modal_agendar_hora" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header modal-header-purple">
                <h5 class="modal-title" id="titulo_modal_agendar_hora">Agendar hora</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">×</span></button>
            </div>
            @if($procedimientos->isEmpty())
                <div class="modal-body text-center py-4">
                    <h6 class="mb-2">Aún no hay procedimientos configurados</h6>
                    <p class="text-muted mb-0">Para agendar una hora primero agrega los procedimientos con su duración y precio.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-dark" data-dismiss="modal"><i class="feather icon-x mr-1"></i>Cancelar</button>
                    <a class="btn btn-info" href="{{ route('peluqueria.configuracion') }}"><i class="feather icon-settings mr-1"></i>Configurar procedimientos</a>
                </div>
            @else
                {{-- data-buscar-tutor: si el RUT no está entre los clientes cargados en la página, se consulta aquí ({tutor, mascotas} o 404) --}}
                <form id="form_agendar_hora" action="{{ route('peluqueria.agenda') }}" method="post" data-buscar-tutor="{{ route('peluqueria.agenda.tutor') }}" novalidate>
                    @csrf
                    {{-- Fecha y hora se juntan aquí al enviar (Y-m-d\TH:i) --}}
                    <input type="hidden" name="inicio" id="agendar_inicio">
                    <input type="hidden" id="agendar_hora">
                    <div class="modal-body">
                        <div class="alert alert-danger d-none" id="error_agendar_hora" role="alert"></div>

                        <ol class="reserva-vet-pasos" id="agendar_pasos">
                            <li class="reserva-vet-paso activo" data-paso="1">
                                <button type="button" class="reserva-vet-paso-boton" data-ir="1" aria-current="step">
                                    <span class="reserva-vet-paso-numero">1</span>
                                    <span class="reserva-vet-paso-texto">Mascota y tutor</span>
                                </button>
                            </li>
                            <li class="reserva-vet-paso" data-paso="2">
                                <button type="button" class="reserva-vet-paso-boton" data-ir="2" disabled>
                                    <span class="reserva-vet-paso-numero">2</span>
                                    <span class="reserva-vet-paso-texto">Servicio</span>
                                </button>
                            </li>
                            <li class="reserva-vet-paso" data-paso="3">
                                <button type="button" class="reserva-vet-paso-boton" data-ir="3" disabled>
                                    <span class="reserva-vet-paso-numero">3</span>
                                    <span class="reserva-vet-paso-texto">Pago</span>
                                </button>
                            </li>
                        </ol>

                        <!--Paso 1: el RUT del tutor trae sus datos y mascotas; si no está registrado se ingresa todo-->
                        @php
                            $regiones_tutor = \App\Models\Region::orderBy('nombre')->get();
                            $comunas_tutor = \App\Models\Ciudad::orderBy('nombre')->get(['id', 'nombre', 'id_region']);
                            // Clientes agrupados por RUT (sin puntos ni guión) para reconocer al tutor al escribirlo.
                            // Los clientes registrados antes de la agenda nueva no tienen RUT y no aparecen aquí
                            $tutores_por_rut = $clientes->filter(fn ($cliente) => !empty($cliente->rut ?? null))
                                ->groupBy(fn ($cliente) => strtoupper(preg_replace('/[^0-9kK]/', '', $cliente->rut)))
                                ->map(fn ($mascotas) => [
                                    'tutor' => [
                                        'tutor' => $mascotas->first()->tutor,
                                        'telefono' => $mascotas->first()->telefono,
                                        'email' => $mascotas->first()->email,
                                        'direccion' => collect([$mascotas->first()->direccion ?? null, optional($comunas_tutor->firstWhere('id', $mascotas->first()->id_ciudad ?? null))->nombre])->filter()->implode(', '),
                                    ],
                                    'mascotas' => $mascotas->map(fn ($mascota) => [
                                        'id' => $mascota->id,
                                        'nombre' => $mascota->mascota,
                                        'especie' => $mascota->especie,
                                        'especie_exotico' => $mascota->especie_exotico ?? '',
                                        'raza' => $mascota->raza,
                                        'cuidados' => $mascota->cuidados,
                                    ])->values(),
                                ]);
                        @endphp
                        <script type="application/json" id="agendar_tutores">@json($tutores_por_rut)</script>
                        <div class="reserva-vet-panel activo" data-paso="1">
                            <div class="reserva-vet-panel-encabezado">
                                <p class="small text-dark mb-0">Escribe el RUT del tutor: si ya está registrado se cargan sus datos y sus mascotas.</p>
                            </div>
                            {{-- Pasa a 1 cuando se registra un tutor nuevo o una mascota nueva --}}
                            <input type="hidden" name="nuevo_cliente" id="agendar_nuevo_cliente" value="0">
                            <div class="form-row">
                                <div class="form-group col-12 col-md-6">
                                    <label class="floating-label-activo-sm" for="agendar_rut">RUT del tutor</label>
                                    <input type="text" class="form-control form-control-sm" id="agendar_rut" name="rut" placeholder="Ej.: 12345678-9" maxlength="10" autocomplete="off" required>
                                    <div class="invalid-feedback">Escribe un RUT válido.</div>
                                </div>
                            </div>
                            <div class="reserva-vet-lugar-vacio mb-3" id="agendar_tutor_espera">
                                <i class="feather icon-user"></i>
                                <span>Al escribir el RUT verás aquí los datos del tutor y sus mascotas.</span>
                            </div>

                            {{-- Tutor registrado: sus datos y sus mascotas para elegir, o una mascota nueva --}}
                            <div class="d-none" id="agendar_tutor_registrado">
                                <div class="reserva-hora-datos mb-3">
                                    <dl class="ficha-previa-datos reserva-hora-dos-columnas">
                                        <div class="ficha-previa-dato"><dt>Tutor</dt><dd data-tutor="tutor"></dd></div>
                                        <div class="ficha-previa-dato"><dt>Teléfono</dt><dd data-tutor="telefono"></dd></div>
                                        <div class="ficha-previa-dato"><dt>Correo electrónico</dt><dd data-tutor="email"></dd></div>
                                        <div class="ficha-previa-dato"><dt>Dirección</dt><dd data-tutor="direccion"></dd></div>
                                    </dl>
                                </div>
                                {{-- Las mascotas del tutor y, al final, "Agregar nueva mascota"; si no tiene ninguna, va directo al registro --}}
                                <div class="form-row" id="agendar_mascotas_contenedor">
                                    <div class="form-group col-12">
                                        <label class="floating-label-activo-sm" for="agendar_mascota_tutor">Mascota</label>
                                        <select class="form-control form-control-sm" id="agendar_mascota_tutor" name="cliente_id" required disabled>
                                            <option value="">Seleccione una mascota</option>
                                        </select>
                                        <div class="invalid-feedback">Selecciona la mascota o agrega una nueva.</div>
                                    </div>
                                </div>
                                <div class="reserva-vet-lugar-vacio mb-3 d-none" id="agendar_cuidados_mascota">
                                    <i class="feather icon-alert-circle"></i>
                                    <span></span>
                                </div>
                            </div>

                            {{-- Tutor nuevo: se registra junto con la hora, con su RUT, dirección, comuna y región --}}
                            <div class="d-none" id="agendar_tutor_nuevo">
                                <div class="reserva-vet-lugar-vacio mb-3">
                                    <i class="feather icon-user-plus"></i>
                                    <span>Tutor no registrado. Completa sus datos para registrarlo junto con la hora.</span>
                                </div>
                                {{-- El controlador recibe un solo "tutor": se arma con nombre y apellidos al escribirlos --}}
                                <input type="hidden" id="agendar_tutor" name="tutor" disabled>
                                <div class="form-row">
                                    <div class="form-group col-12 col-md-4">
                                        <label class="floating-label-activo-sm" for="agendar_nombres">Nombre</label>
                                        <input type="text" class="form-control form-control-sm" id="agendar_nombres" name="nombres" maxlength="150" required disabled>
                                        <div class="invalid-feedback">Escribe el nombre del tutor.</div>
                                    </div>
                                    <div class="form-group col-12 col-sm-6 col-md-4">
                                        <label class="floating-label-activo-sm" for="agendar_apellido_uno">Primer apellido</label>
                                        <input type="text" class="form-control form-control-sm" id="agendar_apellido_uno" name="apellido_uno" maxlength="100" required disabled>
                                        <div class="invalid-feedback">Escribe el primer apellido.</div>
                                    </div>
                                    <div class="form-group col-12 col-sm-6 col-md-4">
                                        <label class="floating-label-activo-sm" for="agendar_apellido_dos">Segundo apellido</label>
                                        <input type="text" class="form-control form-control-sm" id="agendar_apellido_dos" name="apellido_dos" maxlength="100" disabled>
                                    </div>
                                    <div class="form-group col-12 col-sm-6">
                                        <label class="floating-label-activo-sm" for="agendar_email">Correo electrónico (opcional)</label>
                                        <input type="email" class="form-control form-control-sm" id="agendar_email" name="email" placeholder="nombre@correo.cl" maxlength="255" disabled>
                                        <div class="invalid-feedback">Revisa el formato del correo.</div>
                                    </div>
                                    <div class="form-group col-12 col-sm-6">
                                        <label class="floating-label-activo-sm" for="agendar_telefono">Teléfono</label>
                                        <input type="text" class="form-control form-control-sm" id="agendar_telefono" name="telefono" placeholder="+56 9 1234 5678" maxlength="30" required disabled>
                                        <div class="invalid-feedback">Escribe un teléfono de contacto.</div>
                                    </div>
                                    <div class="form-group col-12">
                                        <label class="floating-label-activo-sm" for="agendar_direccion">Dirección</label>
                                        <input type="text" class="form-control form-control-sm" id="agendar_direccion" name="direccion" placeholder="Calle y número" maxlength="255" required disabled>
                                        <div class="invalid-feedback">Escribe la dirección.</div>
                                    </div>
                                    {{-- Primero la región y después la comuna: al cambiar la región solo quedan sus comunas,
                                         y al elegir una comuna su región se completa sola --}}
                                    <div class="form-group col-12 col-sm-6">
                                        <label class="floating-label-activo-sm" for="agendar_region">Región</label>
                                        <select class="form-control form-control-sm" id="agendar_region" name="id_region" required disabled>
                                            <option value="">Seleccione</option>
                                            @foreach($regiones_tutor as $region)
                                                <option value="{{ $region->id }}">{{ $region->nombre }}</option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback">Selecciona la región.</div>
                                    </div>
                                    <div class="form-group col-12 col-sm-6">
                                        <label class="floating-label-activo-sm" for="agendar_comuna">Comuna</label>
                                        <select class="form-control form-control-sm" id="agendar_comuna" name="id_ciudad" required disabled>
                                            <option value="">Seleccione</option>
                                            @foreach($comunas_tutor as $comuna)
                                                <option value="{{ $comuna->id }}" data-region="{{ $comuna->id_region }}">{{ $comuna->nombre }}</option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback">Selecciona la comuna.</div>
                                    </div>
                                </div>
                            </div>

                            {{-- Mascota nueva: la de un tutor nuevo o una más de un tutor registrado --}}
                            <div class="d-none" id="agendar_mascota_nueva">
                                <h6 class="titulo-sm mb-3">Mascota nueva</h6>
                                <div class="form-row">
                                    <div class="form-group col-12 col-md-4">
                                        <label class="floating-label-activo-sm" for="agendar_mascota">Nombre de la mascota</label>
                                        <input type="text" class="form-control form-control-sm" id="agendar_mascota" name="mascota" maxlength="100" required disabled>
                                        <div class="invalid-feedback">Escribe el nombre de la mascota.</div>
                                    </div>
                                    <div class="form-group col-6 col-md-4">
                                        <label class="floating-label-activo-sm" for="agendar_especie">Especie</label>
                                        <select class="form-control form-control-sm" id="agendar_especie" name="especie" required disabled>
                                            <option value="">Seleccione</option>
                                            <option value="Canino">Canino</option>
                                            <option value="Felino">Felino</option>
                                            <option value="Exótico">Exótico</option>
                                        </select>
                                        <div class="invalid-feedback">Selecciona la especie.</div>
                                    </div>
                                    {{-- Con "Exótico" se escribe qué animal es (igual que en los precios del procedimiento); oculto va deshabilitado --}}
                                    <div class="form-group col-6 col-md-4 d-none" id="agendar_exotico_contenedor">
                                        <label class="floating-label-activo-sm" for="agendar_exotico">¿Qué exótico es?</label>
                                        <input type="text" class="form-control form-control-sm" id="agendar_exotico" name="especie_exotico" placeholder="Ej.: Conejo, hurón" maxlength="45" required disabled>
                                        <div class="invalid-feedback">Escribe el tipo de animal.</div>
                                    </div>
                                    <div class="form-group col-6 col-md-4">
                                        <label class="floating-label-activo-sm" for="agendar_raza">Raza (opcional)</label>
                                        <input type="text" class="form-control form-control-sm" id="agendar_raza" name="raza" maxlength="100" disabled>
                                    </div>
                                    <div class="form-group col-12">
                                        <label class="floating-label-activo-sm" for="agendar_cuidados">Cuidados y advertencias (opcional)</label>
                                        <textarea class="form-control form-control-sm" id="agendar_cuidados" name="cuidados" rows="2" maxlength="3000" disabled></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-12 mb-0">
                                    <label class="floating-label-activo-sm" for="agendar_observaciones">Observaciones de la reserva</label>
                                    <textarea class="form-control form-control-sm" id="agendar_observaciones" name="observaciones" rows="2" maxlength="3000"></textarea>
                                </div>
                            </div>
                        </div>

                        <!--Paso 2: procedimiento, fecha y hora con el precio y la duración de la mascota elegida-->
                        <div class="reserva-vet-panel" data-paso="2">
                            <div class="reserva-vet-panel-encabezado">
                                <h6>¿Dónde, qué servicio y cuándo?</h6>
                                <p class="small text-muted mb-0">Elige el lugar de atención, el procedimiento, el día en el calendario y una hora libre.</p>
                            </div>
                            @php
                                // La casa matriz (datos del perfil) y los lugares activos; cada uno lleva su horario para armar su agenda.
                                // Un lugar sin horario guardado usa el rango completo del calendario (08:00 a 22:00)
                                $horario_lugar = fn ($horario) => is_string($horario) ? (json_decode($horario, true) ?: null) : ($horario ?: null);
                                $comuna_matriz = !empty($perfil->id_ciudad) ? \App\Models\Ciudad::find($perfil->id_ciudad) : null;
                                $lugares_agenda = collect([[
                                    'id' => 'matriz',
                                    'nombre' => $perfil->nombre . ' · Casa matriz',
                                    'direccion' => collect([$perfil->direccion ?? null, $comuna_matriz->nombre ?? null])->filter()->implode(', '),
                                    'horario' => $horario_lugar($perfil->horario ?? null),
                                ]])->concat(collect($sucursales ?? [])->filter(fn ($sucursal) => !empty($sucursal->activo))->map(fn ($sucursal) => [
                                    'id' => $sucursal->id,
                                    'nombre' => $sucursal->nombre,
                                    'direccion' => collect([$sucursal->direccion ?? null, $sucursal->comuna ?? null])->filter()->implode(', '),
                                    'horario' => $horario_lugar($sucursal->horario ?? null),
                                ]))->values();
                            @endphp
                            {{-- Primero el lugar: el calendario muestra solo sus días de atención y las horas salen de su horario.
                                 Con un solo lugar queda elegido de una vez --}}
                            <div class="form-row">
                                <div class="form-group col-12 col-md-6">
                                    <label class="floating-label-activo-sm" for="agendar_lugar">Lugar de atención</label>
                                    <select class="form-control form-control-sm" id="agendar_lugar" name="lugar_atencion" required>
                                        @if($lugares_agenda->count() !== 1)
                                            <option value="">Seleccione</option>
                                        @endif
                                        @foreach($lugares_agenda as $lugar_agenda)
                                            <option value="{{ $lugar_agenda['id'] }}" data-nombre="{{ $lugar_agenda['nombre'] }}" data-direccion="{{ $lugar_agenda['direccion'] }}" data-horario="{{ json_encode($lugar_agenda['horario'], JSON_UNESCAPED_UNICODE) }}" @selected($lugares_agenda->count() === 1)>{{ $lugar_agenda['nombre'] }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback">Selecciona el lugar de atención.</div>
                                </div>
                                <div class="form-group col-12 col-md-6 d-none" id="agendar_lugar_detalle">
                                    <p class="small text-muted mb-1 pt-md-1"><i class="feather icon-map-pin icono-purple mr-1"></i><span id="agendar_lugar_direccion"></span></p>
                                    <p class="small text-muted mb-0"><i class="feather icon-clock icono-purple mr-1"></i><span id="agendar_lugar_horario"></span></p>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-12 col-md-6">
                                    <label class="floating-label-activo-sm" for="agendar_procedimiento">Procedimiento</label>
                                    @php
                                        // Precios de cada procedimiento por especie y tamaño, los mismos del panel de configuración.
                                        // Si la tabla aún no existe en la base, queda el precio y la duración generales del procedimiento
                                        $precios_procedimientos = \Illuminate\Support\Facades\Schema::hasTable('peluqueria_procedimiento_precios')
                                            ? \Illuminate\Support\Facades\DB::table('peluqueria_procedimiento_precios')
                                                ->whereIn('procedimiento_id', $procedimientos->pluck('id'))->orderBy('orden')->orderBy('id')
                                                ->get(['procedimiento_id', 'especie', 'especie_exotico', 'tamano', 'peso_desde', 'peso_hasta', 'duracion', 'precio'])
                                                ->groupBy('procedimiento_id')
                                            : collect();
                                    @endphp
                                    <select class="form-control form-control-sm" id="agendar_procedimiento" name="procedimiento_id" required>
                                        <option value="">Seleccione</option>
                                        @foreach($procedimientos as $procedimiento)
                                            @php
                                                // El nombre parte con mayúscula aunque se haya guardado en minúscula
                                                $nombre_procedimiento = \Illuminate\Support\Str::ucfirst(trim($procedimiento->nombre));
                                                $tarifas_procedimiento = $precios_procedimientos->get($procedimiento->id, collect())
                                                    ->map(fn ($precio) => collect((array) $precio)->except('procedimiento_id')->all())->values();
                                                if ($tarifas_procedimiento->isEmpty()) {
                                                    $tarifas_procedimiento = collect([['especie' => 'Todas', 'especie_exotico' => null, 'tamano' => 'Todos', 'peso_desde' => null, 'peso_hasta' => null, 'duracion' => $procedimiento->duracion, 'precio' => $procedimiento->precio]]);
                                                }
                                                // Especie y tamaño junto al nombre, igual que en el panel de configuración, para distinguir
                                                // procedimientos con el mismo nombre (ej.: Tintura · Canino y Tintura · Exótico (araña))
                                                $tarifa_lista = $tarifas_procedimiento->first();
                                                $kilos_lista = fn ($valor) => str_replace('.', ',', rtrim(rtrim(number_format((float) $valor, 1, '.', ''), '0'), '.'));
                                                $especie_lista = match (true) {
                                                    ($tarifa_lista['especie'] ?? 'Todas') === 'Todas' => 'Todas las especies',
                                                    $tarifa_lista['especie'] === 'Exótico' && trim((string) ($tarifa_lista['especie_exotico'] ?? '')) !== '' => 'Exótico (' . trim($tarifa_lista['especie_exotico']) . ')',
                                                    default => $tarifa_lista['especie'],
                                                };
                                                $tamano_lista = match ($tarifa_lista['tamano'] ?? 'Todos') {
                                                    'Pequeño' => 'Pequeño (hasta 10 kg)',
                                                    'Mediano' => 'Mediano (10 a 25 kg)',
                                                    'Grande' => 'Grande (más de 25 kg)',
                                                    'Peso' => 'De ' . $kilos_lista($tarifa_lista['peso_desde'] ?? 0) . ' a ' . $kilos_lista($tarifa_lista['peso_hasta'] ?? 0) . ' kg',
                                                    default => 'Todos los tamaños',
                                                };
                                            @endphp
                                            <option value="{{ $procedimiento->id }}" data-nombre="{{ $nombre_procedimiento }}" data-duracion="{{ $procedimiento->duracion }}" data-precio="{{ $procedimiento->precio }}" data-precios="{{ json_encode($tarifas_procedimiento, JSON_UNESCAPED_UNICODE) }}">{{ $nombre_procedimiento }} · {{ $especie_lista }} · {{ $tamano_lista }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback">Selecciona el procedimiento.</div>
                                </div>
                                <div class="form-group col-6 col-md-3">
                                    <label class="floating-label-activo-sm" for="agendar_duracion">Duración</label>
                                    <input type="text" class="form-control form-control-sm" id="agendar_duracion" placeholder="-" readonly tabindex="-1">
                                </div>
                                <div class="form-group col-6 col-md-3">
                                    <label class="floating-label-activo-sm" for="agendar_valor">Valor</label>
                                    <input type="text" class="form-control form-control-sm" id="agendar_valor" placeholder="-" readonly tabindex="-1">
                                </div>
                            </div>
                            {{-- El peso se pide solo si el precio del procedimiento para esta especie depende del tamaño;
                                 al guardar, PeluqueriaAgendaController calcula el mismo precio y la misma duración --}}
                            <div class="form-row d-none" id="agendar_peso_contenedor">
                                <div class="form-group col-12 col-sm-6">
                                    <label class="floating-label-activo-sm" for="agendar_peso">Peso aproximado (kg)</label>
                                    <input type="number" class="form-control form-control-sm" id="agendar_peso" name="peso" min="0.1" max="150" step="0.1" inputmode="decimal" required disabled>
                                    <div class="invalid-feedback">Indica el peso para calcular el valor.</div>
                                </div>
                            </div>
                            <div class="reserva-vet-aviso d-none" id="aviso_valor_mascota" role="alert"></div>
                            {{-- El calendario va a la izquierda y las horas a la derecha; en celular las horas quedan debajo --}}
                            <div class="row">
                                <div class="col-lg-6 mb-3 mb-lg-0">
                                    {{-- Mientras no hay lugar la fecha queda deshabilitada y se ve el aviso en vez del calendario --}}
                                    <div class="reserva-vet-calendario">
                                        <input type="text" class="d-none" id="agendar_fecha" tabindex="-1" aria-hidden="true" disabled>
                                        <div class="reserva-vet-horas-vacio">
                                            <i class="feather icon-calendar"></i>
                                            <span>Al elegir el lugar de atención verás aquí sus días de atención.</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    {{-- servicios-agenda da el estilo de las horas (mismo de la reserva veterinaria) --}}
                                    <div class="reserva-vet-horas-caja servicios-agenda">
                                        <div class="reserva-vet-horas-cabecera">
                                            <div class="reserva-vet-horas-titulo">Horas disponibles</div>
                                        </div>
                                        <div class="reserva-vet-horas-fecha" id="agendar_horas_fecha">Primero elige la fecha.</div>
                                        <div class="reserva-vet-horas" id="agendar_horas" aria-live="polite"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="reserva-vet-aviso d-none mt-3 mb-0" id="aviso_agendar_hora" role="alert"></div>
                        </div>

                        <!--Paso 3: resumen y forma de pago-->
                        {{-- La forma de pago se guarda con la cita. La pasarela todavía no está conectada: el pago en línea queda pendiente --}}
                        <div class="reserva-vet-panel" data-paso="3">
                            <div class="reserva-vet-panel-encabezado">
                                <h6>Forma de pago</h6>
                                <p class="small text-muted mb-0">Revisa la reserva y elige cómo se pagará.</p>
                            </div>
                            <div class="reserva-vet-resumen mb-3">
                                <div class="reserva-vet-resumen-cita">
                                    <span class="reserva-vet-resumen-cita-icono"><i class="feather icon-calendar"></i></span>
                                    <div class="reserva-vet-resumen-cita-datos">
                                        <span class="reserva-vet-resumen-fecha" id="agendar_resumen_fecha">-</span>
                                        <span class="reserva-vet-resumen-hora"><i class="feather icon-clock"></i><span id="agendar_resumen_hora">-</span></span>
                                    </div>
                                </div>
                                <ul class="reserva-vet-detalle">
                                    <li>
                                        <span class="reserva-vet-detalle-icono"><i class="feather icon-map-pin"></i></span>
                                        <div>
                                            <span class="reserva-vet-detalle-titulo">Lugar de atención</span>
                                            <span class="reserva-vet-detalle-valor" id="agendar_resumen_lugar">-</span>
                                            <span class="reserva-vet-detalle-extra" id="agendar_resumen_lugar_direccion"></span>
                                        </div>
                                    </li>
                                    <li>
                                        <span class="reserva-vet-detalle-icono"><i class="feather icon-scissors"></i></span>
                                        <div>
                                            <span class="reserva-vet-detalle-titulo">Servicio</span>
                                            <span class="reserva-vet-detalle-valor" id="agendar_resumen_servicio">-</span>
                                        </div>
                                    </li>
                                    <li>
                                        <span class="reserva-vet-detalle-icono"><i class="fas fa-paw"></i></span>
                                        <div>
                                            <span class="reserva-vet-detalle-titulo">Mascota</span>
                                            <span class="reserva-vet-detalle-valor" id="agendar_resumen_mascota">-</span>
                                            <span class="reserva-vet-detalle-extra" id="agendar_resumen_tutor"></span>
                                        </div>
                                    </li>
                                    <li>
                                        <span class="reserva-vet-detalle-icono"><i class="feather icon-tag"></i></span>
                                        <div>
                                            <span class="reserva-vet-detalle-titulo">Total a pagar</span>
                                            <span class="reserva-vet-detalle-valor" id="agendar_resumen_total">-</span>
                                        </div>
                                    </li>
                                </ul>
                            </div>

                            {{-- servicios-cercanos da el estilo de las tarjetas que se marcan con un clic --}}
                            <div class="servicios-cercanos">
                                <div class="reserva-vet-lugares mb-2" role="radiogroup" aria-label="Forma de pago">
                                    <label class="reserva-vet-lugar">
                                        <input type="radio" class="reserva-vet-lugar-radio" name="forma_pago" value="efectivo" required>
                                        <span class="reserva-vet-lugar-caja">
                                            <span class="reserva-vet-lugar-icono"><i class="fas fa-money-bill-wave"></i></span>
                                            <span class="reserva-vet-lugar-datos">
                                                <span class="reserva-vet-lugar-nombre">Efectivo</span>
                                                <span class="reserva-vet-lugar-direccion">Se paga en el local.</span>
                                            </span>
                                            <span class="reserva-vet-lugar-marca" aria-hidden="true"><i class="feather icon-check"></i></span>
                                        </span>
                                    </label>
                                    <label class="reserva-vet-lugar">
                                        <input type="radio" class="reserva-vet-lugar-radio" name="forma_pago" value="en_linea" required>
                                        <span class="reserva-vet-lugar-caja">
                                            <span class="reserva-vet-lugar-icono"><i class="feather icon-credit-card"></i></span>
                                            <span class="reserva-vet-lugar-datos">
                                                <span class="reserva-vet-lugar-nombre">Pago en línea</span>
                                                <span class="reserva-vet-lugar-direccion">Tarjeta de débito o crédito por la pasarela de pago.</span>
                                            </span>
                                            <span class="reserva-vet-lugar-marca" aria-hidden="true"><i class="feather icon-check"></i></span>
                                        </span>
                                    </label>
                                </div>
                            </div>
                            <div class="reserva-vet-aviso d-none" id="aviso_forma_pago" role="alert">Elige la forma de pago.</div>

                            <div class="form-row mt-3 d-none" id="agendar_pago_efectivo">
                                <div class="form-group col-6 mb-0">
                                    {{-- Parte con el total a pagar; si el cliente entrega más, se cambia y se calcula el vuelto --}}
                                    <label class="floating-label-activo-sm" for="agendar_monto_recibido">Monto recibido</label>
                                    <input type="number" class="form-control form-control-sm" id="agendar_monto_recibido" name="monto_recibido" min="0" max="999999999" step="1" inputmode="numeric" required>
                                    <div class="invalid-feedback" id="aviso_monto_recibido">Ingresa el monto recibido.</div>
                                </div>
                                <div class="form-group col-6 mb-0">
                                    <label class="floating-label-activo-sm" for="agendar_vuelto">Vuelto</label>
                                    <input type="text" class="form-control form-control-sm" id="agendar_vuelto" placeholder="-" readonly tabindex="-1">
                                </div>
                            </div>
                            <div class="reserva-vet-lugar-vacio mt-3 d-none" id="agendar_pago_linea">
                                <i class="feather icon-lock"></i>
                                <span>Al confirmar, el cobro se completa en la pasarela de pago y la reserva queda a la espera del pago.</span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer reserva-vet-acciones">
                        <button type="button" class="btn btn-outline-dark reserva-vet-volver" id="agendar_cancelar" data-dismiss="modal"><i class="feather icon-x mr-1"></i>Cancelar</button>
                        <button type="button" class="btn btn-outline-secondary reserva-vet-volver d-none" id="agendar_anterior"><i class="feather icon-chevron-left mr-1"></i>Anterior</button>
                        <button type="button" class="btn btn-info" id="agendar_siguiente">Siguiente<i class="feather icon-chevron-right ml-1"></i></button>
                        <button type="submit" class="btn btn-info d-none" id="boton_agendar_hora"><i class="feather icon-check mr-1"></i>Confirmar reserva</button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</div>
@include('app.peluqueria.modal_estado_reserva')

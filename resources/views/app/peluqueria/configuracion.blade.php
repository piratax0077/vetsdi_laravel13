@extends('app.peluqueria.layout')
@section('content')
@php
    // Lugares de atención: la casa matriz (el perfil) siempre primero y luego las sucursales
    $lugares = collect([['valor' => 'matriz', 'nombre' => $perfil->nombre, 'matriz' => true]])
        ->merge(collect($sucursales ?? [])->map(fn ($sucursal) => [
            'valor' => (string) ($sucursal->id ?? $sucursal->nombre),
            'nombre' => $sucursal->nombre,
            'matriz' => false,
        ]));

    // Busca el lugar guardado en un registro, sea por su id o por su nombre
    $lugar_de = function ($guardado) use ($lugares) {
        $guardado = trim((string) $guardado);
        if ($guardado === '') {
            return null;
        }
        return $lugares->first(fn ($lugar) => $lugar['valor'] === $guardado || $lugar['nombre'] === $guardado);
    };
@endphp
<div class="page-header encabezado-configuracion">
    <h4>Panel de configuración</h4>
    {{-- Lugar de atención: deja en las tablas solo lo que corresponde al lugar elegido --}}
    <div class="filtro-lugar">
        <label for="filtro_lugar"><i class="feather icon-map-pin" aria-hidden="true"></i>Lugar de atención</label>
        <select class="form-control form-control-sm" id="filtro_lugar" onchange="filtrar_por_lugar();">
            <option value="todos">Todos los lugares</option>
            @foreach($lugares as $lugar)
                <option value="{{ $lugar['valor'] }}">{{ $lugar['nombre'] }}{{ $lugar['matriz'] ? ' (Casa matriz)' : '' }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <ul class="nav pill-moderno pill-moderno-white" role="tablist">
            <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#procedimientos" role="tab" aria-controls="procedimientos" aria-selected="true">Procedimientos</a></li>
            <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#equipo" role="tab" aria-controls="equipo" aria-selected="false">Equipo</a></li>
            <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#salas_boxes" role="tab" aria-controls="salas_boxes" aria-selected="false">Salas y boxes</a></li>
        </ul>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="tab-content">
            <!--Procedimientos-->
            <div class="tab-pane fade active show" id="procedimientos" role="tabpanel">
                <div class="card">
                    <div class="card-header encabezado-card">
                        <h5 class="encabezado-card-titulo"><span class="encabezado-card-icono"><i class="feather icon-scissors"></i></span>Procedimientos registrados</h5>
                        <div class="encabezado-card-acciones">
                            <button type="button" class="btn btn-info btn-sm" onclick="abrir_modal_procedimiento();"><i class="feather icon-plus mr-1"></i>Agregar procedimiento</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped nowrap tabla-procedimientos mb-0">
                                <thead>
                                    <tr>
                                        <th class="align-middle">Procedimiento</th>
                                        <th class="align-middle">Lugar de atención</th>
                                        <th class="align-middle">Duración</th>
                                        <th class="align-middle">Precio</th>
                                        <th class="align-middle">Cuidados e indicaciones</th>
                                        <th class="align-middle text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $kilos = fn ($valor) => str_replace('.', ',', rtrim(rtrim(number_format((float) $valor, 1, '.', ''), '0'), '.'));
                                        $pesos = fn ($valor) => '$' . number_format((int) $valor, 0, ',', '.');

                                        // A qué mascotas aplica un precio; sin dato se entiende que es para todas
                                        $mascota_precio = function (array $precio) use ($kilos) {
                                            $especie = $precio['especie'] ?? 'Todas';
                                            $exotico = trim((string) ($precio['especie_exotico'] ?? ''));
                                            $desde = $precio['peso_desde'] ?? null;
                                            $hasta = $precio['peso_hasta'] ?? null;
                                            $texto_especie = match (true) {
                                                $especie === 'Todas' => 'Todas las especies',
                                                $especie === 'Exótico' && $exotico !== '' => 'Exótico (' . $exotico . ')',
                                                default => $especie,
                                            };
                                            $texto_tamano = match ($precio['tamano'] ?? 'Todos') {
                                                'Pequeño' => 'Pequeño (hasta 10 kg)',
                                                'Mediano' => 'Mediano (10 a 25 kg)',
                                                'Grande' => 'Grande (más de 25 kg)',
                                                'Peso' => $desde !== null && $desde !== '' && $hasta !== null && $hasta !== ''
                                                    ? 'De ' . $kilos($desde) . ' a ' . $kilos($hasta) . ' kg'
                                                    : 'Por peso',
                                                default => 'Todos los tamaños',
                                            };
                                            return $texto_especie . ' · ' . $texto_tamano;
                                        };
                                    @endphp
                                    @forelse($procedimientos as $procedimiento)
                                        @php
                                            // Lista de precios del procedimiento; si todavía no tiene, se arma una con su precio general
                                            $precios_proc = $procedimiento->precios ?? [];
                                            if (is_string($precios_proc)) {
                                                $precios_proc = json_decode($precios_proc, true) ?: [];
                                            }
                                            $precios_proc = collect($precios_proc)->map(fn ($precio) => (array) $precio)->values();
                                            if ($precios_proc->isEmpty()) {
                                                $precios_proc = collect([[
                                                    'especie' => $procedimiento->especie ?? 'Todas',
                                                    'especie_exotico' => $procedimiento->especie_exotico ?? null,
                                                    'tamano' => $procedimiento->tamano ?? 'Todos',
                                                    'peso_desde' => $procedimiento->peso_desde ?? null,
                                                    'peso_hasta' => $procedimiento->peso_hasta ?? null,
                                                    'duracion' => $procedimiento->duracion,
                                                    'precio' => $procedimiento->precio,
                                                ]]);
                                            }

                                            // Sin lugar guardado, el procedimiento se entiende de la casa matriz
                                            $lugar_proc = $lugar_de($procedimiento->sucursal ?? null) ?? $lugares->first();
                                        @endphp
                                        <tr data-lugar="{{ $lugar_proc['valor'] }}">
                                            <td>
                                                {{-- Especie y tamaño bajo el nombre: cada guardado es un procedimiento para una mascota --}}
                                                <strong>{{ $procedimiento->nombre }}</strong>
                                                <span class="procedimiento-descripcion">{{ $mascota_precio($precios_proc->first()) }}</span>
                                            </td>
                                            <td>
                                                {{ $lugar_proc['nombre'] }}
                                                @if($lugar_proc['matriz'])
                                                    <span class="procedimiento-descripcion">Casa matriz</span>
                                                @endif
                                            </td>
                                            <td>{{ (int) ($precios_proc->first()['duracion'] ?? $procedimiento->duracion) }} min</td>
                                            <td>{{ $pesos($precios_proc->first()['precio'] ?? $procedimiento->precio) }}</td>
                                            <td>
                                                @if(trim((string) $procedimiento->indicaciones) !== '')
                                                    <span class="procedimiento-indicaciones" title="{{ $procedimiento->indicaciones }}">{{ $procedimiento->indicaciones }}</span>
                                                @else
                                                    <span class="text-muted">Sin indicaciones</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                {{-- Los datos del registro van en el botón para cargarlos tal cual en el modal --}}
                                                @php
                                                    $datos_procedimiento = [
                                                        'id' => $procedimiento->id,
                                                        'nombre' => $procedimiento->nombre,
                                                        'duracion' => $procedimiento->duracion,
                                                        'precio' => $procedimiento->precio,
                                                        'activo' => $procedimiento->activo ? 1 : 0,
                                                        'indicaciones' => $procedimiento->indicaciones,
                                                        'sucursal' => $lugar_proc['valor'],
                                                        'precios' => $precios_proc->all(),
                                                    ];
                                                @endphp
                                                <div class="procedimiento-acciones" data-procedimiento="{{ json_encode($datos_procedimiento) }}">
                                                    {{-- Disponibilidad: al moverlo se guarda el procedimiento con los mismos datos y el nuevo estado --}}
                                                    <div class="custom-control custom-switch" title="{{ $procedimiento->activo ? 'Activo' : 'Inactivo' }}">
                                                        <input type="checkbox" class="custom-control-input" id="disponible_{{ $procedimiento->id }}"
                                                            onchange="cambiar_disponibilidad(this);" @checked($procedimiento->activo)>
                                                        <label class="custom-control-label" for="disponible_{{ $procedimiento->id }}">{{ $procedimiento->activo ? 'Activo' : 'Inactivo' }}</label>
                                                    </div>
                                                    <button type="button" class="btn btn-info btn-icon btn-sm" title="Editar procedimiento" onclick="abrir_modal_procedimiento(this);">
                                                        <i class="feather icon-edit"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="procedimiento-vacio">Agrega servicios o procedimientos con su duración y precio.</td>
                                        </tr>
                                    @endforelse
                                    <tr class="fila-sin-lugar d-none">
                                        <td colspan="6" class="procedimiento-vacio">Este lugar de atención aún no tiene procedimientos.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <!--Cierre: Procedimientos-->

            <!--Equipo-->
            <div class="tab-pane fade" id="equipo" role="tabpanel">
                <div class="card">
                    <div class="card-header encabezado-card">
                        <h5 class="encabezado-card-titulo"><span class="encabezado-card-icono"><i class="feather icon-users"></i></span>Equipo de trabajo</h5>
                        <div class="encabezado-card-acciones">
                            <button type="button" class="btn btn-info btn-sm" onclick="abrir_modal_equipo();"><i class="feather icon-plus mr-1"></i>Agregar integrante</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped nowrap tabla-configuracion mb-0">
                                <thead>
                                    <tr>
                                        <th class="align-middle">Nombre / RUT</th>
                                        <th class="align-middle">Cargo</th>
                                        <th class="align-middle">Contacto</th>
                                        <th class="align-middle">Lugar de atención</th>
                                        <th class="align-middle">Estado</th>
                                        <th class="align-middle text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($equipo ?? [] as $integrante)
                                        @php
                                            // Los datos del integrante van en el botón para cargarlos tal cual en el modal
                                            $lugar_integrante = $lugar_de($integrante->sucursal ?? null);
                                            $datos_integrante = [
                                                'id' => $integrante->id ?? null,
                                                'rut' => $integrante->rut ?? '',
                                                'nombres' => $integrante->nombres ?? $integrante->nombre ?? '',
                                                'apellido_uno' => $integrante->apellido_uno ?? '',
                                                'apellido_dos' => $integrante->apellido_dos ?? '',
                                                'cargo' => $integrante->cargo ?? '',
                                                'sucursal' => $lugar_integrante['valor'] ?? '',
                                                'telefono' => $integrante->telefono ?? '',
                                                'email' => $integrante->email ?? '',
                                                'direccion' => $integrante->direccion ?? '',
                                                'id_region' => $integrante->id_region ?? '',
                                                'id_ciudad' => $integrante->id_ciudad ?? '',
                                                'activo' => !empty($integrante->activo) ? 1 : 0,
                                            ];
                                        @endphp
                                        <tr data-lugar="{{ $lugar_integrante['valor'] ?? '' }}">
                                            <td>
                                                <strong>{{ $integrante->nombre }}</strong>
                                                <span class="config-detalle">{{ $integrante->rut }}</span>
                                            </td>
                                            <td>{{ $integrante->cargo }}</td>
                                            <td>
                                                {{ $integrante->telefono }}
                                                <span class="config-detalle">{{ $integrante->email }}</span>
                                            </td>
                                            <td>{{ $integrante->sucursal ?? 'Sin lugar asignado' }}</td>
                                            <td>
                                                <span class="badge {{ !empty($integrante->activo) ? 'badge-success' : 'badge-secondary' }}">{{ !empty($integrante->activo) ? 'Activo' : 'Inactivo' }}</span>
                                            </td>
                                            <td class="text-center" data-equipo="{{ json_encode($datos_integrante) }}">
                                                <button type="button" class="btn btn-info btn-icon btn-sm" title="Editar integrante" onclick="abrir_modal_equipo(this);">
                                                    <i class="feather icon-edit"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="config-vacio">Aún no hay integrantes en el equipo.</td>
                                        </tr>
                                    @endforelse
                                    <tr class="fila-sin-lugar d-none">
                                        <td colspan="6" class="config-vacio">Este lugar de atención aún no tiene integrantes.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <!--Cierre: Equipo-->

            <!--Salas y boxes-->
            <div class="tab-pane fade" id="salas_boxes" role="tabpanel">
                <div class="card">
                    <div class="card-header encabezado-card">
                        <h5 class="encabezado-card-titulo"><span class="encabezado-card-icono"><i class="feather icon-grid"></i></span>Salas y boxes</h5>
                        <div class="encabezado-card-acciones">
                            <button type="button" class="btn btn-info btn-sm" onclick="abrir_modal_sala();"><i class="feather icon-plus mr-1"></i>Agregar sala o box</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped nowrap tabla-configuracion mb-0">
                                <thead>
                                    <tr>
                                        <th class="align-middle">Nombre</th>
                                        <th class="align-middle">Tipo</th>
                                        <th class="align-middle">Lugar de atención</th>
                                        <th class="align-middle">Uso</th>
                                        <th class="align-middle">Estado</th>
                                        <th class="align-middle text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($salas ?? [] as $sala)
                                        @php
                                            // Los datos de la sala van en el botón para cargarlos tal cual en el modal
                                            $lugar_sala = $lugar_de($sala->sucursal ?? null);
                                            $datos_sala = [
                                                'id' => $sala->id ?? null,
                                                'nombre' => $sala->nombre ?? '',
                                                'tipo' => $sala->tipo ?? '',
                                                'uso' => $sala->uso ?? '',
                                                'sucursal' => $lugar_sala['valor'] ?? '',
                                                'activo' => !empty($sala->activo) ? 1 : 0,
                                            ];
                                        @endphp
                                        <tr data-lugar="{{ $lugar_sala['valor'] ?? '' }}">
                                            <td><strong>{{ $sala->nombre }}</strong></td>
                                            <td>{{ $sala->tipo }}</td>
                                            <td>{{ $sala->sucursal ?? 'Sin lugar asignado' }}</td>
                                            <td>{{ $sala->uso ?? 'Sin detalle' }}</td>
                                            <td>
                                                <span class="badge {{ !empty($sala->activo) ? 'badge-success' : 'badge-secondary' }}">{{ !empty($sala->activo) ? 'Disponible' : 'No disponible' }}</span>
                                            </td>
                                            <td class="text-center" data-sala="{{ json_encode($datos_sala) }}">
                                                <button type="button" class="btn btn-info btn-icon btn-sm" title="Editar sala o box" onclick="abrir_modal_sala(this);">
                                                    <i class="feather icon-edit"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="config-vacio">Aún no hay salas ni boxes registrados.</td>
                                        </tr>
                                    @endforelse
                                    <tr class="fila-sin-lugar d-none">
                                        <td colspan="6" class="config-vacio">Este lugar de atención aún no tiene salas ni boxes.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <!--Cierre: Salas y boxes-->
        </div>
    </div>
</div>

@include('app.peluqueria.procedimiento_form')
@include('app.peluqueria.modal_equipo')
@include('app.peluqueria.modal_sala')

{{-- Formulario oculto del interruptor de disponibilidad: usa el mismo guardado del modal --}}
<form id="form_disponibilidad" method="post" action="{{ route('peluqueria.procedimientos.guardar') }}" class="d-none">
    @csrf
    <input type="hidden" name="id">
    <input type="hidden" name="nombre">
    <input type="hidden" name="activo">
    <input type="hidden" name="indicaciones">
    <input type="hidden" name="desde_interruptor" value="1">
    <div class="precios-ocultos"></div>
</form>
@endsection
@section('page-script')
<script>
    // Abre el modal vacío para agregar, o con los datos de la fila para editar
    function abrir_modal_procedimiento(boton, datos_directos) {
        var formulario = document.getElementById('form_procedimiento');
        var datos = datos_directos || (boton ? JSON.parse(boton.closest('[data-procedimiento]').getAttribute('data-procedimiento')) : null);

        var edicion = !!(datos && datos.id);

        formulario.reset();
        formulario.classList.remove('was-validated');
        formulario.elements.id.value = edicion ? datos.id : '';
        formulario.elements.id.disabled = !edicion;
        formulario.elements.nombre.value = datos ? datos.nombre : '';
        document.getElementById('procedimiento_activo').checked = datos ? String(datos.activo) === '1' : true;
        marcar_estado_procedimiento();
        formulario.elements.indicaciones.value = datos && datos.indicaciones ? datos.indicaciones : '';

        // Lugar de atención: el del procedimiento; uno nuevo parte con el lugar que se está mirando o la casa matriz
        var filtro = $('#filtro_lugar').val();
        var lugar = datos && datos.sucursal ? String(datos.sucursal) : (filtro && filtro !== 'todos' ? filtro : 'matriz');
        formulario.elements.sucursal.value = lugar;
        if (formulario.elements.sucursal.value !== lugar) {
            formulario.elements.sucursal.value = 'matriz';
        }

        // Especie, tamaño, duración y precio del procedimiento; uno nuevo parte para todas las mascotas
        var precio = datos && datos.precios && datos.precios.length ? datos.precios[0] : {};
        var valor = function (campo, por_defecto) {
            return precio[campo] !== null && precio[campo] !== undefined ? precio[campo] : por_defecto;
        };
        formulario.elements.especie.value = valor('especie', 'Todas');
        formulario.elements.tamano.value = valor('tamano', 'Todos');
        actualizar_mascota_procedimiento(null);
        formulario.elements.especie_exotico.value = valor('especie_exotico', '');
        formulario.elements.peso_desde.value = valor('peso_desde', '');
        formulario.elements.peso_hasta.value = valor('peso_hasta', '');
        formulario.elements.duracion.value = valor('duracion', 30);
        formulario.elements.precio.value = valor('precio', '');

        document.getElementById('titulo_modal_procedimiento').textContent = edicion ? 'Editar procedimiento' : 'Agregar procedimiento';
        document.getElementById('boton_guardar_procedimiento').disabled = false;
        document.getElementById('boton_guardar_procedimiento').innerHTML = edicion
            ? '<i class="feather icon-save mr-1"></i>Guardar cambios'
            : '<i class="feather icon-plus mr-1"></i>Agregar';

        $('#modal_procedimiento').modal('show');
    }

    // Deja en cada tabla solo las filas del lugar elegido; si no queda ninguna se avisa en la misma tabla
    function filtrar_por_lugar() {
        var lugar = $('#filtro_lugar').val();

        $('#procedimientos, #equipo, #salas_boxes').find('tbody').each(function () {
            var filas = $(this).children('tr[data-lugar]');
            var visibles = 0;

            filas.each(function () {
                var mostrar = lugar === 'todos' || String($(this).data('lugar')) === lugar;
                $(this).toggleClass('d-none', !mostrar);
                if (mostrar) {
                    visibles++;
                }
            });
            $(this).children('.fila-sin-lugar').toggleClass('d-none', !filas.length || visibles > 0);
        });

        // El lugar elegido se recuerda en este navegador para la próxima visita
        try {
            window.localStorage.setItem('peluqueria_filtro_lugar', lugar);
        } catch (error) {}
    }

    // Al cargar se vuelve al último lugar elegido, si todavía existe
    $(function () {
        var guardado = null;
        try {
            guardado = window.localStorage.getItem('peluqueria_filtro_lugar');
        } catch (error) {}
        if (guardado && $('#filtro_lugar option[value="' + guardado.replace(/"/g, '') + '"]').length) {
            $('#filtro_lugar').val(guardado);
        }
        filtrar_por_lugar();
    });

    // La pestaña elegida queda en la dirección, para volver a ella al recargar
    $(function () {
        var pestana = window.location.hash;
        if (pestana && $('.pill-moderno a[href="' + pestana + '"]').length) {
            $('.pill-moderno a[href="' + pestana + '"]').tab('show');
        }
        $('.pill-moderno a[data-toggle="tab"]').on('shown.bs.tab', function (evento) {
            if (window.history && window.history.replaceState) {
                window.history.replaceState(null, '', $(evento.target).attr('href'));
            }
        });
    });

    // "Exótico" muestra el campo para escribir qué animal es y "Por peso (kg)" el rango Desde / Hasta;
    // si se cambia a otra opción esos campos se ocultan, se vacían y no se envían
    function actualizar_mascota_procedimiento(elemento) {
        var exotico = $('#procedimiento_especie').val() === 'Exótico';
        var por_peso = $('#procedimiento_tamano').val() === 'Peso';
        var campo_exotico = $('#procedimiento_exotico');
        var campos_peso = $('#modal_procedimiento .rango-peso input');

        $('#procedimiento_exotico_grupo').toggleClass('d-none', !exotico);
        campo_exotico.prop('disabled', !exotico);
        if (!exotico) {
            campo_exotico.val('');
        }

        $('#modal_procedimiento .rango-peso').toggleClass('d-none', !por_peso);
        campos_peso.prop({ disabled: !por_peso, required: por_peso });
        if (!por_peso) {
            campos_peso.val('');
        }

        if (elemento && exotico && elemento.id === 'procedimiento_especie') {
            campo_exotico.trigger('focus');
        } else if (elemento && por_peso && elemento.id === 'procedimiento_tamano') {
            campos_peso.first().trigger('focus');
        }
    }

    // Equipo y Salas y boxes: el modal parte vacío cada vez que se abre
    function abrir_modal_registro(id_modal) {
        var modal = $('#' + id_modal);
        var formulario = modal.find('form').get(0);

        formulario.reset();
        formulario.classList.remove('was-validated');
        modal.find('.aviso-registro').addClass('d-none').text('');
        modal.find('.interruptor-estado').each(function () { marcar_interruptor_estado(this); });
        modal.find('option[data-region]').prop('hidden', false).prop('disabled', false);
        modal.modal('show');
    }

    // Equipo: abre el modal vacío para agregar, o con los datos de la fila para editar
    function abrir_modal_equipo(boton) {
        var datos = boton ? JSON.parse(boton.closest('[data-equipo]').getAttribute('data-equipo')) : null;
        var formulario = document.getElementById('form_equipo');
        var edicion = !!datos;

        abrir_modal_registro('modal_equipo');
        formulario.elements.id.value = edicion && datos.id ? datos.id : '';
        formulario.elements.id.disabled = !(edicion && datos.id);

        if (edicion) {
            $.each(['rut', 'nombres', 'apellido_uno', 'apellido_dos', 'telefono', 'email', 'direccion'], function (i, campo) {
                formulario.elements[campo].value = datos[campo] || '';
            });

            // Un cargo que no está en la lista (o uno que ya se quitó) se muestra como "Otro" con su nombre escrito
            var cargo = datos.cargo || '';
            $('#equipo_cargo').val(cargo);
            if (cargo !== '' && $('#equipo_cargo').val() !== cargo) {
                $('#equipo_cargo').val('Otro');
                $('#equipo_cargo_otro').val(cargo);
            }
            mostrar_cargo_otro(true);

            $('#equipo_sucursal').val(datos.sucursal ? String(datos.sucursal) : '');

            // Primero la región, para que la lista de comunas quede solo con las suyas
            $('#equipo_region').val(datos.id_region ? String(datos.id_region) : '').trigger('change');
            $('#equipo_ciudad').val(datos.id_ciudad ? String(datos.id_ciudad) : '');

            $('#equipo_activo').prop('checked', String(datos.activo) === '1');
            marcar_interruptor_estado(document.getElementById('equipo_activo'));
        }

        $('#titulo_modal_equipo').text(edicion ? 'Editar integrante' : 'Agregar integrante');
        $('#boton_guardar_equipo').html(edicion
            ? '<i class="feather icon-save mr-1"></i>Guardar cambios'
            : '<i class="feather icon-plus mr-1"></i>Agregar');
    }

    // Salas y boxes: abre el modal vacío para agregar, o con los datos de la fila para editar
    function abrir_modal_sala(boton) {
        var datos = boton ? JSON.parse(boton.closest('[data-sala]').getAttribute('data-sala')) : null;
        var formulario = document.getElementById('form_sala');
        var edicion = !!datos;

        abrir_modal_registro('modal_sala');
        formulario.elements.id.value = edicion && datos.id ? datos.id : '';
        formulario.elements.id.disabled = !(edicion && datos.id);

        if (edicion) {
            formulario.elements.nombre.value = datos.nombre || '';
            formulario.elements.uso.value = datos.uso || '';
            $('#sala_tipo').val(datos.tipo || '');
            $('#sala_sucursal').val(datos.sucursal ? String(datos.sucursal) : '');
            $('#sala_activo').prop('checked', String(datos.activo) === '1');
            marcar_interruptor_estado(document.getElementById('sala_activo'));
        }

        $('#titulo_modal_sala').text(edicion ? 'Editar sala o box' : 'Agregar sala o box');
        $('#boton_guardar_sala').html(edicion
            ? '<i class="feather icon-save mr-1"></i>Guardar cambios'
            : '<i class="feather icon-plus mr-1"></i>Agregar');
    }

    // Equipo: con el cargo "Otro" aparece el campo para escribirlo a mano
    function mostrar_cargo_otro(sin_foco) {
        var otro = $('#equipo_cargo').val() === 'Otro';
        $('#equipo_cargo_otro_grupo').toggleClass('d-none', !otro);
        $('#equipo_cargo_otro').prop('disabled', !otro);
        if (otro && sin_foco !== true) {
            $('#equipo_cargo_otro').trigger('focus');
        } else if (!otro) {
            $('#equipo_cargo_otro').val('');
        }
    }
    $('#equipo_cargo').on('change', mostrar_cargo_otro);
    $('#modal_equipo').on('show.bs.modal', function () {
        setTimeout(function () { mostrar_cargo_otro(true); }, 0);
    });

    // Equipo: la comuna se elige antes que la región, y la región se completa sola con la de la comuna
    $('#equipo_ciudad').on('change', function () {
        var region = $(this).find('option:selected').data('region');
        if (region) {
            $('#equipo_region').val(String(region));
        }
    });

    // Si se cambia la región a mano, la lista de comunas deja solo las de esa región
    $('#equipo_region').on('change', function () {
        var region = String($(this).val());
        var comuna = $('#equipo_ciudad');

        comuna.find('option[data-region]').each(function () {
            var visible = region === '' || String($(this).data('region')) === region;
            $(this).prop('hidden', !visible).prop('disabled', !visible);
        });
        if (comuna.find('option:selected').prop('disabled')) {
            comuna.val('');
        }
    });

    // El texto de cada interruptor de estado dice cómo queda el registro
    function marcar_interruptor_estado(interruptor) {
        var datos = $(interruptor).data();
        $('label[for="' + interruptor.id + '"]').text(interruptor.checked ? datos.textoSi : datos.textoNo);
    }
    $('.interruptor-estado').on('change', function () { marcar_interruptor_estado(this); });

    // Estos registros todavía no tienen dónde guardarse: se revisan los datos y se avisa, sin enviar
    $('.form-registro-pendiente').on('submit', function (evento) {
        evento.preventDefault();
        var aviso = $(this).find('.aviso-registro');

        $(this).find('input[type="text"]').each(function () { this.value = this.value.trim(); });
        if (!this.checkValidity()) {
            this.classList.add('was-validated');
            aviso.addClass('d-none');
            return;
        }
        aviso.removeClass('d-none').text('Los datos están completos. ' + $(this).data('aviso'));
    });

    // El texto del interruptor del modal dice cómo queda el procedimiento
    function marcar_estado_procedimiento() {
        var activo = $('#procedimiento_activo').prop('checked');
        $('label[for="procedimiento_activo"]').text(activo ? 'Procedimiento activo' : 'Procedimiento inactivo');
    }
    $('#procedimiento_activo').on('change', marcar_estado_procedimiento);

    // El interruptor guarda el procedimiento tal como está, cambiando solo si queda activo o inactivo
    function cambiar_disponibilidad(interruptor) {
        var datos = JSON.parse(interruptor.closest('[data-procedimiento]').getAttribute('data-procedimiento'));
        var formulario = document.getElementById('form_disponibilidad');
        var etiqueta = interruptor.parentNode.querySelector('.custom-control-label');

        formulario.elements.id.value = datos.id;
        formulario.elements.nombre.value = datos.nombre;
        formulario.elements.activo.value = interruptor.checked ? 1 : 0;
        formulario.elements.indicaciones.value = datos.indicaciones || '';

        // Especie, tamaño, duración y precio viajan igual que en el modal, para no perderlos al cambiar solo el estado
        var contenedor = $(formulario).find('.precios-ocultos').empty();
        $.each((datos.precios || [])[0] || {}, function (campo, valor) {
            if (valor !== null && valor !== undefined && valor !== '') {
                $('<input type="hidden">').attr('name', campo).val(valor).appendTo(contenedor);
            }
        });

        etiqueta.textContent = interruptor.checked ? 'Activo' : 'Inactivo';
        $('.tabla-procedimientos .custom-control-input').prop('disabled', true);
        formulario.submit();
    }

    // Se envía exactamente lo escrito en el modal; si falta algo se marca el campo en vez de enviar
    document.getElementById('form_procedimiento').addEventListener('submit', function (evento) {
        this.elements.nombre.value = this.elements.nombre.value.trim();

        this.elements.especie_exotico.value = this.elements.especie_exotico.value.trim();

        // El peso "Hasta" tiene que ser mayor que "Desde"
        var desde = this.elements.peso_desde;
        var hasta = this.elements.peso_hasta;
        hasta.setCustomValidity('');
        if (!hasta.disabled && hasta.value !== '' && desde.value !== '' && parseFloat(hasta.value) <= parseFloat(desde.value)) {
            hasta.setCustomValidity('Debe ser mayor que Desde');
        }

        if (!this.checkValidity()) {
            evento.preventDefault();
            this.classList.add('was-validated');
            return;
        }

        document.getElementById('boton_guardar_procedimiento').disabled = true;
    });

    @if($errors->any() && old('nombre') !== null)
        @php
            $procedimiento_escrito = [
                'id' => old('id'),
                'nombre' => old('nombre'),
                'activo' => old('activo', 1),
                'indicaciones' => old('indicaciones'),
                'sucursal' => old('sucursal'),
                'precios' => [[
                    'especie' => old('especie'), 'especie_exotico' => old('especie_exotico'), 'tamano' => old('tamano'),
                    'peso_desde' => old('peso_desde'), 'peso_hasta' => old('peso_hasta'), 'duracion' => old('duracion'), 'precio' => old('precio'),
                ]],
            ];
        @endphp
        // Si el guardado volvió con un error, el modal se abre de nuevo con lo que se había escrito
        $(function () {
            abrir_modal_procedimiento(null, @json($procedimiento_escrito));
        });
    @endif
</script>
@endsection

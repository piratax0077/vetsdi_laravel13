@extends('app.peluqueria.layout')
@php
    // El perfil se busca aquí mismo para el menú: la pantalla solo muestra datos
    $perfil = \App\Models\Peluqueria::where('id_usuario', Auth::id())->first();
    abort_unless($perfil, 403);

    // La casa matriz es el lugar del perfil: siempre va primero en la tabla
    $ciudad_matriz = !empty($perfil->id_ciudad) ? \App\Models\Ciudad::find($perfil->id_ciudad) : null;
    $region_matriz = $ciudad_matriz ? \App\Models\Region::find($ciudad_matriz->id_region) : null;
    $ubicacion_matriz = collect([$region_matriz->nombre ?? null, $ciudad_matriz->nombre ?? null])->filter()->implode(', ');
    $horario_matriz = $perfil->horario ?? null;
    $datos_matriz = [
        'matriz' => true,
        'nombre' => $perfil->nombre,
        'telefono' => $perfil->telefono ?? '',
        'email' => Auth::user()->email ?? '',
        'direccion' => $perfil->direccion ?? '',
        'id_region' => $region_matriz->id ?? null,
        'id_ciudad' => $ciudad_matriz->id ?? null,
        'activo' => 1,
        'horario' => is_string($horario_matriz) ? json_decode($horario_matriz, true) : $horario_matriz,
        'horario_modo' => $perfil->horario_modo ?? null,
    ];
@endphp
@section('content')
<div class="page-header"><h4>Lugares de atención</h4><p>Administra los lugares donde atiende tu peluquería.</p></div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header encabezado-card">
                <h5 class="encabezado-card-titulo"><span class="encabezado-card-icono"><i class="feather icon-map-pin"></i></span>Lugares de atención</h5>
                <div class="encabezado-card-acciones">
                    <button type="button" class="btn btn-info btn-sm" onclick="abrir_modal_sucursal();"><i class="feather icon-plus mr-1"></i>Agregar lugar de atención</button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped nowrap tabla-configuracion mb-0">
                        <thead>
                            <tr>
                                <th class="align-middle">Lugar de atención</th>
                                <th class="align-middle">Dirección</th>
                                <th class="align-middle">Contacto</th>
                                <th class="align-middle">Horario</th>
                                <th class="align-middle">Estado</th>
                                <th class="align-middle text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Casa matriz: los datos del perfil, siempre en la primera fila --}}
                            <tr class="fila-casa-matriz">
                                <td>
                                    <strong>{{ $perfil->nombre }}</strong>
                                    <span class="badge badge-casa-matriz">Casa matriz</span>
                                </td>
                                <td>
                                    <span class="dato-icono"><i class="feather icon-map-pin icono-purple" aria-hidden="true"></i>{{ $perfil->direccion ?: 'Sin dirección' }}</span>
                                    <span class="config-detalle dato-icono-detalle">{{ $ubicacion_matriz }}</span>
                                </td>
                                <td>
                                    <span class="dato-icono"><i class="feather icon-phone icono-purple" aria-hidden="true"></i>{{ $perfil->telefono ?: 'Sin teléfono' }}</span>
                                    <span class="dato-icono"><i class="feather icon-mail icono-purple" aria-hidden="true"></i>{{ Auth::user()->email ?: 'Sin correo' }}</span>
                                </td>
                                <td>
                                    @include('app.peluqueria.horario_resumen', ['horario' => $perfil->horario ?? []])
                                </td>
                                <td>
                                    <span class="badge badge-success">Activo</span>
                                </td>
                                <td class="text-center">
                                    {{-- La casa matriz se edita en el mismo modal y se guarda como el perfil --}}
                                    <button type="button" class="btn btn-info btn-icon btn-sm" title="Editar lugar de atención" data-sucursal="{{ json_encode($datos_matriz) }}" onclick="abrir_modal_sucursal(this);">
                                        <i class="feather icon-edit"></i>
                                    </button>
                                </td>
                            </tr>
                            @foreach($sucursales ?? [] as $sucursal)
                                @php
                                    // Región y comuna con el mismo criterio del perfil: la región sale de la comuna si no viene
                                    $id_region_sucursal = $sucursal->id_region ?? null;
                                    if (!$id_region_sucursal && !empty($sucursal->id_ciudad)) {
                                        $id_region_sucursal = \App\Models\Ciudad::find($sucursal->id_ciudad)->id_region ?? null;
                                    }
                                    $datos_sucursal = [
                                        'id' => $sucursal->id ?? null,
                                        'nombre' => $sucursal->nombre,
                                        'telefono' => $sucursal->telefono ?? '',
                                        'email' => $sucursal->email ?? '',
                                        'direccion' => $sucursal->direccion ?? '',
                                        'id_region' => $id_region_sucursal,
                                        'id_ciudad' => $sucursal->id_ciudad ?? null,
                                        'activo' => !empty($sucursal->activo) ? 1 : 0,
                                        'horario' => is_string($sucursal->horario ?? null) ? json_decode($sucursal->horario, true) : ($sucursal->horario ?? null),
                                        'horario_modo' => $sucursal->horario_modo ?? null,
                                    ];
                                @endphp
                                <tr>
                                    <td><strong>{{ $sucursal->nombre }}</strong></td>
                                    <td>
                                        <span class="dato-icono"><i class="feather icon-map-pin icono-purple" aria-hidden="true"></i>{{ $sucursal->direccion ?: 'Sin dirección' }}</span>
                                        <span class="config-detalle dato-icono-detalle">{{ collect([$sucursal->region ?? null, $sucursal->comuna ?? null])->filter()->implode(', ') }}</span>
                                    </td>
                                    <td>
                                        <span class="dato-icono"><i class="feather icon-phone icono-purple" aria-hidden="true"></i>{{ $sucursal->telefono ?: 'Sin teléfono' }}</span>
                                        <span class="dato-icono"><i class="feather icon-mail icono-purple" aria-hidden="true"></i>{{ ($sucursal->email ?? '') ?: 'Sin correo' }}</span>
                                    </td>
                                    <td>
                                        @include('app.peluqueria.horario_resumen', ['horario' => $sucursal->horario ?? []])
                                    </td>
                                    <td>
                                        <span class="badge {{ !empty($sucursal->activo) ? 'badge-success' : 'badge-secondary' }}">{{ !empty($sucursal->activo) ? 'Activo' : 'Inactivo' }}</span>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-info btn-icon btn-sm" title="Editar lugar de atención" data-sucursal="{{ json_encode($datos_sucursal) }}" onclick="abrir_modal_sucursal(this);">
                                            <i class="feather icon-edit"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@include('app.peluqueria.modal_sucursal')
@endsection
@section('page-script')
<script src="{{ asset('js/peluqueria-horario.js') }}"></script>
<script>
    // Agregar o editar usan el mismo modal: vacío para agregar, con los datos del lugar para editar
    function abrir_modal_sucursal(boton) {
        var formulario = document.getElementById('form_sucursal');
        var datos = boton ? JSON.parse(boton.getAttribute('data-sucursal')) : null;
        var edicion = !!datos;
        var matriz = edicion && !!datos.matriz;

        formulario.reset();
        // La casa matriz usa el guardado del perfil; su correo es el de la cuenta y siempre queda activa
        formulario.dataset.matriz = matriz ? '1' : '';
        formulario.elements.email.disabled = matriz;
        $('#sucursal_activo').prop('disabled', matriz);
        formulario.classList.remove('was-validated');
        formulario.elements.id.value = edicion && datos.id ? datos.id : '';
        formulario.elements.id.disabled = !edicion || matriz;
        formulario.elements.nombre.value = edicion ? datos.nombre || '' : '';
        formulario.elements.telefono.value = edicion ? datos.telefono || '' : '';
        formulario.elements.email.value = edicion ? datos.email || '' : '';
        formulario.elements.direccion.value = edicion ? datos.direccion || '' : '';
        formulario.elements.id_region.value = edicion && datos.id_region ? datos.id_region : '';
        cargar_comunas_sucursal(edicion ? datos.id_ciudad : null);
        $('#sucursal_activo').prop('checked', edicion ? String(datos.activo) === '1' : true);
        marcar_estado_sucursal();
        horario_atencion.cargar($('#form_sucursal .horario-editor'), edicion ? datos.horario : null, edicion ? datos.horario_modo : null);

        $('#titulo_modal_sucursal').text(edicion ? 'Editar lugar de atención' : 'Agregar lugar de atención');
        $('#boton_guardar_sucursal').prop('disabled', false).html(edicion
            ? '<i class="feather icon-save mr-1"></i>Guardar cambios'
            : '<i class="feather icon-plus mr-1"></i>Agregar');
        $('#aviso_sucursal').addClass('d-none').text('');
        $('#modal_sucursal').modal('show');
    }

    // El texto del interruptor dice cómo queda la sucursal
    function marcar_estado_sucursal() {
        var activa = $('#sucursal_activo').prop('checked');
        $('label[for="sucursal_activo"]').text(activa ? 'Lugar activo' : 'Lugar inactivo');
    }
    $('#sucursal_activo').on('change', marcar_estado_sucursal);

    // Comunas de la región elegida, con la misma búsqueda del resto del sistema; al editar queda marcada la del lugar
    function cargar_comunas_sucursal(comuna_elegida) {
        var comuna = $('#sucursal_ciudad');
        var region = $('#sucursal_region').val();

        if (!region) {
            comuna.empty().append(new Option('Primero seleccione la región', ''));
            return;
        }

        comuna.empty().append(new Option('Cargando comunas...', ''));
        $.getJSON(@json(route('buscar_ciudad_region')), { region: region })
            .done(function (ciudades) {
                comuna.empty().append(new Option('Seleccione su comuna', ''));
                $.each(ciudades || [], function (i, ciudad) {
                    comuna.append(new Option(ciudad.nombre, ciudad.id));
                });
                if (comuna_elegida) {
                    comuna.val(String(comuna_elegida));
                }
            })
            .fail(function () {
                comuna.empty().append(new Option('No fue posible cargar las comunas', ''));
            });
    }
    $('#sucursal_region').on('change', function () {
        cargar_comunas_sucursal(null);
    });

    // La casa matriz se envía al mismo guardado de Mi perfil; las sucursales todavía no tienen dónde guardarse
    document.getElementById('form_sucursal').addEventListener('submit', function (evento) {
        evento.preventDefault();
        this.elements.nombre.value = this.elements.nombre.value.trim();
        horario_atencion.validar($(this).find('.horario-editor'));
        if (!this.checkValidity()) {
            this.classList.add('was-validated');
            $('#aviso_sucursal').addClass('d-none');
            return;
        }
        if (this.dataset.matriz) {
            this.method = 'post';
            this.action = @json(route('peluqueria.perfil'));
            $('#boton_guardar_sucursal').prop('disabled', true);
            this.submit();
            return;
        }
        $('#aviso_sucursal').removeClass('d-none').text('Los datos están completos. El guardado de lugares de atención aún no está disponible.');
    });
</script>
@endsection

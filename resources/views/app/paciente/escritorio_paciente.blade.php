@extends('template.usuario.template')
@section('content')
<div class="pcoded-main-container" >
    <div class="pcoded-content">
        <!--Header-->
  
        <div class="page-header encabezado-escritorio">
            <div class="page-block">
                <div class="row align-items-center">
                    <div class="col-md-12">
                        @include('template.include.saludo_escritorio', ['nombre_saludo' => $paciente->nombres, 'texto_saludo' => 'Bienvenido/a a tu escritorio de cuidado de mascotas'])
                        <!--<ul class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ ROUTE('paciente.home') }}">Bienvenido/a a tu escritorio de cuidado de mascotas</a>
                            </li>
                        </ul>-->
                    </div>
                </div>
            </div>
        </div>
        <!--Cierre: Header-->

        <div class="form-row m-b-30 mt-n2">
            <div class="col-md-12">
                <div class="card-deck">
                    <div class="card subir">
                        <a href="{{ route('paciente.mascotas.index') }}">
                            <div class="card-body text-center py-3" style="cursor:pointer">
                                <img class="wid-50 text-center mt-2" src="{{ asset('images/iconos/mascotas.svg') }}">
                                <h5 class="mt-2"> Mis <br>mascotas</h5>
                            </div>
                        </a>
                    </div>
                    <div class="card subir">
                          <a href="{{ ROUTE('paciente.mascotas.suscripcion_servicios') }}"> 
                            <div class="card-body text-center py-3" style="cursor:pointer">
                                <img class="wid-50 text-center mt-2" src="{{ asset('images/iconos/alimento.png') }}">
                                <h5 class="mt-2"> Servicios cercanos </h5>
                            </div>
                          </a>
                    </div>
                    <div class="card subir">
                        <a href="{{ route('paciente.integraciones.alimentos') }}" target="_blank" rel="noopener">
                            <div class="card-body text-center py-3" style="cursor:pointer">
                                <img class="wid-50 text-center mt-2" src="{{ asset('images/iconos/alimento.png') }}" alt="Alimentos">
                                <h5 class="mt-2">Alimentos</h5>
                                <small class="text-muted">Acceso con tu cuenta VET SDI</small>
                            </div>
                        </a>
                    </div>
                    <div class="card subir">
                        <a href="{{ route('paciente.integraciones.farmacia') }}" target="_blank" rel="noopener">
                            <div class="card-body text-center py-3" style="cursor:pointer">
                                <img class="wid-50 text-center mt-2" src="{{ asset('images/iconos/i-medic.svg') }}" alt="Farmacia">
                                <h5 class="mt-2">Farmacia</h5>
                                <small class="text-muted">Farmacia Central</small>
                            </div>
                        </a>
                    </div>
                    <!--<div class="card subir">
                          <a href="{{ ROUTE('paciente.mascotas.pagos_suscripcion') }}">
                            <div class="card-body text-center py-3" style="cursor:pointer">
                                <img class="wid-50 text-center mt-2" src="{{ asset('images/iconos/alimento.png') }}">
                                <h5 class="mt-2"> Suscripciones y facturación </h5>
                            </div>
                        </a>
                    </div>-->
                    <!--<div class="card subir">
                        <a href="{{ ROUTE('paciente.mascotas.inscripcion_medicamentos') }}">
                            <div class="card-body text-center py-3" style="cursor:pointer">
                                <img class="wid-50 text-center mt-2" src="{{ asset('images/iconos/i-medic.svg') }}">
                                <h5 class="mt-2"> Registro de <br>mis medicamentos</h5>
                            </div>
                        </a>
                    </div>-->
                    <!--<div class="card subir">
                        <a href="{{ ROUTE('paciente.convenios') }}">
                            <div class="card-body text-center py-3" style="cursor:pointer">
                                <img class="wid-50 text-center mt-2" src="{{ asset('images/iconos/convenios.png') }}">
                                <h5 class="mt-2"> Mis convenios <br>de atención</h5>
                            </div>
                        </a>
                    </div>-->
                    {{--  <div class="card subir">
                        <a href="https://www.cronicos.cl/" class="btn" type="button">
                            <div class="card-body text-center py-3" style="cursor:pointer">
                                <img class="wid-50 text-center" src="{{ asset('images/iconos/profesionales.svg') }}">
                                <h5 class="mt-2"> Controles de Crónicos</h5>
                            </div>
                        </a>
                        {{--  <a href="https://www.cronicos.cl/" class="btn" type="button">
                            <div class="card-body">
                                <div class="row">
                                    <h5 class="card-body text-center">Controles de Crónicos </h5>
                                    <img class="wid-70 ml-auto" src="{{ asset('images/iconos/cronicos.svg') }}">
                                </div>
                            </div>
                        </a>  --}}
                        {{--  <a href="{{ ROUTE('check_sdi') }}?urla=Inicio&urln=Mi_Ficha_Medica">
                            <div class="card-body text-center py-3" style="cursor:pointer">
                                <img class="wid-50 text-center" src="{{ asset('images/iconos/fmu.svg') }}">
                                <h5 class="mt-1"> Controles de Crónicos </h5>
                            </div>
                        </a>  --}}
                    {{--  </div>  --}}

                </div>
            </div>
        </div>
        @if(($mascotasIncompletas ?? collect())->isNotEmpty())
        <div class="alert alert-warning d-flex justify-content-between align-items-center"><div><strong>Completa la ficha de {{ $mascotasIncompletas->pluck('nombre')->join(', ') }}</strong><br><small>La inscripción rápida desde Veterfarma guardó los datos esenciales. Faltan antecedentes para completar su ficha.</small></div><a href="{{ route('paciente.mis_mascotas') }}" class="btn btn-warning btn-sm">Completar ahora</a></div>
        @endif

        <!--PROX CITAS DE MASCOTAS-->
        <div class="row m-b-30" >
            <div class="col-md-8">
                <div class="card h-100 pb-0" >
                    <div class="card-header">
                        <h5><i class="feather icon-calendar mr-2"></i> Mis próximas citas</h5>
                    </div>
                    <div class="card-body pt-4 pb-0" style="height:290px;">
                        <div class="dt-responsive table-responsive" style="height:290px;back">
                            <table id="simpletable" class="table table-striped table-bordered nowrap table-xs">
                                <thead>
                                    <tr>
                                        <th>Acción</th>
                                        <th>Mascota</th>
                                        <th>Profesional</th>
                                        <th>Información de Atención</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($hora_medica as $hora)
                                        <tr>
                                            <td class="align-middle">
                                                @if (in_array((int) ($hora->id_estado_visual ?? $hora->id_estado), [1, 8, 16], true))
                                                    <button class="btn btn-info btn-icon btn-confirmar-hora"
                                                        data-toggle="tooltip" data-placement="top" title="Confirmar hora"
                                                        onclick="confirmar({{ $hora->id }});">
                                                        <i class="feather icon-check"></i>
                                                    </button>
                                                    <button class="btn btn-danger btn-icon btn-anular-hora"
                                                        data-toggle="tooltip" data-placement="top" title="Anular hora"
                                                        onclick="anular({{ $hora->id }});">
                                                        <i class="feather icon-x"></i>
                                                    </button>
                                                @elseif (($hora->id_estado_visual ?? $hora->id_estado) == 2)
                                                    <button class="btn btn-info btn-icon btn-confirmar-hora"
                                                        data-toggle="tooltip" data-placement="top" title="Hora confirmada"
                                                        disabled="disabled">
                                                        <i class="feather icon-check"></i>
                                                    </button>
                                                    <button class="btn btn-danger btn-icon btn-anular-hora"
                                                        data-toggle="tooltip" data-placement="top" title="Anular hora"
                                                        onclick="anular({{ $hora->id }});">
                                                        <i class="feather icon-x"></i>
                                                    </button>
                                                @else
                                                    <button class="btn btn-info btn-icon btn-confirmar-hora"
                                                        data-toggle="tooltip" data-placement="top" title="Confirmar hora"
                                                        disabled="disabled">
                                                        <i class="feather icon-check"></i>
                                                    </button>
                                                    <button class="btn btn-danger btn-icon btn-anular-hora"
                                                        data-toggle="tooltip" data-placement="top" title="Anular hora"
                                                        disabled="disabled">
                                                        <i class="feather icon-x"></i>
                                                    </button>
                                                @endif
                                            </td>
                                            <td class="align-middle">
                                                <strong>{{ $hora->nombre_mascota ?: 'Sin mascota' }}</strong>
                                                @if (!empty($hora->nombre_especie_mascota))
                                                    <br>{{ $hora->nombre_especie_mascota }}
                                                @endif
                                            </td>
                                            <td>
                                                {{ $hora->nombre_profesional_completo }}<br>
                                                {{ $hora->nombre_especialidad_resumen }}
                                            </td>
                                            <td>
                                                {{ $hora->nombre_lugar_atencion }}<br>
                                                {{ $hora->direccion_lugar_atencion }}<br>
                                                <span style="font-weight:bold;">
                                                    {{ \Carbon\Carbon::parse($hora->fecha_consulta . ' ' . $hora->hora_inicio)->format('d-m-Y H:i') }}
                                                    hrs
                                                </span>
                                            </td>
                                            <td class="align-middle">
                                                @php $estadoVisualHora = (int) ($hora->id_estado_visual ?? $hora->id_estado); @endphp
                                                <span class="estado-hora-chip {{ in_array($estadoVisualHora, [1, 8, 16], true) ? 'pendiente' : ($estadoVisualHora == 2 ? 'confirmada' : ($estadoVisualHora == 3 ? 'cancelada' : (in_array($estadoVisualHora, [4, 5], true) ? 'en-proceso' : ($estadoVisualHora == 6 ? 'realizada' : ($estadoVisualHora == 7 ? 'inasistida' : 'desconocida'))))) }}">
                                                    {{ $hora->texto_estado }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center">No existen registros</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 columna-beneficios">
                <!-- Vouchers de Veterfarma y Alimentos, se cargan por ajax -->
                <section class="card beneficios-mascota" id="vouchers-veterfarma-card">
                    <div class="card-header beneficios-mascota-encabezado">
                        <span class="beneficios-mascota-icono"><i class="feather icon-tag"></i></span>
                        <div class="beneficios-mascota-titulos">
                            <h5>Beneficios para tu mascota</h5>
                            <small>Veterfarma y Alimentos</small>
                        </div>
                        <span class="beneficios-mascota-contador d-none" id="vouchers-veterfarma-contador"></span>
                        <button type="button" class="beneficios-mascota-recargar" id="vouchers-veterfarma-recargar" title="Actualizar beneficios" aria-label="Actualizar beneficios">
                            <i class="feather icon-refresh-cw"></i>
                        </button>
                    </div>
                    <div class="beneficios-mascota-cuerpo" id="vouchers-veterfarma-list" aria-live="polite">
                        <div class="beneficios-mascota-cargando" aria-label="Consultando beneficios"><span></span><span></span></div>
                    </div>
                </section>

                <div class="card subir text-center espacio-promocional">
                    <a href="{{ ROUTE('paciente.mascotas.promociones_especiales') }}" class="espacio-promocional-imagen">
                        <img src="{{ asset('images/iconos/publicidad.jpg') }}" alt="Espacios promocionales">
                    </a>
                    <div class="card-body espacio-promocional-pie">
                        <a href="{{ ROUTE('paciente.mascotas.promociones_generales') }}" class="btn btn-arrastre">
                            <h5 class="card-title mb-0">Visitar Espacios Promocionales</h5>
                        </a>
                    </div>
                </div>
            </div>
            <!-- AGREGAR GEOLOCALIZACION -->
        </div>

        <!--Cierre: Botones acceso examenes y profesional no inscrito-->

        <!--Row Botones-->
        <div class="row">
            <div class="col-md-12">
                <div class="card-deck">
					<div class="card social-widget-card opacidad px-0 bg-info">
						<a href="{{ route('paciente.referidos.index') }}" class="btn" type="button">
							<div class="card-body">
								<img class="wid-40 mb-2" src="{{ asset('images/iconos/referidos.svg') }}">
								<h6 class="my-auto text-white f-18">Invita y gana con Veterchile</h6>
							</div>
						</a>
					</div>
                  	<div class="card social-widget-card  opacidad px-0 bg-info">
						<a href="{{ route('app.descarga') }}" class="btn" type="button" target="_blank">
							<div class="card-body">
								<img class="wid-40 mb-2" src="{{ asset('images/iconos/descargar-app.svg') }}">
								<h6 class="my-auto text-white f-18">Descarga VetPass</h6>
							</div>
						</a>
					</div>
                </div>
            </div>
        </div>
        <!--Cierre: Row Botones-->
    </div>
</div>
@endsection

@section('page-styles')
    <style>
        .estado-hora-chip {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 999px;
            color: #fff;
            font-size: 0.7rem;
            font-weight: 600;
            line-height: 1.1;
            text-align: center;
            white-space: normal;
        }

        .estado-hora-chip.cancelada {
            background-color: #ff3d4f;
        }

        .estado-hora-chip.pendiente {
            background-color: #ffb640;
        }

        .estado-hora-chip.confirmada {
            background-color: #6ac000;
        }

        .estado-hora-chip.en-proceso {
            background-color: #7d4bc4;
        }

        .estado-hora-chip.realizada {
            background-color: #17c1c1;
        }

        .estado-hora-chip.inasistida {
            background-color: #6c757d;
        }

        .estado-hora-chip.desconocida {
            background-color: #4a5568;
        }
    </style>
@endsection

@section('page-script')
    <script>
        function obtenerClaseEstadoHora(idEstado)
        {
            switch (parseInt(idEstado, 10))
            {
                case 1:
                case 8:
                case 16:
                    return 'pendiente';

                case 2:
                    return 'confirmada';

                case 3:
                    return 'cancelada';

                case 4:
                case 5:
                    return 'en-proceso';

                case 6:
                    return 'realizada';

                case 7:
                    return 'inasistida';

                default:
                    return 'desconocida';
            }
        }

        function confirmar(id)
        {
            $('.btn-confirmar-hora').attr('disabled', true);
            $('.btn-anular-hora').attr('disabled', true);

            let url = "{{ route('paciente.hora.medica.confirmar') }}";

            $.ajax({
                url: url,
                type: "POST",
                data: {
                    id_hora: id,
                    _token: CSRF_TOKEN,
                },
                success: function(data)
                {
                    if (data != null && parseInt(data.estado, 10) === 1)
                    {
                        swal({
                            title: "Exito!",
                            text: "Se ha confirmado su hora medica",
                            type: "success",
                        });

                        cargar_horas_medicas();
                    }
                    else
                    {
                        swal({
                            title: "Error!",
                            text: "Se ha presentado un problema en la confirmación su hora medica.\n Intente de nuevo.",
                            type: "success",
                        });

                        cargar_horas_medicas();
                    }
                }
            });
        }

        function anular(id)
        {
            $('.btn-confirmar-hora').attr('disabled', true);
            $('.btn-anular-hora').attr('disabled', true);

            let url = "{{ route('paciente.hora.medica.cancelar') }}";

            $.ajax({
                url: url,
                type: "POST",
                data: {
                    id_hora: id,
                    _token: CSRF_TOKEN,
                },
                success: function(data)
                {
                    if (data != null && parseInt(data.estado, 10) === 1)
                    {
                        swal({
                            title: "Exito!",
                            text: "Se ha Cancelado su hora medica",
                            type: "success",
                        });

                        cargar_horas_medicas();
                    }
                    else
                    {
                        swal({
                            title: "Error!",
                            text: "Se ha presentado un problema en la Cancelación su hora medica.\n Intente de nuevo.",
                            type: "success",
                        });

                        cargar_horas_medicas();
                    }
                }
            });
        }

        function cargar_horas_medicas()
        {
            let url = "{{ route('paciente.hora.medica.ver') }}";
            let tabla = null;

            if ($.fn.DataTable.isDataTable('#simpletable')) {
                tabla = $('#simpletable').DataTable();
                tabla.clear().draw();
            } else {
                $('#simpletable tbody').html('');
            }

            $.ajax({
                url: url,
                type: "GET",
                data: {},
                success: function(data) {
                    if(data != null)
                    {
                        if (data.estado == 1)
                        {
                            if(data.registros.length > 0)
                            {
                                $.each(data.registros, function (key, value) {
                                    var html = '';
                                    var acciones = '';
                                    const estadoVisual = parseInt(value.id_estado_visual ?? value.id_estado, 10);
                                    switch(estadoVisual)
                                    {
                                        case 1:
                                            acciones += '                <button class="btn btn-info btn-icon btn-confirmar-hora" data-toggle="tooltip" data-placement="top" title="Confirmar hora" onclick="confirmar('+value.id+');">';
                                            acciones += '                    <i class="feather icon-check"></i>';
                                            acciones += '                </button>';
                                            acciones += '                <button class="btn btn-danger btn-icon btn-anular-hora" data-toggle="tooltip" data-placement="top" title="Anular hora" onclick="anular('+value.id+');">';
                                            acciones += '                    <i class="feather icon-x"></i>';
                                            acciones += '                </button>';
                                            break

                                        case 16:
                                            acciones += '                <button class="btn btn-info btn-icon btn-confirmar-hora" data-toggle="tooltip" data-placement="top" title="Confirmar hora" onclick="confirmar('+value.id+');">';
                                            acciones += '                    <i class="feather icon-check"></i>';
                                            acciones += '                </button>';
                                            acciones += '                <button class="btn btn-danger btn-icon btn-anular-hora" data-toggle="tooltip" data-placement="top" title="Anular hora" onclick="anular('+value.id+');">';
                                            acciones += '                    <i class="feather icon-x"></i>';
                                            acciones += '                </button>';
                                            break

                                        case 2:
                                            acciones += '                <button class="btn btn-info btn-icon btn-confirmar-hora" data-toggle="tooltip" data-placement="top" title="Confirmar hora" disabled="disabled">';
                                            acciones += '                    <i class="feather icon-check"></i>';
                                            acciones += '                </button>';
                                            acciones += '                <button class="btn btn-danger btn-icon btn-anular-hora" data-toggle="tooltip" data-placement="top" title="Anular hora"  onclick="anular('+value.id+');">';
                                            acciones += '                    <i class="feather icon-x"></i>';
                                            acciones += '                </button>';
                                            break

                                        case 8:
                                            acciones += '                <button class="btn btn-info btn-icon btn-confirmar-hora" data-toggle="tooltip" data-placement="top" title="Confirmar hora" onclick="confirmar('+value.id+');">';
                                            acciones += '                    <i class="feather icon-check"></i>';
                                            acciones += '                </button>';
                                            acciones += '                <button class="btn btn-danger btn-icon btn-anular-hora" data-toggle="tooltip" data-placement="top" title="Anular hora" onclick="anular('+value.id+');">';
                                            acciones += '                    <i class="feather icon-x"></i>';
                                            acciones += '                </button>';
                                            break

                                        default:
                                            acciones += '                <button class="btn btn-info btn-icon btn-confirmar-hora" data-toggle="tooltip" data-placement="top" title="Confirmar hora" disabled="disabled">';
                                            acciones += '                    <i class="feather icon-check"></i>';
                                            acciones += '                </button>';
                                            acciones += '                <button class="btn btn-danger btn-icon btn-anular-hora" data-toggle="tooltip" data-placement="top" title="Anular hora" disabled="disabled">';
                                            acciones += '                    <i class="feather icon-x"></i>';
                                            acciones += '                </button>';
                                    }

                                    var mascotaHtml = '<strong>' + (value.nombre_mascota || 'Sin mascota') + '</strong>';
                                    if (value.nombre_especie_mascota) {
                                        mascotaHtml += '<br>' + value.nombre_especie_mascota;
                                    }

                                    var profesionalHtml = (value.nombre_profesional_completo || '') + '<br>' + (value.nombre_especialidad_resumen || '');
                                    var diaHoraFormato = moment(value.fecha_consulta+' '+value.hora_inicio).format('DD-MM-YYYY HH:mm');
                                    var atencionHtml = (value.nombre_lugar_atencion || '') + '<br>' + (value.direccion_lugar_atencion || '') + '<br><span style="font-weight:bold;">'+diaHoraFormato+' hrs</span>';
                                    var estadoHtml = '<span class="estado-hora-chip '+obtenerClaseEstadoHora(estadoVisual)+'">'+(value.texto_estado || '')+'</span>';

                                    if (tabla) {
                                        tabla.row.add([
                                            acciones,
                                            mascotaHtml,
                                            profesionalHtml,
                                            atencionHtml,
                                            estadoHtml
                                        ]);
                                    } else {
                                        html += '<tr>';
                                        html += '    <td class="align-middle">'+acciones+'</td>';
                                        html += '    <td class="align-middle">'+mascotaHtml+'</td>';
                                        html += '    <td>'+profesionalHtml+'</td>';
                                        html += '    <td>'+atencionHtml+'</td>';
                                        html += '    <td class="align-middle">'+estadoHtml+'</td>';
                                        html += '</tr>';
                                        $('#simpletable tbody').append(html);
                                    }
                                });

                                if (tabla) {
                                    tabla.draw();
                                }
                            } else if (!tabla) {
                                $('#simpletable tbody').append('<tr><td colspan="5" class="text-center">No existen registros</td></tr>');
                            }
                        }
                    }
                }
            });
        }

        // Nombre visible de cada tienda que responde el endpoint de vouchers
        const nombresTiendasBeneficios = { veterfarma: 'Veterfarma', alimentos: 'Alimentos' };

        function tiendasBeneficiosCaidas(data) {
            return Object.keys(data.fuentes || {})
                .filter(function(fuente) { return !data.fuentes[fuente]; })
                .map(function(fuente) { return nombresTiendasBeneficios[fuente] || fuente; });
        }

        // ["Veterfarma", "Alimentos"] -> "Veterfarma y Alimentos"
        function unirNombresTiendas(nombres) {
            return nombres.length > 1
                ? nombres.slice(0, -1).join(', ') + ' y ' + nombres[nombres.length - 1]
                : nombres.join('');
        }

        // "2026-10-31" -> "31 oct" (con año solo si no es el actual)
        function formatearFechaVoucher(fecha) {
            const meses = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
            const partes = String(fecha).slice(0, 10).split('-');
            const mes = meses[parseInt(partes[1], 10) - 1];
            if (partes.length !== 3 || !mes) return String(fecha);

            const anio = parseInt(partes[0], 10) !== new Date().getFullYear() ? ' ' + partes[0] : '';
            return parseInt(partes[2], 10) + ' ' + mes + anio;
        }

        function estadoBeneficios(icono, titulo, detalle, tipo, conReintento) {
            const textos = $('<div>').append(
                $('<strong>', {text: titulo}),
                $('<small>', {text: detalle})
            );

            if (conReintento) {
                textos.append($('<button>', {type: 'button', class: 'beneficios-mascota-reintentar', text: 'Reintentar'}));
            }

            return $('<div>', {class: 'beneficios-mascota-estado ' + (tipo || '')}).append(
                $('<span>', {class: 'beneficios-mascota-estado-icono'}).append($('<i>', {class: 'feather ' + icono})),
                textos
            );
        }

        function cuponVoucher(voucher) {
            const valor = voucher.discount_type === 'percent'
                ? voucher.value + '%'
                : '$' + Number(voucher.value).toLocaleString('es-CL');
            const destino = voucher.scope === 'product' && voucher.product
                ? 'En ' + voucher.product.name
                : voucher.scope === 'client' ? 'Beneficio exclusivo para ti' : 'En toda tu compra';
            const vence = voucher.expires_at ? 'Vence ' + formatearFechaVoucher(voucher.expires_at) : 'Sin vencimiento';

            const acciones = $('<div>', {class: 'cupon-beneficio-fila'});
            if (voucher.code) {
                acciones.append(
                    $('<button>', {type: 'button', class: 'cupon-beneficio-codigo', title: 'Copiar código', 'data-codigo': voucher.code}).append(
                        $('<span>', {text: voucher.code}),
                        $('<i>', {class: 'feather icon-copy'})
                    )
                );
            }
            acciones.append(
                $('<a>', {class: 'cupon-beneficio-usar', href: voucher.store_url || '#', target: '_blank', rel: 'noopener', text: 'Usar'})
                    .append($('<i>', {class: 'feather icon-arrow-right'}))
            );

            return $('<article>', {class: 'cupon-beneficio ' + (voucher.provider === 'alimentos' ? 'alimentos' : 'veterfarma')}).append(
                $('<div>', {class: 'cupon-beneficio-valor'}).append(
                    $('<strong>', {text: valor, class: valor.length > 7 ? 'largo' : ''}),
                    $('<small>', {text: 'dcto.'})
                ),
                $('<div>', {class: 'cupon-beneficio-detalle'}).append(
                    $('<div>', {class: 'cupon-beneficio-fila'}).append(
                        $('<span>', {class: 'cupon-beneficio-tienda', text: voucher.provider_name || 'Beneficio'}),
                        $('<span>', {class: 'cupon-beneficio-vence', text: vence})
                    ),
                    $('<p>', {class: 'cupon-beneficio-destino', text: destino, title: destino}),
                    acciones
                )
            );
        }

        function copiarCodigoVoucher(boton) {
            const codigo = String(boton.attr('data-codigo') || '');
            const avisar = function() {
                boton.addClass('copiado').attr('title', 'Código copiado').find('i').attr('class', 'feather icon-check');
                setTimeout(function() {
                    boton.removeClass('copiado').attr('title', 'Copiar código').find('i').attr('class', 'feather icon-copy');
                }, 1800);
            };

            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(codigo).then(avisar);
                return;
            }

            // Respaldo para sitios en http o navegadores antiguos
            const campo = $('<textarea>', {readonly: true, css: {position: 'fixed', top: 0, left: '-9999px'}}).val(codigo).appendTo('body');
            campo[0].select();
            try {
                if (document.execCommand('copy')) avisar();
            } catch (e) {}
            campo.remove();
        }

        function cargarVouchersVeterfarma() {
            const contenedor = $('#vouchers-veterfarma-list');
            const contador = $('#vouchers-veterfarma-contador').addClass('d-none');
            const recargar = $('#vouchers-veterfarma-recargar').prop('disabled', true).addClass('girando');

            contenedor.html('<div class="beneficios-mascota-cargando" aria-label="Consultando beneficios"><span></span><span></span></div>');

            $.get(@json(route('paciente.vouchers_veterfarma'))).done(function(data) {
                const vouchers = Array.isArray(data.vouchers) ? data.vouchers : [];
                const caidas = tiendasBeneficiosCaidas(data);
                contenedor.empty();

                if (!vouchers.length) {
                    contenedor.append(caidas.length
                        ? estadoBeneficios('icon-wifi-off', 'No pudimos consultar ' + unirNombresTiendas(caidas) + '.', 'Intenta de nuevo en unos minutos.', 'aviso', true)
                        : estadoBeneficios('icon-gift', 'Sin vouchers vigentes por ahora.', 'Cuando tengas uno nuevo aparecerá aquí.'));
                    return;
                }

                contador
                    .text(vouchers.length)
                    .attr('title', vouchers.length === 1 ? '1 voucher disponible' : vouchers.length + ' vouchers disponibles')
                    .removeClass('d-none');

                const lista = $('<div>', {class: 'beneficios-mascota-lista'});
                vouchers.forEach(function(voucher) {
                    lista.append(cuponVoucher(voucher));
                });
                contenedor.append(lista);

                if (caidas.length) {
                    contenedor.append($('<small>', {class: 'beneficios-mascota-nota', text: 'No se pudo consultar ' + unirNombresTiendas(caidas) + ' por ahora.'}));
                }
            }).fail(function() {
                contenedor.empty().append(
                    estadoBeneficios('icon-alert-circle', 'No fue posible consultar los beneficios.', 'Revisa tu conexión e intenta otra vez.', 'error', true)
                );
            }).always(function() {
                recargar.prop('disabled', false).removeClass('girando');
            });
        }

        $(document).ready(function() {
            cargar_horas_medicas();
            cargarVouchersVeterfarma();

            $(document).on('click', '#vouchers-veterfarma-recargar, .beneficios-mascota-reintentar', cargarVouchersVeterfarma);
            $(document).on('click', '.cupon-beneficio-codigo', function() {
                copiarCodigoVoucher($(this));
            });
        });
    </script>
@endsection

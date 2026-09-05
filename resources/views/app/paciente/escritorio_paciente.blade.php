@extends('template.usuario.template')
@section('content')
<div class="pcoded-main-container" >
    <div class="pcoded-content">
        <!--Header-->
  
        <div class="page-header">
            <div class="page-block">
                <div class="row align-items-center">
                    <div class="col-md-12">
                        <div class="page-header-title">
                            <h4 class="m-b-10  text-white">Hola, {{ $paciente->nombres }}</h4>
                            <p class="text-white">Bienvenido/a a tu escritorio de cuidado de mascotas</p>
                        </div>
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
        @if(($mascotasIncompletas ?? collect())->isNotEmpty())
        <div class="alert alert-warning d-flex justify-content-between align-items-center"><div><strong>Completa la ficha de {{ $mascotasIncompletas->pluck('nombre')->join(', ') }}</strong><br><small>La inscripción rápida desde Veterfarma guardó los datos esenciales. Faltan antecedentes para completar su ficha.</small></div><a href="{{ route('paciente.mis_mascotas') }}" class="btn btn-warning btn-sm">Completar ahora</a></div>
        @endif
        <section class="card mb-4" id="vouchers-veterfarma-card">
            <div class="card-header" style="background:#08776f;color:#fff"><h5 class="mb-0 text-white" style="color:#fff!important"><i class="feather icon-tag mr-2" style="color:#fff!important"></i>Beneficios para tu mascota</h5></div>
            <div class="card-body"><p class="text-muted mb-2">Vouchers disponibles en Veterfarma y Alimentos.</p><div class="row" id="vouchers-veterfarma-list"><div class="col-12 text-muted">Consultando beneficios…</div></div></div>
        </section>
        <!--Botones superiores-->

        <div class="row m-b-30 mt-n2">
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
            <div class="col-md-4">
                <div class="card subir text-center h-100" >
                        <a href="{{ ROUTE('paciente.mascotas.promociones_especiales') }}" >
                            <img class="img-fluid card-img-top" src="{{ asset('images/iconos/publicidad.png') }}"
                            alt="Flujo de caja"style="height:290px;">    
                        </a>
                        <div class="card-body"> 
                            <a href="{{ ROUTE('paciente.mascotas.promociones_generales') }}" class="btn  btn-arrastre">
                                <h5 style="font-size: 1.1rem;" class="card-title pt-2">Visitar Espacios Promocionales</h5>
                              
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
								<i class="feather icon-gift text-white mb-3" style="font-size:30px"></i>
								<h5 class="my-auto text-white">Invita y gana con vet sdi</h5>
							</div>
						</a>
					</div>
                  	<div class="card social-widget-card  opacidad px-0 bg-purple">
						<a href="{{ route('app.descarga') }}" class="btn" type="button" target="_blank">
							<div class="card-body">
								<img class="wid-30 mb-3" src="{{ asset('images/iconos/lock.svg') }}">
								<h5 class="my-auto text-white">Descarga VetPass</h5>
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

        function cargarVouchersVeterfarma() {
            $.get(@json(route('paciente.vouchers_veterfarma'))).done(function(data) {
                const contenedor = $('#vouchers-veterfarma-list').empty();
                const vouchers = Array.isArray(data.vouchers) ? data.vouchers : [];
                if (!vouchers.length) {
                    const caidas = Object.keys(data.fuentes || {}).filter(function(fuente) { return !data.fuentes[fuente]; });
                    const mensaje = caidas.length
                        ? 'No hay vouchers disponibles. No se pudo consultar: ' + caidas.join(', ') + '.'
                        : 'No tienes vouchers vigentes por el momento.';
                    contenedor.append($('<div>', {class:'col-12 ' + (caidas.length ? 'text-warning' : 'text-muted'), text:mensaje}));
                    return;
                }
                vouchers.forEach(function(voucher) {
                    const beneficio = voucher.discount_type === 'percent'
                        ? voucher.value + '% de descuento'
                        : '$' + Number(voucher.value).toLocaleString('es-CL') + ' de descuento';
                    const destino = voucher.scope === 'product' && voucher.product
                        ? 'En ' + voucher.product.name
                        : voucher.scope === 'client' ? 'Beneficio exclusivo para ti' : 'En tu compra ' + (voucher.provider_name || '');
                    const tarjeta = $('<div>', {class:'col-md-4 mb-3'}).append(
                        $('<div>', {class:'border rounded h-100 p-3', css:{borderColor:'#b8ddd5',background:'#f2faf8'}}).append(
                            $('<small>', {class:'d-block text-uppercase font-weight-bold mb-1',css:{color:'#08776f'},text:voucher.provider_name || 'Beneficio'}),
                            $('<span>', {class:'badge badge-danger mb-2', text:voucher.code}),
                            $('<h6>', {class:'font-weight-bold mb-1', text:beneficio}),
                            $('<small>', {class:'d-block text-muted mb-2', text:destino}),
                            $('<small>', {class:'text-success', text:voucher.expires_at ? 'Vigente hasta '+String(voucher.expires_at).slice(0,10) : 'Sin fecha de vencimiento'}),
                            $('<a>', {class:'btn btn-sm btn-outline-success btn-block mt-3',href:voucher.store_url || '#',target:'_blank',rel:'noopener',text:'Usar en '+(voucher.provider_name || 'tienda')})
                        )
                    );
                    contenedor.append(tarjeta);
                });
                const caidas = Object.keys(data.fuentes || {}).filter(function(fuente) { return !data.fuentes[fuente]; });
                if (caidas.length) {
                    contenedor.append($('<div>', {class:'col-12 small text-warning', text:'Temporalmente no se pudo consultar: '+caidas.join(', ')+'.'}));
                }
            }).fail(function() {
                $('#vouchers-veterfarma-list').html('<div class="col-12 text-danger">No fue posible consultar los beneficios.</div>');
            });
        }

        $(document).ready(function() {
            cargar_horas_medicas();
            cargarVouchersVeterfarma();
        });
    </script>
@endsection

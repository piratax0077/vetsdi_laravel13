@extends('template.template_mensajes')
@section('content')

    <!--Container Completo-->
    <div class="pcoded-main-container">
        <div class="pcoded-content">
            <!--Header-->
            <div class="row">
                <div class="col-md-12 mb-3 encabezado-pagina">
                    <h5 class="f-26 d-inline">Mis mensajes</h5>
                </div>
            </div>
            <!--Cierre: Header-->
        	<div class="row">
    			<div class="col-sm-12 col-md-12 col-lg-6 col-xl-6">
    				<!--EMISOR DEL MENSAJE-->
    				<div class="card">
    					<div class="card-body py-3">
    						<div class="media align-items-center">
    							<img class="img-radius wid-50 mr-3" src="{{ asset('images/iconos/usuario_profesional.svg') }}" alt="Foto perfil">
    							<div class="media-body">
    								<small class="text-muted d-block">Remitente</small>
                                    @if($mensaje->emisor)
								    	<h5 class="mb-0 text-c-blue" id="nombre_emisor">{{ $mensaje->emisor->nombre }} {{ $mensaje->emisor->apellido_uno }}</h5>
                                    @else
                                        <h5 class="mb-0 text-c-blue" id="nombre_emisor">Usuario #</h5>
                                    @endif
    							</div>
    						</div>
    					</div>
    				</div>
    				<!--ASUNTO Y MENSAJE-->
    				<div class="card">
    					<div class="card-header encabezado-card">
    						<h5 class="encabezado-card-titulo"><span class="encabezado-card-icono"><i class="feather icon-mail"></i></span>Mensaje</h5>
    					</div>
        				<div class="card-body">
        					<h5 class="text-c-blue text-uppercase mb-3" id="asunto_mensaje">{{ $mensaje->asunto }}</h5>
        					<p class="mb-0 contenido-mensaje" id="contenido_mensaje">{{ $mensaje->mensaje }}</p>
        				</div>
        			</div>
    			</div>
    			<!--BANDEJA DE MENSAJES-->
        		<div class="col-sm-12 col-md-12 col-lg-6 col-xl-6">
                    <div class="card">
                        <div class="card-header encabezado-card">
                            <h5 class="encabezado-card-titulo"><span class="encabezado-card-icono"><i class="feather icon-inbox"></i></span>Mensajes recibidos</h5>
                        </div>
                        <div class="card-body">
                            <script>
                                const mensajes = @json($mensajes);
                            </script>
                            <div class="table-responsive">
                                <table id="tablaMensajes" class="table tabla-sin-hover tabla-mensajes" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>Mensaje</th>
                                            <th>Fecha</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($mensajes as $m)
                                            <tr onclick="mostrarMensaje({{ $m['id'] }}, this)" class="{{ ($mensaje->id ?? null) == $m['id'] ? 'fila-seleccionada' : '' }}" style="cursor: pointer;">
                                                <td class="align-middle">
                                                    <div class="media align-items-center">
                                                        <img class="img-radius wid-40" src="{{ asset('images/iconos/usuario_profesional.svg') }}" alt="Foto">
                                                        <div class="media-body ml-3">
                                                            <h6 class="pro-title text-uppercase mb-1">
                                                                {{ strtoupper($m['datos_mensaje']['titulo'] ?? 'Sin título') }}
                                                            </h6>
                                                            <small class="text-muted">
                                                                {{ Str::limit($m['datos_mensaje']['mensaje'] ?? 'Sin mensaje', 60) }}
                                                            </small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="align-middle text-nowrap text-muted">{{ \Carbon\Carbon::parse($m['fecha_envio'])->format('d/m/Y') }}</td>
                                            </tr>
                                        @endforeach

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

        	</div>
        </div>
    </div>
    <!--Cierre: Container Completo-->
@endsection

@section('page-script')
<script>
    $(document).ready(function() {
        $('#tablaMensajes').DataTable({
            paging: true,
            pageLength: 5,
            lengthChange: false,
            searching: true,
            ordering: false,
            info: false,
            language: {
                search: "Buscar:",
                paginate: {
                    previous: "Anterior",
                    next: "Siguiente"
                },
                zeroRecords: "No se encontraron mensajes",
                emptyTable: "No tiene mensajes"
            }
        });
    });

    function mostrarMensaje(id, fila) {
        // Deja marcado el mensaje que se está leyendo
        if (fila) {
            $('#tablaMensajes tbody tr').removeClass('fila-seleccionada');
            $(fila).addClass('fila-seleccionada');
        }

        $.ajax({
            url: `/profesional/mensaje/${id}/json`,
            type: 'GET',
            dataType: 'json',
            success: function(mensaje) {
                console.log(mensaje);
                $('#nombre_emisor').text(mensaje.emisor.name || 'Desconocido');
                $('#asunto_mensaje').text(mensaje.asunto || 'Sin asunto');
                $('#contenido_mensaje').text(mensaje.mensaje || 'Sin contenido');
            },
            error: function(xhr, status, error) {
                console.error('Error al obtener el mensaje:', error);
            }
        });
    }

</script>

@endsection

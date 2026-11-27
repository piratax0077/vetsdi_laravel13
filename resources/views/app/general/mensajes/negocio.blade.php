{{-- Mensajes del negocio (peluquería, centro estético, hotel o cementerio) con sus tutores --}}
@extends($layoutMensajes)

@section('content')
    @if ($recibidos->isEmpty())
        <div class="card">
            <div class="card-header encabezado-card">
                <h5 class="encabezado-card-titulo"><span class="encabezado-card-icono"><i class="feather icon-mail"></i></span>Mensajes</h5>
                <div class="encabezado-card-acciones">
                    <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#modal_nuevo_mensaje_negocio"><i class="feather icon-edit"></i> Nuevo mensaje</button>
                </div>
            </div>
            <div class="card-body">
                <div class="aviso-sin-registros">
                    <span class="aviso-sin-registros-icono"><i class="feather icon-inbox"></i></span>
                    <div>
                        <p class="aviso-sin-registros-titulo">No tienes mensajes</p>
                        <p class="aviso-sin-registros-texto">Cuando un tutor responda a tus mensajes, lo verás aquí y en el ícono de sobre de arriba.</p>
                    </div>
                </div>
            </div>
        </div>
    @else
        @include('template.partials.bandeja_mensajes', [
            'mensajesBandeja' => $recibidos->map(fn ($mensaje) => $mensaje + ['resumen' => $mensaje['mensaje']]),
            'sinLeerBandeja' => $recibidos->where('leido', false)->count(),
            'descripcionBandeja' => 'Respuestas de tus tutores a tus mensajes.',
            'botonNuevoBandeja' => 'Nuevo mensaje',
        ])
    @endif

    {{-- MENSAJES ENVIADOS --}}
    <div class="card">
        <div class="card-header encabezado-card">
            <h5 class="encabezado-card-titulo"><span class="encabezado-card-icono"><i class="feather icon-send"></i></span>Mensajes enviados</h5>
        </div>
        <div class="card-body">
            @if ($enviados->isEmpty())
                <p class="text-muted mb-0">Aún no has enviado mensajes.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-xs mb-0" style="width:100%">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Destinatario</th>
                                <th>Asunto</th>
                                <th>Tipo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($enviados as $enviado)
                                <tr>
                                    <td class="align-middle text-nowrap">{{ $enviado['fecha'] }}</td>
                                    <td class="align-middle">{{ $enviado['destinatario'] }}</td>
                                    <td class="align-middle">{{ $enviado['asunto'] }}</td>
                                    <td class="align-middle">{{ $enviado['tipo'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- NUEVO MENSAJE --}}
    <div class="modal fade" id="modal_nuevo_mensaje_negocio" tabindex="-1" role="dialog" aria-labelledby="modal_nuevo_mensaje_negocio_titulo" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header modal-header-celeste">
                    <h5 class="modal-title" id="modal_nuevo_mensaje_negocio_titulo">Nuevo mensaje</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                </div>
                @if ($tutores->isEmpty())
                    <div class="modal-body">
                        <div class="aviso-sin-registros">
                            <span class="aviso-sin-registros-icono"><i class="feather icon-users"></i></span>
                            <div>
                                <p class="aviso-sin-registros-titulo">Aún no tienes tutores a quienes escribir</p>
                                <p class="aviso-sin-registros-texto">Podrás enviar mensajes a los tutores que tengan cuenta en Veterchile y sean tus clientes.</p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-dark" data-dismiss="modal">Cerrar</button>
                    </div>
                @else
                    <form method="post" action="{{ route($rutaBaseMensajes.'.mensajes.enviar') }}">
                        @csrf
                        <div class="modal-body">
                            <div class="form-group">
                                <label class="floating-label-activo-sm" for="mensaje_destinatario">Destinatario</label>
                                <select class="form-control form-control-sm" id="mensaje_destinatario" name="destinatario" required>
                                    <option value="">Selecciona un tutor</option>
                                    <option value="todos" @selected(old('destinatario') === 'todos')>Todos mis tutores ({{ $tutores->count() }})</option>
                                    @foreach ($tutores as $tutor)
                                        <option value="{{ $tutor['id'] }}" @selected((string) old('destinatario') === (string) $tutor['id'])>
                                            {{ $tutor['nombre'] }}@if ($tutor['rut']) · {{ $tutor['rut'] }}@endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="floating-label-activo-sm" for="mensaje_asunto">Asunto</label>
                                <input type="text" class="form-control form-control-sm" id="mensaje_asunto" name="asunto" maxlength="150" value="{{ old('asunto') }}" required>
                            </div>
                            <div class="form-group mb-0">
                                <label class="floating-label-activo-sm" for="mensaje_texto">Mensaje</label>
                                <textarea class="form-control form-control-sm" id="mensaje_texto" name="mensaje" rows="6" maxlength="3000" required>{{ old('mensaje') }}</textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-dark" data-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-info"><i class="feather icon-send"></i> Enviar mensaje</button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('page-script')
<script src="{{ asset('js/bandeja_mensajes.js') }}?v={{ filemtime(public_path('js/bandeja_mensajes.js')) }}"></script>
<script>
    (function () {
        var rutaMensajes = @json(route($rutaBaseMensajes.'.mensajes'));
        var textos = @json($recibidos->pluck('mensaje', 'id'));

        // El texto ya viene en la página; la consulta solo deja el mensaje como leído
        BandejaMensajes.iniciar({
            cargar: function (id) {
                var datos = function () {
                    return { texto: textos[id] || '' };
                };
                return $.ajax({ url: rutaMensajes, data: { mensaje: id }, dataType: 'text' }).then(datos, datos);
            }
        });

        $('#bandeja_mensajes').on('click', '[data-accion="nuevo-mensaje"]', function () {
            $('#modal_nuevo_mensaje_negocio').modal('show');
        });

        // Si el envío volvió con errores, el formulario se abre de nuevo con lo escrito
        @if ($errors->any())
            $('#modal_nuevo_mensaje_negocio').modal('show');
        @endif
    })();
</script>
@endsection

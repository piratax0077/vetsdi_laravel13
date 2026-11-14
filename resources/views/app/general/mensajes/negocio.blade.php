{{-- Mensajes del negocio (peluquería, centro estético, hotel o cementerio) con sus tutores --}}
@extends($layoutMensajes)

@section('content')
    <!--Header-->
    <div class="row">
        <div class="col-md-12 mb-3 encabezado-pagina">
            <h5 class="f-26 d-inline">Mensajes</h5>
        </div>
    </div>
    <!--Cierre: Header-->

    <div class="row">
        {{-- NUEVO MENSAJE --}}
        <div class="col-sm-12 col-md-12 col-lg-5 col-xl-5">
            <div class="card">
                <div class="card-header encabezado-card">
                    <h5 class="encabezado-card-titulo"><span class="encabezado-card-icono"><i class="feather icon-edit"></i></span>Nuevo mensaje</h5>
                </div>
                <div class="card-body">
                    @if ($tutores->isEmpty())
                        <div class="aviso-sin-registros">
                            <span class="aviso-sin-registros-icono"><i class="feather icon-users"></i></span>
                            <div>
                                <p class="aviso-sin-registros-titulo">Aún no tienes tutores a quienes escribir</p>
                                <p class="aviso-sin-registros-texto">Podrás enviar mensajes a los tutores que tengan cuenta en Veterchile y sean tus clientes.</p>
                            </div>
                        </div>
                    @else
                        <form method="post" action="{{ route($rutaBaseMensajes.'.mensajes.enviar') }}">
                            @csrf
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
                            <div class="form-group">
                                <label class="floating-label-activo-sm" for="mensaje_texto">Mensaje</label>
                                <textarea class="form-control form-control-sm" id="mensaje_texto" name="mensaje" rows="6" maxlength="3000" required>{{ old('mensaje') }}</textarea>
                            </div>
                            <div class="text-right">
                                <button type="submit" class="btn btn-info"><i class="feather icon-send"></i> Enviar mensaje</button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-sm-12 col-md-12 col-lg-7 col-xl-7">
            {{-- MENSAJE ABIERTO --}}
            @if ($abierto)
                <div class="card">
                    <div class="card-header encabezado-card">
                        <h5 class="encabezado-card-titulo"><span class="encabezado-card-icono"><i class="feather icon-mail"></i></span>Mensaje</h5>
                    </div>
                    <div class="card-body">
                        <h5 class="text-c-blue mb-1">{{ $abierto['asunto'] }}</h5>
                        <small class="text-muted d-block mb-3">{{ $abierto['remitente'] }} · {{ $abierto['fecha'] }}</small>
                        <p class="mb-0 contenido-mensaje">{{ $abierto['mensaje'] }}</p>
                    </div>
                </div>
            @endif

            {{-- MENSAJES RECIBIDOS --}}
            <div class="card">
                <div class="card-header encabezado-card">
                    <h5 class="encabezado-card-titulo"><span class="encabezado-card-icono"><i class="feather icon-inbox"></i></span>Mensajes recibidos</h5>
                </div>
                <div class="card-body">
                    @if ($recibidos->isEmpty())
                        <div class="aviso-sin-registros">
                            <span class="aviso-sin-registros-icono"><i class="feather icon-inbox"></i></span>
                            <div>
                                <p class="aviso-sin-registros-titulo">No tienes mensajes</p>
                                <p class="aviso-sin-registros-texto">Cuando un tutor responda a tus mensajes, lo verás aquí y en el ícono de sobre de arriba.</p>
                            </div>
                        </div>
                    @else
                        <ul class="mensajes-tutor-lista">
                            @foreach ($recibidos as $mensaje)
                                <li>
                                    <a href="{{ route($rutaBaseMensajes.'.mensajes', ['mensaje' => $mensaje['id']]) }}" class="mensajes-tutor-item text-decoration-none {{ $mensaje['leido'] ? '' : 'no-leido' }}">
                                        <span class="mensajes-tutor-icono"><i class="feather icon-mail"></i></span>
                                        <span class="mensajes-tutor-texto">
                                            <span class="mensajes-tutor-asunto">{{ $mensaje['asunto'] }}</span>
                                            <span class="mensajes-tutor-detalle">{{ $mensaje['remitente'] }}</span>
                                            <span class="mensajes-tutor-resumen">{{ \Illuminate\Support\Str::limit($mensaje['mensaje'], 110) }}</span>
                                        </span>
                                        <span class="mensajes-tutor-fecha">
                                            {{ $mensaje['fecha'] }}
                                            @unless ($mensaje['leido'])
                                                <span class="badge badge-danger">Nuevo</span>
                                            @endunless
                                        </span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

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
                            <table class="table tabla-sin-hover table-xs mb-0" style="width:100%">
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
        </div>
    </div>
@endsection

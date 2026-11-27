{{--
    Bandeja de mensajes en dos paneles: la lista a la izquierda y el mensaje elegido a la derecha.
    Uso: @include('template.partials.bandeja_mensajes', ['mensajesBandeja' => $mensajes, 'sinLeerBandeja' => $sinLeer])
    Cada mensaje trae: id, remitente, foto, asunto, resumen, tipo, fecha (d-m-Y H:i), leido y, si lo hay, lugar.
    Opcionales: 'descripcionBandeja' (bajada bajo el título), 'botonNuevoBandeja' (texto del botón para escribir),
    'respuestaBandeja' => true agrega la caja para responder y 'accionesBandeja' => true los botones de descargar y eliminar.
    El funcionamiento está en public/js/bandeja_mensajes.js
--}}
@php
    $fotoPorDefecto = asset('images/iconos/usuario_profesional.svg');
    $hoyBandeja = now()->format('d-m-Y');
@endphp

<div class="card bandeja-mensajes" id="bandeja_mensajes">
    <div class="bandeja-mensajes-lista-panel">
        <div class="bandeja-mensajes-cabecera">
            <div class="bandeja-mensajes-cabecera-fila">
                <h5 class="bandeja-mensajes-titulo">Mensajes</h5>
                @if (! empty($botonNuevoBandeja))
                    <button type="button" class="btn btn-info btn-sm" data-accion="nuevo-mensaje">
                        <i class="feather icon-edit"></i> {{ $botonNuevoBandeja }}
                    </button>
                @endif
            </div>
            @if (! empty($descripcionBandeja))
                <p class="bandeja-mensajes-descripcion">{{ $descripcionBandeja }}</p>
            @endif
            <div class="bandeja-mensajes-filtros" role="group" aria-label="Filtrar mensajes">
                <button type="button" class="bandeja-mensajes-filtro activo" data-filtro="todos" aria-pressed="true">Todos</button>
                <button type="button" class="bandeja-mensajes-filtro" data-filtro="no-leidos" aria-pressed="false">
                    No leídos
                    <span class="bandeja-mensajes-filtro-contador {{ $sinLeerBandeja > 0 ? '' : 'd-none' }}">{{ $sinLeerBandeja }}</span>
                </button>
            </div>
        </div>

        <ul class="bandeja-mensajes-lista">
            @foreach ($mensajesBandeja as $mensaje)
                @php
                    // Los de hoy muestran la hora y los demás la fecha
                    [$diaMensaje, $horaMensaje] = array_pad(explode(' ', (string) $mensaje['fecha'], 2), 2, '');
                    $fechaCorta = $diaMensaje === $hoyBandeja && $horaMensaje !== '' ? $horaMensaje : $diaMensaje;
                @endphp
                <li>
                    <button type="button" class="bandeja-mensajes-item {{ $mensaje['leido'] ? '' : 'no-leido' }}"
                        data-id="{{ $mensaje['id'] }}"
                        data-remitente="{{ $mensaje['remitente'] }}"
                        data-asunto="{{ $mensaje['asunto'] }}"
                        data-lugar="{{ $mensaje['lugar'] ?? '' }}"
                        data-detalle="{{ implode(' · ', array_filter([$mensaje['tipo'] ?? '', $mensaje['fecha']])) }}">
                        <img class="bandeja-mensajes-foto" src="{{ $mensaje['foto'] ?: $fotoPorDefecto }}" alt=""
                            onerror="this.onerror=null;this.src='{{ $fotoPorDefecto }}';">
                        <span class="bandeja-mensajes-item-texto">
                            <span class="bandeja-mensajes-item-fila">
                                <span class="bandeja-mensajes-remitente">{{ $mensaje['remitente'] }}</span>
                                <span class="bandeja-mensajes-fecha" title="{{ $mensaje['fecha'] }}">{{ $fechaCorta }}</span>
                            </span>
                            @if (! empty($mensaje['lugar']))
                                <span class="bandeja-mensajes-lugar"><i class="feather icon-map-pin"></i> {{ $mensaje['lugar'] }}</span>
                            @endif
                            <span class="bandeja-mensajes-asunto">{{ $mensaje['asunto'] }}</span>
                            @if (trim((string) $mensaje['resumen']) !== '')
                                <span class="bandeja-mensajes-resumen">{{ \Illuminate\Support\Str::limit($mensaje['resumen'], 120) }}</span>
                            @endif
                        </span>
                        @unless ($mensaje['leido'])
                            <span class="mensajes-encabezado-punto" title="Sin leer"><span class="sr-only">Sin leer</span></span>
                        @endunless
                    </button>
                </li>
            @endforeach
        </ul>
        <p class="bandeja-mensajes-sin-resultados d-none">No tienes mensajes sin leer.</p>
    </div>

    <div class="bandeja-mensajes-lectura">
        <div class="bandeja-mensajes-lectura-vacia">
            <span class="bandeja-mensajes-lectura-vacia-icono"><i class="feather icon-mail"></i></span>
            <p class="bandeja-mensajes-lectura-vacia-titulo">Selecciona un mensaje</p>
            <p class="bandeja-mensajes-lectura-vacia-texto">Elige un mensaje de la lista para leerlo aquí.</p>
        </div>

        <div class="bandeja-mensajes-lectura-contenido d-none">
            <div class="bandeja-mensajes-lectura-cabecera">
                <button type="button" class="bandeja-mensajes-volver" aria-label="Volver a los mensajes" title="Volver a los mensajes">
                    <i class="feather icon-arrow-left"></i>
                </button>
                <img class="bandeja-mensajes-foto bandeja-mensajes-foto-grande" data-lectura="foto" src="{{ $fotoPorDefecto }}" alt=""
                    onerror="this.onerror=null;this.src='{{ $fotoPorDefecto }}';">
                <div class="bandeja-mensajes-lectura-remitente">
                    <span class="bandeja-mensajes-lectura-nombre" data-lectura="remitente"></span>
                    <span class="bandeja-mensajes-lectura-detalle" data-lectura="detalle"></span>
                    <span class="bandeja-mensajes-lugar d-none" data-lectura="lugar"><i class="feather icon-map-pin"></i> <span></span></span>
                </div>
                @if (! empty($accionesBandeja))
                    <div class="bandeja-mensajes-lectura-acciones">
                        <button type="button" class="btn btn-primary btn-icon" data-accion="descargar" title="Descargar" aria-label="Descargar">
                            <i class="feather icon-download"></i>
                        </button>
                        <button type="button" class="btn btn-danger btn-icon" data-accion="eliminar" title="Eliminar" aria-label="Eliminar">
                            <i class="feather icon-x"></i>
                        </button>
                    </div>
                @endif
            </div>

            <div class="bandeja-mensajes-lectura-cuerpo">
                <h4 class="bandeja-mensajes-lectura-asunto" data-lectura="asunto"></h4>
                <p class="bandeja-mensajes-lectura-subtitulo d-none" data-lectura="subtitulo"></p>
                <div class="bandeja-mensajes-lectura-texto" data-lectura="texto"></div>
                <div class="bandeja-mensajes-lectura-archivos d-none" data-lectura="archivos"></div>
            </div>

            @if (! empty($respuestaBandeja))
                <div class="bandeja-mensajes-respuesta d-none" data-lectura="respuesta">
                    <label class="floating-label-activo-sm" for="bandeja_mensajes_respuesta">Responder</label>
                    <textarea class="form-control form-control-sm" id="bandeja_mensajes_respuesta" rows="3" maxlength="3000" placeholder="Escribe tu respuesta"></textarea>
                    <div class="text-right mt-2">
                        <button type="button" class="btn btn-info" data-accion="responder"><i class="feather icon-send"></i> Enviar respuesta</button>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

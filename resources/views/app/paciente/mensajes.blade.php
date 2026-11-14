@extends('template.usuario.template')

@section('content')
<div class="pcoded-main-container">
    <div class="pcoded-content">
        {{-- Título de la sección bajo el botón Atrás, igual que en Mascotas perdidas --}}
        <div class="perdidas-intro">
            <div>
                <h3>Mis mensajes</h3>
                <p>Mensajes y avisos que te envían tu clínica veterinaria y los profesionales que atienden a tus mascotas.</p>
            </div>
        </div>

        <div class="card">
            <div class="card-header encabezado-card">
                <h5 class="encabezado-card-titulo">
                    <span class="encabezado-card-icono"><i class="feather icon-mail"></i></span>Bandeja de entrada
                </h5>
                <div class="encabezado-card-acciones">
                    <span class="badge badge-danger mensajes-tutor-sin-leer {{ $sinLeer > 0 ? '' : 'd-none' }}" id="mensajes_tutor_sin_leer">
                        <span>{{ $sinLeer }}</span> sin leer
                    </span>
                </div>
            </div>
            <div class="card-body">
                @if ($mensajes->isEmpty())
                    <div class="aviso-sin-registros">
                        <span class="aviso-sin-registros-icono"><i class="feather icon-inbox"></i></span>
                        <div>
                            <p class="aviso-sin-registros-titulo">No tienes mensajes</p>
                            <p class="aviso-sin-registros-texto">Cuando tu clínica o un profesional te escriba, lo verás aquí y en el ícono de sobre de arriba.</p>
                        </div>
                    </div>
                @else
                    <ul class="mensajes-tutor-lista">
                        @foreach ($mensajes as $mensaje)
                            <li>
                                <button type="button" class="mensajes-tutor-item {{ $mensaje['leido'] ? '' : 'no-leido' }}" data-id="{{ $mensaje['id'] }}">
                                    <span class="mensajes-tutor-icono"><i class="feather icon-mail"></i></span>
                                    <span class="mensajes-tutor-texto">
                                        <span class="mensajes-tutor-asunto">{{ $mensaje['asunto'] }}</span>
                                        <span class="mensajes-tutor-detalle">{{ $mensaje['remitente'] }} · {{ $mensaje['tipo'] }}</span>
                                        <span class="mensajes-tutor-resumen">{{ \Illuminate\Support\Str::limit($mensaje['mensaje'], 110) }}</span>
                                    </span>
                                    <span class="mensajes-tutor-fecha" title="{{ $mensaje['fecha'] }}">
                                        {{ $mensaje['hace'] }}
                                        @unless ($mensaje['leido'])
                                            <span class="badge badge-danger">Nuevo</span>
                                        @endunless
                                    </span>
                                </button>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('modales')
    <div class="modal fade" id="modal_mensaje_tutor" tabindex="-1" role="dialog" aria-labelledby="modal_mensaje_tutor_titulo" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header modal-header-celeste">
                    <h5 class="modal-title" id="modal_mensaje_tutor_titulo"><i class="feather icon-mail mr-1"></i> Mensaje</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <h4 class="mensajes-tutor-modal-asunto" id="mensaje_tutor_asunto"></h4>
                    <p class="mensajes-tutor-modal-detalle" id="mensaje_tutor_detalle"></p>
                    <div class="mensajes-tutor-modal-cuerpo" id="mensaje_tutor_cuerpo"></div>
                    <div class="mensajes-tutor-modal-archivos d-none" id="mensaje_tutor_archivos"></div>
                    {{-- Respuesta: solo en mensajes de peluquería, hotel o cementerio --}}
                    <div class="form-group mt-3 mb-0 d-none" id="mensaje_tutor_respuesta">
                        <label class="floating-label-activo-sm" for="mensaje_tutor_respuesta_texto">Responder</label>
                        <textarea class="form-control form-control-sm" id="mensaje_tutor_respuesta_texto" rows="3" maxlength="3000" placeholder="Escribe tu respuesta"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-info d-none" id="mensaje_tutor_responder"><i class="feather icon-send"></i> Enviar respuesta</button>
                    <button type="button" class="btn btn-info" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page-script')
<script>
    (function () {
        var rutaVer = @json(route('paciente.mensajes.ver', ['id' => '__ID__']));
        var rutaResponder = @json(route('paciente.mensajes.responder', ['id' => '__ID__']));
        var mensajeAbierto = null;

        function texto(valor) {
            return $('<div>').text(valor == null ? '' : valor).html();
        }

        // Baja en uno los contadores de no leídos de la página y del ícono de arriba
        function descontarNoLeido() {
            var $pagina = $('#mensajes_tutor_sin_leer');
            var restantes = Math.max(0, parseInt($pagina.find('span').text(), 10) - 1);
            $pagina.find('span').text(restantes);
            $pagina.toggleClass('d-none', restantes === 0);

            var $encabezado = $('.mensajes-encabezado-contador');
            if ($encabezado.length) {
                var enEncabezado = Math.max(0, parseInt($encabezado.text(), 10) - 1);
                if (enEncabezado === 0) {
                    $encabezado.remove();
                } else {
                    $encabezado.text(enEncabezado);
                }
            }
        }

        function abrirMensaje(id) {
            var $item = $('.mensajes-tutor-item[data-id="' + id + '"]');

            $.get(rutaVer.replace('__ID__', id)).done(function (respuesta) {
                var datos = respuesta.datos;
                $('#mensaje_tutor_asunto').text(datos.asunto);
                $('#mensaje_tutor_detalle').text(datos.remitente + ' · ' + datos.tipo + ' · ' + datos.fecha);
                $('#mensaje_tutor_cuerpo').html(texto(datos.mensaje).replace(/\n/g, '<br>'));

                var $archivos = $('#mensaje_tutor_archivos').empty();
                (datos.archivos || []).forEach(function (archivo) {
                    $archivos.append('<a class="btn btn-outline-info btn-sm" target="_blank" rel="noopener" href="' + texto(archivo.url) + '"><i class="feather icon-paperclip mr-1"></i>' + texto(archivo.nombre) + '</a>');
                });
                $archivos.toggleClass('d-none', !(datos.archivos || []).length);

                mensajeAbierto = datos.id;
                $('#mensaje_tutor_respuesta_texto').val('');
                $('#mensaje_tutor_respuesta, #mensaje_tutor_responder').toggleClass('d-none', !datos.puede_responder);

                if ($item.hasClass('no-leido')) {
                    $item.removeClass('no-leido').find('.badge').remove();
                    descontarNoLeido();
                }

                $('#modal_mensaje_tutor').modal('show');
            }).fail(function () {
                if (typeof swal === 'function') {
                    swal({ title: 'No pudimos abrir el mensaje', text: 'Intenta de nuevo en unos minutos.', icon: 'error' });
                }
            });
        }

        $(document).on('click', '.mensajes-tutor-item', function () {
            abrirMensaje($(this).data('id'));
        });

        // Envía la respuesta del tutor al negocio que le escribió
        $('#mensaje_tutor_responder').on('click', function () {
            var $boton = $(this);
            var respuesta = $.trim($('#mensaje_tutor_respuesta_texto').val());

            if (!mensajeAbierto || !respuesta) {
                $('#mensaje_tutor_respuesta_texto').focus();
                return;
            }

            $boton.prop('disabled', true);
            $.post(rutaResponder.replace('__ID__', mensajeAbierto), { _token: @json(csrf_token()), mensaje: respuesta })
                .done(function (resultado) {
                    $('#modal_mensaje_tutor').modal('hide');
                    if (typeof swal === 'function') {
                        swal({ title: resultado.mensaje || 'Respuesta enviada.', icon: 'success' });
                    }
                })
                .fail(function () {
                    if (typeof swal === 'function') {
                        swal({ title: 'No pudimos enviar la respuesta', text: 'Intenta de nuevo en unos minutos.', icon: 'error' });
                    }
                })
                .always(function () {
                    $boton.prop('disabled', false);
                });
        });

        // Si se llega desde el ícono de arriba, se abre el mensaje elegido
        var elegido = new URLSearchParams(window.location.search).get('mensaje');
        if (elegido && $('.mensajes-tutor-item[data-id="' + elegido + '"]').length) {
            abrirMensaje(elegido);
        }
    })();
</script>
@endsection

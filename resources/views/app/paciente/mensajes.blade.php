@extends('template.usuario.template')

@section('content')
<div class="pcoded-main-container">
    <div class="pcoded-content">
        @if ($mensajes->isEmpty())
            <div class="card">
                <div class="card-header encabezado-card">
                    <h5 class="encabezado-card-titulo"><span class="encabezado-card-icono"><i class="feather icon-mail"></i></span>Mensajes</h5>
                </div>
                <div class="card-body">
                    <div class="aviso-sin-registros">
                        <span class="aviso-sin-registros-icono"><i class="feather icon-inbox"></i></span>
                        <div>
                            <p class="aviso-sin-registros-titulo">No tienes mensajes</p>
                            <p class="aviso-sin-registros-texto">Cuando tu clínica o un profesional te escriba, lo verás aquí y en el ícono de sobre de arriba.</p>
                        </div>
                    </div>
                </div>
            </div>
        @else
            @include('template.partials.bandeja_mensajes', [
                'mensajesBandeja' => $mensajes->map(fn ($mensaje) => $mensaje + ['resumen' => $mensaje['mensaje']]),
                'sinLeerBandeja' => $sinLeer,
                'descripcionBandeja' => 'Avisos de tu clínica y de los profesionales de tus mascotas.',
                'respuestaBandeja' => true,
            ])
        @endif
    </div>
</div>
@endsection

@section('page-script')
<script src="{{ asset('js/bandeja_mensajes.js') }}?v={{ filemtime(public_path('js/bandeja_mensajes.js')) }}"></script>
<script>
    (function () {
        var rutaVer = @json(route('paciente.mensajes.ver', ['id' => '__ID__']));
        var rutaResponder = @json(route('paciente.mensajes.responder', ['id' => '__ID__']));

        var bandeja = BandejaMensajes.iniciar({
            cargar: function (id) {
                return $.get(rutaVer.replace('__ID__', id)).then(function (respuesta) {
                    var datos = respuesta.datos || {};
                    return {
                        texto: datos.mensaje,
                        archivos: datos.archivos,
                        // Solo los mensajes de peluquería, hotel o cementerio admiten respuesta
                        puedeResponder: datos.puede_responder
                    };
                });
            }
        });

        if (!bandeja) {
            return;
        }

        // Envía la respuesta del tutor al negocio que le escribió
        $('#bandeja_mensajes').on('click', '[data-accion="responder"]', function () {
            var $boton = $(this);
            var $texto = $('#bandeja_mensajes_respuesta');
            var respuesta = $.trim($texto.val());

            if (!bandeja.abierto() || !respuesta) {
                $texto.focus();
                return;
            }

            $boton.prop('disabled', true);
            $.post(rutaResponder.replace('__ID__', bandeja.abierto()), { _token: @json(csrf_token()), mensaje: respuesta })
                .done(function (resultado) {
                    $texto.val('');
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
    })();
</script>
@endsection

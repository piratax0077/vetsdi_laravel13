@extends('template.template_mensajes')
@section('content')
    @php
        // Mismo formato de la bandeja del tutor y del profesional, del más nuevo al más antiguo
        $tiposBandeja = [1 => 'Mensaje directo', 2 => 'Mensaje de difusión', 3 => 'Mensaje directo', 4 => 'Mensaje de difusión', 5 => 'Aviso'];

        $mensajesBandeja = collect($mensajes ?? [])
            ->sortByDesc(fn ($registro) => (string) $registro->fechaConHora())
            ->map(function ($registro) use ($tiposBandeja) {
                $datos = is_array($registro->datos_mensaje) ? $registro->datos_mensaje : [];
                $titulo = trim((string) ($datos['titulo'] ?? ''));
                $asunto = trim((string) ($datos['asunto'] ?? ''));
                $fecha = $registro->fechaConHora();
                $emisor = $registro->emisor ?? null;

                return [
                    'id' => $registro->id,
                    'remitente' => trim((string) ($datos['negocio'] ?? $datos['remitente'] ?? '')) ?: ($emisor ? $emisor->nombreParaMostrar() : 'VetSDI'),
                    'foto' => \App\Models\Mensajes::fotoDelRemitente($emisor),
                    'asunto' => $titulo ?: ($asunto ?: 'Sin título'),
                    'resumen' => (string) ($datos['mensaje'] ?? ''),
                    'tipo' => $tiposBandeja[(int) $registro->tipo_mensaje] ?? 'Mensaje',
                    'lugar' => trim((string) ($datos['lugar'] ?? '')),
                    'fecha' => $fecha ? $fecha->format('d-m-Y H:i') : '',
                    'leido' => (int) $registro->estado !== 1,
                ];
            })
            ->values();
    @endphp

    <!--Container Completo-->
    <div class="pcoded-main-container">
        <div class="pcoded-content">
            @if ($mensajesBandeja->isEmpty())
                <div class="card">
                    <div class="card-header encabezado-card">
                        <h5 class="encabezado-card-titulo"><span class="encabezado-card-icono"><i class="feather icon-mail"></i></span>Mensajes</h5>
                    </div>
                    <div class="card-body">
                        <div class="aviso-sin-registros">
                            <span class="aviso-sin-registros-icono"><i class="feather icon-inbox"></i></span>
                            <div>
                                <p class="aviso-sin-registros-titulo">No tienes mensajes</p>
                                <p class="aviso-sin-registros-texto">Cuando alguien te escriba, lo verás aquí y en el ícono de sobre de arriba.</p>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                @include('template.partials.bandeja_mensajes', [
                    'mensajesBandeja' => $mensajesBandeja,
                    'sinLeerBandeja' => $mensajesBandeja->where('leido', false)->count(),
                    'descripcionBandeja' => 'Mensajes que llegan a tu cuenta.',
                ])
            @endif
        </div>
    </div>
    <!--Cierre: Container Completo-->
@endsection

@section('page-script')
<script src="{{ asset('js/bandeja_mensajes.js') }}?v={{ filemtime(public_path('js/bandeja_mensajes.js')) }}"></script>
<script>
    (function () {
        var rutaVer = @json(route('profesional.mensaje.json', ['id' => '__ID__']));

        BandejaMensajes.iniciar({
            // Se abre el mensaje con el que se llegó a esta página
            abrirAlIniciar: @json((string) request()->route('id')),
            cargar: function (id) {
                return $.getJSON(rutaVer.replace('__ID__', id)).then(function (datos) {
                    datos = datos || {};
                    var titulo = $.trim(datos.titulo || '');
                    var asunto = $.trim(datos.asunto || '');

                    return {
                        subtitulo: titulo && asunto !== titulo ? asunto : '',
                        texto: datos.mensaje,
                        archivos: (datos.archivos || []).map(function (archivo) {
                            return { nombre: archivo.nombre_original || archivo.nombre || 'Archivo adjunto', url: archivo.url };
                        })
                    };
                });
            }
        });
    })();
</script>
@endsection

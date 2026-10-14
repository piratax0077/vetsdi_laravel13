{{--
    Icono de mensajes del profesional para el encabezado.
    Uso: @include('template.partials.mensajes_encabezado')

    Carga por su cuenta los mensajes recibidos por el usuario, así aparece en todos los
    módulos sin depender de que cada controlador los envíe a la vista.
--}}
@php
    $mensajesEncabezado = collect();
    $mensajesSinLeer = 0;

    if (Auth::check() && Auth::user()->hasRole('Profesional')) {
        try {
            $consultaMensajes = \App\Models\Mensajes::where('id_receptor', Auth::id());
            // Estado 1 es no leído
            $mensajesSinLeer = (clone $consultaMensajes)->where('estado', 1)->count();
            $mensajesEncabezado = $consultaMensajes->orderByDesc('fecha_envio')->orderByDesc('id')->limit(8)->get();
        } catch (\Throwable $error) {
            // Si la tabla no está disponible el icono queda sin mensajes
            $mensajesEncabezado = collect();
        }
    }

    $rutaTodosMensajes = \Route::has('profesional.historial_mensajes') ? route('profesional.historial_mensajes') : null;
@endphp

@if (Auth::check() && Auth::user()->hasRole('Profesional'))
    <li>
        <div class="dropdown drp-user">
            <a href="#" class="dropdown-toggle mensajes-encabezado" data-toggle="dropdown" title="Mensajes" aria-label="Mensajes">
                <i class="feather icon-mail icono-header"></i>
                @if ($mensajesSinLeer > 0)
                    <span class="badge badge-danger mensajes-encabezado-contador">{{ $mensajesSinLeer > 99 ? '99+' : $mensajesSinLeer }}</span>
                @endif
            </a>
            <div class="dropdown-menu dropdown-menu-right profile-notification mensajes-encabezado-menu">
                <div class="pro-head font-weight-bold f-16 py-2">
                    Mensajes
                    @if ($mensajesSinLeer > 0)
                        <span class="badge badge-danger">{{ $mensajesSinLeer }} sin leer</span>
                    @endif
                </div>
                <ul class="pro-body mensajes-encabezado-lista">
                    @forelse ($mensajesEncabezado as $mensajeEncabezado)
                        @php
                            $datosMensaje = json_decode($mensajeEncabezado->datos_mensaje, true);
                            $tituloMensaje = is_array($datosMensaje) && !empty($datosMensaje['titulo']) ? $datosMensaje['titulo'] : 'Sin título';
                            $fechaMensaje = $mensajeEncabezado->fecha_envio ?: $mensajeEncabezado->created_at;
                        @endphp
                        <li>
                            <a href="{{ route('profesional.mensaje', ['id' => $mensajeEncabezado->id]) }}" class="dropdown-item mensajes-encabezado-item">
                                <img class="mensajes-encabezado-avatar" src="{{ asset('images/iconos/usuario_profesional.svg') }}" alt="">
                                <span class="mensajes-encabezado-texto">
                                    <span class="pro-title @if ((int) $mensajeEncabezado->estado === 1) font-weight-bold @endif">{{ $tituloMensaje }}</span>
                                    @if ($fechaMensaje)
                                        <span class="pro-date">{{ \Carbon\Carbon::parse($fechaMensaje)->locale('es')->diffForHumans() }}</span>
                                    @endif
                                </span>
                            </a>
                        </li>
                    @empty
                        <li class="mensajes-encabezado-vacio">No tienes mensajes.</li>
                    @endforelse
                    @if ($rutaTodosMensajes)
                        <li class="mensajes-encabezado-todos">
                            <a href="{{ $rutaTodosMensajes }}" class="dropdown-item text-center">Ver todos los mensajes</a>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </li>
@endif

{{--
    Icono de mensajes para el encabezado. Se muestra siempre, en todos los perfiles.
    Uso: @include('template.partials.mensajes_encabezado')                          -> profesional, centro, peluquería, hotel, cementerio
         @include('template.partials.mensajes_encabezado', ['tipoPerfil' => 'tutor']) -> tutor

    Carga por su cuenta los mensajes recibidos por el usuario, así aparece en todos los
    módulos sin depender de que cada controlador los envíe a la vista.
    Va justo al lado del botón "Mi escritorio".
--}}
@php
    $esTutorMensajes = ($tipoPerfil ?? 'profesional') === 'tutor';
    $mensajesEncabezado = collect();
    $mensajesSinLeer = 0;
    // El ícono se muestra siempre; si la cuenta no tiene mensajes queda con la lista vacía
    $mostrarMensajes = true;
    $consultaMensajes = null;
    $esProfesionalMensajes = Auth::check() && Auth::user()->hasRole('Profesional');

    try {
        if ($esTutorMensajes) {
            // Los mensajes del tutor van al id del paciente y solo de los tipos del tutor
            $consultaMensajes = \App\Http\Controllers\MensajesTutorController::consultaDelTutor();
        } elseif (Auth::check()) {
            // Se dejan fuera los tipos del tutor: esos usan el id del paciente y podrían coincidir con el del usuario
            $consultaMensajes = \App\Models\Mensajes::where('id_receptor', Auth::id())
                ->where(function ($consulta) {
                    $consulta->whereNotIn('tipo_mensaje', \App\Http\Controllers\MensajesTutorController::TIPOS_TUTOR)
                        ->orWhereNull('tipo_mensaje');
                });
        }

        if ($consultaMensajes) {
            // Estado 1 es no leído
            $mensajesSinLeer = (clone $consultaMensajes)->where('estado', 1)->count();
            $mensajesEncabezado = $consultaMensajes->orderByDesc('fecha_envio')->orderByDesc('id')->limit(8)->get();
        }
    } catch (\Throwable $error) {
        // Si la tabla no está disponible el icono queda sin mensajes
        $mensajesEncabezado = collect();
    }

    // Peluquería, centro estético, hotel y cementerio tienen su propia bandeja: <perfil>.mensajes
    $perfilRutaMensajes = strstr((string) optional(request()->route())->getName(), '.', true);
    $rutaNegocioMensajes = in_array($perfilRutaMensajes, ['peluqueria', 'centro_estetico', 'guarderia_hotel', 'cementerio_mascotas'], true)
        && \Route::has($perfilRutaMensajes.'.mensajes') ? $perfilRutaMensajes.'.mensajes' : null;

    if ($esTutorMensajes) {
        $rutaTodosMensajes = \Route::has('paciente.mensajes') ? route('paciente.mensajes') : null;
    } elseif ($rutaNegocioMensajes) {
        $rutaTodosMensajes = route($rutaNegocioMensajes);
    } else {
        $rutaTodosMensajes = $esProfesionalMensajes && \Route::has('profesional.historial_mensajes') ? route('profesional.historial_mensajes') : null;
    }
@endphp

@if ($mostrarMensajes)
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
                            $tituloMensaje = is_array($datosMensaje) && !empty($datosMensaje['titulo'])
                                ? $datosMensaje['titulo']
                                : (is_array($datosMensaje) && !empty($datosMensaje['asunto']) ? $datosMensaje['asunto'] : 'Sin título');
                            $fechaMensaje = $mensajeEncabezado->fecha_envio ?: $mensajeEncabezado->created_at;
                            $enlaceMensaje = $esTutorMensajes
                                ? route('paciente.mensajes', ['mensaje' => $mensajeEncabezado->id])
                                : ($rutaNegocioMensajes
                                    ? route($rutaNegocioMensajes, ['mensaje' => $mensajeEncabezado->id])
                                    : route('profesional.mensaje', ['id' => $mensajeEncabezado->id]));
                        @endphp
                        <li>
                            <a href="{{ $enlaceMensaje }}" class="dropdown-item mensajes-encabezado-item">
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

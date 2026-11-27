{{--
    Campana de avisos del encabezado (como las notificaciones de Facebook). Se muestra en todos los perfiles.
    Uso: @include('template.partials.avisos_encabezado')                          -> profesional, centro, peluquería, administrador...
         @include('template.partials.avisos_encabezado', ['tipoPerfil' => 'tutor']) -> tutor

    Tutor: horas por confirmar, recordatorio de la cita el mismo día y próximas dosis o atrasadas
           de vacunas y desparasitaciones de sus mascotas.
    Todos: recordatorio de renovación del plan cuando lo está pagando.
    Usa el mismo desplegable del ícono de mensajes y va justo después de él.
--}}
@php
    $esTutorAvisos = ($tipoPerfil ?? 'profesional') === 'tutor';
    $avisosEncabezado = collect();
    $avisosNuevos = 0;

    try {
        if (Auth::check()) {
            $servicioAvisos = app(\App\Services\AvisosEncabezado::class);
            $avisosEncabezado = $servicioAvisos->delUsuario(Auth::user(), $esTutorAvisos);
            $avisosVistos = $servicioAvisos->vistos(Auth::user());
            $avisosNuevos = $avisosEncabezado->whereNotIn('clave', $avisosVistos)->count();
        }
    } catch (\Throwable $error) {
        // Si la agenda o los planes no están disponibles la campana queda sin avisos
        $avisosEncabezado = collect();
        $avisosNuevos = 0;
        $avisosVistos = [];
    }
@endphp

@if (Auth::check())
    <li>
        <div class="dropdown drp-user avisos-encabezado-contenedor">
            <a href="#" class="dropdown-toggle mensajes-encabezado avisos-encabezado" data-toggle="dropdown" title="Notificaciones" aria-label="Notificaciones"
               data-url-vistos="{{ route('avisos.vistos') }}" data-token="{{ csrf_token() }}" data-perfil="{{ $esTutorAvisos ? 'tutor' : 'otro' }}">
                <i class="feather icon-bell icono-header"></i>
                @if ($avisosNuevos > 0)
                    <span class="badge badge-danger mensajes-encabezado-contador">{{ $avisosNuevos > 99 ? '99+' : $avisosNuevos }}</span>
                @endif
            </a>
            <div class="dropdown-menu dropdown-menu-right profile-notification mensajes-encabezado-menu">
                <div class="pro-head font-weight-bold">
                    Notificaciones
                    @if ($avisosNuevos > 0)
                        <span class="badge badge-danger avisos-encabezado-nuevos">{{ $avisosNuevos }} {{ $avisosNuevos === 1 ? 'nueva' : 'nuevas' }}</span>
                    @endif
                </div>
                <ul class="pro-body mensajes-encabezado-lista">
                    @forelse ($avisosEncabezado as $aviso)
                        @php $avisoNuevo = ! in_array($aviso['clave'], $avisosVistos ?? [], true); @endphp
                        <li>
                            <a href="{{ $aviso['enlace'] ?: '#' }}" class="dropdown-item mensajes-encabezado-item @if ($avisoNuevo) avisos-encabezado-item-nuevo @endif @if (! $aviso['enlace']) avisos-encabezado-sin-enlace @endif">
                                <span class="mensajes-encabezado-texto">
                                    <span class="pro-title font-weight-bold">{{ $aviso['titulo'] }}</span>
                                    @if (! empty($aviso['texto']))
                                        <span class="avisos-encabezado-detalle">{{ $aviso['texto'] }}</span>
                                    @endif
                                </span>
                                @if ($avisoNuevo)
                                    <span class="mensajes-encabezado-punto" title="Sin leer"><span class="sr-only">Sin leer</span></span>
                                @endif
                            </a>
                        </li>
                    @empty
                        <li class="mensajes-encabezado-vacio">No tienes notificaciones.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </li>

    @once
        <script>
            // Al abrir la campana los avisos quedan como vistos y el contador se apaga
            (function () {
                document.addEventListener('click', function (evento) {
                    var campana = evento.target.closest ? evento.target.closest('.avisos-encabezado') : null;
                    if (!campana || campana.getAttribute('data-vistos') === '1') {
                        return;
                    }

                    var contador = campana.querySelector('.mensajes-encabezado-contador');
                    if (!contador) {
                        return;
                    }

                    campana.setAttribute('data-vistos', '1');
                    contador.parentNode.removeChild(contador);

                    var datos = new FormData();
                    datos.append('perfil', campana.getAttribute('data-perfil') || '');

                    fetch(campana.getAttribute('data-url-vistos'), {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': campana.getAttribute('data-token'), 'X-Requested-With': 'XMLHttpRequest' },
                        body: datos,
                        credentials: 'same-origin'
                    }).catch(function () {});
                });

                // Los avisos sin página de destino solo informan, no recargan la vista
                document.addEventListener('click', function (evento) {
                    var avisoSinEnlace = evento.target.closest ? evento.target.closest('.avisos-encabezado-sin-enlace') : null;
                    if (avisoSinEnlace) {
                        evento.preventDefault();
                    }
                });
            })();
        </script>
    @endonce
@endif

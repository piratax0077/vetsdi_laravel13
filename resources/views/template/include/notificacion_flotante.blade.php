{{-- Notificación flotante para el mensaje de sesión ('mensaje'). Se cierra sola a los 6 segundos. --}}
@if(Session::has('mensaje'))
    @php
        $tipoNotificacion = Session::get('tipo_notificacion', 'exito');
        $iconoNotificacion = ['exito' => 'icon-check', 'info' => 'icon-info', 'error' => 'icon-alert-circle'][$tipoNotificacion] ?? 'icon-check';
        $tituloNotificacion = ['exito' => 'Listo', 'info' => 'Información', 'error' => 'Atención'][$tipoNotificacion] ?? 'Listo';
    @endphp
    <link rel="stylesheet" href="{{ asset('css/notificacion_flotante.css') }}">
    <div id="notificacion-flotante" class="notificacion-flotante tipo-{{ $tipoNotificacion }}" role="status" aria-live="polite">
        <div class="notificacion-icono"><i class="feather {{ $iconoNotificacion }}"></i></div>
        <div class="notificacion-texto">
            <p class="notificacion-titulo">{{ $tituloNotificacion }}</p>
            <p class="notificacion-mensaje">{{ Session::get('mensaje') }}</p>
        </div>
        <button type="button" class="notificacion-cerrar" aria-label="Cerrar">&times;</button>
    </div>
    <script>
        (function () {
            var caja = document.getElementById('notificacion-flotante');
            if (!caja) { return; }
            var temporizador;

            function cerrar() {
                clearTimeout(temporizador);
                caja.classList.remove('visible');
                setTimeout(function () { if (caja.parentNode) { caja.parentNode.removeChild(caja); } }, 400);
            }

            function programar() { temporizador = setTimeout(cerrar, 6000); }

            caja.querySelector('.notificacion-cerrar').addEventListener('click', cerrar);
            caja.addEventListener('mouseenter', function () { clearTimeout(temporizador); });
            caja.addEventListener('mouseleave', programar);

            setTimeout(function () { caja.classList.add('visible'); }, 50);
            programar();
        })();
    </script>
@endif

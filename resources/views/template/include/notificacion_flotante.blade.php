{{-- Avisos de sesión como notificación flotante: fondo oscuro, letra blanca e ícono según el estado --}}
@if(session('mensaje') || $errors->any())
<div class="notificaciones-flotantes" aria-live="polite">
    @if(session('mensaje'))
    <div class="notificacion-flotante notificacion-exito" role="status" data-duracion="5000">
        <span class="notificacion-flotante-icono"><i class="feather icon-check" aria-hidden="true"></i></span>
        <div class="notificacion-flotante-texto">{{ session('mensaje') }}</div>
        <button type="button" class="notificacion-flotante-cerrar" aria-label="Cerrar aviso"><i class="feather icon-x" aria-hidden="true"></i></button>
    </div>
    @endif
    @if($errors->any())
    <div class="notificacion-flotante notificacion-error" role="alert" data-duracion="9000">
        <span class="notificacion-flotante-icono"><i class="feather icon-alert-triangle" aria-hidden="true"></i></span>
        <div class="notificacion-flotante-texto">
            <strong>No se pudo guardar</strong>
            <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
        <button type="button" class="notificacion-flotante-cerrar" aria-label="Cerrar aviso"><i class="feather icon-x" aria-hidden="true"></i></button>
    </div>
    @endif
</div>
<script>
(function () {
    var avisos = document.querySelectorAll('.notificacion-flotante');

    function cerrarAviso(aviso) {
        if (aviso.classList.contains('saliendo')) return;
        aviso.classList.add('saliendo');
        setTimeout(function () {
            if (aviso.parentNode) aviso.parentNode.removeChild(aviso);
        }, 300);
    }

    Array.prototype.forEach.call(avisos, function (aviso) {
        var duracion = parseInt(aviso.getAttribute('data-duracion'), 10) || 5000;
        var temporizador = setTimeout(function () { cerrarAviso(aviso); }, duracion);

        // Mientras el mouse está encima el aviso no se va, así alcanza a leerse
        aviso.addEventListener('mouseenter', function () { clearTimeout(temporizador); });
        aviso.addEventListener('mouseleave', function () {
            temporizador = setTimeout(function () { cerrarAviso(aviso); }, 2500);
        });

        aviso.querySelector('.notificacion-flotante-cerrar').addEventListener('click', function () {
            clearTimeout(temporizador);
            cerrarAviso(aviso);
        });
    });
})();
</script>
@endif

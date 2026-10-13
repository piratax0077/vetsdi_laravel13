{{--
    Botón "Volver" automático.

    Va incluido en cada plantilla, justo después del contenido, pero solo aparece sobre el
    título en Mis documentos y en el Panel de configuración. El resto de las páginas va sin
    botón. Si la vista ya incluyó su propio botón (template.partials.boton_volver), este no
    se repite.

    Para sumar otra página, agregar el nombre de su ruta a la lista de abajo.
    Para que una de esas vistas quede sin botón:
        @section('sin_boton_volver', '1')
--}}
@php
    $con_boton_volver = request()->routeIs(
        'profesional.mis_documentos',
        'paciente.receta.documentos',
        '*.configuracion'
    );
    $sin_boton_volver = $__env->hasSection('sin_boton_volver');
@endphp
@if ($con_boton_volver && ! $sin_boton_volver)
    <div id="boton_volver_automatico" style="display: none">
        @include('template.partials.boton_volver')
    </div>
    <script>
        (function() {
            function ubicar_boton_volver() {
                var reserva = document.getElementById('boton_volver_automatico');
                if (!reserva) return;

                var contenido = document.querySelector('.pcoded-content');
                var boton = reserva.querySelector('.boton-volver-fila');

                // Se coloca al comienzo del contenido, sobre el título, salvo que la vista ya tenga uno
                if (contenido && boton && !contenido.querySelector('.boton-volver')) {
                    contenido.insertBefore(boton, contenido.firstChild);
                }
                reserva.parentNode.removeChild(reserva);
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', ubicar_boton_volver);
            } else {
                ubicar_boton_volver();
            }
        })();
    </script>
@endif

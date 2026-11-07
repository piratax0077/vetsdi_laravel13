{{--
    Botón "Volver" automático.

    Va incluido en cada plantilla, justo después del contenido. Aparece sobre el título en
    Mis documentos, en el Panel de configuración y en todas las vistas del tutor y sus
    mascotas (rutas paciente.*), salvo el escritorio del tutor y la descarga de la app. El
    escritorio de cada mascota sí lleva el botón. Si la vista ya incluyó su propio botón (template.partials.boton_volver),
    este no se repite.

    Para sumar otra página, agregar el nombre de su ruta a la lista de abajo.
    Para que una de esas vistas quede sin botón:
        @section('sin_boton_volver', '1')
--}}
@php
    $es_vista_tutor = request()->routeIs('paciente.*', 'mascotas.genealogia.index', 'mascotas.genealogia.show')
        && ! request()->routeIs('paciente.home', 'paciente.dependiente.home');

    // Vistas del profesional (incluye las de veterinaria bajo /Profesional), menos su escritorio
    $es_vista_profesional = (request()->routeIs('profesional.*') || request()->is('Profesional/*'))
        && ! request()->routeIs('profesional.home');

    // Vistas del asistente (todas sus variantes) y de peluquería / centro estético, menos sus escritorios
    $es_vista_asistente = request()->routeIs('asistente*')
        && ! request()->routeIs('asistente.home', 'asistente_adm.home', 'asistentecm.home', 'asistentejcm.home');

    $es_vista_peluqueria = request()->routeIs('peluqueria.*', 'centro_estetico.*')
        && ! request()->routeIs('peluqueria.home', 'centro_estetico.home');

    // Vistas del administrador (centro médico, hospital, contabilidad, gastos, comercial), menos sus escritorios
    // Incluye los módulos del recuadro del escritorio (áreas, historial, operación, personal, hospitalización)
    $es_vista_administrador = (request()->is('Administrador*')
            || request()->routeIs('vet.areas.*', 'vet.historial.*', 'vet.operacion.*', 'vet.trabajadores.*', 'vet.hospital.asignaciones', 'vet.hospital.equipo'))
        && ! request()->routeIs('adm_cm.home', 'adm_hospital.home', 'adm_hospital_ambulatorio.home', 'contabilidad.home', 'gastos.home', 'administrador_comercial.home');

    $con_boton_volver = $es_vista_administrador || $es_vista_tutor ||$es_vista_profesional || $es_vista_asistente || $es_vista_peluqueria || request()->routeIs(
        'profesional.mis_documentos',
        'paciente.receta.documentos',
        '*.configuracion'
    );
    $sin_boton_volver = $__env->hasSection('sin_boton_volver');
@endphp
@if ($con_boton_volver && ! $sin_boton_volver)
    <div id="boton_volver_automatico" style="display: none">
        {{-- Texto y destino fijos: así no toma variables sueltas de la plantilla (ej. el $texto del menú lateral) --}}
        @include('template.partials.boton_volver', ['url' => null, 'texto' => 'Atrás'])
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

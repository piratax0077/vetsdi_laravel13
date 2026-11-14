{{-- Botón "Notas internas" a la izquierda de Alertas: notas que se dejaron sobre el tutor de la mascota.
     Las ve quien las escribió y los profesionales del lugar de atención donde se dejaron.
     Desde el panel se pueden agregar notas nuevas (se guardan con guardar_nota_tutor) --}}
@php
    $idTutorNotas = isset($mascota) && $mascota ? $mascota->id_responsable : null;
    $notasInternasMascota = $idTutorNotas && isset($profesional) && $profesional
        ? \App\Support\NotasInternasMascota::para($mascota, $profesional)
        : null;
    $idLugarAtencionNotas = data_get($fichaAtencion ?? null, 'id_lugar_atencion')
        ?: data_get($hora_medica ?? null, 'id_lugar_atencion');
@endphp

@if (is_array($notasInternasMascota))
<style>
    .notas-internas-mascota__boton {
        position: fixed;
        right: 250px;
        bottom: 28px;
        z-index: 10;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        height: 40px;
        padding: 0 16px;
        border: 0;
        border-radius: 20px;
        background-color: #31bebe;
        color: #fff;
        font-family: 'Nunito', sans-serif;
        font-size: 14px;
        font-weight: 700;
        white-space: nowrap;
        box-shadow: 0 1px 10px rgba(0, 0, 0, .3);
        cursor: pointer;
        transition: background-color .2s ease;
    }
    .notas-internas-mascota__boton:hover,
    .notas-internas-mascota__boton[aria-expanded="true"] { background-color: #279e9e; }
    .notas-internas-mascota__boton:focus-visible { outline: 2px solid #a8e6e6; outline-offset: 2px; }
    .notas-internas-mascota__boton .feather { font-size: 16px; }
    .notas-internas-mascota__contador {
        min-width: 20px;
        height: 20px;
        padding: 0 6px;
        border-radius: 10px;
        background-color: #fff;
        color: #279e9e;
        font-size: 12px;
        line-height: 20px;
        text-align: center;
    }

    .notas-internas-mascota__panel {
        position: fixed;
        right: 20px;
        bottom: 84px;
        z-index: 10;
        width: 380px;
        max-width: calc(100vw - 32px);
        max-height: calc(100vh - 140px);
        overflow-y: auto;
        background-color: #fff;
        border-radius: 10px;
        box-shadow: 0 6px 24px rgba(39, 39, 39, .22);
        font-family: 'Nunito Sans', 'Nunito', sans-serif;
    }
    .notas-internas-mascota__panel[hidden] { display: none; }
    .notas-internas-mascota__cabecera {
        position: sticky;
        top: 0;
        z-index: 1;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 14px;
        background-color: #31bebe;
        border-radius: 10px 10px 0 0;
        color: #fff;
    }
    .notas-internas-mascota__cabecera h6 {
        margin: 0;
        color: #fff;
        font-family: 'Nunito', sans-serif;
        font-size: 15px;
        font-weight: 700;
    }
    .notas-internas-mascota__cerrar {
        border: 0;
        background: transparent;
        color: #fff;
        font-size: 22px;
        line-height: 1;
        cursor: pointer;
    }

    /* Formulario para agregar una nota */
    .notas-internas-mascota__formulario {
        padding: 14px 14px 12px;
        border-bottom: 1px solid #eceef3;
    }
    .notas-internas-mascota__formulario .form-group { margin-bottom: 10px; }
    .notas-internas-mascota__formulario .bloque-estado {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin: 0 0 10px;
        padding: 8px 12px;
    }
    .notas-internas-mascota__pregunta {
        color: #272727;
        font-family: 'Nunito', sans-serif;
        font-size: 13px;
        font-weight: 700;
    }
    .notas-internas-mascota__error {
        margin: 0 0 10px;
        color: #dc3545;
        font-size: 13px;
    }

    .notas-internas-mascota__lista {
        margin: 0;
        padding: 6px 14px 10px;
        list-style: none;
    }
    .notas-internas-mascota__item {
        display: flex;
        gap: 10px;
        padding: 10px 0;
        border-bottom: 1px solid #eceef3;
    }
    .notas-internas-mascota__item:last-child { border-bottom: 0; }
    .notas-internas-mascota__icono {
        flex: 0 0 32px;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background-color: #e3f6f6;
        color: #279e9e;
        font-size: 16px;
        line-height: 32px;
        text-align: center;
    }
    .notas-internas-mascota__titulo {
        display: block;
        margin-bottom: 2px;
        color: #272727;
        font-family: 'Nunito', sans-serif;
        font-size: 14px;
        font-weight: 700;
    }
    .notas-internas-mascota__texto {
        display: block;
        color: #555;
        font-size: 13px;
        line-height: 1.35;
        white-space: pre-line;
        overflow-wrap: anywhere;
    }
    .notas-internas-mascota__datos {
        display: block;
        margin-top: 4px;
        color: #6c757d;
        font-size: 12px;
    }
    .notas-internas-mascota__vacio {
        padding: 16px 14px;
        color: #6c757d;
        font-size: 13px;
        text-align: center;
    }
    .notas-internas-mascota__vacio[hidden] { display: none; }

    /* En pantallas angostas queda sobre el botón Alertas */
    @media (max-width: 575.98px) {
        .notas-internas-mascota__boton { right: 88px; bottom: 76px; padding: 0 12px; }
        .notas-internas-mascota__panel { right: 16px; bottom: 128px; max-height: calc(100vh - 184px); }
    }
</style>

<button type="button" class="notas-internas-mascota__boton" id="notas_internas_mascota_boton"
        aria-controls="notas_internas_mascota_panel" aria-expanded="false">
    <i class="feather icon-file-text"></i>
    Notas internas
    <span class="notas-internas-mascota__contador" id="notas_internas_mascota_contador">{{ count($notasInternasMascota) }}</span>
</button>

<div class="notas-internas-mascota__panel" id="notas_internas_mascota_panel" role="dialog" aria-labelledby="notas_internas_mascota_titulo" hidden>
    <div class="notas-internas-mascota__cabecera">
        <h6 id="notas_internas_mascota_titulo"><i class="feather icon-file-text mr-1"></i> Notas internas del tutor</h6>
        <button type="button" class="notas-internas-mascota__cerrar" id="notas_internas_mascota_cerrar" aria-label="Cerrar">&times;</button>
    </div>

    <form class="notas-internas-mascota__formulario" id="notas_internas_mascota_formulario" novalidate>
        <div class="form-group">
            <label class="floating-label-activo-sm" for="notas_internas_mascota_texto">Nueva nota</label>
            <textarea class="form-control form-control-sm" id="notas_internas_mascota_texto" name="nota" rows="3" maxlength="2000" placeholder="Ej.: Llegó tarde y fue agresivo con el equipo" required></textarea>
            <div class="invalid-feedback">Escribe la nota.</div>
        </div>
        <div class="bloque-estado">
            <span class="notas-internas-mascota__pregunta" id="notas_internas_mascota_pregunta">¿Tutor conflictivo?</span>
            <div class="custom-control custom-switch" role="group" aria-labelledby="notas_internas_mascota_pregunta">
                <input type="checkbox" class="custom-control-input" id="notas_internas_mascota_conflictivo" name="conflictivo" value="1">
                <label class="custom-control-label" for="notas_internas_mascota_conflictivo">No</label>
            </div>
        </div>
        <p class="notas-internas-mascota__error" id="notas_internas_mascota_error" hidden></p>
        <div class="text-right">
            <button type="submit" class="btn btn-info" id="notas_internas_mascota_agregar"><i class="feather icon-plus mr-1"></i>Agregar nota</button>
        </div>
    </form>

    <div class="notas-internas-mascota__vacio" id="notas_internas_mascota_vacio" @if (count($notasInternasMascota)) hidden @endif>
        Todavía no hay notas para este tutor.
    </div>

    <ul class="notas-internas-mascota__lista" id="notas_internas_mascota_lista">
        @foreach ($notasInternasMascota as $nota)
            <li class="notas-internas-mascota__item">
                <span class="notas-internas-mascota__icono">
                    <i class="feather {{ $nota['conflictivo'] ? 'icon-alert-triangle' : 'icon-file-text' }}"></i>
                </span>
                <div>
                    <span class="notas-internas-mascota__titulo">
                        {{ $nota['fecha'] }}
                        @if ($nota['conflictivo'])
                            <span class="badge badge-light-danger ml-1">Conflictivo</span>
                        @endif
                    </span>
                    <span class="notas-internas-mascota__texto">{{ $nota['nota'] }}</span>
                    <span class="notas-internas-mascota__datos">
                        {{ $nota['profesional'] }}@if ($nota['lugar']) · {{ $nota['lugar'] }}@endif
                    </span>
                </div>
            </li>
        @endforeach
    </ul>
</div>

<script>
    (function () {
        var boton = document.getElementById('notas_internas_mascota_boton');
        var panel = document.getElementById('notas_internas_mascota_panel');
        var cerrar = document.getElementById('notas_internas_mascota_cerrar');
        if (!boton || !panel) return;

        // Se ubica justo a la izquierda del botón Alertas, que cambia de ancho según su contador
        function ubicarJuntoAAlertas() {
            var alertas = document.getElementById('alertas_mascota_boton');
            if (window.innerWidth < 576) {
                boton.style.right = '';
                return;
            }
            boton.style.right = alertas
                ? (window.innerWidth - alertas.getBoundingClientRect().left + 10) + 'px'
                : '92px';
        }
        ubicarJuntoAAlertas();
        window.addEventListener('resize', ubicarJuntoAAlertas);

        function mostrar(abrir) {
            panel.hidden = !abrir;
            boton.setAttribute('aria-expanded', abrir ? 'true' : 'false');
        }

        // Sin detener el clic, así el panel de Alertas se cierra solo al abrir este
        boton.addEventListener('click', function () { mostrar(panel.hidden); });
        cerrar.addEventListener('click', function () { mostrar(false); });

        // El botón Alertas detiene su clic; se escucha aparte para cerrar este panel
        var botonAlertas = document.getElementById('alertas_mascota_boton');
        if (botonAlertas) botonAlertas.addEventListener('click', function () { mostrar(false); });

        // Se cierra al hacer clic fuera del panel o con la tecla Escape
        document.addEventListener('click', function (evento) {
            if (!panel.hidden && !panel.contains(evento.target) && !boton.contains(evento.target)) mostrar(false);
        });
        document.addEventListener('keydown', function (evento) {
            if (evento.key === 'Escape' && !panel.hidden) {
                mostrar(false);
                boton.focus();
            }
        });

        // Agregar una nota desde la atención
        var formulario = document.getElementById('notas_internas_mascota_formulario');
        var texto = document.getElementById('notas_internas_mascota_texto');
        var conflictivo = document.getElementById('notas_internas_mascota_conflictivo');
        var etiquetaConflictivo = document.querySelector('label[for="notas_internas_mascota_conflictivo"]');
        var botonAgregar = document.getElementById('notas_internas_mascota_agregar');
        var error = document.getElementById('notas_internas_mascota_error');
        var lista = document.getElementById('notas_internas_mascota_lista');
        var vacio = document.getElementById('notas_internas_mascota_vacio');
        var contador = document.getElementById('notas_internas_mascota_contador');

        conflictivo.addEventListener('change', function () {
            etiquetaConflictivo.textContent = conflictivo.checked ? 'Sí' : 'No';
        });

        function mostrarError(mensaje) {
            error.textContent = mensaje || '';
            error.hidden = !mensaje;
        }

        function crearElemento(etiqueta, clase, contenido) {
            var elemento = document.createElement(etiqueta);
            if (clase) elemento.className = clase;
            if (contenido) elemento.textContent = contenido;
            return elemento;
        }

        function agregarALista(nota) {
            var item = crearElemento('li', 'notas-internas-mascota__item');
            var icono = crearElemento('span', 'notas-internas-mascota__icono');
            icono.appendChild(crearElemento('i', 'feather ' + (nota.conflictivo ? 'icon-alert-triangle' : 'icon-file-text')));

            var cuerpo = document.createElement('div');
            var titulo = crearElemento('span', 'notas-internas-mascota__titulo', nota.fecha + ' ');
            if (nota.conflictivo) titulo.appendChild(crearElemento('span', 'badge badge-light-danger ml-1', 'Conflictivo'));
            cuerpo.appendChild(titulo);
            cuerpo.appendChild(crearElemento('span', 'notas-internas-mascota__texto', nota.texto));
            cuerpo.appendChild(crearElemento('span', 'notas-internas-mascota__datos',
                (nota.profesional || '') + (nota.lugar ? ' · ' + nota.lugar : '')));

            item.appendChild(icono);
            item.appendChild(cuerpo);
            lista.insertBefore(item, lista.firstChild);
            vacio.hidden = true;
            contador.textContent = lista.children.length;
        }

        formulario.addEventListener('submit', function (evento) {
            evento.preventDefault();
            mostrarError('');
            texto.value = texto.value.trim();
            if (!formulario.checkValidity()) {
                formulario.classList.add('was-validated');
                return;
            }

            var datos = new FormData();
            datos.append('nota', texto.value);
            datos.append('conflictivo', conflictivo.checked ? 1 : 0);
            @if ($idLugarAtencionNotas)
                datos.append('id_lugar_atencion', '{{ (int) $idLugarAtencionNotas }}');
            @endif

            botonAgregar.disabled = true;
            fetch("{{ route('profesional.tutor.notas.guardar', ['tutor' => $idTutorNotas]) }}", {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                credentials: 'same-origin',
                body: datos
            }).then(function (respuesta) {
                return respuesta.json().catch(function () { return {}; }).then(function (cuerpo) {
                    if (!respuesta.ok || !cuerpo.nota) {
                        var primero = cuerpo.errors ? cuerpo.errors[Object.keys(cuerpo.errors)[0]][0] : null;
                        throw new Error(primero || cuerpo.msj || 'No se pudo guardar la nota.');
                    }
                    return cuerpo;
                });
            }).then(function (cuerpo) {
                agregarALista(cuerpo.nota);
                formulario.reset();
                formulario.classList.remove('was-validated');
                etiquetaConflictivo.textContent = 'No';
            }).catch(function (problema) {
                mostrarError(problema.message);
            }).then(function () {
                botonAgregar.disabled = false;
            });
        });
    }());
</script>
@endif

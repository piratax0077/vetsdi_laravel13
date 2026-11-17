{{-- Campo libre de examen físico: va en Anamnesis o dentro de la card Examen físico --}}
@php
    $valorExamenFisico = old('examen_fisico', data_get(
        isset($fichaVeterinariaGeneralData) && is_array($fichaVeterinariaGeneralData) ? $fichaVeterinariaGeneralData : [],
        'examen_fisico',
        data_get($fichaAtencion ?? null, 'examen_fisico', '')
    ));
@endphp
<div class="form-group col-12 campo-con-dictado">
    <label class="floating-label-activo-sm" for="examen_fisico">{{ $etiquetaExamenFisico ?? 'Examen Físico' }}</label>
    {{-- Con filasExamenFisico el campo queda siempre de ese alto; si no, se abre al escribir --}}
    <textarea class="form-control caja-texto form-control-sm"
        @if (!empty($filasExamenFisico)) rows="{{ $filasExamenFisico }}" data-fixed-rows @else rows="1" onfocus="this.rows=3" onblur="this.rows=1" @endif
        name="examen_fisico"
        id="examen_fisico"
        placeholder="Describa examen físico">{{ $valorExamenFisico }}</textarea>
    <button type="button" class="boton-dictado" data-dictado-para="examen_fisico" title="Dictar por voz" aria-pressed="false">
        <i class="feather icon-mic"></i> <span class="boton-dictado-texto">Dictar por voz</span>
    </button>
</div>

@once
<script>
    // Dictado por voz para campos de texto: el botón lleva data-dictado-para con el id del campo
    (function () {
        var reconocimiento = null;
        var botonActivo = null;

        function avisar(titulo, texto, icono) {
            if (typeof swal === 'function') { swal({ title: titulo, text: texto, icon: icono }); }
            else { alert(texto); }
        }

        function marcarBoton(boton, escuchando) {
            boton.classList.toggle('grabando', escuchando);
            boton.setAttribute('aria-pressed', escuchando ? 'true' : 'false');
            boton.title = escuchando ? 'Detener dictado' : 'Dictar por voz';
            boton.querySelector('.boton-dictado-texto').textContent = escuchando ? 'Escuchando... detener' : 'Dictar por voz';
        }

        function detener() {
            var boton = botonActivo;
            botonActivo = null;
            if (reconocimiento) { try { reconocimiento.stop(); } catch (e) {} }
            if (boton) { marcarBoton(boton, false); }
        }

        // Inserta el texto donde está el cursor, con un espacio de separación si hace falta
        function insertarTexto(campo, texto) {
            var inicio = campo.selectionStart != null ? campo.selectionStart : campo.value.length;
            var fin = campo.selectionEnd != null ? campo.selectionEnd : campo.value.length;
            var antes = campo.value.slice(0, inicio);
            var despues = campo.value.slice(fin);
            var separador = antes && !/\s$/.test(antes) ? ' ' : '';
            var nuevo = separador + texto.trim() + ' ';
            campo.value = antes + nuevo + despues;
            var cursor = (antes + nuevo).length;
            campo.setSelectionRange(cursor, cursor);
            campo.dispatchEvent(new Event('input', { bubbles: true }));
            campo.dispatchEvent(new Event('change', { bubbles: true }));
        }

        document.addEventListener('click', function (evento) {
            var boton = evento.target.closest('[data-dictado-para]');
            if (!boton) { return; }

            if (botonActivo === boton) { detener(); return; }
            if (botonActivo) { detener(); }

            var Reconocimiento = window.SpeechRecognition || window.webkitSpeechRecognition;
            if (!Reconocimiento) {
                avisar('Dictado no disponible', 'Este navegador no permite el dictado por voz. Prueba con Chrome o Edge.', 'info');
                return;
            }

            var campo = document.getElementById(boton.dataset.dictadoPara);
            if (!campo) { return; }

            reconocimiento = new Reconocimiento();
            reconocimiento.lang = 'es-CL';
            reconocimiento.continuous = true;
            reconocimiento.interimResults = false;

            reconocimiento.onresult = function (e) {
                for (var i = e.resultIndex; i < e.results.length; i++) {
                    if (e.results[i].isFinal) { insertarTexto(campo, e.results[i][0].transcript); }
                }
            };
            reconocimiento.onerror = function (e) {
                if (e.error === 'not-allowed' || e.error === 'service-not-allowed') {
                    avisar('Micrófono bloqueado', 'Permita el uso del micrófono en el navegador para dictar.', 'warning');
                }
                detener();
            };
            reconocimiento.onend = function () {
                // Chrome corta tras un silencio; si no se detuvo a mano, se retoma
                if (botonActivo === boton) { try { reconocimiento.start(); } catch (e) { detener(); } }
            };

            try { reconocimiento.start(); } catch (e) { return; }
            botonActivo = boton;
            marcarBoton(boton, true);
            campo.focus();
        });
    })();
</script>
@endonce

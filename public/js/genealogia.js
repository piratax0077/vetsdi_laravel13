/* Genealogía de mascotas: formulario único de familiares, visor de fotos y vistas previas */
(function () {
    'use strict';

    function leerDatos() {
        var nodo = document.getElementById('datosGenealogia');
        try {
            return nodo ? JSON.parse(nodo.textContent) : null;
        } catch (error) {
            return null;
        }
    }

    function mostrar(elemento, visible) {
        if (elemento) elemento.hidden = !visible;
    }

    /* Vista previa de la foto de la mascota en "Datos del pedigrí" */
    Array.prototype.forEach.call(document.querySelectorAll('.genealogy-photo-input'), function (input) {
        input.addEventListener('change', function () {
            var archivo = this.files && this.files[0];
            var vista = document.querySelector('[data-preview="' + this.name + '"]');
            if (!archivo || !vista) return;
            var lector = new FileReader();
            lector.onload = function (evento) {
                if (vista.tagName !== 'IMG') {
                    var imagen = document.createElement('img');
                    imagen.className = vista.className.replace(' empty', '');
                    imagen.setAttribute('data-preview', vista.getAttribute('data-preview'));
                    imagen.alt = 'Vista previa';
                    vista.parentNode.replaceChild(imagen, vista);
                    vista = imagen;
                }
                vista.src = evento.target.result;
            };
            lector.readAsDataURL(archivo);
        });
    });

    /* Visor de fotos del árbol */
    (function () {
        var fotos = Array.prototype.slice.call(document.querySelectorAll('.js-genealogy-viewer'));
        var visor = document.getElementById('genealogyViewer');
        if (!visor || !fotos.length) return;
        var imagen = visor.querySelector('.genealogy-viewer-image');
        var leyenda = visor.querySelector('.genealogy-viewer-caption');
        var contador = visor.querySelector('.genealogy-viewer-counter');
        var actual = 0;

        function abrir(indice) {
            actual = (indice + fotos.length) % fotos.length;
            imagen.src = fotos[actual].src;
            imagen.alt = fotos[actual].alt || 'Fotografía de mascota';
            leyenda.textContent = fotos[actual].getAttribute('data-caption') || imagen.alt;
            contador.textContent = (actual + 1) + ' de ' + fotos.length;
            visor.classList.add('open');
            document.body.style.overflow = 'hidden';
        }
        function cerrar() {
            visor.classList.remove('open');
            document.body.style.overflow = '';
        }
        fotos.forEach(function (foto, indice) {
            foto.addEventListener('click', function () { abrir(indice); });
        });
        visor.querySelector('.genealogy-viewer-close').addEventListener('click', cerrar);
        visor.querySelector('.genealogy-viewer-prev').addEventListener('click', function () { abrir(actual - 1); });
        visor.querySelector('.genealogy-viewer-next').addEventListener('click', function () { abrir(actual + 1); });
        visor.addEventListener('click', function (evento) { if (evento.target === visor) cerrar(); });
        document.addEventListener('keydown', function (evento) {
            if (!visor.classList.contains('open')) return;
            if (evento.key === 'Escape') cerrar();
            if (evento.key === 'ArrowLeft') abrir(actual - 1);
            if (evento.key === 'ArrowRight') abrir(actual + 1);
        });
    })();

    /* Formulario único de familiares */
    (function () {
        var datos = leerDatos();
        var modal = document.getElementById('modalFamiliar');
        if (!datos || !modal || !window.jQuery) return;

        var $ = window.jQuery;
        var titulo = document.getElementById('modalFamiliarTitulo');
        var campoModo = document.getElementById('familiarModo');
        var campoParentesco = document.getElementById('familiarParentesco');
        var campoRegistroHermano = document.getElementById('familiarRegistroHermano');
        var pasoParentesco = document.getElementById('pasoParentesco');
        var selectorParentesco = document.getElementById('selectorParentesco');
        var detalle = document.getElementById('familiarDetalle');
        var radiosOrigen = modal.querySelectorAll('input[name="origen"]');
        var bloqueRegistrado = document.getElementById('bloqueRegistrado');
        var bloqueExterno = document.getElementById('bloqueExterno');
        var bloqueTipoHermano = document.getElementById('bloqueTipoHermano');
        var grupoSexo = document.getElementById('grupoSexo');
        var selectMascota = document.getElementById('familiarMascota');
        var avisoSinOpciones = document.getElementById('familiarSinOpciones');
        var campoNombre = document.getElementById('familiarNombre');
        var campoEspecie = document.getElementById('familiarEspecie');
        var campoSexo = document.getElementById('familiarSexo');
        var campoTipo = document.getElementById('familiarTipo');
        var bloqueFoto = document.getElementById('bloqueFoto');
        var fotoImagen = document.getElementById('familiarFotoImagen');
        var fotoVacia = document.getElementById('familiarFotoVacia');
        var fotoMensaje = document.getElementById('familiarFotoMensaje');
        var fotoCampo = document.getElementById('familiarFotoCampo');
        var campoFoto = document.getElementById('familiarFoto');
        var botonGuardar = document.getElementById('botonGuardarFamiliar');
        var botonQuitar = document.getElementById('botonQuitarFamiliar');
        var formQuitar = document.getElementById('formQuitarFamiliar');

        var nombreMascota = datos.mascota.nombre;
        var actual = null; // familiar que se está editando

        function etiquetaDe(parentesco) {
            if (parentesco === 'hermano') return 'Hermano o hermana';
            return datos.parentescos[parentesco] ? datos.parentescos[parentesco].etiqueta : '';
        }

        function opcionPorId(id) {
            for (var i = 0; i < datos.opciones.length; i++) {
                if (String(datos.opciones[i].id) === String(id)) return datos.opciones[i];
            }
            return null;
        }

        function origenElegido() {
            for (var i = 0; i < radiosOrigen.length; i++) {
                if (radiosOrigen[i].checked) return radiosOrigen[i].value;
            }
            return '';
        }

        function elegirOrigen(valor) {
            for (var i = 0; i < radiosOrigen.length; i++) {
                radiosOrigen[i].checked = radiosOrigen[i].value === valor;
            }
        }

        // mismo sexo que el parentesco y misma especie que la mascota; las sin dato se muestran igual
        function opcionesPara(parentesco, idActual) {
            var sexo = parentesco !== 'hermano' && datos.parentescos[parentesco] ? datos.parentescos[parentesco].sexo : null;
            var especie = datos.mascota.especie;
            var filtrarEspecie = especie === 'Canino' || especie === 'Felino';
            var usados = {};
            if (parentesco === 'hermano') {
                Object.keys(datos.hermanos || {}).forEach(function (clave) {
                    var hermano = datos.hermanos[clave];
                    if (hermano.mascota_id) usados[hermano.mascota_id] = true;
                });
            }

            return datos.opciones.filter(function (opcion) {
                if (String(opcion.id) === String(idActual)) return true;
                if (usados[opcion.id]) return false;
                if (sexo && opcion.sexo && opcion.sexo !== sexo) return false;
                if (filtrarEspecie && opcion.especie !== especie && opcion.especie !== 'Otro') return false;
                return true;
            });
        }

        function plantillaOpcion(opcion) {
            if (!opcion.id) return opcion.text;
            var datosOpcion = opcionPorId(opcion.id);
            if (!datosOpcion) return opcion.text;
            var $fila = $('<span class="familiar-opcion"></span>');
            if (datosOpcion.foto) {
                $('<img class="familiar-opcion-foto" alt="">').attr('src', datosOpcion.foto).appendTo($fila);
            } else {
                $('<span class="familiar-opcion-foto es-vacia"><i class="fas fa-paw"></i></span>').appendTo($fila);
            }
            var sexo = datosOpcion.sexo === 'M' ? ' · Macho' : (datosOpcion.sexo === 'F' ? ' · Hembra' : '');
            $('<span class="familiar-opcion-texto"></span>')
                .append($('<strong></strong>').text(datosOpcion.nombre))
                .append($('<small></small>').text(datosOpcion.especie + sexo))
                .appendTo($fila);
            return $fila;
        }

        function cargarOpciones(parentesco, idActual) {
            var $select = $(selectMascota);
            if ($select.data('select2')) $select.select2('destroy');
            selectMascota.innerHTML = '';
            selectMascota.appendChild(new Option('Buscar por nombre...', ''));

            var opciones = opcionesPara(parentesco, idActual);
            opciones.forEach(function (opcion) {
                selectMascota.appendChild(new Option(opcion.nombre, opcion.id, false, String(opcion.id) === String(idActual)));
            });
            mostrar(avisoSinOpciones, opciones.length === 0);

            if ($.fn.select2) {
                $select.select2({
                    dropdownParent: $(modal),
                    width: '100%',
                    placeholder: 'Buscar por nombre...',
                    templateResult: plantillaOpcion,
                    templateSelection: plantillaOpcion,
                    language: { noResults: function () { return 'No hay coincidencias'; } }
                });
            }
            return opciones.length;
        }

        function pintarFoto(url, mensaje, permitirSubir) {
            fotoImagen.src = url || '';
            mostrar(fotoImagen, !!url);
            mostrar(fotoVacia, !url);
            fotoMensaje.textContent = mensaje;
            mostrar(fotoCampo, permitirSubir);
        }

        // la foto depende de si es registrado y si esa mascota ya tiene foto de perfil
        function actualizarFoto() {
            var origen = origenElegido();
            campoFoto.value = '';
            if (!origen) {
                mostrar(bloqueFoto, false);
                return;
            }
            mostrar(bloqueFoto, true);
            var mismoFamiliar = actual && actual.origen === origen;

            if (origen === 'registrado') {
                var elegida = opcionPorId(selectMascota.value);
                if (!elegida) {
                    mostrar(bloqueFoto, false);
                } else if (elegida.foto) {
                    pintarFoto(elegida.foto, 'Se usará su foto de perfil del sistema.', false);
                } else if (mismoFamiliar && String(actual.mascota_id) === String(elegida.id) && actual.foto_genealogia) {
                    pintarFoto(actual.foto_genealogia, 'Foto guardada en el árbol. Puedes cambiarla.', true);
                } else {
                    pintarFoto('', 'No tiene foto de perfil. Puedes subir una solo para el árbol.', true);
                }
                return;
            }

            if (mismoFamiliar && actual.foto_genealogia) {
                pintarFoto(actual.foto_genealogia, 'Foto actual. Puedes cambiarla.', true);
            } else {
                pintarFoto('', 'Foto (opcional)', true);
            }
        }

        function aplicarOrigen() {
            var origen = origenElegido();
            var esHermano = campoParentesco.value === 'hermano';
            mostrar(bloqueRegistrado, origen === 'registrado');
            mostrar(bloqueExterno, origen === 'externo');
            mostrar(grupoSexo, esHermano);
            selectMascota.required = origen === 'registrado';
            campoNombre.required = origen === 'externo';
            campoEspecie.required = origen === 'externo';
            actualizarFoto();
            botonGuardar.disabled = !campoParentesco.value || !origen;
        }

        function prepararParentesco(parentesco, familiar) {
            campoParentesco.value = parentesco;
            mostrar(detalle, !!parentesco);
            if (!parentesco) {
                botonGuardar.disabled = true;
                return;
            }
            mostrar(bloqueTipoHermano, parentesco === 'hermano');
            var cantidad = cargarOpciones(parentesco, familiar ? familiar.mascota_id : null);
            if (!familiar) elegirOrigen(cantidad ? 'registrado' : 'externo');
            aplicarOrigen();
        }

        function limpiarCampos() {
            campoRegistroHermano.value = '';
            campoNombre.value = '';
            campoEspecie.value = '';
            campoSexo.value = '';
            campoTipo.value = 'completo';
            campoFoto.value = '';
            elegirOrigen('');
        }

        // el select de parentesco solo ofrece los que aún faltan; hermanos siempre
        function llenarSelectorParentesco(preseleccion) {
            selectorParentesco.innerHTML = '';
            selectorParentesco.appendChild(new Option('Seleccione el familiar', ''));
            Object.keys(datos.parentescos).forEach(function (clave) {
                if (!datos.familiares[clave]) {
                    selectorParentesco.appendChild(new Option(datos.parentescos[clave].etiqueta, clave, false, clave === preseleccion));
                }
            });
            selectorParentesco.appendChild(new Option('Hermano o hermana', 'hermano', false, preseleccion === 'hermano'));
        }

        function abrirAgregar(parentesco) {
            actual = null;
            limpiarCampos();
            campoModo.value = 'agregar';
            titulo.textContent = 'Agregar familiar de ' + nombreMascota;
            mostrar(pasoParentesco, true);
            mostrar(botonQuitar, false);
            llenarSelectorParentesco(parentesco || '');
            prepararParentesco(selectorParentesco.value, null);
        }

        function abrirEditar(parentesco, idHermano) {
            var familiar = parentesco === 'hermano'
                ? (datos.hermanos || {})[idHermano]
                : datos.familiares[parentesco];
            if (!familiar) return abrirAgregar(parentesco);

            actual = familiar;
            limpiarCampos();
            campoModo.value = 'editar';
            campoRegistroHermano.value = parentesco === 'hermano' ? idHermano : '';
            titulo.textContent = 'Editar ' + (familiar.etiqueta || etiquetaDe(parentesco)).toLowerCase() + ': ' + familiar.nombre;
            mostrar(pasoParentesco, false);
            mostrar(botonQuitar, true);

            elegirOrigen(familiar.origen);
            if (familiar.origen === 'externo') {
                campoNombre.value = familiar.nombre || '';
                campoEspecie.value = familiar.especie_externa || '';
                campoSexo.value = familiar.sexo || '';
            }
            if (parentesco === 'hermano') campoTipo.value = familiar.tipo || 'completo';
            prepararParentesco(parentesco, familiar);
        }

        // vuelve a llenar lo que se había escrito cuando el servidor rechazó el formulario
        function restaurarAnterior(anterior) {
            if (anterior.modo === 'editar') {
                abrirEditar(anterior.parentesco, anterior.registro_hermano_id);
            } else {
                abrirAgregar(anterior.parentesco);
            }
            if (anterior.origen) elegirOrigen(anterior.origen);
            if (anterior.nombre) campoNombre.value = anterior.nombre;
            if (anterior.especie) campoEspecie.value = anterior.especie;
            if (anterior.sexo) campoSexo.value = anterior.sexo;
            if (anterior.tipo) campoTipo.value = anterior.tipo;
            if (anterior.mascota_id) $(selectMascota).val(anterior.mascota_id).trigger('change');
            aplicarOrigen();
        }

        selectorParentesco.addEventListener('change', function () {
            limpiarCampos();
            prepararParentesco(this.value, null);
        });
        Array.prototype.forEach.call(radiosOrigen, function (radio) {
            radio.addEventListener('change', aplicarOrigen);
        });
        $(selectMascota).on('change', actualizarFoto);

        campoFoto.addEventListener('change', function () {
            var archivo = this.files && this.files[0];
            if (!archivo) return;
            var lector = new FileReader();
            lector.onload = function (evento) {
                fotoImagen.src = evento.target.result;
                mostrar(fotoImagen, true);
                mostrar(fotoVacia, false);
            };
            lector.readAsDataURL(archivo);
        });

        botonQuitar.addEventListener('click', function () {
            if (!actual) return;
            if (!window.confirm('¿Quitar a ' + actual.nombre + ' del árbol de ' + nombreMascota + '?')) return;
            formQuitar.querySelector('[name="parentesco"]').value = campoParentesco.value;
            formQuitar.querySelector('[name="registro_hermano_id"]').value = campoRegistroHermano.value;
            formQuitar.submit();
        });

        // botón "Agregar familiar" y etiquetas "sin registrar"
        $(document).on('click', '.js-agregar-familiar', function () {
            abrirAgregar(this.getAttribute('data-parentesco'));
            $(modal).modal('show');
        });

        // clic en una ficha para editarla; la foto sigue abriendo el visor
        function abrirDesdeFicha(ficha) {
            abrirEditar(ficha.getAttribute('data-parentesco'), ficha.getAttribute('data-hermano'));
            $(modal).modal('show');
        }
        $(document).on('click', '.js-editar-familiar', function (evento) {
            if ($(evento.target).closest('.js-genealogy-viewer').length) return;
            abrirDesdeFicha(this);
        });
        $(document).on('keydown', '.js-editar-familiar', function (evento) {
            if (evento.key === 'Enter' || evento.key === ' ') {
                evento.preventDefault();
                abrirDesdeFicha(this);
            }
        });

        if (datos.anterior && datos.anterior.parentesco) {
            restaurarAnterior(datos.anterior);
            $(modal).modal('show');
        }
    })();

    /* Avisos flotantes: entran desde abajo y se cierran solos (los errores duran más) */
    Array.prototype.forEach.call(document.querySelectorAll('.notificacion'), function (aviso, indice) {
        var temporizador = null;

        function cerrar() {
            window.clearTimeout(temporizador);
            aviso.classList.remove('visible');
            window.setTimeout(function () {
                if (aviso.parentNode) aviso.parentNode.removeChild(aviso);
            }, 350);
        }

        window.setTimeout(function () { aviso.classList.add('visible'); }, 80 + indice * 120);
        temporizador = window.setTimeout(cerrar, aviso.classList.contains('es-error') ? 9000 : 4500);
        aviso.querySelector('.notificacion-cerrar').addEventListener('click', cerrar);
    });
})();

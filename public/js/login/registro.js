/*
 * Tarjeta de ingreso y registro.
 *
 * El registro vive dentro de la misma tarjeta del ingreso: no hay una pagina
 * aparte, solo se cambia el bloque que esta a la vista. El registro se llena
 * en tres pasos (tipo de cuenta, datos y contrasena) y recien se envia al
 * terminar el ultimo.
 */
(function () {
    'use strict';

    var panelIngreso = document.getElementById('panel-ingreso');
    var panelRegistro = document.getElementById('panel-registro');

    if (!panelIngreso || !panelRegistro || !document.getElementById('form_registro')) {
        return;
    }

    function recorrer(lista, accion) {
        Array.prototype.forEach.call(lista, accion);
    }

    /** Pone o quita una clase segun la condicion. */
    function marcar(elemento, clase, activa) {
        if (activa) {
            elemento.classList.add(clase);
        } else {
            elemento.classList.remove(clase);
        }
    }

    /** Muestra el bloque pedido y deja el foco donde corresponde. */
    function mostrarBloque(cual) {
        var esRegistro = cual === 'registro';

        panelIngreso.hidden = esRegistro;
        panelRegistro.hidden = !esRegistro;

        if (esRegistro) {
            enfocarPaso();
        } else {
            document.getElementById('email').focus();
        }
    }

    // Enlaces "Crea tu cuenta" y "¿Ya tienes cuenta? Ingresa".
    recorrer(document.querySelectorAll('[data-ir-a]'), function (enlace) {
        enlace.addEventListener('click', function (evento) {
            evento.preventDefault();
            mostrarBloque(enlace.getAttribute('data-ir-a'));
        });
    });

    var formulario = document.getElementById('form_registro');
    var resena = document.getElementById('registro-resena');
    var etiquetaRut = document.getElementById('etiqueta-rut');
    var selectTipo = document.getElementById('tipo_cuenta');

    /** Al elegir un tipo de cuenta aparece su resena y se ajusta el rotulo del RUT. */
    function actualizarTipo() {
        var elegido = selectTipo.options[selectTipo.selectedIndex];
        var texto = elegido ? elegido.getAttribute('data-resena') || '' : '';

        if (resena) {
            resena.textContent = texto;
            resena.hidden = texto === '';
        }

        if (etiquetaRut) {
            // En una clinica el RUT que se pide es el de la persona que la administra.
            etiquetaRut.textContent = selectTipo.value === 'clinica'
                ? 'RUT del administrador de la clínica'
                : 'RUT';
        }

        revisarSiTeniaError(selectTipo);
    }

    /* ===== Validaciones, las mismas que despues repite el servidor ===== */

    function rutValido(texto) {
        var limpio = texto.replace(/[^0-9kK]/g, '').toUpperCase();

        if (!/^\d{6,8}[\dK]$/.test(limpio)) {
            return false;
        }

        var cuerpo = limpio.slice(0, -1);
        var suma = 0;
        var multiplo = 2;

        for (var i = cuerpo.length - 1; i >= 0; i--) {
            suma += multiplo * parseInt(cuerpo.charAt(i), 10);
            multiplo = multiplo === 7 ? 2 : multiplo + 1;
        }

        var resto = 11 - (suma % 11);
        var esperado = resto === 11 ? '0' : (resto === 10 ? 'K' : String(resto));

        return limpio.slice(-1) === esperado;
    }

    function telefonoValido(texto) {
        // Se aceptan el +56 y el 0 inicial: el servidor los quita antes de guardar.
        var digitos = texto.replace(/\D/g, '').replace(/^56/, '').replace(/^0/, '');

        return /^[29]\d{8}$/.test(digitos);
    }

    // Cada regla devuelve el mensaje de error, o un texto vacio si el campo esta bien.
    var reglas = {
        tipo_cuenta: function (valor) {
            return valor === '' ? 'Elige el tipo de cuenta que quieres crear.' : '';
        },
        rut: function (valor) {
            if (valor === '' || valor === '-') {
                return 'Ingresa tu RUT.';
            }

            return rutValido(valor) ? '' : 'Ingresa un RUT válido, con su dígito verificador.';
        },
        nombres: function (valor) {
            return valor === '' ? 'Ingresa tus nombres.' : '';
        },
        apellido_uno: function (valor) {
            return valor === '' ? 'Ingresa tu apellido paterno.' : '';
        },
        email_registro: function (valor) {
            if (valor === '') {
                return 'Ingresa tu correo electrónico.';
            }

            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(valor) ? '' : 'Ingresa un correo electrónico válido.';
        },
        telefono: function (valor) {
            if (valor === '') {
                return 'Ingresa tu teléfono.';
            }

            return telefonoValido(valor) ? '' : 'Ingresa un teléfono chileno con el formato +56 9 1234 5678.';
        },
        password_registro: function (valor, campo) {
            // La contrasena se revisa tal cual, sin recortar espacios.
            var clave = campo.value;

            if (clave === '') {
                return 'Ingresa una contraseña.';
            }

            if (clave.length < 8) {
                return 'La contraseña debe tener al menos 8 caracteres.';
            }

            // Hay minuscula si al pasar a mayusculas el texto cambia, y al reves.
            return clave !== clave.toUpperCase() && clave !== clave.toLowerCase() && /\d/.test(clave)
                ? ''
                : 'La contraseña debe incluir una mayúscula, una minúscula y un número.';
        },
        password_confirmation: function (valor, campo) {
            if (campo.value === '') {
                return 'Repite tu contraseña.';
            }

            return campo.value === document.getElementById('password_registro').value
                ? ''
                : 'Las contraseñas no coinciden.';
        }
    };

    /** Muestra u oculta el mensaje de error que acompana a un campo. */
    function mostrarError(campo, mensaje) {
        var aviso = formulario.querySelector('[data-error-de="' + campo.id + '"]');

        marcar(campo, 'es-invalido', mensaje !== '');

        if (aviso) {
            aviso.textContent = mensaje;
            aviso.hidden = mensaje === '';
        }
    }

    function validarCampo(campo) {
        var regla = reglas[campo.id];
        var mensaje = regla ? regla(campo.value.trim(), campo) : '';

        mostrarError(campo, mensaje);

        return mensaje === '';
    }

    /** Un campo marcado en rojo se vuelve a revisar mientras la persona lo corrige. */
    function revisarSiTeniaError(campo) {
        if (campo.classList.contains('es-invalido')) {
            validarCampo(campo);
        }
    }

    if (selectTipo) {
        // Select2 sin buscador: son solo cuatro opciones.
        if (window.jQuery && jQuery.fn.select2) {
            jQuery(selectTipo).select2({
                minimumResultsForSearch: Infinity,
                width: '100%',
                language: {
                    noResults: function () { return 'Sin resultados'; }
                }
            }).on('change', actualizarTipo);
        } else {
            selectTipo.addEventListener('change', actualizarTipo);
        }

        actualizarTipo();
    }

    /* ===== Registro en tres pasos ===== */

    var pasos = formulario.querySelectorAll('.registro-paso');
    var avance = document.getElementById('registro-avance');
    var botonesAvance = formulario.querySelectorAll('[data-ir-a-paso]');
    var botonAtras = document.getElementById('btn-paso-atras');
    var botonSiguiente = document.getElementById('btn-paso-siguiente');
    var botonCrear = document.getElementById('btn-crear-cuenta');
    var textoCrear = botonCrear.textContent;
    var totalPasos = pasos.length;
    var pasoActual = 1;
    var enviando = false;

    function pasoNumero(numero) {
        return formulario.querySelector('.registro-paso[data-paso="' + numero + '"]');
    }

    function camposDelPaso(numero) {
        return pasoNumero(numero).querySelectorAll('input, select');
    }

    /** Revisa todos los campos del paso y devuelve el primero con error, si hay. */
    function validarPaso(numero) {
        var primeroConError = null;

        recorrer(camposDelPaso(numero), function (campo) {
            if (!validarCampo(campo) && !primeroConError) {
                primeroConError = campo;
            }
        });

        return primeroConError;
    }

    function enfocar(campo) {
        // El select de tipo de cuenta esta oculto detras de select2: el foco va a su caja visible.
        var cajaSelect2 = campo.tagName === 'SELECT' && campo.nextElementSibling
            ? campo.nextElementSibling.querySelector('.select2-selection')
            : null;

        (cajaSelect2 || campo).focus();
    }

    /** Deja el foco en el primer campo con error del paso o, si no hay, en el primero. */
    function enfocarPaso() {
        var paso = pasoNumero(pasoActual);
        var campo = paso.querySelector('.es-invalido') || paso.querySelector('input, select');

        if (campo) {
            enfocar(campo);
        }
    }

    /** En el ultimo paso se recuerda a que correo llegara la confirmacion. */
    function actualizarAvisoCorreo() {
        var destino = document.getElementById('registro-aviso-destino');
        var correo = document.getElementById('email_registro').value.trim();

        if (!destino) {
            return;
        }

        destino.textContent = '';

        if (correo !== '') {
            var resaltado = document.createElement('strong');

            resaltado.textContent = correo;
            destino.appendChild(document.createTextNode(' a '));
            destino.appendChild(resaltado);
        }
    }

    function irAPaso(numero, conFoco) {
        pasoActual = numero;

        recorrer(pasos, function (paso) {
            paso.hidden = Number(paso.getAttribute('data-paso')) !== numero;
        });

        recorrer(botonesAvance, function (boton) {
            var suNumero = Number(boton.getAttribute('data-ir-a-paso'));

            marcar(boton.parentNode, 'esta-activo', suNumero === numero);
            marcar(boton.parentNode, 'esta-listo', suNumero < numero);

            // Desde el avance solo se puede volver a un paso ya completado.
            boton.disabled = suNumero >= numero;

            if (suNumero === numero) {
                boton.setAttribute('aria-current', 'step');
            } else {
                boton.removeAttribute('aria-current');
            }
        });

        botonAtras.hidden = numero === 1;
        botonSiguiente.hidden = numero === totalPasos;
        botonCrear.hidden = numero !== totalPasos;

        if (numero === totalPasos) {
            actualizarAvisoCorreo();
        }

        if (conFoco) {
            enfocarPaso();
        }
    }

    /** Pasa al paso siguiente solo si el actual esta completo. */
    function avanzar() {
        var conError = validarPaso(pasoActual);

        if (conError) {
            enfocar(conError);
            return;
        }

        irAPaso(pasoActual + 1, true);
    }

    botonSiguiente.addEventListener('click', avanzar);

    botonAtras.addEventListener('click', function () {
        irAPaso(pasoActual - 1, true);
    });

    recorrer(botonesAvance, function (boton) {
        boton.addEventListener('click', function () {
            irAPaso(Number(boton.getAttribute('data-ir-a-paso')), true);
        });
    });

    // Enter en los primeros pasos avanza en vez de enviar el formulario incompleto.
    formulario.addEventListener('keydown', function (evento) {
        var esEnter = evento.key === 'Enter' || evento.keyCode === 13;

        if (esEnter && evento.target.tagName === 'INPUT' && pasoActual < totalPasos) {
            evento.preventDefault();
            avanzar();
        }
    });

    recorrer(formulario.querySelectorAll('input'), function (campo) {
        campo.addEventListener('input', function () {
            revisarSiTeniaError(campo);

            // Al cambiar la contrasena tambien cambia si la repeticion coincide.
            if (campo.id === 'password_registro') {
                revisarSiTeniaError(document.getElementById('password_confirmation'));
            }
        });
    });

    formulario.addEventListener('submit', function (evento) {
        if (pasoActual < totalPasos) {
            evento.preventDefault();
            avanzar();
            return;
        }

        // Ultima revision de los tres pasos antes de enviar.
        for (var numero = 1; numero <= totalPasos; numero++) {
            var conError = validarPaso(numero);

            if (conError) {
                evento.preventDefault();
                irAPaso(numero, false);
                enfocar(conError);
                return;
            }
        }

        // Evita que un doble clic cree la cuenta dos veces.
        if (enviando) {
            evento.preventDefault();
            return;
        }

        enviando = true;
        botonCrear.disabled = true;
        botonAtras.disabled = true;
        botonCrear.textContent = 'Creando cuenta…';
    });

    // Si se vuelve con el boton "atras" del navegador, el formulario queda usable otra vez.
    window.addEventListener('pageshow', function (evento) {
        if (evento.persisted) {
            enviando = false;
            botonCrear.disabled = false;
            botonAtras.disabled = false;
            botonCrear.textContent = textoCrear;
        }
    });

    // Con el asistente listo aparecen el avance y los botones de cada paso.
    avance.hidden = false;
    irAPaso(Number(formulario.getAttribute('data-paso-inicial')) || 1, !panelRegistro.hidden);
}());

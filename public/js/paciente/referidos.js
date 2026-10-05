// Referidos del tutor (Paciente/Referidos)
document.addEventListener('DOMContentLoaded', function () {
    var PREFIJO = '+569';

    var tipo = document.getElementById('tipo_referido');
    var grupo = document.getElementById('grupo_nombre_centro');
    var campo = document.getElementById('nombre_centro');
    var etiqueta = grupo.querySelector('label');
    var etiquetas = {
        centro: 'Nombre del centro',
        alimentacion: 'Nombre del comercio',
        farmacia: 'Nombre de la farmacia'
    };

    // El nombre del local solo se pide cuando se invita a un centro o comercio
    function actualizarNombreCentro() {
        var esLocal = etiquetas.hasOwnProperty(tipo.value);
        grupo.classList.toggle('d-none', !esLocal);
        campo.required = esLocal;
        if (esLocal) {
            etiqueta.textContent = etiquetas[tipo.value];
        }
    }

    // Select2 sin buscador: son pocas opciones
    if (window.jQuery && jQuery.fn.select2) {
        jQuery(tipo).select2({
            minimumResultsForSearch: Infinity,
            width: '100%',
            dropdownParent: jQuery(tipo).parent()
        }).on('change', actualizarNombreCentro);
    } else {
        tipo.addEventListener('change', actualizarNombreCentro);
    }
    actualizarNombreCentro();

    // WhatsApp: se escriben solo los 8 dígitos y el +569 se antepone al enviar
    var numero = document.getElementById('telefono_numero');
    var telefono = document.getElementById('telefono_invitado');

    function soloDigitos(texto) {
        return texto.replace(/\D+/g, '');
    }

    function actualizarTelefono() {
        var digitos = soloDigitos(numero.value).slice(0, 8);
        numero.value = digitos;
        telefono.value = digitos ? PREFIJO + digitos : '';
    }

    // Si el formulario vuelve con error, se muestra el número sin el prefijo
    numero.value = soloDigitos(telefono.value).replace(/^569/, '');
    actualizarTelefono();
    numero.addEventListener('input', actualizarTelefono);

    var botonCopiar = document.getElementById('copiar_codigo');
    botonCopiar.addEventListener('click', function () {
        var codigo = document.getElementById('codigo_personal').textContent;
        var texto = botonCopiar.querySelector('span');

        function avisar() {
            texto.textContent = 'Copiado';
            setTimeout(function () { texto.textContent = 'Copiar'; }, 2000);
        }

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(codigo).then(avisar);
            return;
        }

        // Sin https no hay portapapeles moderno, se copia desde un campo temporal
        var auxiliar = document.createElement('textarea');
        auxiliar.value = codigo;
        auxiliar.className = 'referidos-copia-auxiliar';
        auxiliar.setAttribute('readonly', '');
        document.body.appendChild(auxiliar);
        auxiliar.select();
        document.execCommand('copy');
        document.body.removeChild(auxiliar);
        avisar();
    });

    var formularios = document.querySelectorAll('.formulario-cancelar-referido');
    for (var i = 0; i < formularios.length; i++) {
        formularios[i].addEventListener('submit', function (evento) {
            if (!confirm('¿Cancelar esta invitación?')) {
                evento.preventDefault();
            }
        });
    }
});

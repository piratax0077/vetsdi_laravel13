/*
 * Visor de las imágenes cargadas en cualquier Dropzone del sistema.
 * Al hacer clic en una miniatura se abre un modal con la imagen en grande y,
 * si hay más de una en la misma zona, se puede pasar de una a otra.
 * No depende de cómo esté configurado cada Dropzone.
 */
(function () {
    'use strict';

    if (window.visorImagenesCargado) {
        return;
    }
    window.visorImagenesCargado = true;

    var modal = null;
    var imagenes = [];
    var actual = 0;
    var urlsTemporales = [];

    function crearModal() {
        if (modal) {
            return modal;
        }

        modal = document.createElement('div');
        modal.className = 'modal fade visor-imagenes';
        modal.id = 'modal_visor_imagenes';
        modal.setAttribute('tabindex', '-1');
        modal.setAttribute('role', 'dialog');
        modal.setAttribute('aria-hidden', 'true');
        modal.setAttribute('aria-labelledby', 'modal_visor_imagenes_titulo');
        modal.innerHTML =
            '<div class="modal-dialog modal-lg" role="document">' +
                '<div class="modal-content">' +
                    '<div class="modal-header modal-header-celeste">' +
                        '<h5 class="modal-title" id="modal_visor_imagenes_titulo">Imagen</h5>' +
                        '<button type="button" class="close" data-visor-cerrar aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>' +
                    '</div>' +
                    '<div class="modal-body visor-imagenes-cuerpo">' +
                        '<img class="visor-imagenes-foto" alt="">' +
                    '</div>' +
                    '<div class="modal-footer visor-imagenes-pie">' +
                        '<span class="visor-imagenes-contador"></span>' +
                        '<button type="button" class="btn btn-info" data-visor-anterior><i class="feather icon-chevron-left"></i> Anterior</button>' +
                        '<button type="button" class="btn btn-info" data-visor-siguiente>Siguiente <i class="feather icon-chevron-right"></i></button>' +
                        '<button type="button" class="btn btn-outline-dark" data-visor-cerrar><i class="feather icon-x"></i> Cerrar</button>' +
                    '</div>' +
                '</div>' +
            '</div>';

        document.body.appendChild(modal);

        modal.addEventListener('click', function (e) {
            if (e.target.closest('[data-visor-cerrar]') || e.target === modal) {
                cerrar();
            } else if (e.target.closest('[data-visor-anterior]')) {
                mover(-1);
            } else if (e.target.closest('[data-visor-siguiente]')) {
                mover(1);
            }
        });

        if (window.jQuery && window.jQuery.fn.modal) {
            window.jQuery(modal).on('hidden.bs.modal', alCerrar);
            // Sobre otro modal abierto, el fondo oscuro del visor debe quedar encima de ese modal
            window.jQuery(modal).on('shown.bs.modal', function () {
                var fondos = document.querySelectorAll('.modal-backdrop');
                if (fondos.length > 1) {
                    fondos[fondos.length - 1].classList.add('visor-imagenes-fondo');
                }
            });
        }

        return modal;
    }

    // Busca el archivo de Dropzone que corresponde a la miniatura
    function archivoDe(preview) {
        var zona = preview.closest('.dropzone');
        var instancia = zona && zona.dropzone;
        if (!instancia || !instancia.files) {
            return null;
        }
        for (var i = 0; i < instancia.files.length; i++) {
            if (instancia.files[i].previewElement === preview) {
                return instancia.files[i];
            }
        }
        return null;
    }

    function respuestaServidor(archivo) {
        if (!archivo || !archivo.xhr || !archivo.xhr.responseText) {
            return '';
        }
        try {
            var datos = JSON.parse(archivo.xhr.responseText);
            return (datos.img && datos.img.url) || datos.url || '';
        } catch (e) {
            return '';
        }
    }

    // La imagen local tiene la calidad original; si no hay, la del servidor o la miniatura
    function datosImagen(preview) {
        var miniatura = preview.querySelector('[data-dz-thumbnail], .dz-image img');
        var archivo = archivoDe(preview);
        var nombre = preview.querySelector('[data-dz-name]');
        var url = '';

        if (archivo && window.Blob && archivo instanceof window.Blob && window.URL && URL.createObjectURL) {
            url = URL.createObjectURL(archivo);
            urlsTemporales.push(url);
        } else {
            url = (archivo && (archivo.url || archivo.dataURL)) || respuestaServidor(archivo) || (miniatura && miniatura.getAttribute('src')) || '';
        }

        return {
            url: url,
            nombre: (archivo && archivo.name) || (nombre && nombre.textContent) || 'Imagen'
        };
    }

    function esImagen(preview) {
        var miniatura = preview.querySelector('[data-dz-thumbnail], .dz-image img');
        return !!(miniatura && miniatura.getAttribute('src'));
    }

    function mostrar() {
        var foto = modal.querySelector('.visor-imagenes-foto');
        var imagen = imagenes[actual];
        var varias = imagenes.length > 1;

        foto.src = imagen.url;
        foto.alt = imagen.nombre;
        modal.querySelector('.modal-title').textContent = imagen.nombre;
        modal.querySelector('.visor-imagenes-contador').textContent = varias ? (actual + 1) + ' de ' + imagenes.length : '';
        modal.querySelector('[data-visor-anterior]').style.display = varias ? '' : 'none';
        modal.querySelector('[data-visor-siguiente]').style.display = varias ? '' : 'none';
    }

    function mover(paso) {
        if (imagenes.length < 2) {
            return;
        }
        actual = (actual + paso + imagenes.length) % imagenes.length;
        mostrar();
    }

    function abrir(preview) {
        var zona = preview.closest('.dropzone');
        var previews = Array.prototype.filter.call(zona.querySelectorAll('.dz-preview'), esImagen);

        crearModal();
        liberarUrls();
        imagenes = previews.map(datosImagen);
        actual = Math.max(0, previews.indexOf(preview));
        mostrar();

        if (window.jQuery && window.jQuery.fn.modal) {
            window.jQuery(modal).modal('show');
        } else {
            modal.style.display = 'block';
            modal.classList.add('show');
            modal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('visor-imagenes-abierto');
        }
    }

    function cerrar() {
        if (!modal) {
            return;
        }
        if (window.jQuery && window.jQuery.fn.modal) {
            window.jQuery(modal).modal('hide');
        } else {
            modal.classList.remove('show');
            modal.style.display = 'none';
            modal.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('visor-imagenes-abierto');
            alCerrar();
        }
    }

    function alCerrar() {
        modal.querySelector('.visor-imagenes-foto').removeAttribute('src');
        liberarUrls();
        // Si el visor se abrió sobre otro modal, ese modal debe seguir con su scroll
        if (document.querySelector('.modal.show')) {
            document.body.classList.add('modal-open');
        }
    }

    function liberarUrls() {
        urlsTemporales.forEach(function (url) {
            URL.revokeObjectURL(url);
        });
        urlsTemporales = [];
    }

    function estaAbierto() {
        return modal && modal.classList.contains('show');
    }

    document.addEventListener('click', function (e) {
        var preview = e.target.closest ? e.target.closest('.dropzone .dz-preview') : null;
        if (!preview || e.target.closest('.dz-remove, a, button') || !esImagen(preview)) {
            return;
        }
        e.preventDefault();
        e.stopPropagation();
        abrir(preview);
    }, true);

    document.addEventListener('keydown', function (e) {
        if (!estaAbierto()) {
            return;
        }
        if (e.key === 'ArrowLeft') {
            mover(-1);
        } else if (e.key === 'ArrowRight') {
            mover(1);
        } else if (e.key === 'Escape' && !(window.jQuery && window.jQuery.fn.modal)) {
            cerrar();
        }
    });
})();

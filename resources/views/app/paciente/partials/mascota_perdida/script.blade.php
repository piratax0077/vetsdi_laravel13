        {{-- Modal de mascota perdida, compartido por Mis mascotas y Mascotas perdidas.
             La vista que lo incluye debe tener: mascotasIniciales, obtenerMascotaPorId, obtenerImagenMascota,
             mascotaEstaFallecida, actualizarMascotasEnLista, rutaImagenCarga, parsearRespuestaImagen,
             obtenerRutaImagenSubida y mostrarErrorCargaImagen. --}}
        // ---- Mascota perdida ----
        var dropzoneMascotaPerdida = null;
        var fotosMascotaPerdida = [];

        function initDropzoneMascotaPerdida()
        {
            var element = document.querySelector('#dropzone-mascota-perdida');
            if (!element) return;

            if (element.dropzone) {
                try { element.dropzone.destroy(); } catch (e) {}
            }

            fotosMascotaPerdida = [];
            $('#perdida_fotos_json').val('[]');

            dropzoneMascotaPerdida = new Dropzone(element, {
                url: rutaImagenCarga,
                method: 'post',
                headers: { 'X-CSRF-TOKEN': CSRF_TOKEN },
                acceptedFiles: 'image/jpeg,image/png,image/jpg,.jpeg,.jpg,.png,image/*',
                maxFilesize: 6,
                maxFiles: 8,
                addRemoveLinks: true,
                createImageThumbnails: true,
                paramName: 'file',
                dictDefaultMessage: 'Arrastra las fotos aquí o haz clic para elegirlas',
                dictRemoveFile: 'Quitar',
                dictInvalidFileType: 'No puedes subir archivos de este tipo.',
                dictFileTooBig: 'El archivo es demasiado grande. Máx. 6 MiB.',
                dictMaxFilesExceeded: 'Puedes subir hasta 8 fotos.',
                success: function(file, response) {
                    var data = parsearRespuestaImagen(response) || response;
                    if (!data || !data.img) return;
                    fotosMascotaPerdida.push(obtenerRutaImagenSubida(data.img));
                    $('#perdida_fotos_json').val(JSON.stringify(fotosMascotaPerdida));
                },
                removedfile: function(file) {
                    if (file.xhr && file.xhr.response) {
                        var data = parsearRespuestaImagen(file.xhr.response);
                        var ruta = data && data.img ? obtenerRutaImagenSubida(data.img) : null;
                        if (ruta) {
                            fotosMascotaPerdida = fotosMascotaPerdida.filter(function(item) {
                                return item !== ruta;
                            });
                            $('#perdida_fotos_json').val(JSON.stringify(fotosMascotaPerdida));
                        }
                    }
                    if (file.previewElement) {
                        file.previewElement.remove();
                    }
                },
                error: function(file, message, xhr) {
                    mostrarErrorCargaImagen(typeof message === 'string' ? message : 'No se pudo subir la imagen.', xhr);
                }
            });
        }

        function mostrarVistaMascotaPerdida(vista)
        {
            var enFormulario = vista === 'formulario';
            $('#perdida_vista_seleccion').toggleClass('d-none', enFormulario);
            $('#perdida_vista_formulario').toggleClass('d-none', !enFormulario);
            $('#btn_publicar_mascota_perdida').toggleClass('d-none', !enFormulario);
            if (enFormulario) {
                initMapaMascotaPerdida();
            }
        }

        function limpiarFormularioMascotaPerdida()
        {
            $('#perdida_hora, #perdida_descripcion, #perdida_recompensa_monto').val('');
            $('#perdida_direccion').empty().trigger('change');
            $('#perdida_fecha').val('{{ date('Y-m-d') }}').trigger('change');
            $('#perdida_recompensa').prop('checked', false);
            toggleRecompensaMascotaPerdida();
            guardarPuntoMascotaPerdida('', '');
            if (mapaMascotaPerdida && pinMascotaPerdida) {
                pinMascotaPerdida.setLatLng(CENTRO_MAPA_PERDIDA);
                mapaMascotaPerdida.setView(CENTRO_MAPA_PERDIDA, 12);
            }
        }

        function abrirModalMascotaPerdida(event)
        {
            if (event && typeof event.preventDefault === 'function') {
                event.preventDefault();
            }

            var $select = $('#perdida_mascota_id');
            $select.html('<option value="">Seleccione una mascota</option>');

            (Array.isArray(mascotasIniciales) ? mascotasIniciales : []).forEach(function(mascota) {
                if (!mascota || !mascota.id || mascotaEstaFallecida(mascota)) return;
                $select.append($('<option></option>').val(mascota.id).text(mascota.nombre || ('Mascota #' + mascota.id)));
            });

            if ($select.find('option').length <= 1) {
                swal({
                    title: 'Sin mascotas disponibles',
                    text: 'No tienes mascotas activas para publicar como perdidas.',
                    icon: 'info'
                });
                return false;
            }

            limpiarFormularioMascotaPerdida();
            mostrarVistaMascotaPerdida('seleccion');
            $('#modal_mascota_perdida').appendTo('body').modal('show');
            return false;
        }

        // Al elegir la mascota se pasa directo al formulario del aviso
        $(document).on('change', '#perdida_mascota_id', function() {
            var mascota = obtenerMascotaPorId($(this).val());
            if (!mascota) return;

            $('#perdida_mascota_nombre').text(mascota.nombre || 'tu mascota');
            $('#perdida_mascota_foto').attr('src', obtenerImagenMascota(mascota));
            mostrarVistaMascotaPerdida('formulario');
        });

        function volverSeleccionMascotaPerdida(event)
        {
            if (event && typeof event.preventDefault === 'function') {
                event.preventDefault();
            }
            $('#perdida_mascota_id').val('');
            mostrarVistaMascotaPerdida('seleccion');
            return false;
        }

        function toggleRecompensaMascotaPerdida()
        {
            var activa = $('#perdida_recompensa').is(':checked');
            $('#perdida_recompensa_detalle').toggleClass('d-none', !activa);
            $('#perdida_recompensa').closest('.perdida-recompensa').toggleClass('activa', activa);
            if (!activa) {
                $('#perdida_recompensa_monto').val('');
            }
        }

        function publicarMascotaPerdida(event)
        {
            if (event && typeof event.preventDefault === 'function') {
                event.preventDefault();
            }

            var aviso = {
                mascota_id: $('#perdida_mascota_id').val(),
                direccion: $.trim($('#perdida_direccion option:selected').text()),
                latitud: $('#perdida_latitud').val(),
                longitud: $('#perdida_longitud').val(),
                fecha: $('#perdida_fecha').val(),
                hora: $('#perdida_hora').val(),
                descripcion: $.trim($('#perdida_descripcion').val()),
                recompensa: $('#perdida_recompensa').is(':checked') ? 1 : 0,
                recompensa_monto: $('#perdida_recompensa_monto').val(),
                fotos: fotosMascotaPerdida.slice()
            };

            var faltantes = [];
            if (!aviso.mascota_id) faltantes.push('Mascota');
            if (!aviso.direccion) faltantes.push('Dirección donde se perdió');
            if (!aviso.fecha) faltantes.push('Fecha');
            if (!aviso.descripcion) faltantes.push('Descripción');

            if (faltantes.length) {
                swal({
                    title: 'Faltan datos',
                    text: 'Completa: ' + faltantes.join(', ') + '.',
                    icon: 'warning'
                });
                return false;
            }

            // Pendiente: enviar "aviso" a la ruta de publicación cuando esté disponible.
            // Mientras tanto se marca en la tarjeta para ver el badge (no queda guardado)
            var mascotaPerdida = obtenerMascotaPorId(aviso.mascota_id);
            if (mascotaPerdida) {
                mascotaPerdida.perdida = 1;
                actualizarMascotasEnLista(mascotaPerdida);
            }
            $('#modal_mascota_perdida').modal('hide');

            swal({
                title: 'Aviso listo',
                text: 'El formulario está completo, pero la publicación aún no está conectada.',
                icon: 'info'
            });
            return false;
        }

        // Mapa de la dirección: pin rojo que se mueve al escribir y se puede arrastrar
        var mapaMascotaPerdida = null;
        var pinMascotaPerdida = null;
        var CENTRO_MAPA_PERDIDA = [-33.4489, -70.6693];

        function initMapaMascotaPerdida()
        {
            if (typeof L === 'undefined' || !document.getElementById('perdida_mapa')) return;

            if (!mapaMascotaPerdida) {
                mapaMascotaPerdida = L.map('perdida_mapa').setView(CENTRO_MAPA_PERDIDA, 12);
                L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; OpenStreetMap'
                }).addTo(mapaMascotaPerdida);

                var iconoRojo = L.icon({
                    iconUrl: 'https://cdn.jsdelivr.net/gh/pointhi/leaflet-color-markers@master/img/marker-icon-2x-red.png',
                    shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
                    iconSize: [25, 41],
                    iconAnchor: [12, 41],
                    shadowSize: [41, 41]
                });

                pinMascotaPerdida = L.marker(CENTRO_MAPA_PERDIDA, { icon: iconoRojo, draggable: true }).addTo(mapaMascotaPerdida);
                pinMascotaPerdida.on('dragend', function() {
                    var punto = pinMascotaPerdida.getLatLng();
                    guardarPuntoMascotaPerdida(punto.lat, punto.lng);
                    actualizarDireccionDesdePin(punto.lat, punto.lng);
                });
            }

            initBuscadorDireccionPerdida();

            setTimeout(function() { mapaMascotaPerdida.invalidateSize(); }, 150);
        }

        function guardarPuntoMascotaPerdida(lat, lng)
        {
            $('#perdida_latitud').val(lat);
            $('#perdida_longitud').val(lng);
        }

        // Sugerencias de direcciones reales (Photon / OpenStreetMap) mientras se escribe
        var URL_BUSCADOR_DIRECCIONES = 'https://photon.komoot.io';
        var LIMITES_CHILE = '-75.8,-56.0,-66.4,-17.4';

        // En Chile OpenStreetMap casi no trae números de casa: si la sugerencia es una
        // calle sin número, se le agrega el número que escribió la persona
        function armarSugerenciaDireccion(lugar, numeroEscrito)
        {
            var p = $.extend({}, lugar.properties || {});
            if (!p.street && p.osm_key === 'highway' && p.name) {
                p.street = p.name;
                p.name = '';
            }
            if (p.street && !p.housenumber && numeroEscrito) {
                p.housenumber = numeroEscrito;
                p.numeroAgregado = true;
            }
            var calle = p.street ? (p.street + (p.housenumber ? ' ' + p.housenumber : '')) : '';
            var titulo = p.name && p.name !== p.street ? p.name : (calle || p.city || 'Ubicación');
            var detalle = [];
            if (calle && calle !== titulo) detalle.push(calle);
            [p.district, p.city || p.county, p.state].forEach(function(parte) {
                if (parte && detalle.indexOf(parte) < 0 && parte !== titulo) detalle.push(parte);
            });

            var texto = [titulo].concat(detalle).join(', ');
            return {
                id: texto,
                text: texto,
                titulo: titulo,
                detalle: detalle.join(', '),
                calle: calle,
                ciudad: p.city || p.county || '',
                numeroAgregado: !!p.numeroAgregado,
                lat: lugar.geometry.coordinates[1],
                lng: lugar.geometry.coordinates[0]
            };
        }

        function extraerNumeroDireccion(texto)
        {
            var numero = String(texto || '').match(/\b\d{1,5}\b/);
            return numero ? numero[0] : '';
        }

        // Busca el punto exacto de la calle con número; si no existe, queda el de la calle
        function afinarPinConNumero(lugar)
        {
            $.getJSON('https://nominatim.openstreetmap.org/search', {
                street: lugar.calle,
                city: lugar.ciudad,
                country: 'Chile',
                format: 'json',
                limit: 1
            }).done(function(resultados) {
                if (!resultados || !resultados.length || !mapaMascotaPerdida) return;
                var lat = parseFloat(resultados[0].lat);
                var lng = parseFloat(resultados[0].lon);
                pinMascotaPerdida.setLatLng([lat, lng]);
                mapaMascotaPerdida.setView([lat, lng], 18);
                guardarPuntoMascotaPerdida(lat, lng);
            });
        }

        function initBuscadorDireccionPerdida()
        {
            var $direccion = $('#perdida_direccion');
            if (!$.fn.select2 || $direccion.hasClass('select2-hidden-accessible')) return;

            $direccion.select2({
                width: '100%',
                dropdownParent: $('#modal_mascota_perdida'),
                dropdownCssClass: 'perdida-direccion-dropdown',
                placeholder: 'Ej. Av. Providencia 1234, Santiago',
                minimumInputLength: 3,
                language: {
                    inputTooShort: function() { return 'Escribe al menos 3 letras'; },
                    searching: function() { return 'Buscando direcciones...'; },
                    noResults: function() { return 'No encontramos esa dirección'; },
                    errorLoading: function() { return 'No se pudieron cargar las sugerencias'; }
                },
                ajax: {
                    url: URL_BUSCADOR_DIRECCIONES + '/api/',
                    dataType: 'json',
                    delay: 350,
                    data: function(params) {
                        return { q: params.term, limit: 6, bbox: LIMITES_CHILE };
                    },
                    processResults: function(data, params) {
                        var numero = extraerNumeroDireccion(params.term);
                        var vistas = {};
                        var resultados = (data && data.features ? data.features : [])
                            .map(function(lugar) { return armarSugerenciaDireccion(lugar, numero); })
                            .filter(function(item) {
                                // Una misma calle puede venir en varios tramos: se muestra una vez
                                if (vistas[item.text]) return false;
                                vistas[item.text] = true;
                                return true;
                            });
                        return { results: resultados };
                    }
                },
                templateResult: function(item) {
                    if (!item.titulo) return item.text;
                    var $fila = $('<div></div>');
                    $('<span class="perdida-sugerencia-titulo"></span>').text(item.titulo).appendTo($fila);
                    if (item.detalle) {
                        $('<span class="perdida-sugerencia-detalle"></span>').text(item.detalle).appendTo($fila);
                    }
                    return $fila;
                },
                templateSelection: function(item) {
                    return item.text || 'Ej. Av. Providencia 1234, Santiago';
                }
            });

            $direccion.on('select2:select', function(e) {
                var lugar = e.params.data;
                if (!lugar || !mapaMascotaPerdida) return;
                pinMascotaPerdida.setLatLng([lugar.lat, lugar.lng]);
                mapaMascotaPerdida.setView([lugar.lat, lugar.lng], 17);
                guardarPuntoMascotaPerdida(lugar.lat, lugar.lng);
                if (lugar.numeroAgregado) {
                    afinarPinConNumero(lugar);
                }
                $('#perdida_mapa_estado').text('Revisa que el pin quede donde se perdió. Puedes arrastrarlo si hace falta.');
            });
        }

        // Al mover el pin se busca la dirección de ese punto y se deja escrita
        function actualizarDireccionDesdePin(lat, lng)
        {
            $('#perdida_mapa_estado').text('Buscando la dirección del punto...');
            $.getJSON(URL_BUSCADOR_DIRECCIONES + '/reverse', { lat: lat, lon: lng, limit: 1 })
                .done(function(data) {
                    if (!data || !data.features || !data.features.length) {
                        $('#perdida_mapa_estado').text('Ubicación ajustada manualmente.');
                        return;
                    }
                    var lugar = armarSugerenciaDireccion(data.features[0]);
                    var opcion = new Option(lugar.text, lugar.id, true, true);
                    $('#perdida_direccion').empty().append(opcion).trigger('change');
                    guardarPuntoMascotaPerdida(lat, lng);
                    $('#perdida_mapa_estado').text('Ubicación ajustada manualmente.');
                })
                .fail(function() {
                    $('#perdida_mapa_estado').text('Ubicación ajustada manualmente.');
                });
        }

        $(document).on('shown.bs.modal', '#modal_mascota_perdida', function() {
            if (!dropzoneMascotaPerdida) {
                initDropzoneMascotaPerdida();
            }
        });

        $(document).on('hidden.bs.modal', '#modal_mascota_perdida', function() {
            if (dropzoneMascotaPerdida && typeof dropzoneMascotaPerdida.removeAllFiles === 'function') {
                dropzoneMascotaPerdida.removeAllFiles(true);
            }
            fotosMascotaPerdida = [];
            $('#perdida_fotos_json').val('[]');
        });

        window.abrirModalMascotaPerdida = abrirModalMascotaPerdida;
        window.volverSeleccionMascotaPerdida = volverSeleccionMascotaPerdida;
        window.toggleRecompensaMascotaPerdida = toggleRecompensaMascotaPerdida;
        window.publicarMascotaPerdida = publicarMascotaPerdida;

        {{-- Tutoría compartida. Usa de la vista que lo incluye: mascotasCache, obtenerMascotaPorId,
             escaparTextoMascota, mascotaEstaFallecida, botonTraspasoCargando, rutaTraspasoBuscarTutor y CSRF_TOKEN. --}}
        // ---- Tutoría compartida ----
        var rutasTutoriaCompartida = {
            listar: @js(route('paciente.tutoria.listar', ['mascota' => '__MASCOTA__'])),
            rut: @js(route('paciente.tutoria.rut', ['mascota' => '__MASCOTA__'])),
            invitar: @js(route('paciente.tutoria.invitar', ['mascota' => '__MASCOTA__'])),
            gestionar: @js(route('paciente.tutoria.gestionar', ['mascota' => '__MASCOTA__', 'id' => '__ID__', 'accion' => '__ACCION__'])),
        };
        var titularTutoria = @json([
            'nombre' => trim(($paciente->nombres ?? '') . ' ' . ($paciente->apellido_uno ?? '')),
            'correo' => strtolower(trim($paciente->email ?? '')),
        ]);
        var invitacionesTutoria = {};
        var tutoriaMascotaId = null;
        var tutoriaPersonaRut = null;
        var tutoriaEnviando = false;
        var tutoriaVolverALista = false;

        var ROLES_TUTORIA = {
            co_tutor: { nombre: 'Co-tutor', titulo: 'Invita a un co-tutor', persona: 'co-tutora', sinAcceso: 'Ya no podrá ver ni editar la información de ' },
            cuidador: { nombre: 'Cuidador', titulo: 'Invita a un cuidador', persona: 'cuidadora', sinAcceso: 'Ya no podrá ver la ficha ni agendar citas de ' },
        };

        var ESTADOS_TUTORIA = {
            pendiente: { texto: 'Invitación pendiente', clase: 'badge-light-warning' },
            activa: { texto: 'Activo', clase: 'badge-light-success' },
            expirada: { texto: 'Expirada', clase: 'badge-light-secondary' },
            rechazada: { texto: 'Rechazada', clase: 'badge-light-danger' },
        };

        function rolTutoria(rol)
        {
            return ROLES_TUTORIA[rol] || ROLES_TUTORIA.co_tutor;
        }

        function nombreMascotaTutoria()
        {
            var mascota = obtenerMascotaPorId(tutoriaMascotaId);
            return mascota && mascota.nombre ? mascota.nombre : 'tu mascota';
        }

        function esCorreoTutoria(valor)
        {
            return /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(valor);
        }

        function pareceRutTutoria(valor)
        {
            return /^[0-9.]{6,12}-?[0-9kK]$/.test(valor);
        }


        function formatearFechaTutoria(fechaIso)
        {
            var fecha = new Date(fechaIso);
            if (isNaN(fecha.getTime())) return '';
            var dia = ('0' + fecha.getDate()).slice(-2);
            var mes = ('0' + (fecha.getMonth() + 1)).slice(-2);
            return dia + '-' + mes + '-' + fecha.getFullYear();
        }

        // Una pendiente cuyo plazo ya pasó se muestra como expirada aunque el servidor no la haya marcado
        function estadoInvitacionTutoria(invitacion)
        {
            if (invitacion.estado === 'pendiente' && invitacion.vence_el && new Date(invitacion.vence_el) < new Date()) {
                return 'expirada';
            }
            return invitacion.estado;
        }

        function mascotasTitularTutoria()
        {
            var vistas = {};
            var lista = [];
            Object.keys(mascotasCache).forEach(function(clave){
                var mascota = mascotasCache[clave];
                if (!mascota || !mascota.id || vistas[mascota.id]) return;
                vistas[mascota.id] = true;
                if (mascotaEstaFallecida(mascota) || String(mascota.estado) === '0') return;
                if (mascota.rol_acceso && mascota.rol_acceso !== 'titular') return;
                lista.push(mascota);
            });
            return lista;
        }

        function errorRespuestaTutoria(xhr, porDefecto)
        {
            var datos = (xhr && xhr.responseJSON) || {};
            if (datos.errors) return Object.values(datos.errors).flat().join('\n');
            if (datos.msj) return datos.msj;
            if (xhr && xhr.status === 409 && datos.message) return datos.message;
            if (xhr && xhr.status === 419) return 'Tu sesión venció. Recarga la página e intenta nuevamente.';
            if (xhr && xhr.status === 403) return 'Solo el tutor titular puede gestionar los accesos de esta mascota.';
            return porDefecto;
        }

        function solicitudTutoria(accion, datos)
        {
            var ruta = rutasTutoriaCompartida[accion] || rutasTutoriaCompartida.gestionar;
            return $.ajax({
                url: ruta.replace('__MASCOTA__', datos.mascota_id).replace('__ID__', datos.id || '').replace('__ACCION__', accion),
                type: accion === 'listar' ? 'get' : 'post',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                data: datos,
            });
        }

        function abrirTutoriaCompartida(event, idMascota)
        {
            if (event && typeof event.preventDefault === 'function') {
                event.preventDefault();
            }

            var mascotas = mascotasTitularTutoria();
            if (!mascotas.length) {
                swal({
                    title: 'Sin mascotas',
                    text: 'Agrega una mascota para compartir su tutoría con otra persona.',
                    icon: 'info',
                });
                return false;
            }

            var $select = $('#tutoria_mascota_id').empty();
            mascotas.forEach(function(mascota){
                $select.append($('<option></option>').val(mascota.id).text(mascota.nombre || 'Sin nombre'));
            });

            var elegida = idMascota && $select.find('option[value="' + idMascota + '"]').length ? idMascota : mascotas[0].id;
            $select.val(String(elegida));
            cambiarMascotaTutoria(elegida);

            $('#modal_tutoria_compartida, #modal_invitar_cotutor').each(function(){
                if (!$(this).parent().is('body')) $(this).appendTo('body');
            });
            $('#modal_tutoria_compartida').modal('show');
            return false;
        }

        window.abrirTutoriaCompartida = abrirTutoriaCompartida;

        function cambiarMascotaTutoria(idMascota)
        {
            tutoriaMascotaId = String(idMascota);
            $('.tutoria-nombre-mascota').text(nombreMascotaTutoria());

            $('#tutoria_lista').html('<p class="tutoria-vacio"><span class="spinner-border spinner-border-sm mr-1" role="status" aria-hidden="true"></span> Cargando...</p>');
            solicitudTutoria('listar', { mascota_id: tutoriaMascotaId })
                .done(function(respuesta){
                    invitacionesTutoria[String(idMascota)] = (respuesta && respuesta.invitaciones) || [];
                    if (tutoriaMascotaId === String(idMascota)) renderListaTutoria();
                })
                .fail(function(xhr){
                    if (tutoriaMascotaId !== String(idMascota)) return;
                    $('#tutoria_lista').html('<p class="tutoria-vacio">' + escaparTextoMascota(errorRespuestaTutoria(xhr, 'No se pudo cargar la lista. Intenta nuevamente.')) + '</p>');
                });
        }

        function filaPersonaTutoria(invitacion)
        {
            var estado = estadoInvitacionTutoria(invitacion);
            var datosEstado = ESTADOS_TUTORIA[estado] || ESTADOS_TUTORIA.pendiente;
            var nombre = invitacion.nombre || invitacion.correo;
            var detalle = [rolTutoria(invitacion.rol).nombre];
            if (invitacion.nombre && invitacion.correo) detalle.push(invitacion.correo);
            if (estado === 'pendiente' && invitacion.vence_el) detalle.push('Vence el ' + formatearFechaTutoria(invitacion.vence_el));

            var acciones = '';
            if (estado === 'pendiente') {
                acciones += '<button type="button" class="tutoria-accion" data-accion="reenviar" data-id="' + invitacion.id + '"><i class="feather icon-refresh-cw"></i> Reenviar</button>';
                acciones += '<button type="button" class="tutoria-accion tutoria-accion-peligro" data-accion="cancelar" data-id="' + invitacion.id + '"><i class="feather icon-x"></i> Cancelar invitación</button>';
            } else if (estado === 'activa') {
                acciones += '<button type="button" class="tutoria-accion tutoria-accion-peligro" data-accion="quitar" data-id="' + invitacion.id + '"><i class="feather icon-user-x"></i> Quitar acceso</button>';
            } else if (estado === 'expirada') {
                acciones += '<button type="button" class="tutoria-accion" data-accion="reenviar" data-id="' + invitacion.id + '"><i class="feather icon-rotate-cw"></i> Enviar de nuevo</button>';
            }

            return '' +
                '<div class="traspaso-tutor tutoria-persona" data-id="' + invitacion.id + '">' +
                '    <span class="traspaso-tutor-avatar">' + escaparTextoMascota(String(nombre || '?').charAt(0).toUpperCase()) + '</span>' +
                '    <div class="traspaso-tutor-datos">' +
                '        <strong class="traspaso-tutor-nombre">' + escaparTextoMascota(nombre) + '</strong>' +
                '        <span class="traspaso-tutor-rut">' + escaparTextoMascota(detalle.join(' · ')) + '</span>' +
                '    </div>' +
                '    <span class="badge ' + datosEstado.clase + ' tutoria-estado">' + datosEstado.texto + '</span>' +
                (acciones ? '    <div class="tutoria-acciones">' + acciones + '</div>' : '') +
                '</div>';
        }

        function renderListaTutoria()
        {
            var invitaciones = invitacionesTutoria[tutoriaMascotaId] || [];
            var html = '' +
                '<div class="traspaso-tutor">' +
                '    <span class="traspaso-tutor-avatar"><i class="feather icon-star"></i></span>' +
                '    <div class="traspaso-tutor-datos">' +
                '        <strong class="traspaso-tutor-nombre">' + escaparTextoMascota(titularTutoria.nombre || 'Tú') + ' (tú)</strong>' +
                '        <span class="traspaso-tutor-rut">Puede invitar, quitar accesos y eliminar la mascota</span>' +
                '    </div>' +
                '    <span class="traspaso-tutor-estado">Tutor titular</span>' +
                '</div>';

            if (!invitaciones.length) {
                html += '<p class="tutoria-vacio">Aún no compartes a ' + escaparTextoMascota(nombreMascotaTutoria()) +
                    ' con nadie. Invita a un co-tutor o a un cuidador para que te ayude.</p>';
            } else {
                invitaciones.forEach(function(invitacion){
                    html += filaPersonaTutoria(invitacion);
                });
            }

            $('#tutoria_lista').html(html);
        }

        function buscarInvitacionTutoria(id)
        {
            return (invitacionesTutoria[tutoriaMascotaId] || []).filter(function(item){
                return String(item.id) === String(id);
            })[0] || null;
        }

        function reemplazarInvitacionTutoria(invitacion)
        {
            var lista = invitacionesTutoria[tutoriaMascotaId] || [];
            var indice = -1;
            lista.forEach(function(item, i){
                if (String(item.id) === String(invitacion.id)) indice = i;
            });
            if (indice >= 0) {
                lista[indice] = invitacion;
            } else {
                lista.unshift(invitacion);
            }
            invitacionesTutoria[tutoriaMascotaId] = lista;
        }

        function quitarInvitacionTutoria(id)
        {
            invitacionesTutoria[tutoriaMascotaId] = (invitacionesTutoria[tutoriaMascotaId] || []).filter(function(item){
                return String(item.id) !== String(id);
            });
        }


        function abrirInvitacionTutoria()
        {
            resetInvitacionTutoria();
            tutoriaVolverALista = true;
            $('#modal_tutoria_compartida').one('hidden.bs.modal', function(){
                $('#modal_invitar_cotutor').modal('show');
            }).modal('hide');
        }

        function resetInvitacionTutoria()
        {
            tutoriaEnviando = false;
            tutoriaPersonaRut = null;
            $('#form_invitar_cotutor')[0].reset();
            $('#invitacion_persona').addClass('d-none');
            errorInvitacionTutoria('');
            botonTraspasoCargando($('#btn_enviar_invitacion'), false);
            actualizarTituloInvitacionTutoria();
        }

        function actualizarTituloInvitacionTutoria()
        {
            var rol = $('#form_invitar_cotutor input[name="rol"]:checked').val();
            $('#modalInvitarCotutorLabel').text(rolTutoria(rol).titulo);
        }

        function errorInvitacionTutoria(mensaje)
        {
            $('#invitacion_destino').toggleClass('is-invalid', !!mensaje);
            $('#invitacion_destino_error').toggleClass('d-none', !mensaje).text(mensaje || '');
        }

        function buscarPersonaRutTutoria(rut)
        {
            var diferido = $.Deferred();
            $.ajax({
                url: rutasTutoriaCompartida.rut.replace('__MASCOTA__', tutoriaMascotaId),
                type: 'get',
                headers: { 'Accept': 'application/json' },
                data: { rut: rut },
            }).done(function(respuesta){
                var persona = respuesta && respuesta.tutor;
                if (!persona || !persona.nombre) {
                    diferido.reject('No encontramos a nadie con ese RUT. Ingresa su correo electrónico.');
                    return;
                }
                if (!persona.email) {
                    diferido.reject('Esta persona no tiene un correo registrado. Ingresa su correo electrónico.');
                    return;
                }
                diferido.resolve(persona);
            }).fail(function(xhr){
                diferido.reject(errorRespuestaTutoria(xhr, 'No se pudo buscar el RUT. Ingresa su correo electrónico.'));
            });

            return diferido.promise();
        }

        function mostrarPersonaRutTutoria(persona)
        {
            tutoriaPersonaRut = persona;
            $('#invitacion_persona_nombre').text(persona.nombre);
            $('#invitacion_persona_correo').text('RUT ' + (persona.rut || '') + ' · ' + persona.email);
            $('#invitacion_persona .traspaso-tutor-estado').toggleClass('d-none', !persona.id);
            $('#invitacion_persona').removeClass('d-none');
        }

        function buscarRutInvitacionTutoria(rut)
        {
            var $boton = $('#btn_enviar_invitacion');
            if ($boton.prop('disabled')) return;

            botonTraspasoCargando($boton, true, 'Buscando…');
            buscarPersonaRutTutoria(rut)
                .done(function(persona){
                    botonTraspasoCargando($boton, false);
                    if ($.trim($('#invitacion_destino').val()) !== rut) return;
                    mostrarPersonaRutTutoria(persona);
                })
                .fail(function(mensaje){
                    botonTraspasoCargando($boton, false);
                    errorInvitacionTutoria(mensaje);
                });
        }

        // Revisa duplicados contra la lista de la mascota antes de enviar
        function errorDuplicadoTutoria(correo)
        {
            if (titularTutoria.correo && correo === titularTutoria.correo) {
                return 'Ese es tu propio correo. Ingresa el de la persona que quieres invitar.';
            }

            var mensaje = '';
            (invitacionesTutoria[tutoriaMascotaId] || []).forEach(function(item){
                if (mensaje || String(item.correo).toLowerCase() !== correo) return;
                var estado = estadoInvitacionTutoria(item);
                if (estado === 'activa') {
                    mensaje = 'Esta persona ya es ' + rolTutoria(item.rol).persona + ' de ' + nombreMascotaTutoria() + '.';
                } else if (estado === 'pendiente') {
                    mensaje = 'Ya enviaste una invitación a este correo.';
                }
            });
            return mensaje;
        }

        function enviarInvitacionTutoria()
        {
            if (tutoriaEnviando) return;

            var destino = $.trim($('#invitacion_destino').val() || '');
            var rol = $('#form_invitar_cotutor input[name="rol"]:checked').val() || 'co_tutor';
            var $boton = $('#btn_enviar_invitacion');

            // Con RUT, primero se muestra a quién se va a invitar y se envía en el segundo clic
            if (!esCorreoTutoria(destino) && pareceRutTutoria(destino) && !tutoriaPersonaRut) {
                buscarRutInvitacionTutoria(destino);
                return;
            }

            var correo = tutoriaPersonaRut ? String(tutoriaPersonaRut.email).toLowerCase() : destino.toLowerCase();
            if (!esCorreoTutoria(correo)) {
                errorInvitacionTutoria(/^[0-9.\-kK]+$/.test(destino) ? 'Ingresa un RUT válido.' : 'Ingresa un correo válido.');
                $('#invitacion_destino').trigger('focus');
                return;
            }

            var duplicado = errorDuplicadoTutoria(correo);
            if (duplicado) {
                errorInvitacionTutoria(duplicado);
                return;
            }

            errorInvitacionTutoria('');
            tutoriaEnviando = true;
            botonTraspasoCargando($boton, true, 'Enviando…');

            var datos = {
                mascota_id: tutoriaMascotaId,
                correo: tutoriaPersonaRut ? null : correo,
                rut: tutoriaPersonaRut ? tutoriaPersonaRut.rut : '',
                rol: rol,
            };

            solicitudTutoria('invitar', datos).done(function(respuesta){
                tutoriaEnviando = false;
                botonTraspasoCargando($boton, false);
                if (respuesta && respuesta.invitacion) reemplazarInvitacionTutoria(respuesta.invitacion);

                $('#modal_invitar_cotutor').one('hidden.bs.modal', function(){
                    swal({
                        title: 'Invitación enviada a ' + (tutoriaPersonaRut ? tutoriaPersonaRut.nombre : correo),
                        icon: 'success',
                        buttons: false,
                        timer: 3000,
                    });
                }).modal('hide');
            }).fail(function(xhr){
                tutoriaEnviando = false;
                botonTraspasoCargando($boton, false);
                errorInvitacionTutoria(errorRespuestaTutoria(xhr, 'No se pudo enviar la invitación. Intenta nuevamente.'));
            });
        }

        function accionInvitacionTutoria(accion, id, $boton)
        {
            var invitacion = buscarInvitacionTutoria(id);
            if (!invitacion) return;

            var textos = { reenviar: 'Enviando…', cancelar: 'Cancelando…', quitar: 'Quitando…' };
            botonTraspasoCargando($boton, true, textos[accion]);
            $('#tutoria_lista .tutoria-accion').prop('disabled', true);

            var mascotaId = tutoriaMascotaId;
            solicitudTutoria(accion, { id: id, mascota_id: mascotaId }).done(function(respuesta){
                if (tutoriaMascotaId !== mascotaId) return;
                if (accion === 'reenviar') {
                    reemplazarInvitacionTutoria((respuesta && respuesta.invitacion) || invitacion);
                } else {
                    quitarInvitacionTutoria(id);
                }
                renderListaTutoria();

                var mensajes = {
                    reenviar: 'Invitación enviada a ' + invitacion.correo,
                    cancelar: 'Invitación cancelada',
                    quitar: (invitacion.nombre || invitacion.correo) + ' ya no tiene acceso a ' + nombreMascotaTutoria(),
                };
                swal({ title: mensajes[accion], icon: 'success', buttons: false, timer: 3000 });
            }).fail(function(xhr){
                if (tutoriaMascotaId !== mascotaId) return;
                botonTraspasoCargando($boton, false);
                $('#tutoria_lista .tutoria-accion').prop('disabled', false);
                swal({ title: 'No se pudo completar la acción', text: errorRespuestaTutoria(xhr, 'Intenta nuevamente.'), icon: 'error' });
            });
        }

        $(document).on('change', '#tutoria_mascota_id', function(){
            cambiarMascotaTutoria(this.value);
        });

        $(document).on('click', '#btn_tutoria_invitar', abrirInvitacionTutoria);

        $(document).on('change', '#form_invitar_cotutor input[name="rol"]', actualizarTituloInvitacionTutoria);

        $(document).on('input', '#invitacion_destino', function(){
            tutoriaPersonaRut = null;
            $('#invitacion_persona').addClass('d-none');
            errorInvitacionTutoria('');
        });

        $(document).on('blur', '#invitacion_destino', function(){
            var valor = $.trim(this.value || '');
            if (!tutoriaPersonaRut && !esCorreoTutoria(valor) && pareceRutTutoria(valor)) {
                buscarRutInvitacionTutoria(valor);
            }
        });

        $(document).on('submit', '#form_invitar_cotutor', function(e){
            e.preventDefault();
            enviarInvitacionTutoria();
        });

        // Al cerrar la invitación se vuelve a la lista de la mascota
        $(document).on('hidden.bs.modal', '#modal_invitar_cotutor', function(){
            if (!tutoriaVolverALista) return;
            tutoriaVolverALista = false;
            renderListaTutoria();
            $('#modal_tutoria_compartida').modal('show');
        });

        $(document).on('click', '#tutoria_lista .tutoria-accion', function(){
            var $boton = $(this);
            var accion = $boton.data('accion');
            var id = $boton.data('id');
            var invitacion = buscarInvitacionTutoria(id);
            if (!invitacion) return;

            if (accion !== 'quitar') {
                accionInvitacionTutoria(accion, id, $boton);
                return;
            }

            var rol = rolTutoria(invitacion.rol);
            swal({
                title: '¿Quitar a ' + (invitacion.nombre || invitacion.correo) + ' como ' + rol.nombre.toLowerCase() + '?',
                text: rol.sinAcceso + nombreMascotaTutoria() + '.',
                icon: 'warning',
                buttons: ['Cancelar', 'Quitar acceso'],
                dangerMode: true,
            }).then(function(confirmado){
                if (confirmado) accionInvitacionTutoria('quitar', id, $boton);
            });
        });

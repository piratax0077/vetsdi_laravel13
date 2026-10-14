            {{-- Información clínica propia de la mascota, separada del tutor. --}}
            <div class="card-sidebar mb-0 rounded-0">
                <div class="card-header-sidebar" id="headingMascota">
                    <button class="btn btn-light btn-block text-left text-info" type="button"
                        data-toggle="collapse" data-target="#collapseMascota" aria-expanded="true"
                        aria-controls="collapseMascota">
                        <i class="feather icon-chevron-down float-right pt-1 flecha-accordion"></i>
                        INFORMACIÓN GENERAL DE LA MASCOTA
                    </button>
                </div>
                <div id="collapseMascota" class="collapse show" aria-labelledby="headingMascota"
                    data-parent="#accordionExample">
                    <div class="card-body-sidebar">
                        @if(isset($mascota) && $mascota)
                            @php
                                $especiesSidebar = \App\Models\EspecieMascota::with(['razas', 'tamanos'])->orderBy('nombre')->get();
                                $edadSidebar = $mascota->fecha_nacimiento ? $mascota->fecha_nacimiento->diff(now()) : null;
                                $datosSidebar = [
                                    'Nombre' => $mascota->nombre,
                                    'Especie' => optional($mascota->especieMascota)->nombre ?: $mascota->otra_especie,
                                    'Raza' => optional($mascota->razaMascota)->nombre,
                                    'Sexo' => $mascota->sexo === 'M' ? 'Macho' : ($mascota->sexo === 'F' ? 'Hembra' : null),
                                    'Nacimiento / edad' => $edadSidebar ? $mascota->fecha_nacimiento->format('d-m-Y').' ('.$edadSidebar->y.' años y '.$edadSidebar->m.' meses)' : null,
                                    'Tamaño' => optional($mascota->tamanoMascota)->nombre ?: $mascota->tamano,
                                    'Microchip' => $mascota->chip,
                                    'Esterilizado/a' => $mascota->esterilizado ? 'Sí' : 'No',
                                    'Fecha de esterilización' => optional($mascota->fecha_esterilizacion)->format('d-m-Y'),
                                    'Enfermedad crónica' => $mascota->enfermedad_cronica,
                                    'Dieta' => $mascota->dieta,
                                    'Última desparasitación' => optional($mascota->ultima_desparasitacion)->format('d-m-Y'),
                                    'Productos' => $mascota->producto_desparasitacion,
                                    'Cirugías' => $mascota->cirugias,
                                    'Vacunas' => $mascota->vacunas,
                                    'Viajes' => $mascota->viajes,
                                    '¿Vive con animales?' => $mascota->vive_con_animales === null ? null : ($mascota->vive_con_animales ? 'Sí' : 'No'),
                                ];
                            @endphp
                            <div id="info_mascota_sidebar_read">
                                <dl class="mascota-sidebar-datos mb-3">
                                    @foreach($datosSidebar as $etiqueta => $valor)
                                        <div class="mascota-sidebar-fila">
                                            <dt>{{ $etiqueta }}</dt>
                                            <dd>{{ filled($valor) ? $valor : 'Sin registro' }}</dd>
                                        </div>
                                    @endforeach
                                </dl>
                                <button type="button" class="btn btn-primary-light-c btn-sm mb-3" onclick="editarInformacionMascotaSidebar()">
                                    <i class="feather icon-edit"></i> Editar información
                                </button>
                            </div>

                            <form id="info_mascota_sidebar_edit" class="pt-3" style="display:none;" onsubmit="guardarInformacionMascotaSidebar(); return false;">
                                <div id="sidebar_mascota_error" class="alert alert-danger" role="alert" style="display:none;"></div>
                                <div class="form-group mb-2">
                                    <label class="mb-1" for="sidebar_mascota_nombre">Nombre</label>
                                    <input type="text" required maxlength="255" id="sidebar_mascota_nombre" class="form-control form-control-sm" value="{{ $mascota->nombre }}">
                                </div>
                                <div class="form-group mb-3">
                                    <label for="sidebar_mascota_especie_id">Especie</label>
                                    <select required id="sidebar_mascota_especie_id" class="form-control form-control-sm" onchange="actualizarCatalogosMascotaSidebar()">
                                        <option value="">Seleccione</option>
                                        @foreach($especiesSidebar as $especie)
                                            <option value="{{ $especie->id }}" @selected($mascota->especie_id == $especie->id)>{{ $especie->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group mb-3" id="sidebar_grupo_otra_especie">
                                    <label for="sidebar_mascota_otra_especie">Detalle de la especie</label>
                                    <input id="sidebar_mascota_otra_especie" maxlength="500" class="form-control form-control-sm" value="{{ $mascota->otra_especie }}">
                                </div>
                                <div class="form-group mb-3">
                                    <label for="sidebar_mascota_raza_id">Raza</label>
                                    <select id="sidebar_mascota_raza_id" class="form-control form-control-sm"></select>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="sidebar_mascota_tamano_id">Tamaño</label>
                                    <select required id="sidebar_mascota_tamano_id" class="form-control form-control-sm"></select>
                                </div>
                                <div class="form-group mb-2">
                                    <label class="mb-1" for="sidebar_mascota_chip">Microchip</label>
                                    <input type="text" maxlength="255" id="sidebar_mascota_chip" class="form-control form-control-sm" value="{{ $mascota->chip }}">
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-6 mb-2">
                                        <label class="mb-1" for="sidebar_mascota_fecha_nacimiento">Nacimiento</label>
                                    <input type="date" max="{{ date('Y-m-d') }}" id="sidebar_mascota_fecha_nacimiento" class="form-control form-control-sm"
                                            value="{{ optional($mascota->fecha_nacimiento)->format('Y-m-d') }}">
                                    </div>
                                    <div class="form-group col-6 mb-2">
                                        <label class="mb-1" for="sidebar_mascota_sexo">Sexo</label>
                                    <select id="sidebar_mascota_sexo" class="form-control form-control-sm">
                                            <option value="M" @selected($mascota->sexo === 'M')>Macho</option>
                                            <option value="F" @selected($mascota->sexo === 'F')>Hembra</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group mb-2">
                                    <label class="mb-1" for="sidebar_mascota_esterilizado">Esterilizado/a</label>
                                    <select id="sidebar_mascota_esterilizado" class="form-control form-control-sm" onchange="actualizarFechaEsterilizacionSidebar()">
                                        <option value="0" @selected(!$mascota->esterilizado)>No</option>
                                        <option value="1" @selected($mascota->esterilizado)>Sí</option>
                                    </select>
                                </div>
                                <div class="form-group mb-2" id="sidebar_grupo_fecha_esterilizacion">
                                    <label class="mb-1" for="sidebar_mascota_fecha_esterilizacion">Fecha de esterilización</label>
                                    <input type="date" max="{{ date('Y-m-d') }}" id="sidebar_mascota_fecha_esterilizacion" class="form-control form-control-sm"
                                        value="{{ optional($mascota->fecha_esterilizacion)->format('Y-m-d') }}">
                                    <small class="text-muted">Puede quedar sin fecha cuando no se conoce.</small>
                                </div>
                                <div class="form-group mb-2">
                                    <label class="mb-1" for="sidebar_mascota_enfermedad_cronica">Enfermedad crónica</label>
                                    <textarea maxlength="500" id="sidebar_mascota_enfermedad_cronica" class="form-control form-control-sm" rows="2">{{ $mascota->enfermedad_cronica }}</textarea>
                                </div>
                                <div class="form-group mb-2">
                                    <label class="mb-1" for="sidebar_mascota_dieta">Dieta</label>
                                    <textarea id="sidebar_mascota_dieta" class="form-control form-control-sm" rows="2">{{ $mascota->dieta }}</textarea>
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-6 mb-2">
                                        <label class="mb-1" for="sidebar_mascota_ultima_desparasitacion">Última desparasitación</label>
                                    <input type="date" max="{{ date('Y-m-d') }}" id="sidebar_mascota_ultima_desparasitacion" class="form-control form-control-sm"
                                            value="{{ optional($mascota->ultima_desparasitacion)->format('Y-m-d') }}">
                                    </div>
                                    <div class="form-group col-6 mb-2">
                                        <label class="mb-1" for="sidebar_mascota_producto_desparasitacion">Producto</label>
                                    <input type="text" maxlength="255" id="sidebar_mascota_producto_desparasitacion" class="form-control form-control-sm"
                                            value="{{ $mascota->producto_desparasitacion }}">
                                    </div>
                                </div>
                                @foreach(['cirugias' => 'Cirugías', 'vacunas' => 'Vacunas', 'viajes' => 'Viajes'] as $campo => $etiqueta)
                                    <div class="form-group mb-3">
                                        <label for="sidebar_mascota_{{ $campo }}">{{ $etiqueta }}</label>
                                        <textarea id="sidebar_mascota_{{ $campo }}" class="form-control form-control-sm" rows="2">{{ $mascota->{$campo} }}</textarea>
                                    </div>
                                @endforeach
                                <div class="form-group mb-3">
                                    <label for="sidebar_mascota_vive_con_animales">¿Vive con animales?</label>
                                    <select id="sidebar_mascota_vive_con_animales" class="form-control form-control-sm">
                                        <option value="" @selected($mascota->vive_con_animales === null)>Sin registro</option>
                                        <option value="1" @selected($mascota->vive_con_animales === true)>Sí</option>
                                        <option value="0" @selected($mascota->vive_con_animales === false)>No</option>
                                    </select>
                                </div>
                                <div class="mascota-sidebar-acciones text-center mt-3">
                                    <button type="submit" class="btn btn-info btn-sm">
                                        <i class="feather icon-save"></i> Guardar
                                    </button>
                                    <button type="button" class="btn btn-danger-light-c btn-sm" onclick="cancelarInformacionMascotaSidebar()">
                                        <i class="feather icon-x"></i> Cancelar
                                    </button>
                                </div>
                            </form>

                            @once
                            <script>
                                const catalogosMascotaSidebar = @json($especiesSidebar);
                                function actualizarCatalogosMascotaSidebar(razaId = '', tamanoId = '') {
                                    const especie = catalogosMascotaSidebar.find(item => String(item.id) === $('#sidebar_mascota_especie_id').val());
                                    const llenar = function (selector, opciones, seleccionado, placeholder) {
                                        const select = $(selector).empty().append(new Option(placeholder, ''));
                                        (opciones || []).forEach(item => select.append(new Option(item.nombre, item.id)));
                                        select.val(String(seleccionado || ''));
                                    };
                                    llenar('#sidebar_mascota_raza_id', especie?.razas, razaId, 'Sin raza');
                                    llenar('#sidebar_mascota_tamano_id', especie?.tamanos, tamanoId, 'Seleccione');
                                    $('#sidebar_grupo_otra_especie').toggle(!!especie?.requiere_detalle);
                                    $('#sidebar_mascota_otra_especie').prop('required', !!especie?.requiere_detalle);
                                }
                                function editarInformacionMascotaSidebar() {
                                    $('#info_mascota_sidebar_read').hide();
                                    $('#info_mascota_sidebar_edit').show();
                                    $('#sidebar_mascota_error').hide().text('');
                                    actualizarCatalogosMascotaSidebar(@json($mascota->raza_id), @json($mascota->tamano_id));
                                    actualizarFechaEsterilizacionSidebar();
                                }

                                function cancelarInformacionMascotaSidebar() {
                                    if ($('#info_mascota_sidebar_edit button[type=submit]').prop('disabled')) return;
                                    document.getElementById('info_mascota_sidebar_edit').reset();
                                    $('#info_mascota_sidebar_edit').hide();
                                    $('#info_mascota_sidebar_read').show();
                                }

                                function actualizarFechaEsterilizacionSidebar() {
                                    $('#sidebar_grupo_fecha_esterilizacion').toggle($('#sidebar_mascota_esterilizado').val() === '1');
                                }

                                function guardarInformacionMascotaSidebar() {
                                    const form = document.getElementById('info_mascota_sidebar_edit');
                                    if (!form.reportValidity() || $(form).find('button[type=submit]').prop('disabled')) return;
                                    const esterilizado = $('#sidebar_mascota_esterilizado').val();
                                    const fechaEsterilizacion = $('#sidebar_mascota_fecha_esterilizacion').val();
                                    const chip = $('#sidebar_mascota_chip').val().trim();
                                    const datos = {
                                        _token: @json(csrf_token()),
                                        _method: 'PUT',
                                        id_responsable: @json($mascota->id_responsable),
                                        id_lugar_atencion: $('#id_lugar_atencion').val() || null,
                                        tiene_chip: chip ? 1 : 0,
                                        chip: chip,
                                        nombre: $('#sidebar_mascota_nombre').val().trim(),
                                        especie_id: $('#sidebar_mascota_especie_id').val(),
                                        raza_id: $('#sidebar_mascota_raza_id').val(),
                                        otra_especie: $('#sidebar_mascota_otra_especie').prop('required') ? $('#sidebar_mascota_otra_especie').val() : '',
                                        tamano_id: $('#sidebar_mascota_tamano_id').val(),
                                        fecha_nacimiento: $('#sidebar_mascota_fecha_nacimiento').val(),
                                        sexo: $('#sidebar_mascota_sexo').val(),
                                        esterilizado: esterilizado,
                                        fecha_esterilizacion_desconocida: esterilizado === '1' && !fechaEsterilizacion ? 1 : 0,
                                        fecha_esterilizacion: esterilizado === '1' ? fechaEsterilizacion : '',
                                        enfermedad_cronica: $('#sidebar_mascota_enfermedad_cronica').val(),
                                        dieta: $('#sidebar_mascota_dieta').val(),
                                        ultima_desparasitacion: $('#sidebar_mascota_ultima_desparasitacion').val(),
                                        producto_desparasitacion: $('#sidebar_mascota_producto_desparasitacion').val(),
                                        cirugias: $('#sidebar_mascota_cirugias').val(),
                                        vacunas: $('#sidebar_mascota_vacunas').val(),
                                        viajes: $('#sidebar_mascota_viajes').val(),
                                        vive_con_animales: $('#sidebar_mascota_vive_con_animales').val()
                                    };

                                    if (!datos.nombre) {
                                        swal({ title: 'Editar mascota', text: 'El nombre es obligatorio.', icon: 'warning', button: 'Aceptar' });
                                        return;
                                    }

                                    const botones = $(form).find('button');
                                    const error = $('#sidebar_mascota_error').hide().text('');
                                    const mostrarError = function (respuesta) {
                                        const errores = respuesta.error || respuesta.errors || {};
                                        const detalle = Object.values(errores).flat().join('\n');
                                        error.text(detalle || respuesta.msj || respuesta.message || 'No se pudo guardar. Intente nuevamente.').show();
                                        error[0].scrollIntoView({block: 'nearest'});
                                    };
                                    botones.prop('disabled', true);
                                    $.ajax({
                                        url: @json(route('paciente.mascotas.update', $mascota->id)),
                                        type: 'POST',
                                        data: datos,
                                        headers: {Accept: 'application/json'}
                                    }).done(function (respuesta) {
                                        if (respuesta.estado != 1) {
                                            mostrarError(respuesta);
                                            return;
                                        }
                                        // Reload only after a successful save so all clinical summaries stay in sync.
                                        window.location.reload();
                                    }).fail(function (xhr) {
                                        mostrarError(xhr.responseJSON || {});
                                    }).always(function () {
                                        botones.prop('disabled', false);
                                    });
                                }
                            </script>
                            @endonce
                        @else
                            <p class="text-muted mb-0">No se encontró la mascota asociada a esta atención.</p>
                        @endif
                    </div>
                </div>
            </div>


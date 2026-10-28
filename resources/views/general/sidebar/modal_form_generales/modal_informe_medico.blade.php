<div id="modal_inf_medico" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modal_inf_medico"

    aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered" data-backdrop="static" role="document">

        <div class="modal-content">

            <div class="modal-header modal-header-purple">

                <h5 class="modal-title text-center">Informe Médico</h5>

                <button type="button" class="close text-white"  data-dismiss="modal" aria-label="Close" onclick="$('#modal_inf_medico').modal('hide')"><span aria-hidden="true">×</span></button>

            </div>

            <div class="modal-body">

                <div id="form_informe_medico">

                    <div class="form-row">

                        <div class="form-group col-sm-12 col-md-12">

                            <label class="floating-label-activo-sm">Nombre</label>

                            <input type="text" class="form-control form-control-sm"

                                name="nombre_paciente_informe_medico" id="nombre_paciente_informe_medico"

                                value="{{ $paciente->nombres . ' ' . $paciente->apellido_uno . ' ' . $paciente->apellido_dos }}">

                        </div>

                        <div class="form-group col-sm-12 col-md-12">

                            <label class="floating-label-activo-sm">Rut</label>

                            <input type="person" class="form-control form-control-sm"

                                name="rut_paciente_informe_medico" id="rut_paciente_informe_medico"

                                value="{{ $paciente->rut }}">

                        </div>

                        <div class="form-group col-sm-12 col-md-12">

                            <label class="floating-label-activo-sm">Edad</label>

                            <input type="number" class="form-control form-control-sm"

                                name="edad_paciente_informe_medico" id="edad_paciente_informe_medico"

                                value="{{ \Carbon\Carbon::parse($paciente->fecha_nac)->diff(\Carbon\Carbon::now())->format('%y') }}">

                        </div>

                        <div class="form-group col-sm-12 col-md-12">

                            <label class="floating-label-activo-sm">Dirección</label>

                            <input type="address" class="form-control form-control-sm"

                                name="direccion_paciente_informe_medico" id="direccion_paciente_informe_medico"

								@if($paciente->id_direccion)

									value="{{ $paciente->Direccion()->first()->direccion . ' ' . $paciente->Direccion()->first()->numero_dir }}"

								@else

									value=""

								@endif

							>



                        </div>

                        <div class="form-group col-sm-12 col-md-12">

                            <label class="floating-label-activo-sm">Regi&oacute;n</label>

                            <select id="region_paciente_informe_medico" name="region_paciente_informe_medico"

                                class="form-control form-control-sm">

                                <option value="0">Seleccione</option>

                                @foreach ($regiones as $r)

									@if($paciente->id_direccion)

										@if($paciente->Direccion())

											@if ($r->id == $paciente->Direccion()->first()->Ciudad()->first()->id_region)

												<option id="{{ $r->id }}" selected> {{ $r->nombre }} </option>

											@endif

										@endif

									@endif

                                    <option id="{{ $r->id }}"> {{ $r->nombre }} </option>

                                @endforeach

                            </select>

                        </div>

                        <div class="form-group col-sm-12 col-md-12">

                            <label class="floating-label-activo-sm">Ciudad</label>

                            <select id="region_paciente_informe_medico" name="region_paciente_informe_medico"

                                class="form-control form-control-sm">

                                <option value="0">Seleccione</option>

                                @foreach ($ciudades as $c)

									@if($paciente->id_direccion)

										@if ($c->id == $paciente->Direccion()->first()->Ciudad()->first()->id)

											<option id="{{ $c->id }}" selected> {{ $c->nombre }} </option>

										@endif

                                    @endif

                                    <option id="{{ $c->id }}"> {{ $c->nombre }} </option>

                                @endforeach

                            </select>

                        </div>

                        {{--  <div class="form-group col-sm-12 col-md-12">

                            <label class="floating-label-activo-sm">Fecha</label>

                            <input type="date" class="form-control form-control-sm" name="fecha_informe_medico"

                                id="fecha_informe_medico">

                        </div>  --}}

                        <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 mb-3">

                            <h6 class="text-c-blue mt-3">El Profesional que suscribe informa que</h6>

                        </div>

                        <div class="form-group col-sm-12 col-md-12">

                            <label class="floating-label-activo-sm">Descripción</label>

                            <textarea type="text" class="form-control form-control-sm" rows="3" name="comentarios_informe_medico" id="comentarios_informe_medico"></textarea>

                        </div>

                    </div>

                    {{--  <div class="form-row">

                        <div class="col-sm-12 col-md-12 text-center">

                            <button type="button" class="btn btn-sm btn-primary">Ver documento en PDF</button>

                        </div>

                    </div>  --}}

                </div>
                <div id="informe_veterinario_estado" role="status" aria-live="polite"></div>
                <a id="informe_veterinario_pdf" class="btn btn-danger-light btn-sm" target="_blank" rel="noopener" hidden>Ver PDF</a>
                <button type="button" id="informe_veterinario_reenviar" class="btn btn-info btn-sm" onclick="reenviarInformeVeterinario()" hidden>Reintentar envío al tutor</button>

            </div>

            <div class="modal-footer">

                <button type="button" class="btn btn-outline-dark" data-dismiss="modal" onclick="$('#modal_inf_medico').modal('hide')"><i class="feather icon-x"></i> Cancelar</button>

                <button type="button" id="informe_veterinario_generar" onclick="generarInformeVeterinario();" class="btn btn-info"><i class="feather icon-check"></i> Generar Informe</button>

            </div>

        </div>

    </div>

</div>
<script>
    var informeVeterinarioEnProceso = false;
    var informeVeterinarioGuardado = null;
    var informeVeterinarioTextoGuardado = null;
    function textoInformeVeterinario() {
        var campo = $('#comentarios_informe_medico');
        return $.fn.summernote && campo.next('.note-editor').length ? campo.summernote('code') : campo.val();
    }
    function generarInformeVeterinario() {
        if (informeVeterinarioEnProceso) return;
        var texto = textoInformeVeterinario() || '';
        if (!texto.replace(/<[^>]*>/g, '').replace(/&nbsp;/g, ' ').trim()) {
            $('#informe_veterinario_estado').text('Escriba la descripción del informe.');
            return;
        }
        if (informeVeterinarioGuardado && texto === informeVeterinarioTextoGuardado) {
            $('#informe_veterinario_estado').text('Este informe ya está guardado. Puede ver el PDF o reintentar el envío.');
            return;
        }
        informeVeterinarioEnProceso = true;
        $('#informe_veterinario_generar').prop('disabled', true);
        $('#informe_veterinario_estado').text('Guardando informe y enviando PDF al tutor…');
        $.ajax({
            url: '{{ route("ficha_medica.registrar_informe_medico") }}', type: 'POST', dataType: 'json',
            data: {_token: '{{ csrf_token() }}', comentarios_informe_medico: texto, hora_medica: $('#hora_medica').val(), id_lugar_atencion: $('#id_lugar_atencion').val(), tipo_informe: 1},
            success: function(respuesta) {
                if (respuesta.estado != 1) { $('#informe_veterinario_estado').text(respuesta.msj || 'No fue posible guardar el informe.'); return; }
                informeVeterinarioGuardado = respuesta.id_last;
                informeVeterinarioTextoGuardado = texto;
                $('#id_informe_medico_email').val(respuesta.id_last);
                $('#informe_veterinario_pdf').attr('href', respuesta.pdf_url).prop('hidden', !respuesta.pdf_url);
                $('#informe_veterinario_reenviar').prop('hidden', !respuesta.envio || respuesta.envio.estado == 1);
                $('#informe_veterinario_estado').text(respuesta.msj || 'Informe guardado.');
            },
            error: function(xhr) {
                var respuesta = xhr.responseJSON || {};
                $('#informe_veterinario_estado').text(respuesta.errors ? Object.values(respuesta.errors).flat().join(' ') : (respuesta.message || 'No fue posible guardar el informe. Inténtelo nuevamente.'));
            },
            complete: function() { informeVeterinarioEnProceso = false; $('#informe_veterinario_generar').prop('disabled', false); }
        });
    }
    function reenviarInformeVeterinario() {
        if (informeVeterinarioEnProceso || !informeVeterinarioGuardado) return;
        informeVeterinarioEnProceso = true;
        $('#informe_veterinario_reenviar').prop('disabled', true);
        $.ajax({
            url: '{{ route("ficha_medica.enviar_informe_medico_email") }}', type: 'POST', dataType: 'json',
            data: {_token: '{{ csrf_token() }}', id_informe_medico: informeVeterinarioGuardado},
            success: function(respuesta) {
                $('#informe_veterinario_estado').text(respuesta.msj || 'Envío completado.');
                $('#informe_veterinario_reenviar').prop('hidden', respuesta.estado == 1);
            },
            error: function(xhr) { $('#informe_veterinario_estado').text(xhr.responseJSON?.msj || xhr.responseJSON?.message || 'No se pudo enviar. El informe sigue guardado.'); },
            complete: function() { informeVeterinarioEnProceso = false; $('#informe_veterinario_reenviar').prop('disabled', false); }
        });
    }
</script>

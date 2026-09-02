<!--Diagnóstico-->
@php
    $esFichaVeterinaria = isset($mascota) && !empty($mascota);
    $datosVeterinarios = isset($fichaVeterinariaGeneralData) && is_array($fichaVeterinariaGeneralData)
        ? $fichaVeterinariaGeneralData
        : [];
    $diagnosticoActual = old(
        'descripcion_hipotesis',
        data_get($fichaAtencion ?? null, 'hipotesis_diagnostico', data_get($datosVeterinarios, 'descripcion_hipotesis', ''))
    );
    $cieActual = old(
        'descripcion_cie',
        data_get($fichaAtencion ?? null, 'diagnostico_ce10', data_get($datosVeterinarios, 'descripcion_cie', ''))
    );
    $observacionesActuales = old(
        'obs_clinicas',
        data_get($datosVeterinarios, 'obs_clinicas', data_get($fichaAtencion ?? null, 'obs_clinicas', ''))
    );
    $indicacionesActuales = old(
        'indicaciones',
        data_get($fichaAtencion ?? null, 'indicaciones', data_get($datosVeterinarios, 'indicaciones', ''))
    );
@endphp
<div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
    <div class="card-a">
        <div class="card-header-a " id="diagnostico">
            <button class="accor-closed btn pt-1 pb-0 pl-1 btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#diagnostico_c" aria-expanded="false" aria-controls="diagnostico_c">
                Diagnóstico e Indicaciones
            </button>
        </div>
        <div id="diagnostico_c" class="collapse show" aria-labelledby="diagnostico" data-parent="#diagnostico">
            <div class="card-body-aten-a  shadow-none">
                <div class="form-row">
                    <div class="form-group col-sm-12 col-md-6 col-lg-6 col-xl-6">
                        <label class="floating-label-activo-sm" for="descripcion_hipotesis">Diagnóstico</label>
                        <input type="text" class="form-control form-control-sm"  data-input_igual="lic_descripcion_hipotesis,hipotesis_certificado,eno_diagnositico_confirmado,diagnostico_cons,diag_endos_eda" name="descripcion_hipotesis" id="descripcion_hipotesis" onchange="cargarIgual('descripcion_hipotesis')" value="{{ $diagnosticoActual }}">
                    </div>
                    @unless($esFichaVeterinaria)
                    <div class="form-group col-sm-12 col-md-6 col-lg-6 col-xl-6">
                        <label class="floating-label-activo-sm" for="descripcion_cie">Diagnóstico CIE-10</label>
                        <input type="text" class="form-control form-control-sm" data-input_igual="lic_descripcion_cie,descripcion_cie_esp,eno_diagnostico_cie" name="descripcion_cie" id="descripcion_cie" value="{{ $cieActual }}" onchange="cargarIgual('descripcion_cie')">
                        <input type="hidden" class="form-control form-control-sm" data-input_igual="id_lic_descripcion_cie,id_descripcion_cie_esp,eno_id_diagnostico_cie" name="id_descripcion_cie" id="id_descripcion_cie" value="" onchange="cargarIgual('id_descripcion_cie')">
                    </div>
                    @endunless
                    <div class="form-group col-sm-12 col-md-6 col-lg-6 col-xl-6">
                        <label class="floating-label-activo-sm" for="descripcion_hipotesis">Obs. Cínicas para recordar en consulta</label>
                        <textarea class="form-control caja-texto form-control-sm" rows="1" onfocus="this.rows=5" onblur="this.rows=1;" name="obs_clinicas" id="obs_clinicas">{{ $observacionesActuales }}</textarea>
                    </div>
                    <div class="form-group col-sm-12 {{ $esFichaVeterinaria ? 'col-md-12 col-lg-12 col-xl-12' : 'col-md-6 col-lg-6 col-xl-6' }}">
                        <label class="floating-label-activo-sm" for="indicaciones">Indicaciones</label>
                        <textarea class="form-control caja-texto form-control-sm" rows="1" onfocus="this.rows=5" onblur="this.rows=1;" name="indicaciones" id="indicaciones">{{ $indicacionesActuales }}</textarea>
                    </div>
                    <div class=" col-sm-12 col-md-4 col-lg-4 col-xl-4 mb-3 pt-2">
                        <div class="custom-control custom-switch" >
                            <input type="checkbox" class="custom-control-input" id="motivo1"  data-toggle="collapse" data-target="#motivo1_c" aria-expanded="false" aria-controls="motivo1_c">
                            <label class="custom-control-label font-weight-bold text-c-blue" for="motivo1">Enviar indicación por email</label>
                        </div>
                    </div>
                    <div class=" col-sm-12 col-md-4 col-lg-4 col-xl-4 mb-3 pt-2">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="motivo2"  data-toggle="collapse" data-target="#motivo2_c" aria-expanded="false" aria-controls="motivo2_c">
                            <label class="custom-control-label font-weight-bold text-c-blue" for="motivo2">Imprimir indicaciones</label>
                        </div>
                    </div> 
                    <div class=" col-sm-12 col-md-4 col-lg-4 col-xl-4 mb-3">
                        <button type="button" id="btn_agendar_proximo_control" class="btn btn-outline-purple btn-xs mt-1 btn-block" onclick="abrirAgendaProximoControl()">
                            <i class="feather icon-calendar"></i> Agendar Próximo Control
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

@once
<div class="modal fade" id="modal_agendar_proximo_control" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="titulo_agendar_proximo_control" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document" style="max-width:900px;width:calc(100% - 30px);">
        <div class="modal-content">
            <div class="modal-header bg-info py-2">
                <h5 class="modal-title text-white" id="titulo_agendar_proximo_control">
                    <i class="feather icon-calendar mr-2"></i>Agendar próximo control
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="pc_id_profesional">
                <input type="hidden" id="pc_id_paciente">
                <input type="hidden" id="pc_id_mascota">

                <div class="row">
                    <div class="form-group col-md-6">
                        <label class="floating-label-activo-sm">Lugar de atención</label>
                        <select class="form-control form-control-sm" id="pc_lugar_atencion" onchange="cargarDiasProximoControl()">
                            <option value="">Cargando...</option>
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label class="floating-label-activo-sm">Fecha del próximo control</label>
                        <input type="date" class="form-control form-control-sm" id="pc_fecha" onchange="cargarHorasProximoControl()" disabled>
                    </div>
                    <div class="col-12 mb-3">
                        <small class="text-muted">Días de atención: <strong id="pc_dias_atencion">Seleccione un lugar.</strong></small>
                    </div>
                    <div class="col-12">
                        <h6 class="text-info mb-3" id="pc_titulo_horas">Seleccione lugar y fecha para consultar disponibilidad.</h6>
                        <div class="row mx-0" id="pc_horas_disponibles"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function abrirAgendaProximoControl() {
        const lugarAtencion = document.getElementById('id_lugar_atencion')?.value || '';
        const idPaciente = document.getElementById('id_paciente_fc')?.value || '';
        const idMascota = document.getElementById('id_mascota')?.value
            || document.getElementById('reserva_hora_mascota_id')?.value
            || @json(data_get($mascota ?? null, 'id', ''));
        const idProfesional = document.getElementById('id_profesional_fc')?.value || '';

        if (!lugarAtencion || !idPaciente || !idProfesional) {
            const mensaje = 'No se pudo identificar el profesional, responsable o lugar de atención de esta ficha.';
            if (typeof swal === 'function') {
                swal({ title: 'Próximo control', text: mensaje, icon: 'warning', button: 'Aceptar' });
            } else {
                alert(mensaje);
            }
            return;
        }

        $('#pc_id_profesional').val(idProfesional);
        $('#pc_id_paciente').val(idPaciente);
        $('#pc_id_mascota').val(idMascota);
        $('#pc_fecha').val('').prop('disabled', true);
        $('#pc_horas_disponibles').empty();
        $('#pc_titulo_horas').text('Cargando lugares de atención...');
        $('#modal_agendar_proximo_control').modal('show');

        $.get(@json(route('profesional.lugaresAtencionProfesionalBuscador')), {
            id_profesional: idProfesional
        }).done(function (data) {
            const select = $('#pc_lugar_atencion').empty().append('<option value="">Seleccione</option>');
            if (data.estado == 1) {
                $.each(data.registros || [], function (_, lugar) {
                    select.append($('<option>', { value: lugar.id, text: lugar.nombre }));
                });
                select.val(lugarAtencion);
                cargarDiasProximoControl();
            } else {
                $('#pc_titulo_horas').text('El profesional no tiene lugares de atención configurados.');
            }
        }).fail(function () {
            $('#pc_titulo_horas').text('No fue posible cargar los lugares de atención.');
        });
    }

    function cargarDiasProximoControl() {
        const idProfesional = $('#pc_id_profesional').val();
        const lugarAtencion = $('#pc_lugar_atencion').val();
        $('#pc_fecha').val('').prop('disabled', true);
        $('#pc_horas_disponibles').empty();

        if (!lugarAtencion) {
            $('#pc_dias_atencion').text('Seleccione un lugar.');
            return;
        }

        $.get(@json(route('profesional.DiasLaboralesProfesionaLugarAtencionBuscador')), {
            id_profesional: idProfesional,
            lugar_atencion: lugarAtencion
        }).done(function (data) {
            const horario = data?.registros?.horario_agenda_laboral || '';
            if (data.estado != 1 || !horario) {
                $('#pc_dias_atencion').text('No informados');
                $('#pc_titulo_horas').text('No existe agenda configurada para este lugar.');
                return;
            }

            const nombres = ['', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
            const dias = String(horario).split(',').filter(Boolean);
            $('#pc_dias_atencion').text(dias.map(dia => nombres[Number(dia)] || dia).join(' - '));
            const hoy = new Date();
            const maximo = new Date();
            maximo.setDate(maximo.getDate() + 60);
            $('#pc_fecha')
                .attr('min', hoy.toISOString().slice(0, 10))
                .attr('max', maximo.toISOString().slice(0, 10))
                .data('dias-activos', dias)
                .prop('disabled', false);
            $('#pc_titulo_horas').text('Seleccione una fecha.');
        }).fail(function () {
            $('#pc_titulo_horas').text('No fue posible consultar los días de atención.');
        });
    }

    function cargarHorasProximoControl() {
        const fecha = $('#pc_fecha').val();
        const dias = $('#pc_fecha').data('dias-activos') || [];
        if (!fecha) return;

        const diaSemana = new Date(fecha + 'T12:00:00').getDay();
        const diaSistema = diaSemana === 0 ? '7' : String(diaSemana);
        if (!dias.includes(diaSistema)) {
            $('#pc_horas_disponibles').empty();
            $('#pc_titulo_horas').text('El profesional no atiende el día seleccionado.');
            return;
        }

        $('#pc_titulo_horas').text('Buscando horas disponibles...');
        $('#pc_horas_disponibles').empty();
        $.get(@json(route('profesional.HorasDisponiblesProfesionalLugarAtencionBuscador')), {
            id_profesional: $('#pc_id_profesional').val(),
            id_lugar_atencion: $('#pc_lugar_atencion').val(),
            dia: fecha
        }).done(function (data) {
            if (data.estado != 1 || !data.registros || !data.registros.length) {
                $('#pc_titulo_horas').text('Sin horas disponibles para esta fecha.');
                return;
            }
            $('#pc_titulo_horas').text('Seleccione una hora disponible:');
            $.each(data.registros, function (_, registro) {
                const hora = String(registro.hora).slice(0, 5);
                $('<button>', {
                    type: 'button',
                    class: 'btn btn-outline-info btn-sm m-1',
                    text: hora
                }).on('click', function () {
                    confirmarProximoControl(registro.hora);
                }).appendTo('#pc_horas_disponibles');
            });
        }).fail(function () {
            $('#pc_titulo_horas').text('No fue posible consultar las horas disponibles.');
        });
    }

    function confirmarProximoControl(hora) {
        const fecha = $('#pc_fecha').val();
        const horaCorta = String(hora).slice(0, 5);
        const reservar = function () {
            $.post(@json(route('paciente.solicitar.hora')), {
                _token: @json(csrf_token()),
                fecha_consulta: fecha + ' ' + hora,
                reserva_hora_id: $('#pc_id_paciente').val(),
                id_profesional: $('#pc_id_profesional').val(),
                id_lugar_atencion: $('#pc_lugar_atencion').val(),
                id_asistente: 2,
                origen: 'ficha_veterinaria',
                tipo_hora_medica: 'C',
                id_mascota: $('#pc_id_mascota').val()
            }).done(function (respuesta) {
                if (typeof respuesta === 'string') {
                    try { respuesta = JSON.parse(respuesta); } catch (_) {}
                }
                if (respuesta.estado === 'error' || respuesta.estado === 0) {
                    swal({ title: 'No se pudo reservar', text: respuesta.msj || 'La hora ya no está disponible.', icon: 'error', button: 'Aceptar' });
                    cargarHorasProximoControl();
                    return;
                }
                $('#modal_agendar_proximo_control').modal('hide');
                swal({ title: 'Hora reservada', text: 'El próximo control quedó agendado para ' + fecha + ' a las ' + horaCorta + '.', icon: 'success', button: 'Aceptar' });
            }).fail(function (xhr) {
                swal({ title: 'No se pudo reservar', text: xhr.responseJSON?.msj || 'La hora ya no está disponible.', icon: 'error', button: 'Aceptar' });
                cargarHorasProximoControl();
            });
        };

        swal({
            title: 'Confirmar próximo control',
            text: '¿Desea reservar el ' + fecha + ' a las ' + horaCorta + '?',
            icon: 'warning',
            buttons: ['Cancelar', 'Reservar']
        }).then(function (confirmado) {
            if (confirmado) reservar();
        });
    }
</script>
@endonce

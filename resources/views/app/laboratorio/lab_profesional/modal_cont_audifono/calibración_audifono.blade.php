<!--datos Contacto-->
<div id="cal_audifono" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="cal_audifono" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h5 class="modal-title d-inline mt-1">Calibración de audífono</h5>
                    <p class="font-weight-bold mt-1 mb-0 text-white float-md-right">
                        @php
                            $meses = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
                            $fecha = \Carbon\Carbon::parse(now());
                            $mes = $meses[($fecha->format('n')) - 1];
                            $fecha = $fecha->format('d') . ' de ' . $mes . ' de ' . $fecha->format('Y');
                        @endphp
                        {{ $fecha }}
                    </p>
                <button type="button" class="close text-white" data-dismiss="modal"  onclick="$('#cal_audifono').modal('hide')" aria-label="Close"><span aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="row info-basica" id="info-basica-1">
                    <div class="col-md-12">
                     <div class="form- row">
                            <div class="col-sm-12 col-md-12 col-lg-6 col-xl-6">
                                <div class="form-group">
                                    <label class="floating-label-activo-sm t-red" for="av_subj_sc_od">Motivo del control</label>
                                    <select name="mot_cont_audif" id="mot_cont_audif" class="form-control form-control-sm" onchange="evaluar_para_carga_detalle('mot_cont_audif','div_mot_cont_audif','obs_mot_cont_audif',3);">
                                        <option value="0">Seleccione</option>
                                        <option value="1">Control de Venta</option>
                                        <option value="2">Recalibración</option>
                                        <option value="3">Otros (anotar)</option>
                                    </select>
                                </div>
                                <div class="form-group" id="div_mot_cont_audif" style="display:none;">
                                    <label class="floating-label-activo-sm t-red" for="obs_av_subj_sc_od">Otro Motivo <i>(describir)</i></label>
                                    <textarea class="form-control form-control-sm" rows="1"  onfocus="this.rows=3" onblur="this.rows=1;" name="obs_mot_cont_audif" id="obs_mot_cont_audif"></textarea>
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-12 col-lg-6 col-xl-6">
                                <div class="form-group">
                                    <label class="floating-label-activo-sm t-red" for="av_subj_sc_od">Estado del Audífono</label>
                                    <select name="est_audifono" id="est_audifono" class="form-control form-control-sm" onchange="evaluar_para_carga_detalle('est_audifono','div_est_audifono','obs_est_audifono',3);">
                                        <option value="0">Seleccione</option>
                                        <option value="1">Buenas condiciones</option>
                                        <option value="2">Deteriorado</option>
                                        <option value="3">Otros (anotar)</option>
                                    </select>
                                </div>
                                <div class="form-group" id="div_est_audifono" style="display:none;">
                                    <label class="floating-label-activo-sm t-red" for="obs_est_audifono">Estado del audífono<i>(describir)</i></label>
                                    <textarea class="form-control form-control-sm"  rows="1"  onfocus="this.rows=3" onblur="this.rows=1;" name="obs_est_audifono" id="obs_est_audifono"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">



                        <div class="col-sm-12 col-md-12 col-lg-6 col-xl-6">
                            <div class="form-group">
                                <label class="floating-label-activo">Marca</label>
                                <input type="text" class="form-control form-control-sm" placeholder="MARCA AUDÍFONO" id="n_serie_aud_izq" name="n_serie_aud_izq" value="">
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-12 col-lg-6 col-xl-6">
                            <div class="form-group">
                                <label class="floating-label-activo">Modelo</label>
                                <input type="text" class="form-control form-control-sm" placeholder="MODELO" id="n_serie_aud_izq" name="n_serie_aud_izq" value="">
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-12 col-lg-6 col-xl-6">
                            <div class="form-group">
                                <label class="floating-label-activo">N° de serie</label>
                                <input type="text" class="form-control form-control-sm" placeholder="N° SERIE" id="n_serie_aud_izq" name="n_serie_aud_izq" value="">
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-12 col-lg-6 col-xl-6">
                            <div class="form-group">
                                <label class="floating-label-activo">Fecha de entrega</label>
                                <input type="date" class="form-control form-control-sm" placeholder="Fecha de entrega" id="fecha_ent_aud_izq" name="fecha_ent_aud_izq" value="">
                            </div>
                        </div>

                        <div class="col-sm-12 col-md-12 col-lg-6 col-xl-6">
                            <div class="form-group">
                                <label class="floating-label-activo-sm t-blue" for="av_pris_oi">Acciones de calibrado</label>
                                <textarea class="form-control form-control-sm" data-titulo="Prisma OI" data-seccion="Agudeza Visual"  rows="1"  onfocus="this.rows=2" onblur="this.rows=1;" name="av_pris_oi" id="av_pris_oi"></textarea>
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-12 col-lg-6 col-xl-6">
                            <div class="form-group">
                                <label class="floating-label-activo-sm t-blue" for="av_pris_oi">Opinión del paciente</label>
                                <textarea class="form-control form-control-sm" data-titulo="Prisma OI" data-seccion="Agudeza Visual"  rows="1"  onfocus="this.rows=2" onblur="this.rows=1;" name="av_pris_oi" id="av_pris_oi"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
				<button type="button" class="btn btn-sm btn-danger-light-c" data-dismiss="modal" onclick="$('#cal_audifono').modal('hide')" ><i class="feather icon-x"></i> Cerrar</button>
				<button type="button" class="btn btn-sm btn-primary-light-c" onclick="agendar_cont_calibracion();"><i class="feather icon-save"></i> Agendar control</button>
                <button type="button" class="btn btn-sm btn-info-light-c" onclick="registrar_calibracion();"><i class="feather icon-save"></i> Guardar</button>
			</div>
        </div>
    </div>

</div>
<script>
    function calib_audif (){
        $('#cal_audifono').modal('show');
    }
     function evaluar_para_carga_detalle(select, div, input, valor)
        {
            var valor_select = $('#'+select+'').val();
            if(valor_select == valor) $('#'+div+'').show();
            else {
                $('#'+div+'').hide();
                $('#'+input+'').val('');
            }
        }
</script>

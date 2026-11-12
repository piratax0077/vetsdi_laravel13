    <!--Sidebar 3 cda)-->
    <div class="position-fixed w-100 h-100"></div>
    <div id="formularios_medicina_gen" class="bs-canvas bs-canvas-anim bs-canvas-right position-fixed bg-light h-100 shadow-lg" data-width="370px" data-offset="true">
       <header class="bs-canvas-header p-3 bg-info overflow-auto d-flex justify-content-between">
             <button type="button" class="bs-canvas-close close" onclick="$('#formularios_medicina_gen').modal('hide')" aria-label="Close" data-dismiss="modal"> <span aria-hidden="true" class="text-white">&times;</span></button>
            {{--  <button type="button" class="bs-canvas-close float-left close" aria-label="Close"><span aria-hidden="true" class="text-white">&times;</span></button>  --}}
            <h5 class="d-inline-block text-light mb-0">Formularios Medicina General</h5>
        </header>
        <div class="bs-canvas-content">
                <div class="accordion" id="accordion_side_bar">


                  <div class="accordion">
                        @include('general.sidebar.seccion_consentimientos')
                        <!-- SECCION CONSENTIMIENTOS -->
                  </div>
                @include('atencion_veterinaria.include.sidebar_mis_documentos'){{-- plantillas del profesional --}}
                <div class="card-sidebar">
                    <div class="card-header-sidebar" id="heading_recom">
                        <h2 class="mb-0">
                        <button class="btn btn-light btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapse_recom" aria-expanded="false" aria-controls="collapse_recom"><i class="feather icon-chevron-down float-right pt-1 flecha-accordion"></i>
                        <span class="sb-acordeon__icono"><i class="feather icon-clipboard"></i></span>
                        <span class="sb-acordeon__titulo">INDICACIONES A PACIENTES</span>
                        </button>
                        </h2>
                    </div>
                    <div id="collapse_recom" class="collapse" aria-labelledby="heading_recom" data-parent="#accordion_side_bar">
                        <div class="card-body-sidebar">
                            <button type="button" class="btn btn-sm btn-info btn-block text-left" onclick="icirugia()";>+ Indicaciones post cirugía En General</button>
                            <button type="button" class="btn btn-sm btn-info btn-block text-left" onclick="ind_rge()";>+ Recomendaciones Paciente con RGE</button>
                            @include('atencion_veterinaria.include.boton_indicacion_personalizada')
                        </div>
                    </div>
                    @include("general.modal.m_cuidados_cirugia")
                    @if(!empty($mascota) && !empty($hora_medica->id) && !empty($profesional))
                        @include('atencion_veterinaria.include.consentimiento_biopsia', ['indicacionPersonalizada' => true])
                    @endif
                </div>
                <div class="card-sidebar">
                    <div class="card-header-sidebar" id="heading_hosp">
                        <h2 class="mb-0">
                        <button class="btn btn-light btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapse_hosp" aria-expanded="false" aria-controls="collapse_hosp"><i class="feather icon-chevron-down float-right pt-1 flecha-accordion"></i>
                        <span class="sb-acordeon__icono"><i class="feather icon-plus-square"></i></span>
                        <span class="sb-acordeon__titulo">FORMULARIOS DE HOSPITALIZACIÓN</span>
                        </button>
                        </h2>
                    </div>
                    <div id="collapse_hosp" class="collapse" aria-labelledby="headinghospm" data-parent="#accordion_side_bar">
                        <div class="card-body-sidebar">
                            <button type="button" class="btn btn-sm btn-info btn-block text-left" onclick="sol_pabellon()";>+ Solicitud Pabellón</button>
                            <button type="button" class="btn btn-sm btn-info btn-block text-left" onclick="ingresohosp()";>+ Hospitalización </button>
                            <button type="button" class="btn btn-sm btn-info btn-block text-left" onclick="r_ingreso()";>+ Requisitos de ingreso</button>
                        </div>
                    </div>
                    @include("general.hospitalizacion.modals.in_solic_pabellon")
                    @include("general.hospitalizacion.modals.ingreso_hosp")
                    @include("general.modal.m_req_ingreso")
                </div>
				<div class="card-sidebar">
                    <div class="card-header-sidebar" id="heading_sugerencias">
                        <h2 class="mb-0">
                        <button class="btn btn-light btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapse_sugerencias" aria-expanded="false" aria-controls="collapse_sugerencias"><i class="feather icon-chevron-down float-right pt-1 flecha-accordion"></i>
                            <span class="sb-acordeon__icono"><i class="feather icon-help-circle"></i></span>
                            <span class="sb-acordeon__titulo">AYÚDENOS A MEJORAR</span>
                        </button>
                        </h2>
                    </div>
                    <div id="collapse_sugerencias" class="collapse" aria-labelledby="heading_sugerencias" data-parent="#accordion_side_bar">
                        <div class="card-body-sidebar">
                            <button type="button" class="btn btn-sm btn-info btn-block text-left" onclick="medicamentos_faltantes();">+ Medicamentos faltantes</button>
                            <button type="button" class="btn btn-sm btn-info btn-block text-left" onclick="examenes_faltantes();">+ Exámenes faltantes</button>
                            <button type="button" class="btn btn-sm btn-info btn-block text-left" onclick="abrirSugerirRecurso();">+ Sugerir recurso</button>
                        </div>
                    </div>
                    @include("general.modal.m_medicamentos_faltantes")
                    @include("general.modal.m_examenes_faltantes")
                    @include("atencion_veterinaria.include.modal_sugerir_recurso")
                </div>
            </div>
        </div>
    </div>
    <!--SIDEBAR 3 (cda)-->

    <!--MODALS SIDE BAR GENERALES-->









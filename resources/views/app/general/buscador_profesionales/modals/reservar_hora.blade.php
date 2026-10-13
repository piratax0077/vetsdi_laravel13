<!--Paso 3: lugar, fecha y hora (antes era el modal "Reservar hora")-->
<div class="reserva-vet-panel" data-paso="3">
    <input type="hidden" name="modal_reserva_hora_id_profesional" id="modal_reserva_hora_id_profesional" value="">
    <input type="hidden" name="modal_reserva_hora_tipo_agenda" id="modal_reserva_hora_tipo_agenda" value="">

    <div class="reserva-vet-panel-encabezado">
        <h6>¿Dónde y cuándo quiere la atención?</h6>
        <p class="small text-muted mb-0">Elija el lugar y un día marcado en el calendario. Al hacer clic en una hora pasará a confirmar la reserva.</p>
    </div>

    @if(!empty($es_veterinaria_busqueda) && empty($mascota))
        @php($mascotasReserva = $mascotas ?? collect())
        <div class="form-row mt-3" id="seleccion_mascota_reserva">
            <div class="col-12">
                <div class="form-group">
                    <label class="floating-label-activo-sm" for="modal_reserva_id_mascota">¿Para qué mascota desea reservar?</label>
                    <select class="form-control form-control-sm" id="modal_reserva_id_mascota" name="modal_reserva_id_mascota"
                        onchange="window.idMascotaReserva=this.value;$('#reserva_nombre_mascota').text($(this).find('option:selected').data('nombre') || '');">
                        @if($mascotasReserva->count() !== 1)
                            <option value="">Seleccione una mascota</option>
                        @endif
                        @foreach($mascotasReserva as $mascotaReserva)
                            <option value="{{ $mascotaReserva->id }}"
                                data-nombre="{{ $mascotaReserva->nombre }}"
                                {{ $mascotasReserva->count() === 1 ? 'selected' : '' }}>
                                {{ $mascotaReserva->nombre }}{{ !empty($mascotaReserva->especie) ? ' — '.$mascotaReserva->especie : '' }}
                            </option>
                        @endforeach
                    </select>
                    @if($mascotasReserva->isEmpty())
                        <small class="text-danger d-block mt-1">Debe registrar una mascota antes de reservar una cita.</small>
                    @elseif($mascotasReserva->count() > 1)
                        <small class="text-muted d-block mt-1">Seleccione la mascota antes de elegir una hora disponible.</small>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <div class="form-row mt-3">
        <div class="col-sm-12 col-md-6">
            <div class="form-group">
                <label class="floating-label-activo-sm" for="modal_reserva_hora_lugar_atencion">Lugar de atención</label>
                <select class="form-control form-control-sm" id="modal_reserva_hora_lugar_atencion" name="modal_reserva_hora_lugar_atencion" onchange="carga_calendario_profesional();">
                    <option value="">Seleccione</option>
                </select>
            </div>
        </div>
        <div class="col-sm-12 col-md-6">
            <div class="form-group">
                <p class="small text-muted mb-0 pt-1"><i class="feather icon-clock mr-1"></i>Atiende los días <strong class="text-dark" id="modal_reserva_dias_atencion"></strong></p>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6 mb-3 mb-lg-0">
            <!--flatpickr cambia el input a texto, por eso se oculta con d-none y se muestra solo el calendario.
                Mientras la fecha está deshabilitada (sin lugar o sin días) se ve el aviso en su lugar-->
            <div class="reserva-vet-calendario">
                <input class="d-none" type="date" name="modal_reserva_fecha" id="modal_reserva_fecha" onchange="cargar_horas_disponibles_calendario_profesion(this.value);" min="{{ date('Y-m-d') }}" max="{{ date('Y-m-d', strtotime('+60 days')) }}" tabindex="-1" aria-hidden="true" disabled>
                <div class="reserva-vet-horas-vacio">
                    <i class="feather icon-calendar"></i>
                    <span>Al elegir un lugar verá aquí los días con atención.</span>
                </div>
            </div>
            <div class="reserva-vet-leyenda" aria-hidden="true">
                <span><i class="reserva-vet-leyenda-disponible"></i>Disponible</span>
                <span><i class="reserva-vet-leyenda-elegido"></i>Elegido</span>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="reserva-vet-horas-caja">
                <div class="reserva-vet-horas-cabecera">
                    <div class="reserva-vet-horas-titulo">Horas disponibles</div>
                </div>
                <div class="reserva-vet-horas-fecha" id="modal_reserva_fecha_seleccionada"></div>
                <!--La grilla se llena al elegir un día; vacía deja ver el aviso que viene después-->
                <div class="reserva-vet-horas" aria-live="polite">
                    <div class="reserva-vet-horas-grilla" id="modal_reserva_hora_lista_horas"></div>
                    <div class="reserva-vet-horas-vacio">
                        <i class="feather icon-clock"></i>
                        <span>Las horas aparecerán aquí cuando elija un día en el calendario.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="reserva-vet-acciones">
        <button type="button" class="btn btn-outline-secondary reserva-vet-volver" onclick="irPasoReservaHora(2);">
            <i class="feather icon-chevron-left"></i> Anterior
        </button>
    </div>
</div>

<!--Paso 4: resumen y confirmación (antes era el modal "Tomar hora")-->
<div class="reserva-vet-panel" data-paso="4">
    <div class="reserva-vet-panel-encabezado">
        <h6>Revise su reserva</h6>
        <p class="small text-muted mb-0">Si todo está correcto, confirme para agendar la hora.</p>
    </div>

    {{--  BUSCADOR DE RUT  --}}
    <div class="row div_rut_buscar">
        <div class="col-sm-12 col-md-12">
            <div class="form-group">
                <h6 class="text-c-blue ml-2 mb-3">Ingrese el rut del paciente</h6>
            </div>
        </div>
        <div class="col-sm-8 col-md-8 mb-3">
            <form id="validacion_rut_form">
                <div class="form-group" id="validacion_rut_div">
                    <input type="text" id="rut_paciente_reserva" name="rut_paciente_reserva" class="form-control" placeholder="Rut del paciente" aria-label="Rut del paciente" aria-describedby="button-addon2" required oninput="formatoRut(this)">
                </div>
            </form>
        </div>
        <div class="col-sm-4 col-md-4 mb-3">
            <button class="btn btn-info" onclick="buscar_paciente();" type="button" id="button-addon2">
                <i class="feather icon-search"></i> Buscar
            </button>
        </div>
    </div>
    <form id="form_reseva_de_horas">
        <input type="hidden" name="_token" id="_token" value="{{ csrf_token() }}">
        <input type="hidden" id="reserva_hora_id_profesional" name="reserva_hora_id_profesional" value="">
        <input type="hidden" id="reserva_hora_id_paciente" name="reserva_hora_id_paciente" value="">
        <input type="hidden" id="reserva_hora_id_lugar_atencion" name="reserva_hora_id_lugar_atencion" value="">
        <input type="hidden" id="reserva_hora_fecha_consulta" name="reserva_hora_fecha_consulta" value="">
        <input type="hidden" id="reserva_hora_hora_consulta" name="reserva_hora_hora_consulta" value="">
        <input type="hidden" id="reserva_hora_origen" name="reserva_hora_origen" value="escritorio_paciente">
        <input type="hidden" id="reserva_hora_id_asistente" name="reserva_hora_id_asistente" value="2">

        {{--  VISUALIZACION DE DATOS DEL PACIENTE  --}}
        <div id="reserva_datos_paciente">
            <div class="row">
                <div class="col-lg-6 mb-3 mb-lg-0">
                    <div class="reserva-vet-resumen">
                        <!--Lo más importante primero: cuándo es la hora-->
                        <div class="reserva-vet-resumen-cita">
                            <span class="reserva-vet-resumen-cita-icono"><i class="feather icon-calendar"></i></span>
                            <div class="reserva-vet-resumen-cita-datos">
                                <span class="reserva-vet-resumen-fecha" id="reserva_resumen_fecha">-</span>
                                <span class="reserva-vet-resumen-hora">
                                    <i class="feather icon-clock"></i>
                                    <span id="reserva_resumen_hora">-</span>
                                </span>
                            </div>
                        </div>
                        <ul class="reserva-vet-detalle">
                            @if(!empty($es_veterinaria_busqueda))
                                <li>
                                    <span class="reserva-vet-detalle-icono"><i class="fas fa-paw"></i></span>
                                    <div>
                                        <span class="reserva-vet-detalle-titulo">Mascota</span>
                                        <span class="reserva-vet-detalle-valor" id="reserva_nombre_mascota">{{ \Illuminate\Support\Str::ucfirst(trim($mascota->nombre ?? '')) }}</span>
                                    </div>
                                </li>
                            @endif
                            <li>
                                <span class="reserva-vet-detalle-icono"><i class="feather icon-user"></i></span>
                                <div>
                                    <span class="reserva-vet-detalle-titulo">{{ !empty($es_veterinaria_busqueda) ? 'Veterinario' : 'Profesional' }}</span>
                                    <span class="reserva-vet-detalle-valor" id="reserva_resumen_veterinario">-</span>
                                </div>
                            </li>
                            <li>
                                <span class="reserva-vet-detalle-icono"><i class="feather icon-map-pin"></i></span>
                                <div>
                                    <span class="reserva-vet-detalle-titulo">Lugar de atención</span>
                                    <span class="reserva-vet-detalle-valor" id="reserva_resumen_lugar">-</span>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>

                <!--Datos de quien reserva, con la misma lista de las citas de la agenda-->
                <div class="col-lg-6 reserva-hora-datos">
                    <h6 class="titulo-item mt-0"><i class="feather icon-user mr-2"></i>{{ !empty($es_veterinaria_busqueda) ? 'Datos del tutor' : 'Datos del paciente' }}</h6>
                    <dl class="ficha-previa-datos">
                        <div class="ficha-previa-dato">
                            <dt>Rut</dt>
                            <dd id="reserva_rut_paciente"></dd>
                        </div>
                        <div class="ficha-previa-dato">
                            <dt>Nombre</dt>
                            <dd id="reserva_hora_nombre"></dd>
                        </div>
                        <div class="ficha-previa-dato">
                            <dt>F. nacimiento</dt>
                            <dd id="reserva_fecha_nacimiento"></dd>
                        </div>
                        <div class="ficha-previa-dato">
                            <dt>Sexo</dt>
                            <dd id="reserva_sexo"></dd>
                        </div>
                        @if(empty($es_veterinaria_busqueda))
                            <div class="ficha-previa-dato">
                                <dt>Convenio</dt>
                                <dd id="reserva_convenio"></dd>
                            </div>
                        @endif
                        <div class="ficha-previa-dato">
                            <dt>Dirección</dt>
                            <dd id="reserva_direccion"></dd>
                        </div>
                        <div class="ficha-previa-dato">
                            <dt>Correo</dt>
                            <dd id="reserva_hora_email"></dd>
                        </div>
                        <div class="ficha-previa-dato">
                            <dt>Teléfono</dt>
                            <dd id="reserva_hora_telefono"></dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="form-row mt-4 pt-2">
                @if(!empty($es_veterinaria_busqueda))
                    <div class="col-sm-12 col-md-6">
                        <div class="form-group">
                            <label class="floating-label-activo-sm" for="reserva_voucher_externo_id">Voucher veterinario</label>
                            <select id="reserva_voucher_externo_id" class="form-control form-control-sm">
                                <option value="">Sin voucher</option>
                            </select>
                            <small id="reserva_voucher_ayuda" class="text-muted d-block mt-1">
                                Puede asignar un voucher vigente si la mascota dispone de uno.
                            </small>
                        </div>
                    </div>
                @endif
                <div class="col-sm-12 {{ !empty($es_veterinaria_busqueda) ? 'col-md-6' : 'col-md-12' }}">
                    <div class="form-group">
                        <label class="floating-label-activo-sm" for="reserva_hora_descripcion">Descripción reserva</label>
                        <input type="text" class="form-control form-control-sm" name="reserva_hora_descripcion" id="reserva_hora_descripcion">
                    </div>
                </div>
            </div>

            <div id="seccion_acompanante">
                <label class="label">Acompañado por </label>
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <div class="switch switch-success d-inline m-r-10">
                                <input type="checkbox" id="acompanante_representante" value="1" checked>
                                <label for="acompanante_representante" class="cr"></label>
                            </div>
                            <label><strong>Representante</strong></label>
                        </div>
                    </div>
                    <div class="col-sm-6"><div id="div_info_representante"></div></div>
                </div>
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <div class="switch switch-success d-inline m-r-10">
                                <input type="checkbox" id="acompanante_acompanante" value="1">
                                <label for="acompanante_acompanante" class="cr"></label>
                            </div>
                            <label><strong>Acompañante</strong></label>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <div id="div_info_acompanante" style="display: none">
                            <div class="form-group">
                                <label class="floating-label-activo-sm">Acompañante</label>
                                {{-- <input type="text" class="form-control form-control-sm" name="reserva_hora_id_acompanante" id="reserva_hora_id_acompanante"> --}}
                                <select class="form-control form-control-sm" multiple name="reserva_hora_id_acompanante" id="reserva_hora_id_acompanante">
                                    {{-- <option value="">Seleccione</option> --}}
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="seccion_autorizacion">
                <label class="label">Autorización Atención</label>
                <div class="form-group">
                    <div class="switch switch-success d-inline m-r-10">
                        <input type="checkbox" id="autorizacion_atencion" value="1" checked>
                        <label for="autorizacion_atencion" class="cr"></label>
                    </div>
                    <label><strong>Autorizar Atención</strong></label>
                </div>
                <p class="small text-muted mb-1">Al confirmar, usted da su consentimiento al profesional para realizar la atención.</p>
                <p class="small text-muted mb-0">Si no lo autoriza ahora, el profesional puede solicitarlo posteriormente.</p>
            </div>

            <div class="reserva-vet-acciones">
                <button type="button" class="btn btn-outline-secondary reserva-vet-volver" onclick="irPasoReservaHora(3);">
                    <i class="feather icon-chevron-left"></i> Anterior
                </button>
                <button type="button" onclick="agendar_hora();" class="btn btn-info"><i class="feather icon-check"></i> Confirmar reserva</button>
            </div>
        </div>

        {{--  FORMULARIO DE PACIENTE NUEVO  --}}
        <div id="reserva_agregar_paciente_hora">
            <div class="row">
                <div class="col-sm-12 col-md-12">
                    <div class="alert alert-danger" role="alert">
                        Paciente no registrado, complete los datos para registrar al paciente
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12 col-md-12">
                    <div class="form-group">
                        <label class="floating-label-activo-sm">Nombres</label>
                        <input type="text" required class="form-control form-control-sm" name="reserva_hora_nombres_paciente" id="reserva_hora_nombres_paciente">
                    </div>
                </div>
                <div class="col-sm-12 col-md-12">
                    <div class="form-group">
                        <label class="floating-label-activo-sm">Primer Apellido</label>
                        <input type="text" class="form-control form-control-sm" name="reserva_hora_apellido_uno" id="reserva_hora_apellido_uno">
                    </div>
                </div>
                <div class="col-sm-12 col-md-12">
                    <div class="form-group">
                        <label class="floating-label-activo-sm">Segundo Apellido</label>
                        <input type="text" class="form-control form-control-sm" name="reserva_hora_apellido_dos" id="reserva_hora_apellido_dos">
                    </div>
                </div>
                <div class="col-sm-6 col-md-6">
                    <div class="form-group">
                        <label class="floating-label-activo-sm">F. Nacimiento</label>
                        <input type="date" class="form-control form-control-sm" name="reserva_hora_fecha_nac" id="reserva_hora_fecha_nac">
                    </div>
                </div>
                <div class="col-sm-6 col-md-6">
                    <div class="form-group">
                        <label class="floating-label-activo-sm">Sexo</label>
                        <select id="reserva_hora_sexo" name="reserva_hora_sexo" class="form-control form-control-sm">
                            <option value="0">Selecione una opci&oacute;n</option>
                            <option value="F">Femenino</option>
                            <option value="M">Masculino</option>
                        </select>
                    </div>
                </div>
                <div class="col-sm-12 col-md-12">
                    <div class="form-group">
                        <label class="floating-label-activo-sm">Profesión u Oficio</label>
                        <select id="reserva_hora_profesion" name="reserva_hora_profesion" class="form-control form-control-sm">
                            <option value="0">Selecione una opci&oacute;n</option>
                            @if (isset($profesion_oficio))
                                @foreach ($profesion_oficio as $prof_ofic)
                                    <option value="{{ $prof_ofic->id }}">{{ $prof_ofic->nombre }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                </div>
                <div class="col-sm-12 col-md-12">
                    <div class="form-group">
                        <label class="floating-label-activo-sm">Previsi&oacute;n</label>
                        <select id="reserva_hora_convenio" name="reserva_hora_convenio" class="form-control form-control-sm">
                            <option value="0">Selecione una opci&oacute;n</option>
                            @if (isset($prevision))
                                @foreach ($prevision as $p)
                                    <option value="{{ $p->id }}">{{ $p->nombre }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                </div>
                <div class="col-sm-9 col-md-9">
                    <div class="form-group">
                        <label class="floating-label-activo-sm">Direcci&oacute;n</label>
                        <input type="address" class="form-control form-control-sm" name="reserva_hora_direccion" id="reserva_hora_direccion">
                    </div>
                </div>

                <div class="col-sm-3 col-md-3">
                    <div class="form-group">
                        <label class="floating-label-activo-sm">N&uacute;mero</label>
                        <input type="address" class="form-control form-control-sm" name="reserva_hora_numero_dir" id="reserva_hora_numero_dir">
                    </div>
                </div>


                <div class="col-sm-12 col-md-12">
                    <div class="form-group">
                        <label class="floating-label-activo-sm">Region</label>
                        <select id="region_agregar" onchange="buscar_ciudad();" name="region_agregar" class="form-control" required>
                            <option value="0">Seleccione Regi&oacute;n</option>
                            @if (isset($region))
                                @foreach ($region as $reg)
                                    <option value="{{ $reg->id }}">{{ $reg->nombre }} </option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                </div>

                <div class="col-sm-12 col-md-12">
                    <div class="form-group">
                        <label class="floating-label-activo-sm">Ciudad</label>
                        <select id="ciudad_agregar" name="ciudad_agregar" class="form-control" required>
                            <option value="0">Seleccione Ciudad</option>
                        </select>
                    </div>
                </div>
                <div class="col-sm-12 col-md-12">
                    <div class="form-group">
                        <label class="floating-label-activo-sm">Correo Electr&oacute;nico</label>
                        <input type="text" class="form-control form-control-sm" onblur="validar_email_agenda()" name="reserva_hora_correo" id="reserva_hora_correo">
                        <span id="mensaje_email_reserva" style="display:none"></span>
                    </div>
                </div>
                <div class="col-sm-12 col-md-12">
                    <div class="form-group">
                        <label class="floating-label-activo-sm">Tel&eacute;fono</label>
                        <input type="tel" class="form-control form-control-sm" name="reserva_hora_telefono_uno" id="reserva_hora_telefono_uno">
                    </div>
                </div>
                <div class="col-sm-12 col-md-12">
                    <div class="form-group">
                        <label class="floating-label-activo-sm">Descrici&oacute;n Reserva</label>
                        <input type="text" class="form-control form-control-sm" name="reserva_hora_descripcion" id="reserva_hora_descripcion">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12 col-md-12">
                    <div class="form-group">
                        <h6 class="text-c-blue ml-2 mb-3">Enviar confirmaci&oacute;n</h6>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6 col-md-6">
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="reserva_hora_confirmacion" name="reserva_hora_confirmacion">
                            <label class="custom-control-label" for="reserva_hora_confirmacion">Correo electr&oacute;nico</label>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-6">
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="reserva_hora_sms" name="reserva_hora_sms">
                            <label class="custom-control-label" for="sms">SMS</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="reserva-vet-acciones">
                <button type="button" class="btn btn-outline-secondary reserva-vet-volver" onclick="irPasoReservaHora(3);">
                    <i class="feather icon-chevron-left"></i> Anterior
                </button>
                <button type="button" id="guardar_reserva_paciente" onclick="agendar_hora_paciente_nuevo();" class="btn btn-info">
                    <i class="feather icon-check"></i> Tomar hora
                </button>
            </div>
        </div>
    </form>
</div>

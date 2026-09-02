<div id="antecedentes_paciente" class="bs-canvas bs-canvas-anim bs-canvas-right position-fixed bg-light h-100 shadow-lg"
    data-width="380px" data-offset="true">
    <header class="bs-canvas-header p-3 bg-info overflow-auto d-flex justify-content-between">
        <button type="button" class="bs-canvas-close close" aria-label="Close"><span aria-hidden="true"
                class="text-light">&times;</span></button>
        <h5 class="d-inline text-light mb-0 mt-1">Antecedentes veterinarios</h5>

    </header>
    <div class="bs-canvas-content">
        <div class="accordion" id="accordionExample">
              <!------------------INFO PCTE---------------------->
            <div class="card-sidebar mb-0 rounded-0">
                <div class="card-header-sidebar" id="headingOne">
                    <button class="btn btn-light btn-block text-left text-info" type="button" data-toggle="collapse"
                        data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne"><i
                            class="feather icon-chevron-down float-right pt-1 flecha-accordion"></i>
                        TUTOR / RESPONSABLE
                    </button>
                </div>

                <div id="collapseOne" class="collapse show" aria-labelledby="headingOne"
                    data-parent="#accordionExample">
                    <div class="card-body-sidebar">
                        <div id="info_paciente">
                               <div class="form-row align-items-center pt-1">
                                <div class="col-1"><img class="wid-30 pl-1 mr-2" src="{{ asset('images/sdi-iconos/rut-info.png') }}"></div>
                                <div class="col-10  ml-2 text-secondary">
                                    <h6 class="text-dark">RUT</h6>
                                    <p>{{ $paciente->rut }}
                                    </p>
                                </div>
                            </div>
                            <hr class="mt-2 mb-2">
                            <div class="form-row align-items-center mt-1">
                                <div class="col-1"><img class=" wid-30 pl-1 mr-2" src="{{ asset('images/sdi-iconos/usuario-info.png') }}"></div>
                                <div class="col-10  ml-2 text-secondary">
                                    <h6 class="text-dark">Nombre</h6>
                                    <p id="nombre_completo_paciente">
                                    {{ $paciente->nombres }}
                                    {{ $paciente->apellido_uno . ' ' . $paciente->apellido_dos }}</p>
                                </div>
                            </div>
                            <hr class="mt-2 mb-2">
                            <div class="form-row align-items-center mt-1">
                                <div class="col-1"><img class=" wid-30 pl-2 mr-2" src="{{ asset('images/sdi-iconos/nacimiento-info.png') }}"></div>
                                <div class="col-10  ml-2 text-secondary">
                                    <h6 class="text-dark">Fecha Nacimiento</h6>
                                    <p id="fecha_nac_paciente">
                                        {{ $paciente->fecha_nac }} -
                                    (<span>{{ \Carbon\Carbon::parse($paciente->fecha_nac)->diff(\Carbon\Carbon::now())->format('%y') }}</span>
                                    años)
                                    </p>
                                </div>
                            </div>
                            <hr class="mt-2 mb-2">
                            <div class="form-row align-items-center mt-1">
                                <div class="col-1"></div><!--<img class=" wid-30 pl-2 mr-2" src="{{ asset('images/sdi-iconos/femenino-info.png') }}"></div>-->
                                <!--<div class="col-1"><img class=" wid-30 pl-2 mr-2" src="{{ asset('images/sdi-iconos/masculino-info.png') }}"></div>-->
                                <div class="col-10  ml-2 text-secondary">
                                    <h6 class="text-dark">Sexo</h6>
                                    <p id="sexo_paciente">
                                    @if ($paciente->sexo == 'M')
                                        Masculino
                                    @else
                                        Femenino
                                    @endif
                                    </p>
                                </div>
                            </div>
                            <hr class="mt-2 mb-2">
                            <div class="form-row align-items-center mt-1">
                                <div class="col-1"><img class=" wid-30 pl-1 mr-2" src="{{ asset('images/sdi-iconos/prevision-info.png') }}"></div>
                                <div class="col-10  ml-2 text-secondary">
                                    <h6 class="text-dark">Previsión</h6>
                                    <p id="prevision_paciente">{{ $paciente->Prevision()->first()->nombre }}</p>
                                </div>
                            </div>
                            <hr class="mt-2 mb-2">
                            <div class="form-row align-items-center mt-1">
                                <div class="col-1"><img class=" wid-30 pl-2 mr-2" src="{{ asset('images/sdi-iconos/hogar-info.png') }}"></div>
                                <div class="col-10  ml-2 text-secondary">
                                    <h6 class="text-dark">Dirección</h6>
                                    <p id="direccion_paciente_">

                                    @if (isset($paciente))
                                        @if ($paciente->Direccion()->first() != null)
                                            {{ $paciente->Direccion()->first()->direccion . ' ' . $paciente->Direccion()->first()->numero_dir }}
                                        @else
                                            <span class="error">NO ha registrado dirección</span>
                                        @endif
                                    @else
                                        <span class="error">No ha registrado dirección</span>
                                    @endif

                                    </p>
                                </div>
                            </div>
                            <hr class="mt-2 mb-2">
                            <div class="form-row align-items-center mt-1">
                                <div class="col-1"><img class=" wid-30 pl-2 mr-2" src="{{ asset('images/sdi-iconos/hogar-info.png') }}"></div>
                                <div class="col-10  ml-2 text-secondary">
                                    <h6 class="text-dark">Región - Comuna</h6>
                                    <p id="comuna_region_paciente">
                                    @if (isset($paciente))
                                        @if ( $paciente->id_direccion )
                                            @if ($paciente->Direccion()->first()->Ciudad()->first() != null)
                                                {{ $paciente->Direccion()->first()->Ciudad()->first()->nombre }}<br>
                                                {{ $paciente->Direccion()->first()->Ciudad()->first()->Region()->first()->nombre }}
                                            @else
                                                <span class="error">No se ha registrado ciudad</span>
                                            @endif
                                        @else
                                            <span class="error">NO se ha registrado ciudad</span>
                                        @endif
                                    @else
                                        <span class="error">NO se ha registrado ciudad</span>
                                    @endif
                                    </p>
                                </div>
                            </div>
                            <hr class="mt-2 mb-2">
                            <div class="form-row align-items-center mt-1">
                                <div class="col-1"><img class=" wid-30 pl-2 mr-2" src="{{ asset('images/sdi-iconos/email-info.png') }}"></div>
                                <div class="col-10  ml-2 text-secondary">
                                    <h6 class="text-dark">Email</h6>
                                    <p id="email_paciente_">
                                    {{ $paciente->email }}
                                    </p>
                                </div>
                            </div>
                            <hr class="mt-2 mb-2">
                            <div class="form-row align-items-center mt-1">
                                <div class="col-1"><img class=" wid-30 pl-1 mr-2" src="{{ asset('images/sdi-iconos/telefono-info.png') }}"></div>
                                <div class="col-10  ml-2 text-secondary">
                                    <h6 class="text-dark">Teléfono</h6>
                                    <p id="telefono_paciente_">
                                      {{ $paciente->telefono_uno }}
                                    </p>
                                </div>
                            </div>
                            <div class="form-row mt-3 mb-5">
                                <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 text-center">
                                    <button type="button" class="btn btn-primary-light-c btn-xxs"
                                        onclick="editarInformacionPaciente()"><i class="feather icon-edit"></i> Editar
                                        información</button>
                                </div>
                            </div>
                        </div>
                        <div id="info_paciente-edit" style="display: none">
                            <div class="form-row pt-3">
                                <label class="col-2 text-dark font-weight-bolder">Rut</label>
                                <div class="col-9 ml-2 text-secondary">
                                    <input type="text" id="paciente_rut_edit" class="form-control form-control-sm"
                                        value="{{ $paciente->rut }}">
                                </div>
                            </div>
                            <hr class="mt-2">
                            <div class="form-row mt-1">
                                <label class="col-2  text-dark font-weight-bolder">Nombre</label>
                                <div class="col-9 ml-2 text-secondary">
                                    <input type="text" id="paciente_nombre_edit" class="form-control form-control-sm"
                                        value="{{ $paciente->nombres }}">
                                </div>
                            </div>
                            <hr class="mt-2">
                            <div class="form-row mt-1">
                                <label class="col-2 text-dark font-weight-bolder">Apellido Paterno</label>
                                <div class="col-9 ml-2 text-secondary">
                                    <input type="text" id="paciente_apellido_uno_edit" class="form-control form-control-sm"
                                        value="{{ $paciente->apellido_uno }}">
                                </div>
                            </div>
                            <hr class="mt-2">
                            <div class="form-row mt-1">
                                <label class="col-2 text-dark font-weight-bolder">Apellido Materno</label>
                                <div class="col-9 ml-2 text-secondary">
                                    <input type="text" id="paciente_apellido_dos_edit" class="form-control form-control-sm"
                                        value="{{ $paciente->apellido_dos }}">
                                </div>
                            </div>
                            <hr class="mt-2">
                            <div class="form-row mt-1">
                                <label class="col-2 text-dark font-weight-bolder">FN</label>
                                <div class="col-9 ml-2 text-secondary">
                                    <input type="date" name="paciente_fn_edit" id="paciente_fn_edit"
                                        class="form-control form-control-sm" value="{{ $paciente->fecha_nac }}">
                                </div>
                            </div>
                            <hr class="mt-2">
                            <div class="form-row mt-1">
                                <label class="col-2 text-dark font-weight-bolder">Sexo</label>
                                <div class="col-9 ml-2 text-secondary">
                                    <select name="paciente_sexo_edit" id="paciente_sexo_edit" class="form-control form-control-sm">
                                        <option value="M" @if ($paciente->sexo == 'M') selected @endif>
                                            Masculino</option>
                                        <option value="F" @if ($paciente->sexo == 'F') selected @endif>
                                            Femenino</option>
                                    </select>
                                </div>
                            </div>
                            <hr class="mt-2">
                            <div class="form-row mt-1">
                                <label class="col-2  text-c-blue font-weight-bolder">Convenios</label>
                                <div class="col-9 ml-2 text-secondary">
                                    {{-- <input type="text" class="form-control" id="paciente_convenio_edit"
                                        value="{{ $paciente->Prevision()->first()->nombre }}"> --}}
                                    <select name="paciente_convenio_edit" id="paciente_convenio_edit" class="form-control form-control-sm">
                                        <option value="0">Selecione una opción</option>
                                        @foreach($prevision as $pre)
                                            @if($pre->id == 12)
                                                @continue
                                            @endif
                                            <option value="{{ $pre->id }}" @if ($paciente->Prevision()->first()->nombre == $pre->nombre) selected @endif> {{ $pre->nombre }}</option>
                                        @endforeach
                                                {{-- <option {{ $paciente->Prevision()->first()->nombre == 'Particular' ? 'selected' : '' }} value="particular">Particular Sin Convenio</option>
                                                <option {{ $paciente->Prevision()->first()->nombre == 'Fonasa' ? 'selected' : '' }} value="1">Fonasa</option>
                                                <option {{ $paciente->Prevision()->first()->nombre == 'Banmedica' ? 'selected' : '' }} value="2">Banmedica</option>
                                                <option {{ $paciente->Prevision()->first()->nombre == 'Colmena' ? 'selected' : '' }} value="colmena">Colmena</option>
                                                <option {{ $paciente->Prevision()->first()->nombre == 'Cruz Verde' ? 'selected' : '' }} value="cruz verde">Cruz Verde</option>
                                                <option {{ $paciente->Prevision()->first()->nombre == 'Nueva MasVida' ? 'selected' : '' }} value="nueva masvida">Nueva MasVida</option>
                                                <option {{ $paciente->Prevision()->first()->nombre == 'Consalud' ? 'selected' : '' }} value="consalud">Consalud</option> --}}

                                    </select>
                                </div>
                            </div>
                            <hr class="mt-2">
                            <div class="form-row mt-1">
                                <label class="col-2 text-dark font-weight-bolder">Dirección</label>
                                <div class="col-9 ml-2 text-secondary">
                                    @if($paciente->id_direccion == null)
                                        <span class="error">No ha registrado dirección</span>
                                        @else
                                    <input type="text" class="form-control form-control-sm" id="paciente_dir_edit"
                                        value="{{ $paciente->Direccion()->first()->direccion . ' ' . $paciente->Direccion()->first()->numero_dir }}">
                                    @endif
                                </div>
                            </div>
                            <hr class="mt-2">
                            <div class="form-row mt-1 text-dark ">
                                <label class="col-2 text-dark font-weight-bolder">Región</label>
                                <div class="col-9 ml-2 text-secondary">
                                    @if($paciente->id_direccion == null)
                                        <span class="error">No ha registrado región</span>
                                    @else
                                    <select name="paciente_region_edit" id="paciente_region_edit"
                                        class="form-control form-control-sm" onchange="buscar_ciudad_paciente();">
                                        <option value="0">Seleccione región</option>
                                        @foreach ($regiones as $region)
                                            <option value="{{ $region->id }}"
                                                @if ($paciente->Direccion()->first()->Ciudad()->first()->Region()->first()->id == $region->id) selected @endif>{{ $region->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @endif
                                </div>
                            </div>
                            <hr class="mt-2">
                            <div class="form-row mt-1">
                                <label class="col-2 text-dark font-weight-bolder">Comuna</label>
                                <div class="col-9 ml-2 text-secondary">
                                    <select name="paciente_comuna_edit" id="paciente_comuna_edit"
                                        class="form-control form-control-sm">
                                        <option value="0">Seleccione comuna</option>
                                        @foreach ($ciudades as $comuna)
                                            <option value="{{ $comuna->id }}"
                                                @if ($paciente->id_direccion && $paciente->Direccion()->first()->Ciudad()->first()->id == $comuna->id) selected @endif>{{ $comuna->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <hr class="mt-2">
                            <div class="form-row mt-1">
                                <label class="col-2 text-dark font-weight-bolder">Email</label>
                                <div class="col-9 ml-2 text-secondary">
                                    <input type="text" id="paciente_email_edit" name="paciente_edit_email"
                                        class="form-control form-control-sm" value="{{ $paciente->email }}">
                                </div>
                            </div>
                            <hr class="mt-2">
                            <div class="form-row mt-1">
                                <label class="col-2 text-dark font-weight-bolder">Teléfono</label>
                                <div class="col-9 ml-2 text-secondary">
                                    <input type="text" class="form-control form-control-sm" id="paciente_telefono_edit"
                                        name="paciente_telefono_edit" value="{{ $paciente->telefono_uno }}">
                                </div>
                            </div>
                            <div class="form-row mt-3 mb-5">
                                <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 text-center">
                                    <button type="button" class="btn btn-primary-light-c btn-xxs"
                                        onclick="guardarInformacionPaciente()"><i class="feather icon-save"></i>
                                        Guardar información</button>
                                    <button type="button" class="btn btn-danger-light-c btn-xxs"
                                        onclick="cancelarInformacionPaciente()"><i class="feather icon-x"></i> Cancelar
                                        edición</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            {{-- Información clínica propia de la mascota, separada del tutor. --}}
            <div class="card-sidebar mb-0 rounded-0">
                <div class="card-header-sidebar" id="headingMascota">
                    <button class="btn btn-light btn-block text-left text-info collapsed" type="button"
                        data-toggle="collapse" data-target="#collapseMascota" aria-expanded="false"
                        aria-controls="collapseMascota">
                        <i class="feather icon-chevron-down float-right pt-1 flecha-accordion"></i>
                        MASCOTA
                    </button>
                </div>
                <div id="collapseMascota" class="collapse" aria-labelledby="headingMascota"
                    data-parent="#accordionExample">
                    <div class="card-body-sidebar">
                        @if(isset($mascota) && $mascota)
                            <div class="form-row align-items-center pt-1">
                                <div class="col-1"><i class="feather icon-heart text-info"></i></div>
                                <div class="col-10 ml-2 text-secondary">
                                    <h6 class="text-dark">Nombre</h6>
                                    <p>{{ $mascota->nombre ?: 'Sin registro' }}</p>
                                </div>
                            </div>
                            <hr class="mt-2 mb-2">
                            <div class="form-row align-items-center">
                                <div class="col-1"><i class="feather icon-tag text-info"></i></div>
                                <div class="col-10 ml-2 text-secondary">
                                    <h6 class="text-dark">Especie / raza</h6>
                                    <p>
                                        {{ optional($mascota->especieMascota)->nombre
                                            ?? $mascota->otra_especie
                                            ?? $mascota->especie
                                            ?? 'Sin especie' }}
                                        /
                                        {{ optional($mascota->razaMascota)->nombre ?? 'Sin raza' }}
                                    </p>
                                </div>
                            </div>
                            <hr class="mt-2 mb-2">
                            <div class="form-row align-items-center">
                                <div class="col-1"><i class="feather icon-calendar text-info"></i></div>
                                <div class="col-10 ml-2 text-secondary">
                                    <h6 class="text-dark">Nacimiento / edad</h6>
                                    <p>
                                        @if($mascota->fecha_nacimiento)
                                            {{ $mascota->fecha_nacimiento->format('d-m-Y') }}
                                            ({{ $mascota->fecha_nacimiento->diffForHumans(null, true) }})
                                        @else
                                            Sin registro
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <hr class="mt-2 mb-2">
                            <div class="form-row align-items-center">
                                <div class="col-1"><i class="feather icon-info text-info"></i></div>
                                <div class="col-10 ml-2 text-secondary">
                                    <h6 class="text-dark">Sexo / esterilización</h6>
                                    <p>
                                        {{ $mascota->sexo ?: 'Sin registro' }} /
                                        {{ $mascota->esterilizado ? 'Esterilizado/a' : 'No esterilizado/a' }}
                                    </p>
                                </div>
                            </div>
                            <hr class="mt-2 mb-2">
                            <div class="form-row align-items-center">
                                <div class="col-1"><i class="feather icon-cpu text-info"></i></div>
                                <div class="col-10 ml-2 text-secondary">
                                    <h6 class="text-dark">Microchip</h6>
                                    <p>{{ $mascota->chip ?: 'Sin registro' }}</p>
                                </div>
                            </div>
                            <hr class="mt-2 mb-2">
                            <div class="form-row align-items-center">
                                <div class="col-1"><i class="feather icon-activity text-info"></i></div>
                                <div class="col-10 ml-2 text-secondary">
                                    <h6 class="text-dark">Antecedentes sanitarios</h6>
                                    <p>
                                        Enfermedad crónica: {{ $mascota->enfermedad_cronica ?: 'Sin registro' }}<br>
                                        Dieta: {{ $mascota->dieta ?: 'Sin registro' }}<br>
                                        Última desparasitación:
                                        {{ optional($mascota->ultima_desparasitacion)->format('d-m-Y') ?? 'Sin registro' }}
                                    </p>
                                </div>
                            </div>
                            <div class="form-row mt-3 mb-3">
                                <div class="col-12 text-center">
                                    <button type="button" class="btn btn-primary-light-c btn-xxs" onclick="editarInformacionMascotaSidebar()">
                                        <i class="feather icon-edit"></i> Editar mascota
                                    </button>
                                </div>
                            </div>

                            <div id="info_mascota_sidebar_edit" class="border-top pt-3" style="display:none;">
                                <div class="form-group mb-2">
                                    <label class="floating-label-activo-sm">Nombre</label>
                                    <input type="text" id="sidebar_mascota_nombre" class="form-control form-control-sm" value="{{ $mascota->nombre }}">
                                </div>
                                <div class="form-group mb-2">
                                    <label class="floating-label-activo-sm">Microchip</label>
                                    <input type="text" id="sidebar_mascota_chip" class="form-control form-control-sm" value="{{ $mascota->chip }}">
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-6 mb-2">
                                        <label class="floating-label-activo-sm">Nacimiento</label>
                                        <input type="date" id="sidebar_mascota_fecha_nacimiento" class="form-control form-control-sm"
                                            value="{{ optional($mascota->fecha_nacimiento)->format('Y-m-d') }}">
                                    </div>
                                    <div class="form-group col-6 mb-2">
                                        <label class="floating-label-activo-sm">Sexo</label>
                                        <select id="sidebar_mascota_sexo" class="form-control form-control-sm">
                                            <option value="M" @selected($mascota->sexo === 'M')>Macho</option>
                                            <option value="F" @selected($mascota->sexo === 'F')>Hembra</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group mb-2">
                                    <label class="floating-label-activo-sm">Esterilizado/a</label>
                                    <select id="sidebar_mascota_esterilizado" class="form-control form-control-sm" onchange="actualizarFechaEsterilizacionSidebar()">
                                        <option value="0" @selected(!$mascota->esterilizado)>No</option>
                                        <option value="1" @selected($mascota->esterilizado)>Sí</option>
                                    </select>
                                </div>
                                <div class="form-group mb-2" id="sidebar_grupo_fecha_esterilizacion">
                                    <label class="floating-label-activo-sm">Fecha de esterilización</label>
                                    <input type="date" id="sidebar_mascota_fecha_esterilizacion" class="form-control form-control-sm"
                                        value="{{ optional($mascota->fecha_esterilizacion)->format('Y-m-d') }}">
                                    <small class="text-muted">Puede quedar sin fecha cuando no se conoce.</small>
                                </div>
                                <div class="form-group mb-2">
                                    <label class="floating-label-activo-sm">Enfermedad crónica</label>
                                    <textarea id="sidebar_mascota_enfermedad_cronica" class="form-control form-control-sm" rows="2">{{ $mascota->enfermedad_cronica }}</textarea>
                                </div>
                                <div class="form-group mb-2">
                                    <label class="floating-label-activo-sm">Dieta</label>
                                    <textarea id="sidebar_mascota_dieta" class="form-control form-control-sm" rows="2">{{ $mascota->dieta }}</textarea>
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-6 mb-2">
                                        <label class="floating-label-activo-sm">Última desparasitación</label>
                                        <input type="date" id="sidebar_mascota_ultima_desparasitacion" class="form-control form-control-sm"
                                            value="{{ optional($mascota->ultima_desparasitacion)->format('Y-m-d') }}">
                                    </div>
                                    <div class="form-group col-6 mb-2">
                                        <label class="floating-label-activo-sm">Producto</label>
                                        <input type="text" id="sidebar_mascota_producto_desparasitacion" class="form-control form-control-sm"
                                            value="{{ $mascota->producto_desparasitacion }}">
                                    </div>
                                </div>
                                <div class="text-center mt-3">
                                    <button type="button" class="btn btn-info btn-xxs" onclick="guardarInformacionMascotaSidebar()">
                                        <i class="feather icon-save"></i> Guardar
                                    </button>
                                    <button type="button" class="btn btn-danger-light-c btn-xxs" onclick="cancelarInformacionMascotaSidebar()">
                                        <i class="feather icon-x"></i> Cancelar
                                    </button>
                                </div>
                            </div>

                            @once
                            <script>
                                function editarInformacionMascotaSidebar() {
                                    $('#info_mascota_sidebar_edit').slideDown();
                                    actualizarFechaEsterilizacionSidebar();
                                }

                                function cancelarInformacionMascotaSidebar() {
                                    $('#info_mascota_sidebar_edit').slideUp();
                                }

                                function actualizarFechaEsterilizacionSidebar() {
                                    $('#sidebar_grupo_fecha_esterilizacion').toggle($('#sidebar_mascota_esterilizado').val() === '1');
                                }

                                function guardarInformacionMascotaSidebar() {
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
                                        especie_id: @json($mascota->especie_id),
                                        raza_id: @json($mascota->raza_id),
                                        otra_especie: @json($mascota->otra_especie),
                                        tamano_id: @json($mascota->tamano_id),
                                        fecha_nacimiento: $('#sidebar_mascota_fecha_nacimiento').val(),
                                        sexo: $('#sidebar_mascota_sexo').val(),
                                        esterilizado: esterilizado,
                                        fecha_esterilizacion_desconocida: esterilizado === '1' && !fechaEsterilizacion ? 1 : 0,
                                        fecha_esterilizacion: fechaEsterilizacion,
                                        enfermedad_cronica: $('#sidebar_mascota_enfermedad_cronica').val(),
                                        dieta: $('#sidebar_mascota_dieta').val(),
                                        ultima_desparasitacion: $('#sidebar_mascota_ultima_desparasitacion').val(),
                                        producto_desparasitacion: $('#sidebar_mascota_producto_desparasitacion').val()
                                    };

                                    if (!datos.nombre) {
                                        swal({ title: 'Editar mascota', text: 'El nombre es obligatorio.', icon: 'warning', button: 'Aceptar' });
                                        return;
                                    }

                                    $.ajax({
                                        url: @json(route('paciente.mascotas.update', $mascota->id)),
                                        type: 'POST',
                                        data: datos
                                    }).done(function (respuesta) {
                                        if (respuesta.estado != 1) {
                                            const errores = respuesta.error ? Object.values(respuesta.error).flat().join('\n') : '';
                                            swal({ title: 'No se pudo actualizar', text: respuesta.msj + (errores ? '\n' + errores : ''), icon: 'error', button: 'Aceptar' });
                                            return;
                                        }
                                        swal({ title: 'Mascota actualizada', text: 'La información fue guardada correctamente.', icon: 'success', button: 'Aceptar' })
                                            .then(function () { window.location.reload(); });
                                    }).fail(function (xhr) {
                                        swal({ title: 'No se pudo actualizar', text: xhr.responseJSON?.message || 'Revise los datos ingresados.', icon: 'error', button: 'Aceptar' });
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

              <!------------------CONTACTO EMERGENCIA SOS-------------------->
            <div class="card-sidebar">
                <div class="card-header-sidebar" id="headingTwo">
                    <h2 class="mb-0">
                        <button class="btn btn-light btn-block text-left collapsed text-info" type="button"
                            data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false"
                            aria-controls="collapseTwo"><i
                                class="feather icon-chevron-down float-right pt-1 flecha-accordion"></i>
                            CONTACTO DE EMERGENCIA
                        </button>
                    </h2>
                </div>
                <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionExample">
                    <div class="card-body-sidebar">
                        <div id="info_contacto">
                            <div class="form-row align-items-center pt-1">
                                <div class="col-1"><img class=" wid-30 pl-1 mr-2" src="{{ asset('images/sdi-iconos/rut-info.png') }}"></div>
                                <div class="col-10  ml-2 text-secondary">
                                    <h6 class="text-dark">RUT</h6>
                                    <p >

                                    @if ($paciente->ContactosEmergencia()->first() != null)
                                        <span class="info">
                                            {{ $paciente->ContactosEmergencia()->first()->rut }}
                                        </span>
                                    @else
                                        <span class="info">Sin registro de contacto</span>
                                    @endif

                                    </p>
                                </div>
                            </div>

                            <hr class="mt-2 mb-2">
                            <div class="form-row align-items-center mt-1">
                                <div class="col-1"><img class=" wid-30 pl-1 mr-2" src="{{ asset('images/sdi-iconos/usuario-info.png') }}"></div>
                                <div class="col-10  ml-2 text-secondary">
                                    <h6 class="text-dark">Nombre</h6>
                                    <p id="nombre_completo_contacto">
                                    @if ($paciente->ContactosEmergencia()->first() != null)
                                        <span class="info">
                                            {{ $paciente->ContactosEmergencia()->first()->nombre }}
                                        </span>
                                    @else
                                        <span class="info">Sin registro de contacto</span>
                                    @endif


                                    </p>
                                </div>
                            </div>

                            <hr class="mt-2 mb-2">
                            <div class="form-row align-items-center mt-1">
                                <div class="col-1"><img class=" wid-30 pl-1 mr-2" src="{{ asset('images/sdi-iconos/usuario-info.png') }}"></div>
                                <div class="col-10  ml-2 text-secondary">
                                    <h6 class="text-dark">Apellidos</h6>
                                    <p id="apellidos_contacto">

                                    @if ($paciente->ContactosEmergencia()->first() != null)
                                        <span class="info">
                                            {{ $paciente->ContactosEmergencia()->first()->apellido_uno . ' ' . $paciente->ContactosEmergencia()->first()->apellido_dos }}
                                        </span>
                                    @else
                                        <span class="info">Sin registro de contacto</span>
                                    @endif

                                    </p>
                                </div>
                            </div>

                            <hr class="mt-2 mb-2">
                            <div class="form-row align-items-center mt-1">
                                <div class="col-1"><img class=" wid-30 pl-2 mr-2" src="{{ asset('images/sdi-iconos/hogar-info.png') }}"></div>
                                <div class="col-10  ml-2 text-secondary">
                                    <h6 class="text-dark">Dirección</h6>
                                    <p>
                                    @if ($paciente->ContactosEmergencia()->first() != null)
                                        <span class="info">

                                            {{ $paciente->ContactosEmergencia()->first()->Direccion()->first()->direccion .
                                                ' ' .
                                                $paciente->ContactosEmergencia()->first()->Direccion()->first()->numero_dir }}
                                        </span>
                                    @else
                                        <span class="info">Sin registro de contacto</span>
                                    @endif

                                    </p>
                                </div>
                            </div>

                            <hr class="mt-2 mb-2">
                            <div class="form-row align-items-center mt-1">
                                <div class="col-1"><img class=" wid-30 pl-2 mr-2" src="{{ asset('images/sdi-iconos/hogar-info.png') }}"></div>
                                <div class="col-10  ml-2 text-secondary">
                                    <h6 class="text-dark">Región - Comuna</h6>
                                    <p id="comuna_region_contacto">

                                    @if ($paciente->ContactosEmergencia()->first() != null)
                                        <span class="info">
                                            {{ $paciente->ContactosEmergencia()->first()->Direccion()->first()->Ciudad()->first()->nombre }}
                                        </span>
                                    @else
                                        <span class="info">Sin registro de contacto</span>
                                    @endif

                                    </p>
                                </div>
                            </div>

                            <hr class="mt-2 mb-2">
                            <div class="form-row align-items-center mt-1">
                                <div class="col-1"><img class=" wid-30 pl-2 mr-2" src="{{ asset('images/sdi-iconos/email-info.png') }}"></div>
                                <div class="col-10  ml-2 text-secondary">
                                    <h6 class="text-dark">Email</h6>
                                    <p id="email_contacto_">

                                    @if ($paciente->ContactosEmergencia()->first() != null)
                                        <span class="info">
                                            {{ $paciente->ContactosEmergencia()->first()->email }}
                                        </span>
                                    @else
                                        <span class="info">Sin registro de contacto</span>
                                    @endif

                                    </p>
                                </div>
                            </div>

                            <hr class="mt-2 mb-2">
                            <div class="form-row align-items-center mt-1">
                                <div class="col-1"><img class=" wid-30 pl-1 mr-2" src="{{ asset('images/sdi-iconos/telefono-info.png') }}"></div>
                                <div class="col-10  ml-2 text-secondary">
                                    <h6 class="text-dark">Teléfono</h6>
                                    <p>  @if ($paciente->ContactosEmergencia()->first() != null)
                                        <span class="info">
                                            {{ $paciente->ContactosEmergencia()->first()->telefono }}
                                        </span>
                                    @else
                                        <span class="info">Sin registro de contacto</span>
                                    @endif
                                    </p>
                                </div>
                            </div>

                            <div class="form-row mt-3 mb-5">
                                <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 text-center">
                                    <button type="button" class="btn btn-primary-light-c btn-xxs"
                                        onclick="editarInformacionContacto()"><i class="feather icon-edit"></i> Editar
                                        información</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @if ($paciente->ContactosEmergencia()->exists())
                        <div id="info_contacto-edit" style="display: none">
                        <div class="form-row pt-3">
                            <label class="col-2 text-dark font-weight-bolder">Rut</label>
                            <div class="col-9 ml-2 text-secondary">
                               <input id="contacto_rut_edit" name="contacto_rut_edit" type="text" class="form-control form-control-sm" value="{{ $paciente->ContactosEmergencia()->first()->rut }}">
                            </div>
                        </div>
                        <hr class="mt-2">
                        <div class="form-row mt-1">
                            <label class="col-2  text-dark font-weight-bolder">Nombre</label>
                            <div class="col-9 ml-2 text-secondary">
                                <input id="contacto_nombre_edit" name="contacto_nombre_edit" type="text" class="form-control form-control-sm" value="{{ $paciente->ContactosEmergencia()->first()->nombre }}">
                            </div>
                        </div>
                        <hr class="mt-2">
                        <div class="form-row mt-1">
                            <label class="col-2 text-dark font-weight-bolder">Apellido Paterno</label>
                            <div class="col-9 ml-2 text-secondary">
                                <input id="contacto_apellido_uno" name="contacto_apellido_uno" type="text" class="form-control form-control-sm" value="{{ $paciente->ContactosEmergencia()->first()->apellido_uno }}">
                            </div>
                        </div>
                        <hr class="mt-2">
                        <div class="form-row mt-1">
                            <label class="col-2 text-dark font-weight-bolder">Apellido Materno</label>
                            <div class="col-9 ml-2 text-secondary">
                                <input id="contacto_apellido_dos" name="contacto_apellido_dos" type="text" class="form-control form-control-sm" value="{{ $paciente->ContactosEmergencia()->first()->apellido_dos }}">
                            </div>
                        </div>
                        <hr class="mt-2">
                        <div class="form-row mt-1">
                            <label class="col-2 text-dark font-weight-bolder">FN</label>
                            <div class="col-9 ml-2 text-secondary">
                                <input type="date" name="contacto_fn_edit" id="contacto_fn_edit" value="{{ $paciente->ContactosEmergencia()->first()->fecha_nac }}">
                            </div>
                        </div>
                        <hr class="mt-2">
                        <div class="form-row mt-1">
                            <label class="col-2 text-dark font-weight-bolder">Sexo</label>
                            <div class="col-9 ml-2 text-secondary">
                                <select name="contacto_sexo_edit" id="contacto_sexo_edit" class="form-control form-control-sm">
                                    <option value="M" @if ($paciente->ContactosEmergencia()->first()->sexo == 'M') selected @endif>Masculino
                                    </option>
                                    <option value="F" @if ($paciente->ContactosEmergencia()->first()->sexo == 'F') selected @endif>Femenino
                                    </option>
                                </select>
                            </div>
                        </div>
                        {{-- <hr class="mt-2">
                        <div class="form-row mt-1">
                            <label class="col-2  text-c-blue font-weight-bolder">Convenios</label>
                            <div class="col-9 ml-2 text-secondary">

                            </div>
                        </div> --}}
                        <hr class="mt-2">
                        <div class="form-row mt-1">
                            <label class="col-2 text-dark font-weight-bolder">Dirección</label>
                            <div class="col-9 ml-2 text-secondary">
                                <input type="text" class="form-control form-control-sm" id="contacto_dir_edit"
                                    value="{{ $paciente->ContactosEmergencia()->first()->Direccion()->first()->direccion .
                                                ' ' .
                                                $paciente->ContactosEmergencia()->first()->Direccion()->first()->numero_dir }}">
                            </div>
                        </div>
                        <hr class="mt-2">
                        <div class="form-row mt-1 text-dark ">
                            <label class="col-2 text-dark font-weight-bolder">Región</label>
                            <div class="col-9 ml-2 text-secondary">
                                <select name="contacto_region_edit" onchange="buscar_ciudad_contacto({{ $paciente->ContactosEmergencia()->first()->Direccion()->first()->Ciudad()->first()->id }});" id="contacto_region_edit" class="form-control form-control-sm"
                                    >
                                    <option value="0">Seleccione región</option>
                                    @foreach ($regiones as $region)
                                        <option value="{{ $region->id }}"
                                            @if ($paciente->ContactosEmergencia()->first()->Direccion()->first()->Ciudad()->first()->Region()->first()->id == $region->id) selected @endif>{{ $region->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <hr class="mt-2">
                        <div class="form-row mt-1">
                            <label class="col-2 text-dark font-weight-bolder">Comuna</label>
                            <div class="col-9 ml-2 text-secondary">
                                <select name="contacto_comuna_edit" id="contacto_comuna_edit" class="form-control form-control-sm">
                                    <option value="0">Seleccione comuna</option>
                                    @foreach ($paciente->comunas_contacto_emer as $comuna)
                                        <option value="{{ $comuna->id }}"
                                            @if ($paciente->ContactosEmergencia()->first()->Direccion()->first()->Ciudad()->first()->id == $comuna->id) selected @endif>{{ $comuna->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <hr class="mt-2">
                        <div class="form-row mt-1">
                            <label class="col-2 text-dark font-weight-bolder">Email</label>
                            <div class="col-9 ml-2 text-secondary">
                                <input type="text" id="contacto_email_edit" name="contacto_email_edit"
                                    class="form-control form-control-sm" value="{{ $paciente->ContactosEmergencia()->first()->email }}">
                            </div>
                        </div>
                        <hr class="mt-2">
                        <div class="form-row mt-1">
                            <label class="col-2 text-dark font-weight-bolder">Teléfono</label>
                            <div class="col-9 ml-2 text-secondary">
                                <input type="text" class="form-control form-control-sm" id="contacto_telefono_edit"
                                    name="contacto_telefono_edit" value="{{ $paciente->ContactosEmergencia()->first()->telefono }}">
                            </div>
                        </div>
                        <div class="form-row mt-3 mb-5">
                            <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 text-center">
                                <button type="button" class="btn btn-primary-light-c btn-xxs"
                                    onclick="guardarInformacionContacto()"><i class="feather icon-save"></i> Guardar
                                    información</button>
                                <button type="button" class="btn btn-danger-light-c btn-xxs"
                                    onclick="cancelarInformacionContacto()"><i class="feather icon-x"></i> Cancelar
                                    edición</button>
                            </div>
                        </div>
                    </div>
                    @else
                        <div class="alert alert-warning">
                            El paciente no tiene contacto de emergencia registrado.
                        </div>
                    @endif

                </div>
            </div>

        </div>
        <!------------------ANTECEDENTES MÉDICOS---------------------->
        <div class="card-sidebar" style="display:none;">
            <div class="card-header-sidebar" id="headingThree">
                <h2 class="mb-0">
                    <button class="btn btn-light btn-block text-left collapsed text-info" type="button"
                        data-toggle="collapse" data-target="#collapseThree" aria-expanded="false"
                        aria-controls="collapseThree"><i
                            class="feather icon-chevron-down float-right pt-1 flecha-accordion"></i>
                        ANTECEDENTES MÉDICOS
                    </button>
                </h2>
            </div>
            <div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="#accordionExample">
                <div class="card-body-sidebar">
                        <div class="form-row align-items-center pt-2">
                            <div class="col-1"><img class="wid-30 pl-1 mt-n3 mr-2" src="{{ asset('images/sdi-iconos/medicamento-info.png') }}"></div>
                            <div class="col-10  ml-2 text-secondary">
                                <h6 class="text-dark">Medicamentos Crónicos</h6>
                                <ul class="listas_sidebar">
                                    <ul>

                                        @if (isset($medicamentos_cronicos))
                                            @foreach ($medicamentos_cronicos as $med_cronico)
                                                <li>{{ $med_cronico->nombre_medicamento_cronico }}</li>
                                            @endforeach
                                        @else
                                            <li>Sin registro</li>
                                        @endif

                                    </ul>
                                </ul>
                            </div>
                        </div>

                        <hr class="mt-n1 mb-2">
                        <div class="form-row align-items-center ">
                            <div class="col-1"><img class=" wid-30  mt-n3 pl-1 mr-2" src="{{ asset('images/sdi-iconos/prohibicion-danger.png') }}"></div>
                            <div class="col-10  ml-2 text-secondary">
                                <h6 class="text-dark">Alergias e Intolerancias</h6>
                               <ul class="listas_sidebar">
                                @if (isset($alergias) && count($alergias) > 0)
                                    @foreach ($alergias as $alergia)
                                        <li>{{ $alergia->nombre_alergia }}</li>
                                    @endforeach
                                @else
                                    <li>Sin registro</li>
                                @endif

                            </ul>
                            </div>
                        </div>

                        <hr class="mt-0 mb-2">
                            <div class="form-row align-items-center mt-1">
                                <div class="col-1"><img class=" wid-30 pl-1  mt-n3 mr-2" src="{{ asset('images/sdi-iconos/discapacidad-info.png') }}"></div>
                                <div class="col-10  ml-2 text-secondary">
                                    <h6 class="text-dark">Discapacidad</h6>
                                    <ul>
                                        @if (isset($discapacidades) && count($discapacidades) > 0)
                                            @foreach ($discapacidades as $d)
                                                <li>{{ $d->nombre }}</li>
                                            @endforeach
                                        @else
                                            <li>Sin registro</li>
                                        @endif
                                    </ul>
                                </div>
                            </div>

                            <hr class="mt-n1 mb-2">
                            <div class="form-row align-items-center mt-1">
                                <div class="col-1"><img class=" wid-30  mt-n3 pl-1 mr-2" src="{{ asset('images/sdi-iconos/cx-info.png') }}"></div>
                                <div class="col-10  ml-2 text-secondary">
                                    <h6 class="text-dark">Operaciones</h6>
                                    <ul class="listas_sidebar">

                                @if (isset($antecedentes_quirurgicos))
                                    @foreach ($antecedentes_quirurgicos as $antecedente_quirurgico)
                                        <li>{{ $antecedente_quirurgico->operacion }}</li>
                                    @endforeach
                                @else
                                    <li>Sin registro</li>
                                @endif

                            </ul>
                                </div>
                            </div>

                            <hr class="mt-n1 mb-2">
                            <div class="form-row align-items-center mt-1">
                                <div class="col-1"><img class=" wid-30 pl-1 mr-2" src="{{ asset('images/sdi-iconos/incidente-cx.png') }}"></div>
                                <div class="col-10  ml-2 text-secondary">
                                    <h6 class="text-dark">Incidentes Cirugía</h6>
                                    <ul class="listas_sidebar">

                                    </ul>
                                </div>
                            </div>
                            <hr class="mt-2 mb-2">
                            <div class="form-row align-items-center mt-1">
                                <div class="col-1"><img class=" wid-30 pl-1 mr-2" src="{{ asset('images/sdi-iconos/sangre-danger.png') }}"></div>
                                <div class="col-10  ml-2 text-secondary">
                                    <h6 class="text-dark">Grupo Sanguíneo</h6>
                                    <p>
                                        @if ($paciente->Antecedentes()->first() != null)
                                            @if ($paciente->Antecedentes()->first()->GrupoSanguineo()->first() != null)
                                                {{ $paciente->Antecedentes()->first()->GrupoSanguineo()->first()->nombre_gs }}
                                            @endif
                                        @else
                                            Sin registro
                                        @endif
                                    </p>
                                </div>
                            </div>

                            <hr class="mt-2 mb-2">
                            <div class="form-row align-items-center mt-1">
                                <div class="col-1"><img class=" wid-30 pl-1 mr-2" src="{{ asset('images/sdi-iconos/transfusion-info.png') }}"></div>
                                <div class="col-10  ml-2 text-secondary">
                                    <h6 class="text-dark">¿Acepta Transfusión de Sangre?</h6>
                                    <p >
                                         @if ($paciente->Antecedentes()->first() != null && $paciente->Antecedentes()->first()->transfusion == 1)
                                Si
                            @else
                                Sin registro
                            @endif
                                    </p>
                                </div>
                            </div>

                            <hr class="mt-2 mb-2">
                            <div class="form-row align-items-center mt-1 pb-3">
                                <div class="col-1"><img class=" wid-30 pl-1 mr-2" src="{{ asset('images/sdi-iconos/donante-info.png') }}"></div>
                                <div class="col-10  ml-2 text-secondary">
                                    <h6 class="text-dark">¿Donante de Órganos?</h6>
                                    <p>
                                        @if ($paciente->Antecedentes()->first() != null && $paciente->Antecedentes()->first()->dona_organos == 1)
                                Si
                            @else
                                Sin registro
                            @endif
                                    </p>
                                </div>
                            </div>




                    {{-- <div class="form-row mt-3 mb-5">
                        <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 text-center">
                            <button type="button" class="btn btn-primary-light-c btn-xxs"><i
                                    class="feather icon-edit"></i> Editar información</button>
                        </div>
                    </div> --}}
                </div>
            </div>

            {{-- <div class="card-sidebar">
                <div class="card-header-sidebar" id="headingThree">
                    <h2 class="mb-0">
                        <button class="btn btn-light btn-block text-left text-info collapsed" type="button"
                            data-toggle="collapse" data-target="#collapseCuatro" aria-expanded="false"
                            aria-controls="collapseThree"><i
                                class="feather icon-chevron-down float-right pt-1 flecha-accordion"></i>
                            PATOLOGÍAS CRÓNICAS
                        </button>
                    </h2>
                </div>
                <div id="collapseCuatro" class="collapse" aria-labelledby="headingThree"
                    data-parent="#accordionExample">
                    <div class="card-body-sidebar">
                        <div class="form-row pt-3">
                            <label class="col-4 text-dark font-weight-bolder">
                                ¿Es paciente crónico?
                            </label>
                            <div class="col-7 ml-2 text-secondary">
                                @if ($patoligias_cronicas != null && $patoligias_cronicas != '')
                                    Si
                                @else
                                    No
                                @endif
                            </div>
                        </div>
                        <hr class="mt-2">
                        <div class="form-row mt-1">
                            <label class="col-4 text-dark font-weight-bolder">
                                Patologías Crónicas
                            </label>
                            <div class="col-7 ml-2 text-secondary listas_sidebar">
                                <ul>
                                    @if (isset($patoligias_cronicas))
                                        @foreach ($patoligias_cronicas as $patoligia_cronica)
                                            <li>{{ $patoligia_cronica->nombre_patologia_cronica }}</li>
                                        @endforeach
                                    @else
                                        <li>Sin registro</li>
                                    @endif

                                </ul>
                            </div>
                        </div>
                        <div class="form-row mt-3 mb-5">
                            <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 text-center">
                                <button type="button" class="btn btn-primary-light-c btn-xxs"><i
                                        class="feather icon-edit"></i> Editar información</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div> --}}

            {{--  <div class="card-sidebar">
                <div class="card-header-sidebar" id="headingThree">
                    <h2 class="mb-0">
                        <button class="btn btn-light btn-block text-left collapsed" type="button"
                            data-toggle="collapse" data-target="#collapseCinco" aria-expanded="false"
                            aria-controls="collapseThree">
                            ATENCIONES MÉDICAS PREVIAS<i
                                class="feather icon-chevron-down float-right pt-1 flecha-accordion"></i>
                        </button>
                    </h2>
                </div>
                <div id="collapseCinco" class="collapse" aria-labelledby="headingThree"
                    data-parent="#accordionExample">
                    <div class="card-body-sidebar">

                        @if (isset($fichas))
                            @foreach ($fichas as $fic)
                                @if ($fic->Profesional()->first()->id == $profesional->id)
                                    <div class="row mr-2">
                                        <div class="col-sm-12 col-md-12 bg-light tarjeta-consultas shadow-sm rounded">
                                            <p class="pt-3 pl-2 text-secondary">
                                                {{ $fic->created_at }}<br>
                                                {{ $fic->Profesional()->first()->Especialidad()->first()->nombre }}<br>
                                                {{ $fic->Profesional()->first()->nombre .' ' .$fic->Profesional()->first()->apellido_uno .' ' .$fic->Profesional()->first()->apellido_dos }}
                                            </p>
                                        </div>
                                    </div>
                                @endif
                            @endforeach

                        @endif

                    </div>
                </div>
            </div>  --}}

        </div>
          <!------------------ANTECEDENTES FMU---------------------->
        <div class="card-sidebar">
            <div class="card-header-sidebar" id="headingFour">
                <h2 class="mb-0">
                    <button class="btn btn-light btn-block text-left collapsed text-info" type="button"
                        data-toggle="collapse" data-target="#collapseFour" aria-expanded="false"
                        aria-controls="collapseFour"><i
                            class="feather icon-chevron-down float-right pt-1 flecha-accordion"></i>
                        ANTECEDENTES VETERINARIOS PARA FVU
                    </button>
                </h2>
            </div>
            <div id="collapseFour" class="collapse" aria-labelledby="headingFour" data-parent="#accordionExample">
                <div class="card-body-sidebar">
                    @include('atencion_veterinaria.partials.llenar_antecedentes_fmu')

                </div>
            </div>

            {{-- <div class="card-sidebar">
                <div class="card-header-sidebar" id="headingThree">
                    <h2 class="mb-0">
                        <button class="btn btn-light btn-block text-left text-info collapsed" type="button"
                            data-toggle="collapse" data-target="#collapseCuatro" aria-expanded="false"
                            aria-controls="collapseThree"><i
                                class="feather icon-chevron-down float-right pt-1 flecha-accordion"></i>
                            PATOLOGÍAS CRÓNICAS
                        </button>
                    </h2>
                </div>
                <div id="collapseCuatro" class="collapse" aria-labelledby="headingThree"
                    data-parent="#accordionExample">
                    <div class="card-body-sidebar">
                        <div class="form-row pt-3">
                            <label class="col-4 text-dark font-weight-bolder">
                                ¿Es paciente crónico?
                            </label>
                            <div class="col-7 ml-2 text-secondary">
                                @if ($patoligias_cronicas != null && $patoligias_cronicas != '')
                                    Si
                                @else
                                    No
                                @endif
                            </div>
                        </div>
                        <hr class="mt-2">
                        <div class="form-row mt-1">
                            <label class="col-4 text-dark font-weight-bolder">
                                Patologías Crónicas
                            </label>
                            <div class="col-7 ml-2 text-secondary listas_sidebar">
                                <ul>
                                    @if (isset($patoligias_cronicas))
                                        @foreach ($patoligias_cronicas as $patoligia_cronica)
                                            <li>{{ $patoligia_cronica->nombre_patologia_cronica }}</li>
                                        @endforeach
                                    @else
                                        <li>Sin registro</li>
                                    @endif

                                </ul>
                            </div>
                        </div>
                        <div class="form-row mt-3 mb-5">
                            <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 text-center">
                                <button type="button" class="btn btn-primary-light-c btn-xxs"><i
                                        class="feather icon-edit"></i> Editar información</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div> --}}

            {{--  <div class="card-sidebar">
                <div class="card-header-sidebar" id="headingThree">
                    <h2 class="mb-0">
                        <button class="btn btn-light btn-block text-left collapsed" type="button"
                            data-toggle="collapse" data-target="#collapseCinco" aria-expanded="false"
                            aria-controls="collapseThree">
                            ATENCIONES MÉDICAS PREVIAS<i
                                class="feather icon-chevron-down float-right pt-1 flecha-accordion"></i>
                        </button>
                    </h2>
                </div>
                <div id="collapseCinco" class="collapse" aria-labelledby="headingThree"
                    data-parent="#accordionExample">
                    <div class="card-body-sidebar">

                        @if (isset($fichas))
                            @foreach ($fichas as $fic)
                                @if ($fic->Profesional()->first()->id == $profesional->id)
                                    <div class="row mr-2">
                                        <div class="col-sm-12 col-md-12 bg-light tarjeta-consultas shadow-sm rounded">
                                            <p class="pt-3 pl-2 text-secondary">
                                                {{ $fic->created_at }}<br>
                                                {{ $fic->Profesional()->first()->Especialidad()->first()->nombre }}<br>
                                                {{ $fic->Profesional()->first()->nombre .' ' .$fic->Profesional()->first()->apellido_uno .' ' .$fic->Profesional()->first()->apellido_dos }}
                                            </p>
                                        </div>
                                    </div>
                                @endif
                            @endforeach

                        @endif

                    </div>
                </div>
            </div>  --}}

        </div>
    </div>
</div>

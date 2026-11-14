                            @php
                                // En el modal "Agendar hora" del escritorio del tutor primero se elige la mascota,
                                // así que el resto de los pasos se corre un número.
                                $con_paso_mascota = !empty($con_paso_mascota);
                                $pr = $con_paso_mascota ? 1 : 0;
                                // Servicios del tutor (consulta, procedimiento, exámenes, peluquería, hotel y cementerio):
                                // los usa el modal del escritorio y también la página de reservar hora de cada mascota,
                                // donde la mascota ya viene elegida y no hay paso Mascota.
                                $flujo_tutor = $con_paso_mascota || !empty($flujo_tutor);
                            @endphp
                            <input type="hidden" name="select_tipo_agenda" id="select_tipo_agenda" value="1,2">

                            {{-- En el modal la mascota se elige en el primer paso, así que este recuadro no va --}}
                            @if(!empty($es_veterinaria_busqueda) && !$con_paso_mascota)
                                @php
                                    // Misma foto que muestra el menú lateral de la mascota
                                    $foto_base_mascota_reserva = asset('images/iconos/mascotas.svg');
                                    $foto_mascota_reserva = ($mascota->foto_url ?? null) ?: $foto_base_mascota_reserva;
                                @endphp
                                <!--Para quién es la hora; al elegir veterinario su nombre queda a la derecha-->
                                <div class="reserva-vet-profesional">
                                    <img class="reserva-vet-profesional-icono" id="reserva_hora_mascota_foto" src="{{ $foto_mascota_reserva }}" alt="Foto de la mascota" data-foto-base="{{ $foto_base_mascota_reserva }}" onerror="this.onerror=null;this.src='{{ $foto_base_mascota_reserva }}';">
                                    <div class="reserva-vet-profesional-datos">
                                        <span class="reserva-vet-profesional-etiqueta">Reservando hora para</span>
                                        <span class="reserva-vet-profesional-nombre" id="reserva_hora_mascota_nombre">{{ !empty($mascota) ? (\Illuminate\Support\Str::ucfirst(trim($mascota->nombre ?? '')) ?: '-') : 'Su mascota' }}</span>
                                    </div>
                                    <span class="reserva-vet-profesional-especialidad" id="reserva_hora_veterinario"></span>
                                </div>
                            @endif

                            <!--Indicador de pasos-->
                            <ol class="reserva-vet-pasos" id="reserva_hora_pasos">
                                @if($con_paso_mascota)
                                    <li class="reserva-vet-paso activo" data-paso="1">
                                        <button type="button" class="reserva-vet-paso-boton" onclick="irPasoReservaHora(1);" aria-current="step">
                                            <span class="reserva-vet-paso-numero">1</span>
                                            <span class="reserva-vet-paso-texto">Mascota</span>
                                        </button>
                                    </li>
                                @endif
                                <li class="reserva-vet-paso {{ $con_paso_mascota ? '' : 'activo' }}" data-paso="{{ 1 + $pr }}">
                                    <button type="button" class="reserva-vet-paso-boton" onclick="irPasoReservaHora({{ 1 + $pr }});" {!! $con_paso_mascota ? 'disabled' : 'aria-current="step"' !!}>
                                        <span class="reserva-vet-paso-numero">{{ 1 + $pr }}</span>
                                        <span class="reserva-vet-paso-texto">Búsqueda</span>
                                    </button>
                                </li>
                                <li class="reserva-vet-paso" data-paso="{{ 2 + $pr }}">
                                    <button type="button" class="reserva-vet-paso-boton" onclick="irPasoReservaHora({{ 2 + $pr }});" disabled>
                                        <span class="reserva-vet-paso-numero">{{ 2 + $pr }}</span>
                                        <span class="reserva-vet-paso-texto">{{ $flujo_tutor ? 'Resultado' : (!empty($es_veterinaria_busqueda) ? 'Veterinario' : 'Profesional') }}</span>
                                    </button>
                                </li>
                                <li class="reserva-vet-paso" data-paso="{{ 3 + $pr }}">
                                    <button type="button" class="reserva-vet-paso-boton" onclick="irPasoReservaHora({{ 3 + $pr }});" disabled>
                                        <span class="reserva-vet-paso-numero">{{ 3 + $pr }}</span>
                                        <span class="reserva-vet-paso-texto">Fecha y hora</span>
                                    </button>
                                </li>
                                <li class="reserva-vet-paso" data-paso="{{ 4 + $pr }}">
                                    <button type="button" class="reserva-vet-paso-boton" onclick="irPasoReservaHora({{ 4 + $pr }});" disabled>
                                        <span class="reserva-vet-paso-numero">{{ 4 + $pr }}</span>
                                        <span class="reserva-vet-paso-texto">Confirmar</span>
                                    </button>
                                </li>
                            </ol>

                            @if($con_paso_mascota)
                                @php
                                    $mascotas_paso_reserva = $mascotas ?? collect();
                                @endphp
                                <!--Paso 1: para qué mascota es la hora. Si no está registrada se agrega acá mismo-->
                                <div class="reserva-vet-panel activo" data-paso="1">
                                    <div class="reserva-vet-panel-encabezado">
                                        <h6>¿Para qué mascota necesita la hora?</h6>
                                        <p class="small text-muted mb-0">Elija una de sus mascotas o registre una nueva para continuar con la reserva.</p>
                                    </div>

                                    <!--Una mascota ya registrada se elige en el select; una nueva se agrega con la tarjeta de al lado-->
                                    <div class="reserva-vet-mascota-eleccion mt-3">
                                        <div class="form-group mb-0 reserva-vet-mascota-select {{ $mascotas_paso_reserva->isEmpty() ? 'd-none' : '' }}">
                                            <label class="floating-label-activo-sm" for="reserva_hora_id_mascota">Mis mascotas</label>
                                            <select class="form-control form-control-sm" id="reserva_hora_id_mascota" onchange="elegirMascotaReservaHora(this);">
                                                <option value="">Seleccione</option>
                                                @foreach($mascotas_paso_reserva as $mascota_paso_reserva)
                                                    @php
                                                        $especie_paso_reserva = optional($mascota_paso_reserva->especieMascota)->nombre ?: $mascota_paso_reserva->otra_especie;
                                                    @endphp
                                                    <option value="{{ $mascota_paso_reserva->id }}" data-nombre="{{ $mascota_paso_reserva->nombre }}">{{ $mascota_paso_reserva->nombre }}{{ $especie_paso_reserva ? ' — '.$especie_paso_reserva : '' }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <span class="reserva-vet-mascota-o {{ $mascotas_paso_reserva->isEmpty() ? 'd-none' : '' }}" aria-hidden="true">o</span>

                                        <button type="button" class="reserva-vet-lugar-caja reserva-vet-mascota-nueva-boton" id="btn_mascota_nueva_reserva" onclick="abrirMascotaNuevaReservaHora();" aria-pressed="false" aria-controls="reserva_hora_mascota_nueva">
                                            <span class="reserva-vet-lugar-icono"><i class="feather icon-plus"></i></span>
                                            <span class="reserva-vet-lugar-datos">
                                                <span class="reserva-vet-lugar-nombre">Mascota nueva</span>
                                                <span class="reserva-vet-lugar-direccion">{{ $mascotas_paso_reserva->isEmpty() ? 'Aún no tiene mascotas registradas. Agréguela para reservar.' : 'Agendar para una mascota que aún no está registrada' }}</span>
                                            </span>
                                            <span class="reserva-vet-lugar-marca" aria-hidden="true"><i class="feather icon-check"></i></span>
                                        </button>
                                    </div>

                                    <!--Registro rápido: solo lo necesario para agendar; el resto se completa después en Mis mascotas-->
                                    <div class="reserva-vet-mascota-nueva" id="reserva_hora_mascota_nueva" style="display:none;">
                                        <h6 class="titulo-item mt-0">Nueva mascota</h6>
                                        <div class="form-row">
                                            <div class="col-sm-12 col-md-6">
                                                <div class="form-group">
                                                    <label class="floating-label-activo-sm" for="reserva_mascota_nombre">Nombre</label>
                                                    <input type="text" class="form-control form-control-sm" id="reserva_mascota_nombre" maxlength="255">
                                                </div>
                                            </div>
                                            <div class="col-sm-12 col-md-6">
                                                <div class="form-group">
                                                    <label class="floating-label-activo-sm" for="reserva_mascota_especie">Especie</label>
                                                    <select class="form-control form-control-sm" id="reserva_mascota_especie" onchange="cambiarEspecieMascotaReservaHora();" data-buscador-especie>
                                                        <option value="">Seleccione</option>
                                                        <option value="canina">Canino</option>
                                                        <option value="felina">Felino</option>
                                                        <option value="otra">Otro</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-sm-12 col-md-6" id="reserva_mascota_otra_especie_caja" style="display:none;">
                                                <div class="form-group">
                                                    <label class="floating-label-activo-sm" for="reserva_mascota_otra_especie">¿Cuál es la especie?</label>
                                                    <input type="text" class="form-control form-control-sm" id="reserva_mascota_otra_especie" maxlength="500" placeholder="Ej: conejo, hámster, tortuga">
                                                </div>
                                            </div>
                                            <div class="col-sm-12 col-md-6" id="reserva_mascota_raza_caja" style="display:none;">
                                                <div class="form-group">
                                                    <label class="floating-label-activo-sm" for="reserva_mascota_raza">Raza</label>
                                                    <select class="form-control form-control-sm" id="reserva_mascota_raza" data-buscador-raza data-especie="#reserva_mascota_especie">
                                                        <option value="">Mixto</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-sm-12 col-md-6">
                                                <div class="form-group">
                                                    <label class="floating-label-activo-sm" for="reserva_mascota_sexo">Sexo</label>
                                                    <select class="form-control form-control-sm" id="reserva_mascota_sexo">
                                                        <option value="">Seleccione</option>
                                                        <option value="M">Macho</option>
                                                        <option value="F">Hembra</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="reserva-vet-acciones">
                                            <button type="button" class="btn btn-outline-secondary reserva-vet-volver" onclick="cancelarMascotaNuevaReservaHora();">Cancelar</button>
                                            <button type="button" class="btn btn-info" id="btn_guardar_mascota_reserva" onclick="guardarMascotaNuevaReservaHora();">
                                                <i class="feather icon-save"></i> Guardar mascota
                                            </button>
                                        </div>
                                    </div>

                                    <div class="reserva-vet-acciones" id="reserva_hora_mascota_acciones">
                                        <button type="button" class="btn btn-info" id="btn_continuar_mascota_reserva" onclick="continuarMascotaReservaHora();" disabled>
                                            Continuar <i class="feather icon-chevron-right"></i>
                                        </button>
                                    </div>
                                </div>
                            @endif

                            <!--Paso de búsqueda: cómo buscar-->
                            <div class="reserva-vet-panel {{ $con_paso_mascota ? '' : 'activo' }}" data-paso="{{ 1 + $pr }}">
                                <div class="reserva-vet-panel-encabezado">
                                    <h6>¿Cómo quiere buscar su hora?</h6>
                                    <p class="small text-muted mb-0">Elija una forma de búsqueda y complete los datos.</p>
                                </div>

                                <!--Las tres formas de buscar son pestañas con el aspecto de las tarjetas de lugar-->
                                <ul class="nav reserva-vet-lugares" id="Buscadores" role="tablist">
                                    <li class="nav-item reserva-vet-lugar">
                                        <button type="button" class="nav-link reserva-vet-lugar-caja active" onclick="$('#select_tipo_agenda').val(1);$('#div_resultado_busqueda').html('')" id="buscar_especialidad-tab" data-toggle="tab" data-target="#buscar_especialidad" role="tab" aria-controls="buscar_especialidad" aria-selected="true">
                                            <span class="reserva-vet-lugar-icono"><i class="feather icon-heart"></i></span>
                                            <span class="reserva-vet-lugar-datos">
                                                <span class="reserva-vet-lugar-nombre">{{ $flujo_tutor ? 'Servicio' : (!empty($es_veterinaria_busqueda) ? 'Servicio veterinario' : 'Especialidad') }}</span>
                                                <span class="reserva-vet-lugar-direccion">{{ $flujo_tutor ? 'Consulta, procedimientos, peluquería y más' : (!empty($es_veterinaria_busqueda) ? 'Por servicio y motivo de consulta' : 'Por profesión y especialidad') }}</span>
                                            </span>
                                            <span class="reserva-vet-lugar-marca" aria-hidden="true"><i class="feather icon-check"></i></span>
                                        </button>
                                    </li>
                                    <li class="nav-item reserva-vet-lugar">
                                        <button type="button" class="nav-link reserva-vet-lugar-caja" onclick="$('#select_tipo_agenda').val(1);$('#div_resultado_busqueda').html('');setTimeout(function(){ $('#buscar_profesional_dato_profesional').trigger('focus'); }, 150)" id="buscar_profesional-tab" data-toggle="tab" data-target="#buscar_profesional" role="tab" aria-controls="buscar_profesional" aria-selected="false">
                                            <span class="reserva-vet-lugar-icono"><i class="feather icon-user"></i></span>
                                            <span class="reserva-vet-lugar-datos">
                                                <span class="reserva-vet-lugar-nombre">{{ !empty($es_veterinaria_busqueda) ? 'Veterinario/a' : 'Profesional' }}</span>
                                                <span class="reserva-vet-lugar-direccion">Por nombre o RUT</span>
                                            </span>
                                            <span class="reserva-vet-lugar-marca" aria-hidden="true"><i class="feather icon-check"></i></span>
                                        </button>
                                    </li>
                                    <li class="nav-item reserva-vet-lugar">
                                        <button type="button" class="nav-link reserva-vet-lugar-caja" onclick="$('#select_tipo_agenda').val(3);$('#div_resultado_busqueda').html('')" id="buscar_videoconsulta-tab" data-toggle="tab" data-target="#buscar_videoconsulta" role="tab" aria-controls="buscar_videoconsulta" aria-selected="false">
                                            <span class="reserva-vet-lugar-icono"><i class="feather icon-video"></i></span>
                                            <span class="reserva-vet-lugar-datos">
                                                <span class="reserva-vet-lugar-nombre">{{ !empty($es_veterinaria_busqueda) ? 'Videoconsulta veterinaria' : 'Videoconsulta' }}</span>
                                                <span class="reserva-vet-lugar-direccion">Atención a distancia</span>
                                            </span>
                                            <span class="reserva-vet-lugar-marca" aria-hidden="true"><i class="feather icon-check"></i></span>
                                        </button>
                                    </li>
                                </ul>

                                <div class="tab-content" id="BuscadoresContent">

                                    <!--Hora por especialidad-->
                                    <div class="tab-pane fade show active" id="buscar_especialidad" role="tabpanel" aria-labelledby="buscar_especialidad-tab">
                                        {{--  <form>  --}}
                                            <p class="small text-muted mb-4">{{ !empty($es_veterinaria_busqueda) ? 'Seleccione el servicio y la prestación veterinaria que necesita su mascota.' : 'Ingrese los datos solicitados para buscar horas por especialidad.' }}</p>
                                            <div class="form-row">
                                                <div class="col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label class="floating-label-activo-sm">{{ !empty($es_veterinaria_busqueda) ? 'Servicio' : 'Profesión' }}</label>
                                                        <select class="form-control form-control-sm" name="buscar_especialidad_profesion" id="buscar_especialidad_profesion" onchange="buscar_tipo_especialidad(this);">
                                                            <option value="">Seleccione</option>

                                                            @if(!empty($es_veterinaria_busqueda))
                                                                @foreach(($catalogo_veterinario_busqueda ?? []) as $area)
                                                                    <option value="{{ $area['slug'] }}">{{ $area['nombre'] }}</option>
                                                                @endforeach
                                                            @elseif(isset($profesiones))
                                                                @foreach($profesiones as $pro_key => $pro)
                                                                @php
                                                                    $selected_id1 = '';
                                                                    if((int)$filtros['id_profesion']==$pro->id && (int)$filtros['id_profesion']>0)
                                                                    $selected_id1 = 'selected';
                                                                @endphp
                                                                    <option value="{{ $pro->id }}" {{$selected_id1}}>{{ $pro->nombre }}</option>
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-sm-12 col-md-6 reserva-campo-veterinario">
                                                    <div class="form-group">
                                                        <label class="floating-label-activo-sm" id="label_buscar_especialidad_especialidad">{{ !empty($es_veterinaria_busqueda) ? 'Procedimiento / Exámenes' : 'Especialidad' }}</label>
                                                        <select class="form-control form-control-sm" name="buscar_especialidad_especialidad" id="buscar_especialidad_especialidad">
                                                            <option value="">Seleccione</option>
                                                            @if(!empty($es_veterinaria_busqueda))
                                                            @elseif(isset($especialidades))
                                                                @foreach($especialidades as $esp_key => $esp)
                                                                @php
                                                                    $selected_id2 = '';
                                                                    if($filtros['id_especialidad']==$esp->id && $filtros['id_especialidad']!=0)
                                                                    $selected_id2 = 'selected';
                                                                @endphp
                                                                    <option value="{{ $esp->id }}" {{$selected_id2}}>{{ $esp->nombre }}</option>
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-sm-12 col-md-6 reserva-campo-veterinario">
                                                    <div class="form-group">
                                                        <label class="floating-label-activo-sm">Región</label>
                                                        <select class="form-control form-control-sm" name="buscar_especialidad_region" id="buscar_especialidad_region" onchange="buscar_ciudad(this);">
                                                            <option value="">Seleccione</option>
                                                            @foreach($regiones as $reg_key => $reg)
                                                                <option value="{{ $reg->id }}">{{ $reg->nombre }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-sm-12 col-md-6 reserva-campo-veterinario">
                                                    <div class="form-group">
                                                        <label class="floating-label-activo-sm">Comuna</label>
                                                        <select class="form-control form-control-sm" name="buscar_especialidad_comuna" id="buscar_especialidad_comuna">
                                                            <option value="">Seleccione</option>
                                                            @foreach($ciudades as $ciu_key => $ciu)
                                                                <option value="{{ $ciu->id }}">{{ $ciu->nombre }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>

                                                {{-- <div class="col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <div class="switch switch-success d-inline m-r-10">
                                                            <input type="checkbox" id="buscar_especialidad_hora24" value="1">
                                                            <label for="buscar_especialidad_hora24" class="cr"></label>
                                                        </div>
                                                        <label><strong>Buscar horas para las próx. 24 hrs</strong></label>
                                                    </div>
                                                </div> --}}
                                            </div>
                                            @if($flujo_tutor)
                                                @include('app.paciente.partials.reserva_servicios_tutor')
                                            @endif
                                            <div class="reserva-vet-acciones" id="reserva_acciones_veterinario">
                                                @if($con_paso_mascota)
                                                    <button type="button" class="btn btn-outline-secondary reserva-vet-volver" onclick="irPasoReservaHora(1);">
                                                        <i class="feather icon-chevron-left"></i> Anterior
                                                    </button>
                                                @endif
                                                <button class="btn btn-info" type="button" id="btn_buscar_especialidad" onclick="{{ $flujo_tutor ? 'buscarSegunServicioReserva();' : 'buscar_profesional_especialidad();' }}"><i class="feather icon-search"></i> Buscar horas</button>
                                            </div>
                                        {{--  </form>  --}}
                                    </div>

                                    <!--Hora por profesional-->
                                    <div class="tab-pane fade" id="buscar_profesional" role="tabpanel" aria-labelledby="buscar_profesional-tab">
                                        <form onsubmit="event.preventDefault(); buscar_profesional_profesional();">
                                            <p class="small text-muted mb-4">{{ !empty($es_veterinaria_busqueda) ? 'Ingrese nombre o RUT del veterinario/a. Región y comuna son opcionales para acotar la búsqueda.' : 'Ingrese los datos solicitados para buscar horas por profesional.' }}</p>
                                            <div class="form-row">
                                                <div class="col-sm-12 col-md-12">
                                                    <div class="form-group">
                                                        <label class="floating-label-activo-sm">{{ !empty($es_veterinaria_busqueda) ? 'Nombre o RUT del veterinario/a' : 'Nombre o RUT del profesional' }}</label>
                                                        <input type="text" class="form-control form-control-sm" name="buscar_profesional_dato_profesional" id="buscar_profesional_dato_profesional">
                                                    </div>
                                                </div>
                                                <div class="col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label class="floating-label-activo-sm">Región</label>
                                                        <select class="form-control form-control-sm" name="buscar_profesional_region" id="buscar_profesional_region" onchange="buscar_ciudad(this);">
                                                            <option value="">Seleccione</option>
                                                            @foreach($regiones as $reg_key => $reg)
                                                                <option value="{{ $reg->id }}">{{ $reg->nombre }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label class="floating-label-activo-sm">Comuna</label>
                                                        <select class="form-control form-control-sm" name="buscar_profesional_comuna" id="buscar_profesional_comuna">
                                                            <option value="">Seleccione</option>
                                                            @foreach($ciudades as $ciu_key => $ciu)
                                                                <option value="{{ $ciu->id }}">{{ $ciu->nombre }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>

                                                {{-- <div class="col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <div class="switch switch-success d-inline m-r-10">
                                                            <input type="checkbox" id="buscar_profesional_hora24" value="1">
                                                            <label for="buscar_profesional_hora24" class="cr"></label>
                                                        </div>
                                                        <label><strong>Buscar horas para las próx. 24 hrs</strong></label>
                                                    </div>
                                                </div> --}}
                                            </div>
                                            <div class="reserva-vet-acciones">
                                                @if($con_paso_mascota)
                                                    <button type="button" class="btn btn-outline-secondary reserva-vet-volver" onclick="irPasoReservaHora(1);">
                                                        <i class="feather icon-chevron-left"></i> Anterior
                                                    </button>
                                                @endif
                                                <button class="btn btn-info" type="button" onclick="buscar_profesional_profesional();"><i class="feather icon-search"></i> Buscar horas</button>
                                            </div>
                                        </form>
                                    </div>

                                    <!--Hora por videoconsulta-->
                                    <div class="tab-pane fade" id="buscar_videoconsulta" role="tabpanel" aria-labelledby="buscar_videoconsulta-tab">
                                        {{--  <form>  --}}
                                            <p class="small text-muted mb-4">{{ !empty($es_veterinaria_busqueda) ? 'Seleccione el servicio y la prestación para buscar horas de videoconsulta veterinaria.' : 'Ingrese los datos solicitados para buscar horas por especialidad.' }}</p>
                                            <div class="form-row">
                                                <div class="col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label class="floating-label-activo-sm">{{ !empty($es_veterinaria_busqueda) ? 'Servicio' : 'Profesión' }}</label>
                                                        <select class="form-control form-control-sm" name="buscar_videoconsulta_profesion" id="buscar_videoconsulta_profesion" onchange="buscar_tipo_especialidad(this);">
                                                            <option value="">Seleccione</option>

                                                            @if(!empty($es_veterinaria_busqueda))
                                                                @foreach(($catalogo_videoconsulta_busqueda ?? $catalogo_veterinario_busqueda ?? []) as $area)
                                                                    <option value="{{ $area['slug'] }}">{{ $area['nombre'] }}</option>
                                                                @endforeach
                                                            @elseif(isset($profesiones))
                                                                @foreach($profesiones as $pro_key => $pro)
                                                                @php
                                                                    $selected_id1 = '';
                                                                    if((int)$filtros['id_profesion']==$pro->id && (int)$filtros['id_profesion']>0)
                                                                    $selected_id1 = 'selected';
                                                                @endphp
                                                                    <option value="{{ $pro->id }}" {{$selected_id1}}>{{ $pro->nombre }}</option>
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label class="floating-label-activo-sm" id="label_buscar_videoconsulta_especialidad">{{ !empty($es_veterinaria_busqueda) ? 'Prestación veterinaria' : 'Especialidad' }}</label>
                                                        <select class="form-control form-control-sm" name="buscar_videoconsulta_especialidad" id="buscar_videoconsulta_especialidad">
                                                            <option value="">Seleccione</option>
                                                            @if(!empty($es_veterinaria_busqueda))
                                                            @elseif(isset($especialidades))
                                                                @foreach($especialidades as $esp_key => $esp)
                                                                @php
                                                                    $selected_id2 = '';
                                                                    if($filtros['id_especialidad']==$esp->id && $filtros['id_especialidad']!=0)
                                                                    $selected_id2 = 'selected';
                                                                @endphp
                                                                    <option value="{{ $esp->id }}" {{$selected_id2}}>{{ $esp->nombre }}</option>
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                    </div>
                                                </div>
                                                {{-- <div class="col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <label class="floating-label-activo-sm">Previsión</label>
                                                        <select class="form-control form-control-sm" name="prevision" id="buscar_videoconsulta_prevision">
                                                            <option value="">Seleccione</option>
                                                        </select>
                                                    </div>
                                                </div> --}}

                                                {{-- <div class="col-sm-12 col-md-6">
                                                    <div class="form-group">
                                                        <div class="switch switch-success d-inline m-r-10">
                                                            <input type="checkbox" id="buscar_videoconsulta_hora24" value="1">
                                                            <label for="buscar_videoconsulta_hora24" class="cr"></label>
                                                        </div>
                                                        <label><strong>Buscar horas para las próx. 24 hrs</strong></label>
                                                    </div>
                                                </div> --}}
                                            </div>
                                            <div class="reserva-vet-acciones">
                                                @if($con_paso_mascota)
                                                    <button type="button" class="btn btn-outline-secondary reserva-vet-volver" onclick="irPasoReservaHora(1);">
                                                        <i class="feather icon-chevron-left"></i> Anterior
                                                    </button>
                                                @endif
                                                <button class="btn btn-info" type="button" id="btn_buscar_especialidad_video_consulta" onclick="buscar_profesional_especialidad_video_consulta();"><i class="feather icon-search"></i> Buscar horas</button>
                                            </div>
                                        {{--  </form>  --}}
                                    </div>
                                </div>
                            </div>

                            <!--Paso de resultados de la búsqueda-->
                            <div class="reserva-vet-panel" data-paso="{{ 2 + $pr }}">
                                <div class="reserva-vet-panel-encabezado">
                                    <h6 id="reserva_resultado_titulo">{{ !empty($es_veterinaria_busqueda) ? 'Elija un veterinario' : 'Elija un profesional' }}</h6>
                                    <p class="small text-muted mb-0" id="reserva_resultado_detalle">Cada tarjeta muestra la próxima hora disponible. Puede revisar la ficha antes de reservar.</p>
                                </div>

                                <!--Las tarjetas se llenan al buscar-->
                                <div class="reserva-vet-veterinarios" id="div_resultado_busqueda"></div>

                                <div class="reserva-vet-acciones">
                                    <button type="button" class="btn btn-outline-secondary reserva-vet-volver" onclick="irPasoReservaHora({{ 1 + $pr }});">
                                        <i class="feather icon-chevron-left"></i> Cambiar búsqueda
                                    </button>
                                </div>
                            </div>

                            <!--Pasos de fecha y hora, y confirmación-->
                            @include("app.general.buscador_profesionales.modals.reservar_hora")

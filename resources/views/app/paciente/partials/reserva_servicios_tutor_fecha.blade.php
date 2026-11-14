{{-- Modal "Reservar hora" del escritorio del tutor, paso de fecha: lo que se completa después de elegir
     una peluquería o un hotel en el paso de resultados (ver reserva_servicios_tutor_script). --}}
@php
    $paso_resultado_servicio = 2 + ($pr ?? 0);
@endphp

<!--Peluquería / grooming: servicios con su valor, día y hora según la agenda de la peluquería-->
<div class="reserva-servicio-fecha" id="reserva_fecha_peluqueria" style="display:none;">
    <div class="reserva-vet-panel-encabezado">
        <h6 id="peluqueria_titulo">Servicios, día y hora</h6>
        <p class="small text-muted mb-0" id="peluqueria_detalle"></p>
    </div>

    <h6 class="titulo-item">Servicios</h6>
    <p class="small text-muted mb-3">Puede elegir más de uno. El valor final lo confirma la peluquería según su mascota.</p>
    <div class="reserva-vet-lugares" id="peluqueria_servicios"></div>
    <p class="reserva-servicio-total" id="peluqueria_total"></p>

    <div id="peluqueria_bloque_fecha" style="display:none;">
        <h6 class="titulo-item">Día y hora</h6>
        <div class="row">
            <div class="col-lg-6 mb-3 mb-lg-0">
                <div class="reserva-vet-calendario">
                    <input class="d-none" type="date" id="peluqueria_fecha" tabindex="-1" aria-hidden="true" data-fecha-nativa>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="reserva-vet-horas-caja">
                    <div class="reserva-vet-horas-cabecera">
                        <div class="reserva-vet-horas-titulo">Horas disponibles</div>
                    </div>
                    <div class="reserva-vet-horas-fecha" id="peluqueria_fecha_texto"></div>
                    <div class="reserva-vet-horas" aria-live="polite">
                        <div class="reserva-vet-horas-grilla" id="peluqueria_horas"></div>
                        <div class="reserva-vet-horas-vacio">
                            <i class="feather icon-clock"></i>
                            <span>Las horas aparecerán aquí cuando elija un día en el calendario.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group mt-4">
            <label class="floating-label-activo-sm" for="peluqueria_observaciones">Indicaciones para la peluquería</label>
            <input type="text" class="form-control form-control-sm" id="peluqueria_observaciones" maxlength="3000" placeholder="Ej: es nervioso con el secador">
        </div>
    </div>

    <div class="reserva-vet-acciones">
        <button type="button" class="btn btn-outline-secondary reserva-vet-volver" onclick="irPasoReservaHora({{ $paso_resultado_servicio }});">
            <i class="feather icon-chevron-left"></i> Anterior
        </button>
        <button type="button" class="btn btn-info" id="btn_reservar_peluqueria" onclick="reservarPeluqueriaTutor();" disabled>
            <i class="feather icon-check"></i> Reservar peluquería
        </button>
    </div>
</div>

<!--Hotel: alojamiento o cuidado diurno y sus fechas. Se envía como solicitud al hotel-->
<div class="reserva-servicio-fecha" id="reserva_fecha_hotel" style="display:none;">
    <div class="reserva-vet-panel-encabezado">
        <h6 id="hotel_titulo">Tipo de estadía y fechas</h6>
        <p class="small text-muted mb-0" id="hotel_detalle"></p>
    </div>

    <h6 class="titulo-item">Tipo de estadía</h6>
    <div class="reserva-vet-lugares" id="hotel_servicios"></div>

    <!--Alojamiento: fecha de ingreso y salida-->
    <div id="hotel_bloque_alojamiento" style="display:none;">
        <div class="form-row">
            <div class="col-sm-12 col-md-6">
                <div class="form-group">
                    <label class="floating-label-activo-sm" for="hotel_ingreso">Fecha de ingreso</label>
                    <input type="date" class="form-control form-control-sm" id="hotel_ingreso" min="{{ date('Y-m-d') }}">
                </div>
            </div>
            <div class="col-sm-12 col-md-6">
                <div class="form-group">
                    <label class="floating-label-activo-sm" for="hotel_salida">Fecha de salida</label>
                    <input type="date" class="form-control form-control-sm" id="hotel_salida" min="{{ date('Y-m-d') }}">
                </div>
            </div>
        </div>
    </div>

    <!--Cuidado diurno: plan por semana o por mes, como lo ofrece el hotel-->
    <div id="hotel_bloque_guarderia" style="display:none;">
        <div class="form-row">
            <div class="col-sm-12 col-md-4">
                <div class="form-group">
                    <label class="floating-label-activo-sm" for="hotel_guarderia_inicio">Fecha de inicio</label>
                    <input type="date" class="form-control form-control-sm" id="hotel_guarderia_inicio" min="{{ date('Y-m-d') }}">
                </div>
            </div>
            <div class="col-sm-6 col-md-4">
                <div class="form-group">
                    <label class="floating-label-activo-sm" for="hotel_guarderia_periodo">Plan</label>
                    <select class="form-control form-control-sm" id="hotel_guarderia_periodo">
                        <option value="semana">Por semana</option>
                        <option value="mes">Por mes</option>
                    </select>
                </div>
            </div>
            <div class="col-sm-6 col-md-4">
                <div class="form-group">
                    <label class="floating-label-activo-sm" for="hotel_guarderia_cantidad">Cantidad</label>
                    <select class="form-control form-control-sm" id="hotel_guarderia_cantidad">
                        @for($cantidad = 1; $cantidad <= 12; $cantidad++)
                            <option value="{{ $cantidad }}">{{ $cantidad }}</option>
                        @endfor
                    </select>
                </div>
            </div>
        </div>
        <p class="small text-muted mb-3" id="hotel_guarderia_dias"></p>
    </div>

    <div class="form-group" id="hotel_bloque_indicaciones" style="display:none;">
        <label class="floating-label-activo-sm" for="hotel_indicaciones">Indicaciones para el hotel</label>
        <input type="text" class="form-control form-control-sm" id="hotel_indicaciones" maxlength="2000" placeholder="Ej: alimentación, medicamentos, horarios">
    </div>

    <div class="reserva-vet-acciones">
        <button type="button" class="btn btn-outline-secondary reserva-vet-volver" onclick="irPasoReservaHora({{ $paso_resultado_servicio }});">
            <i class="feather icon-chevron-left"></i> Anterior
        </button>
        <button type="button" class="btn btn-info" id="btn_reservar_hotel" onclick="reservarHotelTutor();" disabled>
            <i class="feather icon-check"></i> Solicitar reserva
        </button>
    </div>
</div>

{{-- Estado de una reserva y acceso a su ficha; lo abre peluqueria-agenda.js al confirmar una hora o al pulsar una atención del calendario.
     El estado del pago viene de la cita guardada; las atenciones anteriores a la agenda nueva lo muestran sin registrar. --}}
<div id="modal_estado_reserva" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="titulo_modal_estado_reserva" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header modal-header-purple">
                <h5 class="modal-title" id="titulo_modal_estado_reserva">Estado de la reserva</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="reserva-vet-resumen">
                    <div class="reserva-vet-resumen-cita">
                        <span class="reserva-vet-resumen-cita-icono"><i class="feather icon-calendar"></i></span>
                        <div class="reserva-vet-resumen-cita-datos">
                            <span class="reserva-vet-resumen-fecha" id="estado_reserva_fecha">-</span>
                            <span class="reserva-vet-resumen-hora"><i class="feather icon-clock"></i><span id="estado_reserva_hora">-</span></span>
                        </div>
                    </div>
                    <ul class="reserva-vet-detalle">
                        <li>
                            <span class="reserva-vet-detalle-icono"><i class="fas fa-paw"></i></span>
                            <div>
                                <span class="reserva-vet-detalle-titulo">Mascota</span>
                                <span class="reserva-vet-detalle-valor" id="estado_reserva_mascota">-</span>
                                <span class="reserva-vet-detalle-extra" id="estado_reserva_tutor"></span>
                            </div>
                        </li>
                        <li>
                            <span class="reserva-vet-detalle-icono"><i class="feather icon-scissors"></i></span>
                            <div>
                                <span class="reserva-vet-detalle-titulo">Servicio</span>
                                <span class="reserva-vet-detalle-valor" id="estado_reserva_servicio">-</span>
                            </div>
                        </li>
                        <li>
                            <span class="reserva-vet-detalle-icono"><i class="feather icon-credit-card"></i></span>
                            <div>
                                <span class="reserva-vet-detalle-titulo">Estado del pago</span>
                                <span class="reserva-vet-detalle-valor"><span class="badge" id="estado_reserva_pago">-</span></span>
                                <span class="reserva-vet-detalle-extra" id="estado_reserva_pago_detalle"></span>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer reserva-vet-acciones">
                <button type="button" class="btn btn-outline-dark reserva-vet-volver" data-dismiss="modal"><i class="feather icon-x mr-1"></i>Cerrar</button>
                <a class="btn btn-info" href="#" id="estado_reserva_ficha"><i class="feather icon-file-text mr-1"></i>Ir a la ficha para atender</a>
            </div>
        </div>
    </div>
</div>

{{-- Modal "Reservar hora" del escritorio del tutor, paso de búsqueda: servicios sin búsqueda todavía.
     Peluquería y hotel se buscan con región y comuna; sus resultados y su formulario van en los pasos siguientes. --}}

<!--Cementerio / cremación: todavía sin reserva desde aquí-->
<div class="reserva-servicio-especial" id="reserva_servicio_cementerio" style="display:none;">
    <div class="reserva-vet-lugar-vacio">
        <i class="feather icon-info"></i>
        <span>Muy pronto podrá reservar cementerio y cremación desde aquí.</span>
    </div>

    {{-- "Anterior" vuelve al paso Mascota; en la página de la mascota la búsqueda ya es el primer paso --}}
    @if(!empty($con_paso_mascota))
        <div class="reserva-vet-acciones">
            <button type="button" class="btn btn-outline-secondary reserva-vet-volver" onclick="irPasoReservaHora(1);">
                <i class="feather icon-chevron-left"></i> Anterior
            </button>
        </div>
    @endif
</div>

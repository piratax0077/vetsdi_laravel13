<!--BOTON-->
<button type="button" class="btn btn-agenda btn-warning d-inline float-right mb-2 mt-0 ml-2 mr-3" onclick="abrir_simbologia_agenda();" data-toggle="tooltip" data-placement="top" title="Significado de estados de agenda"><i class="fas fa-info"></i></button>
<!--MODAL-->
<div class="modal fade" id="modal_simbologia_agenda" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="modal_simbologia_agenda" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header modal-header-purple">
                <h5 class="modal-title">Significado de los estados de la agenda</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar" onclick="$('#modal_simbologia_agenda').modal('hide');">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <ul class="simbologia-agenda-lista">
                    <li class="simbologia-agenda-item simbologia-agenda-item--pre-reservada">
                        <span class="simbologia-agenda-punto"></span>
                        <span class="simbologia-agenda-texto">
                            <span class="simbologia-agenda-nombre">Hora pre reservada</span>
                            <span class="simbologia-agenda-detalle">Reserva en proceso, aún sin completar</span>
                        </span>
                    </li>
                    <li class="simbologia-agenda-item simbologia-agenda-item--reservada">
                        <span class="simbologia-agenda-punto"></span>
                        <span class="simbologia-agenda-texto">
                            <span class="simbologia-agenda-nombre">Hora reservada</span>
                            <span class="simbologia-agenda-detalle">Hora tomada, falta confirmarla</span>
                        </span>
                    </li>
                    <li class="simbologia-agenda-item simbologia-agenda-item--confirmada">
                        <span class="simbologia-agenda-punto"></span>
                        <span class="simbologia-agenda-texto">
                            <span class="simbologia-agenda-nombre">Hora confirmada</span>
                            <span class="simbologia-agenda-detalle">Asistencia confirmada</span>
                        </span>
                    </li>
                    <li class="simbologia-agenda-item simbologia-agenda-item--en-espera">
                        <span class="simbologia-agenda-punto"></span>
                        <span class="simbologia-agenda-texto">
                            <span class="simbologia-agenda-nombre">En espera de atención</span>
                            <span class="simbologia-agenda-detalle">El paciente ya llegó</span>
                        </span>
                    </li>
                    <li class="simbologia-agenda-item simbologia-agenda-item--realizando">
                        <span class="simbologia-agenda-punto"></span>
                        <span class="simbologia-agenda-texto">
                            <span class="simbologia-agenda-nombre">Realizando atención</span>
                            <span class="simbologia-agenda-detalle">Atención en curso</span>
                        </span>
                    </li>
                    <li class="simbologia-agenda-item simbologia-agenda-item--realizada">
                        <span class="simbologia-agenda-punto"></span>
                        <span class="simbologia-agenda-texto">
                            <span class="simbologia-agenda-nombre">Atención realizada</span>
                            <span class="simbologia-agenda-detalle">Atención finalizada</span>
                        </span>
                    </li>
                    <li class="simbologia-agenda-item simbologia-agenda-item--no-disponible">
                        <span class="simbologia-agenda-punto"></span>
                        <span class="simbologia-agenda-texto">
                            <span class="simbologia-agenda-nombre">Horario no disponible</span>
                            <span class="simbologia-agenda-detalle">Fuera del horario de atención</span>
                        </span>
                    </li>
                    <li class="simbologia-agenda-item simbologia-agenda-item--bloqueado">
                        <span class="simbologia-agenda-punto"></span>
                        <span class="simbologia-agenda-texto">
                            <span class="simbologia-agenda-nombre">Horario bloqueado</span>
                            <span class="simbologia-agenda-detalle">Bloqueado por el profesional</span>
                        </span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
      /**CIERRE MODAL**/
    function abrir_simbologia_agenda()
    {
        $('#modal_simbologia_agenda').modal('show');
    }
    function cerrar_simbologia_agenda() {
        $('#modal_simbologia_agenda').modal ('hide');
      }
    /**CIERRE: CIERRE MODAL**/

</script>





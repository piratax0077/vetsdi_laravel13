<!--BOTON-->
<button type="button" class="btn btn-agenda btn-warning d-inline float-right mb-2 mt-0 ml-2 mr-3" onclick="abrir_simbologia_agenda();" data-toggle="tooltip" data-placement="top" title="Significado de estados de agenda"><i class="fas fa-info"></i></button>
<!--MODAL-->
<div class="modal fade" id="modal_simbologia_agenda" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="modal_simbologia_agenda" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header bg-white">
            <h5 class="modal-title text-c-blue">Significado de los estados de la agenda</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="$('#modal_simbologia_agenda').modal('hide');" style="background-color: #fff!important; color:#656565!important;">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <ul class="simbologia-agenda-lista">
                    <li class="simbologia-agenda-item"><i class="fas fa-circle simbologia-agenda-punto text-c-blue"></i><span class="simbologia-agenda-nombre">Hora Pre Reservada</span></li>
                    <li class="simbologia-agenda-item"><i class="fas fa-circle simbologia-agenda-punto hora-reservada"></i><span class="simbologia-agenda-nombre">Hora Reservada</span></li>
                    <li class="simbologia-agenda-item"><i class="fas fa-circle simbologia-agenda-punto hora-confirmada"></i><span class="simbologia-agenda-nombre">Hora Confirmada</span></li>
                    <li class="simbologia-agenda-item"><i class="fas fa-circle simbologia-agenda-punto text-purple"></i><span class="simbologia-agenda-nombre">En espera de atención</span></li>
                    <li class="simbologia-agenda-item"><i class="fas fa-circle simbologia-agenda-punto realizando-atencion"></i><span class="simbologia-agenda-nombre">Realizando Atención</span></li>
                    <li class="simbologia-agenda-item"><i class="fas fa-circle simbologia-agenda-punto atencion-fin"></i><span class="simbologia-agenda-nombre">Atención Realizada</span></li>
                    <li class="simbologia-agenda-item"><i class="fas fa-circle simbologia-agenda-punto no-disponible-agenda"></i><span class="simbologia-agenda-nombre">Horario No Disponible</span></li>
                    <li class="simbologia-agenda-item"><i class="fas fa-circle simbologia-agenda-punto text-danger"></i><span class="simbologia-agenda-nombre">Horario Bloqueado</span></li>
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





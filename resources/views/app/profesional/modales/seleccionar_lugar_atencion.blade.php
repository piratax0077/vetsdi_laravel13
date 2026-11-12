<div id="modal_seleccionar_lugar_atencion" class="modal fade" tabindex="-1" role="dialog"
    aria-labelledby="modal_seleccionar_lugar_atencion" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header modal-header-purple">
                <h5 class="modal-title mt-1" id="modal_indicar_examen">Seleccione lugar de atención</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="cerrar_modal();"><span
                        aria-hidden="true">×</span>
                </button>
            </div>
            <form method="GET" action="{{ route('profesional.mi_agenda') }}" id="form_ir_mi_agenda">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <input type="hidden" name="lugares_atencion" id="lugares_atencion">
                                <div class="row" id="lista_lugares_atencion" role="group" aria-label="Lugares de atención"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-dark" onclick="cerrar_modal();" data-dismiss="modal"><i class="feather icon-x"></i> Cancelar</button>
                    <button type="submit" class="btn btn-info" id="btn_modal_seleccionar_lugar_atencion_ir"> <i class="feather icon-check"></i> Ir a mi agenda</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).on('click', '#lista_lugares_atencion [data-lugar-agenda]', function () {
        $('#lugares_atencion').val(this.dataset.lugarAgenda);
        $('#lista_lugares_atencion [data-lugar-agenda]').removeClass('active').attr('aria-pressed', 'false');
        $(this).addClass('active').attr('aria-pressed', 'true');
        document.getElementById('form_ir_mi_agenda').requestSubmit();
    });

    $(document).on('submit', '#form_ir_mi_agenda', function (event) {
        var idLugarAtencion = $('#lugares_atencion').val();

        if (!idLugarAtencion || Number(idLugarAtencion) === 0) {
            event.preventDefault();
            validar_seleccionar_lugar_atencion();
            return;
        }

        $('#btn_modal_seleccionar_lugar_atencion_ir').prop('disabled', true);
    });
</script>

<!--datos banco-->
@include('app.adm_cm.partials.cuenta_bancaria_diseno')
<div id="dat_bancarios" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="dat_bancarios" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header modal-header-purple">
                <h5 class="modal-title d-inline mt-1">Cuenta bancaria</h5>
                <button type="button" data-dismiss="modal" aria-label="Close" onclick="$(this).closest('.modal').modal('hide');"><span aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-pills cuenta-pestanas" id="liquidacion_pills" role="tablist"></ul>
                <div class="tab-content" id="liquidaciones"></div>
            </div>
        </div>
    </div>
</div>


<script>
    /** Modals datos bancarios */
    function datos_depositos(tipo, id)
    {
        $('#liquidaciones').html('');
        $('#liquidacion_pills').html('');

        let url = "{{ route('profesional.liquidacion_ver_profesional') }}";
        $.ajax({
            url: url,
            type: "get",
            data: {
                id_seccion: id
            },
        })
        .done(function(data) {
            if (data.estado == 1)
            {
                pintar_cuentas_bancarias(data.registros);
                $('#dat_bancarios').modal('show');
            }
            else
            {
                /** no encontrado */
                swal({
                    title: "El usuario no posee cuentas registradas.",
                    icon: "error",
                });
            }

        })
        .fail(function(jqXHR, ajaxOptions, thrownError) {
            console.log(jqXHR, ajaxOptions, thrownError)
        });

    }
</script>

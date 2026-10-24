{{-- Notas internas del tutor en Mascotas y tutores; es el mismo bloque de la pestaña de la pantalla del tutor.
     Lo abre abrir_notas_tutor() --}}
<div class="modal fade" id="modal_notas_tutor" tabindex="-1" role="dialog" aria-labelledby="titulo_modal_notas_tutor" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header modal-header-purple">
                <div>
                    <h5 class="modal-title" id="titulo_modal_notas_tutor">Notas internas del tutor</h5>
                    <small class="d-block text-white">Solo las ve el equipo de la peluquería; el tutor no las ve.</small>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                @include('app.peluqueria.notas_tutor', ['en_modal' => true])
            </div>
        </div>
    </div>
</div>

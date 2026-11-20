{{-- Recomendaciones para paciente con reflujo gastroesofágico (RGE); se abre con ind_rge() --}}
<div id="ind_reflujoge" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="ind_reflujoge"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header modal-header-purple">
                <h5 class="modal-title text-center">Recomendaciones Paciente con Reflujo Gastroesofágico</h5>
                <button type="button" class="close text-white" data-dismiss="modal" onclick="$(this).closest('.modal').modal('hide');" aria-label="Cerrar PDF"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <embed src="{{ asset('documentos/generales/REFLUJO%20.pdf') }}" type="application/pdf"
                    data-documento="REFLUJO .pdf" data-url="documentos/generales/REFLUJO .pdf" width="100%"
                    height="750px" />
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal" onclick="$(this).closest('.modal').modal('hide');">Cerrar</button>
                    <button type="button" class="btn btn-info"
                        onclick="envio_indicaciones_pdf('ind_reflujoge');">Enviar al Paciente</button>
                </div>
            </div>
        </div>
    </div>
</div>

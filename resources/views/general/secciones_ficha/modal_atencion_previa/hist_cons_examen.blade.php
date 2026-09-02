<div id="m_cons_examen" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="m_cons_examenLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h5 class="modal-title text-white" id="id_ficha_examen">Exámenes</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table id="table_atecion_previa_tabla_examen_paciente" class="display table table-striped dt-responsive nowrap table-sm" style="width:100%">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Examen</th>
                                <th>Tipo</th>
                                <th>Prioridad</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function buscar_examenes(idFichaClinica) {
        var $modal = $('#m_cons_examen');
        var $tabla = $('#table_atecion_previa_tabla_examen_paciente');
        var $cuerpo = $tabla.find('tbody');

        if ($.fn.DataTable && $.fn.DataTable.isDataTable($tabla[0])) {
            $tabla.DataTable().clear().destroy();
        }

        $('#id_ficha_examen').text('Exámenes');
        $cuerpo.html('<tr><td colspan="4" class="text-center py-3"><i class="fas fa-spinner fa-spin mr-2"></i>Cargando exámenes...</td></tr>');
        $modal.modal('show');

        $.ajax({
            url: "{{ route('examenes.ver_examenes') }}",
            type: 'get',
            dataType: 'json',
            data: { id_ficha_atencion: idFichaClinica }
        }).done(function (data) {
            var nombrePaciente = data && data.paciente && data.paciente.nombre_paciente
                ? data.paciente.nombre_paciente
                : '';
            var registros = data && Array.isArray(data.registros) ? data.registros : [];

            $('#id_ficha_examen').text('Exámenes' + (nombrePaciente ? ' de: ' + nombrePaciente : ''));
            $cuerpo.empty();

            if (!registros.length) {
                $cuerpo.html('<tr><td colspan="4" class="text-center text-muted py-3">No existen exámenes registrados.</td></tr>');
                return;
            }

            registros.forEach(function (registro) {
                var prioridades = { 1: 'Baja', 2: 'Media', 3: 'Alta', 4: 'Urgente' };
                var prioridad = prioridades[Number(registro.id_prioridad)] || 'Sin prioridad';
                var fecha = registro.fecha || historialFecha(registro.created_at);

                $cuerpo.append('<tr>' +
                    '<td>' + historialTextoSeguro(fecha) + '</td>' +
                    '<td>' + historialTextoSeguro(registro.examen) + '</td>' +
                    '<td>' + historialTextoSeguro(registro.tipo_examen) + '</td>' +
                    '<td>' + historialTextoSeguro(prioridad) + '</td>' +
                '</tr>');
            });

            if ($.fn.DataTable) {
                $tabla.DataTable({
                    responsive: true,
                    paging: false,
                    searching: false,
                    info: false,
                    destroy: true
                });
            }
        }).fail(function (jqXHR) {
            var mensaje = jqXHR.responseJSON && jqXHR.responseJSON.message
                ? jqXHR.responseJSON.message
                : 'No fue posible cargar los exámenes.';
            $cuerpo.html('<tr><td colspan="4" class="text-center text-danger py-3">' + historialTextoSeguro(mensaje) + '</td></tr>');
        });
    }
</script>

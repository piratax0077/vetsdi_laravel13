<div id="m_rx_orl" class="modal fade" role="dialog" aria-labelledby="m_rx_orl" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h5 class="modal-title text-white text-center">Solicitud Exámenes Radiológicos Otorrinolaringología</h5>
                <button type="button" class="close text-white" data-dismiss="modal" onclick="$('#cerrarsol_ex_rx_orl()').modal('hide')" aria-label="Close"><span aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <form>F
                    <div class="form-row mt-1">
                        <div class="form-group col-sm-12 col-md-6 col-lg-6 col-xl-6">
                            <label class="floating-label-activo-sm" for="ex-funcional">Exámenes radiológico</label>
                            <select class="js-example-basic-multiple select2" name="ex-radiologico_orl" id="ex-radiologico_orl" multiple="multiple">
                                <option value="1"> 04 01 002  &nbsp;  |  &nbsp;Rx. CAVUM RINOFARÍNGEO	</option>
                                <option value="2"> 04 01 031  &nbsp; | Rx. CAVIDADES PARANASALES</option>
                                <option value="3"> 04 05 001  &nbsp;  |  &nbsp;TAC CAVIDADES PARANASALES</option>
                                <option value="4"> 04 03 006  &nbsp;  |  &nbsp;TAC OIDO</option>
                                <option value="5"> 04 03 003  &nbsp;  |  &nbsp;TAC FOSA POSTERIOR</option>
                                <option value="6">  04 05 001  &nbsp;  |  &nbsp; RNM FOSA POSTERIOR</option>
                                <option value="7"> 04 05 001  &nbsp;  |  &nbsp; RNM OÍDOS</option>
                                <option value="8"> 04 03 008  &nbsp;  |  &nbsp; TAC DE COLUMNA CERVICAL</option>
                                <option value="8"> 04 05 005  &nbsp;  |  &nbsp; RNM DE COLUMNA CERVICAL</option>
                                <option value="9"> 04 01 043  &nbsp;  |  &nbsp; RADIOGRAFÍA DE COLUMNA CERVICAL FUNCIONAL</option>
                            </select>
                        </div>
                        <div class="form-group col-sm-12 col-md-6 col-lg-6 col-xl-6">
                            <label class="floating-label-activo-sm">Diagnóstico</label>
                            <input type="text" class="form-control" data-input_igual="descripcion_hipotesis,diagnostico_rx,diagnostico_comunes,diagnostico_endoscopico" name="diagnostico_especialidad" id="diagnostico_especialidad" onchange="cargarIgual('diagnostico_especialidad');" >
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-sm-12">
                            <div class="form-group fill">
                                <label class="floating-label-activo-sm">Observaciones</label>
                                <textarea class="form-control caja-texto form-control-sm mt-1" rows="1"  onfocus="this.rows=3" onblur="this.rows=1;" name="observaciones_especialidad" id="observaciones_especialidad"></textarea>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <button type="button" class="btn btn-success btn-sm float-right" onclick="guardar_examenes(1)">
                            <i class="fa fa-plus"></i> Agregar examen</button>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-sm-12 mt-3">
                            <!--**** Al agregar un examen, se debe cargar la tabla *****-->
                            <!--Tabla-->
                            <div class="table-responsive">
                                <table class="table table-bordered table-xs" id="table_examen_1">
                                    <thead>
                                        <tr>
                                            <th class="text-center align-middle">Fecha</th>
                                            <th class="text-center align-middle">Examen</th>
                                            <th class="text-center align-middle">Diagnóstico</th>
                                            <th class="text-center align-middle">Observaciones</th>
                                            <th class="text-center align-middle">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if(isset($examenes_plan_tratamiento))
                                            @foreach ($examenes_plan_tratamiento as $examen)
                                                @foreach (json_decode($examen->examenes, true) as $examen_nombre)
                                                    <tr>
                                                        {{-- Fecha --}}
                                                        <td class="text-center align-middle">
                                                            {{ \Carbon\Carbon::parse($examen->created_at)->format('d-m-Y H:i') }}
                                                        </td>

                                                        {{-- Examen --}}
                                                        <td class="text-left align-middle">
                                                            • {{ $examen_nombre }}
                                                        </td>

                                                        {{-- Diagnóstico --}}
                                                        <td class="text-left align-middle">
                                                            {{ $examen->diagnostico }}
                                                        </td>

                                                        {{-- Observaciones --}}
                                                        <td class="text-left align-middle">
                                                            {{ $examen->observaciones }}
                                                        </td>

                                                        {{-- Acciones --}}
                                                        <td class="text-center align-middle">
                                                            <button type="button"
                                                                class="btn btn-danger btn-sm mb-1"
                                                                onclick="eliminarExamen('{{ $examen->id }}',1, '{{ $examen_nombre }}')"
                                                            ><i class="fas fa-trash"></i></button>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @endforeach
                                        @endif
                                    </tbody>


                                </table>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <button type="button" class="btn btn-success btn-sm" onclick="generarPDFtipoExamen(1)">Generar PDF</button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger btn-sm" onclick="cerrarsol_ex_rx_orl();" data-bs-dismiss="modal" >Cancelar</button>
                <button type="submit" class="btn btn-info btn-sm"> Guardar</button>
            </div>
        </div>
    </div>
</div>
<style>
    .select2-dropdown{
        z-index: 9999 !important;
    }
</style>
<script>

    function sol_ex_rx_orl()
    {
         $('#m_rx_orl').modal('show');
    }
       function cerrarsol_ex_rx_orl() {
        $('#m_rx_orl').modal ('hide');
      }

    function guardar_examenes(tipo) {
        if(tipo == 1){
            // Obtener valores de los exámenes seleccionados
            var select = document.getElementById("ex-funcional");
            var selectedOptions = Array.from(select.selectedOptions);
            // Obtener diagnóstico y observaciones
            var diagnostico = document.getElementById('diagnostico_especialidad').value;
            var observaciones = document.getElementById("observaciones_especialidad").value;
        }else if(tipo == 2){
            // Obtener valores de los exámenes seleccionados
            var select = document.getElementById("examen_rx");
            var selectedOptions = Array.from(select.selectedOptions);
            // Obtener diagnóstico y observaciones
            var diagnostico = document.getElementById('diagnostico_rx').value;
            var observaciones = document.getElementById("observaciones_rx").value;
        }else if(tipo == 3){
            // Obtener valores de los exámenes seleccionados
            var select = document.getElementById("examenes_endoscopico");
            var selectedOptions = Array.from(select.selectedOptions);
            // Obtener diagnóstico y observaciones
            var diagnostico = document.getElementById('diagnostico_endoscopico').value;
            var observaciones = document.getElementById("observaciones_endoscopias").value;
        }


        if (selectedOptions.length === 0) {
            alert("Debe seleccionar al menos un examen.");
            return;
        }



        let examenes_texto = selectedOptions.map(option => option.text);

        let data = {
            diagnostico: diagnostico,
            observaciones: observaciones,
            id_ficha_atencion: $('#id_fc').val(),
            tipo_examen: tipo,
            examenes: examenes_texto,
            _token: CSRF_TOKEN
        }

        let url = "{{ ROUTE('profesional.examen.registro') }}";
        $.ajax({
            type:'post',
            url: url,
            data: data,
            success: function(resp){
                console.log(resp);
                if (resp.success) {
                    limpiar_campos(tipo);
                    swal({
                        title:'Se han guardado con éxito los examenes',
                        icon:'success',
                    });
                    let tbody = $('#table_examen_'+tipo+' tbody');
                    tbody.empty(); // Limpiar tabla

                    resp.examenes.forEach(item => {
                        item.examenes.forEach(nombre_examen => {
                            tbody.append(`
                                <tr>
                                    <td class="text-center align-middle">${item.fecha}</td>
                                    <td class="align-middle">${nombre_examen}</td>
                                    <td class="align-middle">${item.diagnostico}</td>
                                    <td class="align-middle">${item.observaciones || ''}</td>
                                    <td class="text-center align-middle">
                                        <button type="button" class="btn btn-danger btn-sm" onclick="eliminarExamen(${item.id},${tipo},'${nombre_examen}')">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            `);
                        });
                    });

                }
            },
            error: function(error){
                console.log(error.responseText);
            }
        })
    }

     function limpiar_campos(tipo){
        if(tipo == 1){
            // limpiar campos
            $('#ex-funcional').val(null).trigger('change');
        }else if(tipo == 2){
            // limpiar campos
            $('#examen_rx').val(null).trigger('change');
        }else if(tipo == 3){
            // limpiar campos
            $('#examenes_endoscopico').val(null).trigger('change');
        }else if(tipo == 4){
            // limpiar campos
            $('#ex-frecuente').val(null).trigger('change');
        }
    }

    function generarPDFtipoExamen(tipo) {
            let id_ficha_atencion = $('#id_fc').val(); // input hidden en tu HTML
            let auto = 1; // o el valor real que quieras enviar
            let url = "{{ route('pdf.orden_examenes_tipo_examen') }}";

            Fancybox.show(
                [{
                    src: "{{ route('pdf.orden_examenes_tipo_examen') }}?id=" + id_ficha_atencion + "&tipo=" + tipo,
                    type: "iframe",
                    preload: false,
                }, ]
            );
    }

    function eliminarExamen(id,tipo, nombre_examen = null) {
    if (!confirm("¿Está seguro de eliminar este examen?")) return;

    $.ajax({
        url: "{{ route('profesional.examen.eliminar') }}",
        type: 'POST',
        data: {
            id: id,
            id_ficha_atencion: $('#id_fc').val(),
            tipo: tipo,
            nombre_examen: nombre_examen,
            _token: CSRF_TOKEN
        },
        success: function (resp) {
            console.log(resp);
            if (resp.success) {
                swal({
                        title:'Se ha eliminado con éxito el examen',
                        icon:'success',
                    });
                 let tbody = $('#table_examen_'+tipo+' tbody');
                    tbody.empty(); // Limpiar tabla

                    resp.examenes.forEach(item => {
                        item.examenes.forEach(nombre_examen => {
                            tbody.append(`
                                <tr>
                                    <td class="text-center align-middle">${item.fecha}</td>
                                    <td class="align-middle">${nombre_examen}</td>
                                    <td class="align-middle">${item.diagnostico}</td>
                                    <td class="align-middle">${item.observaciones || ''}</td>
                                    <td class="text-center align-middle">
                                        <button type="button" class="btn btn-danger btn-sm" onclick="eliminarExamen(${item.id},${tipo}, '${nombre_examen}')">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            `);
                        });
                    });
            } else {
                alert(resp.message || "Error al eliminar");
            }
        },
        error: function (xhr) {
            console.error(xhr.responseText);
            alert("Ocurrió un error al eliminar.");
        }
    });
}

function generarPDF(id, nombre_examen = null) {
        let id_ficha_atencion = $('#id_fc').val(); // input hidden en tu HTML
        let auto = 1; // o el valor real que quieras enviar
        let url = "{{ route('pdf.orden_examenes_plan_tto') }}";


        Fancybox.show(
            [{
                src: "{{ route('pdf.orden_examenes_plan_tto') }}?id=" + id + "&nombre=" + nombre_examen,
                type: "iframe",
                preload: false,
            }, ]
        );

        // $('#m_bronco').modal('hide');
        // $('#m_rx_brpul').modal('hide');
        // $('#m_espiro').modal('hide');
    }

</script>
{{--  <link rel="stylesheet"  href="{{ asset('css\plugins\select2.min.css') }}">

<link rel="stylesheet" href="{{ asset('css/plugins/select2.min.css') }}">
<!-- select2 Js -->
<script src="{{ asset('js/plugins/select2.full.min.js') }}"></script>
<!-- form-select-custom Js -->
<script src="{{ asset('js/pages/form-select-custom.js') }}"></script>
<!-- select2 css -->  --}}

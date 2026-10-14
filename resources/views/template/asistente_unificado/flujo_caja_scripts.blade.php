@if(str_contains(request()->route()?->getActionName() ?? '', 'FlujoCajaController'))
<script>

        function cargar_flujo_caja() {
            var fecha = $('#rinde_fecha').val();
            var asistente = $('#rinde_asistente').val();
            var convenio = $('#rinde_convenio').val();
            var estado_consulta = $('#rinde_estado_consulta').val();

            let url = "{{ route('flujo_caja.data_flujo_caja') }}";


            $.ajax({

                    url: url,
                    type: "GET",
                    data: {
                        fecha : fecha,
                        asistente : asistente,
                        convenio : convenio,
                        estado_consulta : estado_consulta,
                    },
                })
                .done(function(data) {

                    console.log(data);
                    if (data.estado == 1)
                    {
                        $('#tabla_rendir_caja tbody').html('');
                        for (i = 0; i < data.registros.length; i++) {

                            var j = 1; //contador para asignar id al boton que borrara la fila
                            var fila = '';
                            fila += '<tr >';
                            fila += '    <td class="align-middle text-center">'+ data.registros[i].tipo_bono.nombre+'</td>';
                            fila += '    <td class="align-middle text-center">'+ data.registros[i].numero_bono+'</td>';
                            fila += '    <td class="align-middle text-center">'+ data.registros[i].convenio.nombre+'</td>';
                            fila += '    <td class="align-middle text-center">'+ data.registros[i].fecha_atencion+'</td>';
                            fila += '    <td class="align-middle text-center">';
                            fila += '        <span>'+ data.registros[i].paciente.nombres+' '+ data.registros[i].paciente.apellido_uno+' '+ data.registros[i].paciente.apellido_dos+'</span><br>';
                            fila += '        <span>'+ data.registros[i].paciente.rut +'</span>';
                            fila += '    </td>';
                            fila += '    <td class="align-middle text-center">$'+ data.registros[i].valor_atencion +'</td>';
                            fila += '    <td class="align-middle text-center">'+ data.registros[i].parametro.valor +'</td>';
                            fila += '    {{--  <td class="align-middle text-center">{{ $value_b->observaciones }}</td>  --}}';
                            fila += '    <td class="align-middle text-center">';
                            fila += '        <div class="form-group">';
                            fila += '            <div class="switch switch-success d-inline m-r-10">';
                            fila += '                <input type="checkbox" id="rendir_caja_'+ data.registros[i].id +'">';
                            fila += '                <label for="rendir_caja_'+ data.registros[i].id +'"';
                            fila += '                    class="cr"></label>';
                            fila += '            </div>';
                            fila += '        </div>';
                            fila += '    </td>';
                            fila += '</tr>';
                            j++;

                            $('#tabla_rendir_caja tbody').append(fila);

                        }
                    }
                    else
                    {
                        $('#tabla_rendir_caja tbody').html('');
                        $('#tabla_rendir_caja tbody').append('<tr><td colspan="8"> Sin registros</td></tr>');

                    }

                })
                .fail(function(jqXHR, ajaxOptions, thrownError) {
                    console.log(jqXHR, ajaxOptions, thrownError)
                });
        }

        function cargar_flujo_caja_programa() {
            var fecha = $('#rinde_fecha').val();
            var asistente = $('#rinde_asistente').val();
            var convenio = $('#rinde_convenio').val();
            var estado_consulta = $('#rinde_estado_consulta').val();

            let url = "{{ route('flujo_caja.data_flujo_caja_programa') }}";


            $.ajax({

                    url: url,
                    type: "GET",
                    data: {
                        fecha : fecha,
                        asistente : asistente,
                        convenio : convenio,
                        estado_consulta : estado_consulta,
                    },
                })
                .done(function(data) {

                    if (data.estado == 1) {

                    }
                    else
                    {

                    }

                })
                .fail(function(jqXHR, ajaxOptions, thrownError) {
                    console.log(jqXHR, ajaxOptions, thrownError)
                });
        }

        function cargar_flujo_caja_rendicion() {
            var fecha = $('#rinde_fecha').val();
            var asistente = $('#rinde_asistente').val();
            var convenio = $('#rinde_convenio').val();
            var estado_consulta = $('#rinde_estado_consulta').val();

            let url = "{{ route('flujo_caja.data_flujo_caja_rendidos') }}";


            $.ajax({

                    url: url,
                    type: "GET",
                    data: {
                        fecha : fecha,
                        asistente : asistente,
                        convenio : convenio,
                        estado_consulta : estado_consulta,
                    },
                })
                .done(function(data) {

                    if (data.estado == 1) {

                    }
                    else
                    {

                    }

                })
                .fail(function(jqXHR, ajaxOptions, thrownError) {
                    console.log(jqXHR, ajaxOptions, thrownError)
                });
        }

        function cargar_flujo_caja_rendicion_programa() {
            var fecha = $('#rinde_fecha').val();
            var asistente = $('#rinde_asistente').val();
            var convenio = $('#rinde_convenio').val();
            var estado_consulta = $('#rinde_estado_consulta').val();

            let url = "{{ route('flujo_caja.data_flujo_caja_rendidos_programa') }}";


            $.ajax({

                    url: url,
                    type: "GET",
                    data: {
                        fecha : fecha,
                        asistente : asistente,
                        convenio : convenio,
                        estado_consulta : estado_consulta,
                    },
                })
                .done(function(data) {

                    if (data.estado == 1) {

                    }
                    else
                    {

                    }

                })
                .fail(function(jqXHR, ajaxOptions, thrownError) {
                    console.log(jqXHR, ajaxOptions, thrownError)
                });
        }


        function seleccionar_bonos_rendicion(){
            var estado  = $('#enviar_todos').is(':checked')
            $("input:checkbox").each(function() {
                if($(this).attr('id') != 'enviar_todos')
                {
                    if(estado != $(this).is(':checked'))
                    {
                        $(this).trigger('click');
                    }
                }
            });
        }

    </script>
@endif

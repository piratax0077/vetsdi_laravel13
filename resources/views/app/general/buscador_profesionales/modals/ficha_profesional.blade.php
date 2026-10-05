<div class="modal fade ficha-profesional" id="ficha_profesional" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="modal_info_pro_nombre" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <!--Cabecera con foto, nombre y especialidad-->
            <div class="ficha-profesional__cabecera">
                <button type="button" class="ficha-profesional__cerrar" data-dismiss="modal" aria-label="Cerrar">
                    <i class="feather icon-x" aria-hidden="true"></i>
                </button>
                <img class="ficha-profesional__foto" id="modal_info_pro_foto" src="{{ asset('images/iconos/usuario_profesional.svg') }}" alt="Foto del veterinario">
                <div class="ficha-profesional__identidad">
                    <h5 class="ficha-profesional__nombre" id="modal_info_pro_nombre"></h5>
                    <!--Número de registro: se llenará cuando tengamos el dato-->
                    <span class="ficha-profesional__registro">MVA: <span id="modal_info_pro_mva"></span></span>
                    <span class="ficha-profesional__especialidad" id="modal_info_pro_especialidad"><span id="modal_info_pro_tipo_especialidad">Especialidad</span><span id="modalinfo_pro_sub_tipo_especialidad"></span></span>
                </div>
            </div>

            <div class="modal-body ficha-profesional__cuerpo">
                <!--Lugares de atención: van como tarjetas porque suelen ser pocos y el convenio es texto largo-->
                <section class="ficha-profesional__seccion">
                    <h6 class="ficha-profesional__titulo">Lugares de atención</h6>
                    <div class="ficha-profesional__lugares" id="modal_info_pro_lugares"></div>
                    <div class="ficha-profesional__vacio" id="modal_info_pro_lugares_vacio" style="display:none;">Sin lugares de atención registrados.</div>
                </section>

                <!--Formación y antecedentes-->
                <section class="ficha-profesional__seccion">
                    <h6 class="ficha-profesional__titulo">Información profesional</h6>
                    <ul class="ficha-profesional__formacion" id="modal_info_pro_academicos"></ul>
                    <div class="ficha-profesional__vacio" id="modal_info_pro_academicos_vacio" style="display:none;">Sin antecedentes profesionales registrados.</div>
                </section>
            </div>
        </div>
    </div>
</div>

<script>
    // Llena el modal con la respuesta de profesional.informacionProfesional.
    // La usan Mis veterinarios, el buscador y Profesionales del centro.
    function pintarFichaProfesional(data) {
        const escapar = function(texto) {
            return $('<div>').text(texto == null ? '' : String(texto)).html();
        };

        const profesional = data.profesional || {};
        const tipoEspecialidad = profesional.tipo_especialidad ? profesional.tipo_especialidad.nombre : (profesional.especialidad ? profesional.especialidad.nombre : 'Veterinario/a');
        const subTipoEspecialidad = profesional.sub_tipo_especialidad ? profesional.sub_tipo_especialidad.nombre : '';
        const nombre = [profesional.nombre, profesional.apellido_uno, profesional.apellido_dos].filter(Boolean).join(' ');

        $('#modal_info_pro_foto').attr('src', profesional.img_profesional || "{{ asset('images/iconos/usuario_profesional.svg') }}");
        $('#modal_info_pro_nombre').text(nombre || 'Veterinario/a');
        // En la base vienen en mayúsculas; se dejan solo con la primera letra en mayúscula
        const primeraMayuscula = function(texto) {
            texto = String(texto || '').trim().toLocaleLowerCase('es');
            return texto.charAt(0).toLocaleUpperCase('es') + texto.slice(1);
        };

        $('#modal_info_pro_tipo_especialidad').text(primeraMayuscula(tipoEspecialidad));
        $('#modalinfo_pro_sub_tipo_especialidad').text(subTipoEspecialidad !== '' ? ' · ' + primeraMayuscula(subTipoEspecialidad) : '');

        // Lugares de atención
        const lugares = data.lugares_atencion || [];
        let htmlLugares = '';
        $.each(lugares, function(_, lugar) {
            const dir = lugar.direccion
                ? [lugar.direccion.direccion, lugar.direccion.numero_dir ? '#' + lugar.direccion.numero_dir : '', lugar.direccion.ciudad ? lugar.direccion.ciudad.nombre : ''].filter(Boolean).join(', ')
                : 'Dirección no informada';
            const textoConvenios = lugar.convenio && lugar.convenio.convenios ? String(lugar.convenio.convenios) : '';
            const convenios = textoConvenios.split(/[,;]/).map(function(c) { return c.trim(); }).filter(Boolean);

            htmlLugares += '<article class="ficha-profesional__lugar">';
            htmlLugares += '    <h6 class="ficha-profesional__lugar-nombre">' + escapar(lugar.nombre || 'Sin nombre') + '</h6>';
            htmlLugares += '    <p class="ficha-profesional__lugar-linea"><i class="feather icon-map-pin" aria-hidden="true"></i><span>' + escapar(dir) + '</span></p>';
            if (lugar.telefono) {
                htmlLugares += '    <p class="ficha-profesional__lugar-linea"><i class="feather icon-phone" aria-hidden="true"></i><a href="tel:' + escapar(String(lugar.telefono).replace(/\s+/g, '')) + '">' + escapar(lugar.telefono) + '</a></p>';
            }
            if (lugar.email) {
                htmlLugares += '    <p class="ficha-profesional__lugar-linea"><i class="feather icon-mail" aria-hidden="true"></i><a href="mailto:' + escapar(lugar.email) + '">' + escapar(lugar.email) + '</a></p>';
            }
            htmlLugares += '    <div class="ficha-profesional__convenios">';
            htmlLugares += '        <span class="ficha-profesional__etiqueta">Convenios</span>';
            if (convenios.length) {
                $.each(convenios, function(_, convenio) {
                    htmlLugares += '<span class="ficha-profesional__convenio">' + escapar(convenio) + '</span>';
                });
            } else {
                htmlLugares += '<span class="ficha-profesional__sin-dato">No informado</span>';
            }
            htmlLugares += '    </div>';
            htmlLugares += '</article>';
        });
        $('#modal_info_pro_lugares').html(htmlLugares);
        $('#modal_info_pro_lugares_vacio').toggle(lugares.length === 0);

        // Antecedentes académicos
        const antecedentes = profesional.antecedente_academico || [];
        let htmlFormacion = '';
        $.each(antecedentes, function(_, item) {
            const detalle = [item.universidad, item.ciudad_pais, item.anio].filter(Boolean).join(' · ');
            htmlFormacion += '<li class="ficha-profesional__formacion-item">';
            htmlFormacion += '    <span class="ficha-profesional__etiqueta">' + escapar(item.tipo_antecedente_academico ? item.tipo_antecedente_academico.nombre : 'Antecedente') + '</span>';
            htmlFormacion += '    <strong class="ficha-profesional__formacion-nombre">' + escapar(item.nombre || 'No informado') + '</strong>';
            if (detalle) {
                htmlFormacion += '    <span class="ficha-profesional__formacion-detalle">' + escapar(detalle) + '</span>';
            }
            htmlFormacion += '</li>';
        });
        $('#modal_info_pro_academicos').html(htmlFormacion);
        $('#modal_info_pro_academicos_vacio').toggle(antecedentes.length === 0);
    }
</script>

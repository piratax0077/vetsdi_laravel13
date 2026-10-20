@once
<script>
function editarTutorSidebar(boton) {
    const panel = boton.closest('.card-body-sidebar');
    panel.querySelector('[id="info_paciente"]').style.display = 'none';
    const editor = panel.querySelector('[id="info_paciente-edit"]');
    editor.style.display = 'block';
    editor.querySelector('input:not([readonly])').focus();
}
function cancelarTutorSidebar(boton) {
    const panel = boton.closest('.card-body-sidebar');
    panel.querySelector('[id="info_paciente"]').style.display = 'block';
    panel.querySelector('[id="info_paciente-edit"]').style.display = 'none';
}
function guardarTutorSidebar(boton) {
    const panel = boton.closest('.card-body-sidebar');
    const editor = panel.querySelector('[id="info_paciente-edit"]');
    const valor = id => editor.querySelector('[id="'+id+'"]')?.value || '';
    const datos = {
        id: editor.dataset.tutorId,
        nombre: valor('paciente_nombre_edit'), apellido_uno: valor('paciente_apellido_uno_edit'),
        apellido_dos: valor('paciente_apellido_dos_edit'), fecha_nacimiento: valor('paciente_fn_edit'),
        sexo: valor('paciente_sexo_edit'), convenio: valor('paciente_convenio_edit'),
        direccion: valor('paciente_dir_edit'), numero_direccion: valor('tutor_numero_direccion_edit'),
        region: valor('paciente_region_edit'), ciudad: valor('paciente_comuna_edit'),
        email: valor('paciente_email_edit'), telefono: valor('paciente_telefono_edit')
    };
    if (!datos.nombre.trim() || !datos.fecha_nacimiento) {
        swal({title:'Revisa los datos del tutor',text:'Completa el nombre y la fecha de nacimiento.',icon:'warning'});
        return;
    }
    boton.disabled = true;
    $.ajax({url:editor.dataset.url,type:'GET',data:datos})
        .done(function (respuesta) {
            if (respuesta.estado != 1 || !respuesta.paciente || respuesta.direccion?.estado === 0) {
                swal({title:'No se pudo guardar',text:respuesta.msj || 'Revisa los datos e inténtalo nuevamente.',icon:'error'});
                return;
            }
            const tutor = respuesta.paciente;
            const texto = (id, valor) => { const nodo = panel.querySelector('[id="'+id+'"]'); if(nodo) nodo.textContent = valor || ''; };
            texto('nombre_completo_paciente',[tutor.nombres,tutor.apellido_uno,tutor.apellido_dos].filter(Boolean).join(' '));
            texto('fecha_nac_paciente',tutor.fecha_nac);
            texto('sexo_paciente',tutor.sexo === 'M' ? 'Masculino' : 'Femenino');
            texto('email_paciente_',tutor.email); texto('telefono_paciente_',tutor.telefono_uno);
            texto('direccion_paciente_',[datos.direccion,datos.numero_direccion].filter(Boolean).join(' '));
            texto('comuna_region_paciente',[tutor.ciudad,tutor.region].filter(Boolean).join(' — '));
            texto('prevision_paciente',editor.querySelector('[id="paciente_convenio_edit"] option:checked')?.textContent.trim());
            cancelarTutorSidebar(boton);
            swal({title:'Información del tutor guardada',icon:'success'});
        })
        .fail(function () { swal({title:'No se pudo guardar',text:'Comprueba la conexión y tu sesión e inténtalo nuevamente.',icon:'error'}); })
        .always(function () { boton.disabled = false; });
}
</script>
@endonce

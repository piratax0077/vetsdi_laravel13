<div id="a_area_cm" class="modal fade validar-area" tabindex="-1" role="dialog" aria-labelledby="a_area_cm" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header modal-header-purple">
                <h5 class="modal-title text-center">Agregar área del Centro Médico</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" onclick="$(this).closest('.modal').modal('hide');"><span aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
				<form novalidate>
					<div class="form-row">
						<div class="col-sm-12 col-md-12 col-lg-6 col-xl-6">
							<div class="form-group">
								<!--Cargar áreas-->
								<label class="floating-label-activo-sm" for="tipo_area">Área</label>
								<select class="form-control form-control-sm" id="tipo_area" data-validar="seleccion" data-requerido="Seleccione un área">
									<option value="0">Seleccione</option>
									@foreach ($tipos_areas_cm as $tipo_area_cm)
                                        <option value="{{ $tipo_area_cm->id }}">{{ $tipo_area_cm->nombre }}</option>
                                    @endforeach
								</select>
							</div>
						</div>
						<div class="col-sm-12 col-md-12 col-lg-6 col-xl-6">
							<div class="form-group">
								<label class="floating-label-activo-sm" for="responsable_cargo_area">Responsable</label>
								<select class="form-control form-control-sm" id="responsable_cargo_area" data-validar="seleccion" data-requerido="Seleccione un responsable">
                                        <option value="0">Seleccione</option>
										@foreach ($profesionales as $profesional)
                                            <option value="{{ $profesional->id_profesional }}">{{ $profesional->nombre }} {{ $profesional->apellido_uno }} {{ $profesional->apellido_dos }}</option>
                                        @endforeach
								</select>
							</div>
						</div>
					</div>
				<div class="form-row">
					<div class="col-sm-12 col-md-12 col-lg-4 col-xl-4">
						<div class="form-group">
							<label class="floating-label-activo-sm" for="e_cont">Contacto (email)</label>
							<input type="text" class="form-control form-control-sm" name="e_cont" id="e_cont" data-validar="email" data-requerido="Ingrese un email de contacto">
						</div>
					</div>
					<div class="col-sm-12 col-md-12 col-lg-4 col-xl-4">
						<div class="form-group">
							<label class="floating-label-activo-sm" for="tel_c">Teléfono</label>
							<input type="number" class="form-control form-control-sm" name="tel_c" id="tel_c" data-validar="telefono" data-requerido="Ingrese un teléfono de contacto">
						</div>
					</div>
					<div class="col-sm-12 col-md-12 col-lg-4 col-xl-4">
						<div class="form-group">
							<label class="floating-label-activo-sm" for="n_pers">N°/ Pers. a cargo</label>
							<input type="number" class="form-control form-control-sm" name="n_pers" id="n_pers" min="0" step="1" data-validar="personas" data-requerido="Ingrese el número de personas a cargo">
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-info mx-auto" onclick="guardar_area_cm();"><i class="feather icon-plus"></i> Agregar</button>
			</div>

			</form>
		</div>
	</div>
</div>

<script>
 function ag_area_cm() {
            $('#a_area_cm').modal('show');
        }

// Devuelve el mensaje de error del campo, o vacío si está correcto.
function error_campo_area(campo){
    var valor = $.trim(campo.val());
    var regla = campo.data('validar');

    if (valor === '' || (regla === 'seleccion' && valor === '0')) {
        return campo.data('requerido');
    }
    if (regla === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(valor)) {
        return 'Ingrese un email válido, por ejemplo nombre@correo.cl';
    }
    if (regla === 'telefono' && !/^\d{7,15}$/.test(valor)) {
        return 'Ingrese un teléfono válido, de 7 a 15 dígitos';
    }
    if (regla === 'personas' && !/^\d+$/.test(valor)) {
        return 'Ingrese un número entero, 0 o mayor';
    }
    return '';
}

function pintar_error_area(campo, mensaje){
    campo.toggleClass('is-invalid', mensaje !== '').attr('aria-invalid', mensaje !== '' ? 'true' : 'false');
    campo.siblings('.invalid-feedback').remove();
    if (mensaje !== '') {
        campo.after($('<div class="invalid-feedback" role="alert"></div>').text(mensaje).prepend('<i class="feather icon-alert-circle"></i> '));
    }
}

// Revisa todo el modal y deja el foco en el primer campo pendiente.
function validar_formulario_area(modal){
    var valido = true;
    $(modal).find('[data-validar]').each(function(){
        var mensaje = error_campo_area($(this));
        pintar_error_area($(this), mensaje);
        if (mensaje !== '') {
            valido = false;
        }
    });
    $(modal).find('.is-invalid').first().trigger('focus');
    return valido;
}

// Lleva a cada campo los errores que devuelve el servidor; si no hay ninguno que mostrar, avisa con el mensaje general.
function errores_servidor_area(xhr, campos){
    var errores = (xhr.responseJSON && xhr.responseJSON.errors) || {};
    var mostrados = 0;
    $.each(campos, function(nombre, selector){
        if (errores[nombre]) {
            pintar_error_area($(selector), errores[nombre][0]);
            mostrados++;
        }
    });
    if (mostrados == 0) {
        swal({ title: "Error", text: "No se pudo guardar el área. Intente nuevamente.", icon: "error" });
    }
}

document.addEventListener('DOMContentLoaded', function(){
    // Al escribir solo se corrige un error ya marcado; el campo se revisa completo al salir de él.
    $(document).on('input', '.validar-area [data-validar].is-invalid', function(){
        pintar_error_area($(this), error_campo_area($(this)));
    });
    $(document).on('change focusout', '.validar-area [data-validar]', function(){
        pintar_error_area($(this), error_campo_area($(this)));
    });
    $(document).on('hidden.bs.modal', '.validar-area', function(){
        $(this).find('[data-validar]').each(function(){
            pintar_error_area($(this), '');
        });
    });
});

function guardar_area_cm(){
    if (!validar_formulario_area('#a_area_cm')) {
        return false;
    }

    var tipo_area = $('#tipo_area').val();
    var responsable_cargo = $('#responsable_cargo_area').val();
    var e_cont = $('#e_cont').val();
    var tel_c = $('#tel_c').val();
    var n_pers = $('#n_pers').val();
    var id_institucion = $('#id_institucion').val();
    var token = '{{csrf_token()}}';

    $.ajax({
        url: '{{ ROUTE("adm_cm.agregar_area_cm") }}',
        type: 'POST',
        data: {
            id_institucion: id_institucion,
            tipo_institucion: $('#tipo_institucion').val(),
            responsable_cargo: responsable_cargo,
            e_cont: e_cont,
            tel_c: tel_c,
            n_pers: n_pers,
            tipo_area: tipo_area,
            _token: token
        },
        success: function (data) {
            console.log(data);
            if (data.estado == 1) {
                $('#a_area_cm').modal('hide');
                $('#tipo_area').val('0');
                $('#responsable_cargo_area').val('0');
                $('#e_cont').val('');
                $('#tel_c').val('');
                $('#n_pers').val('');
                $('#tabla_area_cm').load('/adm_cm/tabla_area_cm');
                $('#contenedor_areas_cm').empty();
                $('#contenedor_areas_cm').append(data.v);
            } else if (data.campo == 'tipo_area') {
                pintar_error_area($('#tipo_area'), data.mensaje);
            } else {
                swal({
                    title: "Error",
                    text: data.mensaje,
                    icon: "error",
                });
            }
        },
        error: function (data) {
            console.log('Error:', data);
            errores_servidor_area(data, { tipo_area: '#tipo_area', responsable_cargo: '#responsable_cargo_area', e_cont: '#e_cont', tel_c: '#tel_c', n_pers: '#n_pers' });
        }
    });
}
</script>

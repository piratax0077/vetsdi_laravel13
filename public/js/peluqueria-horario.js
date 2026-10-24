// Días y horario de atención de la peluquería (Mi perfil y lugares de atención).
// Cada .horario-editor funciona por su cuenta, así puede haber más de uno en la misma pantalla.
var horario_atencion = (function ($) {
    var dias_laborales = ['martes', 'miercoles', 'jueves', 'viernes'];

    function fila_dia(editor, dia) {
        return editor.find('.horario-fila[data-dia="' + dia + '"]');
    }

    function check_dia(editor, dia) {
        return editor.find('.horario-abierto[data-dia="' + dia + '"]');
    }

    function por_semana(editor) {
        return editor.find('.horario-modo-valor').val() === 'semana';
    }

    // Solo se ven las filas de los días abiertos; las horas de los cerrados no se envían
    function actualizar_filas(editor) {
        var semana = por_semana(editor);
        var visibles = 0;

        editor.find('.horario-abierto').each(function () {
            var dia = $(this).data('dia');
            var fila = fila_dia(editor, dia);

            fila.toggleClass('cerrado', !this.checked);
            fila.find('input[type="time"]').prop('disabled', !this.checked);
            if (this.checked && !(semana && dias_laborales.indexOf(dia) !== -1)) {
                visibles++;
            }
        });

        editor.find('.horario-sin-dias').toggle(visibles === 0);
        editor.find('.horario-copiar').toggle(!semana && visibles > 1);
    }

    // Con la semana junta, martes a viernes quedan iguales al lunes
    function copiar_lunes(editor) {
        var horas_lunes = fila_dia(editor, 'lunes').find('input[type="time"]');
        var abierto = check_dia(editor, 'lunes').prop('checked');

        dias_laborales.forEach(function (dia) {
            var horas = fila_dia(editor, dia).find('input[type="time"]');
            check_dia(editor, dia).prop('checked', abierto);
            horas.eq(0).val(horas_lunes.eq(0).val());
            horas.eq(1).val(horas_lunes.eq(1).val());
        });
    }

    // Día por día: el horario del primer día abierto pasa a todos los demás días abiertos
    function copiar_primero(editor) {
        var filas = editor.find('.horario-fila:not(.cerrado)');
        var origen = filas.first().find('input[type="time"]');

        filas.each(function () {
            var horas = $(this).find('input[type="time"]');
            horas.eq(0).val(origen.eq(0).val());
            horas.eq(1).val(origen.eq(1).val());
        }).removeClass('con-error');
    }

    function aplicar_modo(editor, modo) {
        var semana = modo === 'semana';
        var boton_lunes = editor.find('label[for="' + check_dia(editor, 'lunes').attr('id') + '"]');
        var nombre_lunes = fila_dia(editor, 'lunes').find('.horario-fila-nombre');

        editor.find('.horario-modo-valor').val(modo);
        editor.find('.horario-modo a').each(function () {
            var activo = $(this).data('modo') === modo;
            $(this).toggleClass('active', activo).attr('aria-selected', activo ? 'true' : 'false');
        });
        editor.toggleClass('horario-por-semana', semana);
        boton_lunes.text(semana ? 'Lun a Vie' : boton_lunes.data('corto'));
        nombre_lunes.text(semana ? 'Lunes a viernes' : nombre_lunes.data('dia-nombre'));

        if (semana) {
            copiar_lunes(editor);
        }
        actualizar_filas(editor);
    }

    function semana_igual(editor) {
        var dato = function (dia) {
            var horas = fila_dia(editor, dia).find('input[type="time"]');
            return [check_dia(editor, dia).prop('checked'), horas.eq(0).val(), horas.eq(1).val()].join('|');
        };
        return dias_laborales.every(function (dia) {
            return dato(dia) === dato('lunes');
        });
    }

    // Carga un horario guardado ({lunes: {abierto, desde, hasta}, ...}); sin datos todos los días parten cerrados
    function cargar(editor, horario, modo) {
        horario = horario || {};
        editor.find('.horario-abierto').each(function () {
            var dato = horario[$(this).data('dia')] || {};
            var horas = fila_dia(editor, $(this).data('dia')).find('input[type="time"]');

            this.checked = !!dato.abierto && String(dato.abierto) !== '0';
            horas.eq(0).val(dato.desde || '09:00');
            horas.eq(1).val(dato.hasta || '18:00');
        });
        editor.find('.horario-fila').removeClass('con-error');
        aplicar_modo(editor, modo === 'dias' || !semana_igual(editor) ? 'dias' : 'semana');
    }

    // Después de un reset del formulario vuelve a la forma de ingreso con que se abrió
    function restablecer(editor) {
        editor.find('.horario-fila').removeClass('con-error');
        aplicar_modo(editor, editor.data('modo-inicial'));
    }

    // Antes de guardar: cada día abierto debe cerrar después de abrir
    function validar(editor) {
        var valido = true;

        if (por_semana(editor)) {
            copiar_lunes(editor);
        }
        actualizar_filas(editor);

        editor.find('.horario-fila').each(function () {
            var horas = $(this).find('input[type="time"]');
            var apertura = horas.get(0);
            var cierre = horas.get(1);

            cierre.setCustomValidity('');
            if (!cierre.disabled && apertura.value && cierre.value && cierre.value <= apertura.value) {
                cierre.setCustomValidity('La hora de cierre debe ser posterior a la de apertura');
            }
            var con_error = !cierre.disabled && !(apertura.checkValidity() && cierre.checkValidity());
            $(this).toggleClass('con-error', con_error);
            if (con_error) {
                valido = false;
            }
        });

        return valido;
    }

    $(document).on('click', '.horario-editor .horario-modo a', function (evento) {
        evento.preventDefault();
        aplicar_modo($(this).closest('.horario-editor'), $(this).data('modo'));
    });

    $(document).on('change', '.horario-editor .horario-abierto', function () {
        var editor = $(this).closest('.horario-editor');
        if (por_semana(editor) && $(this).data('dia') === 'lunes') {
            copiar_lunes(editor);
        }
        actualizar_filas(editor);
    });

    // Mientras la semana va junta, cambiar las horas del lunes cambia también las de martes a viernes
    $(document).on('change input', '.horario-editor .horario-fila[data-dia="lunes"] input', function () {
        var editor = $(this).closest('.horario-editor');
        if (por_semana(editor)) {
            copiar_lunes(editor);
        }
    });

    $(document).on('change input', '.horario-editor .horario-fila input[type="time"]', function () {
        $(this).closest('.horario-fila').removeClass('con-error');
    });

    $(document).on('click', '.horario-editor .horario-copiar', function () {
        copiar_primero($(this).closest('.horario-editor'));
    });

    $(function () {
        $('.horario-editor').each(function () {
            aplicar_modo($(this), $(this).data('modo-inicial'));
        });
    });

    return { cargar: cargar, restablecer: restablecer, validar: validar };
})(jQuery);

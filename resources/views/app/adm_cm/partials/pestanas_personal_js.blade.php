{{-- Funciones de la pestaña Asistentes (traídas de la página Personal) --}}
<script>
    $(document).ready(function() {
        $("#agregar_profesional_int_rut").rut({
            formatOn: 'keyup',
            minimumLength: 2,
            validateOn: 'change',
            useThousandsSeparator : false
        });
        $(".js-example-basic-multiple").select2();
        $('#edit_dias_laborales').select2({
            placeholder: "Seleccione los días laborales",
            allowClear: true
        });
    });

    /* ===== Asistentes ===== */
    // Se recarga la página: la pestaña activa queda en la URL (?tab=asistentes) y se vuelve a abrir
    function cargar_tabla_asistentes()
    {
        location.reload();
    }
</script>

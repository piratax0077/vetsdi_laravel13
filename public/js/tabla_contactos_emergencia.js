$(document).ready(function() {
    var tabla_contactos = $('#contactos_emergencia').DataTable({
        responsive: true,
    });

    // La tabla se arma con su pestaña oculta y queda con un ancho equivocado;
    // al mostrar la pestaña se recalcula para que no aparezca scroll horizontal.
    $(document).on('shown.bs.tab', '[data-toggle="tab"], [data-bs-toggle="tab"]', function () {
        tabla_contactos.columns.adjust();

        if (tabla_contactos.responsive)
            tabla_contactos.responsive.recalc();
    });
});

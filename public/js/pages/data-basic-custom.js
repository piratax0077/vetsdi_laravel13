$(document).ready(function() {
    setTimeout(function() {
        // [ Zero Configuration ] start
        if ($('#simpletable').length) $('#simpletable').DataTable();

        // [ Default Ordering ] start
        if ($('#order-table').length) $('#order-table').DataTable({
            "order": [
                [3, "desc"]
            ]
        });

        // [ Multi-Column Ordering ]
        if ($('#multi-colum-dt').length) $('#multi-colum-dt').DataTable({
            columnDefs: [{
                targets: [0],
                orderData: [0, 1]
            }, {
                targets: [1],
                orderData: [1, 0]
            }, {
                targets: [4],
                orderData: [4, 0]
            }]
        });

        // [ Complex Headers ]
        if ($('#complex-dt').length) $('#complex-dt').DataTable();

        // [ DOM Positioning ]
        if ($('#DOM-dt').length) $('#DOM-dt').DataTable({
            "dom": '<"top"i>rt<"bottom"flp><"clear">'
        });

        // [ Alternative Pagination ]
        if ($('#alt-pg-dt').length) $('#alt-pg-dt').DataTable({
            "pagingType": "full_numbers"
        });

        // [ Scroll - Vertical ]
        if ($('#scr-vrt-dt').length) $('#scr-vrt-dt').DataTable({
            "scrollY": "200px",
            "scrollCollapse": true,
            "paging": false
        });

        // [ Scroll - Vertical, Dynamic Height ]
        if ($('#scr-vtr-dynamic').length) $('#scr-vtr-dynamic').DataTable({
            scrollY: '50vh',
            scrollCollapse: true,
            paging: false
        });

        // [ Language - Comma Decimal Place ]
        if ($('#lang-dt').length) $('#lang-dt').DataTable({
            "language": {
                "decimal": ",",
                "thousands": "."
            }
        });

    }, 350);
});

(function () {
    'use strict';
    document.addEventListener('DOMContentLoaded', function () {
        var vista = new URLSearchParams(window.location.search).get('hospital_vista');
        if (!['pacientes', 'hospitalizacion', 'plazas'].includes(vista) || !window.jQuery) return;
        var $ = window.jQuery;
        $('#ar-dep-tab').tab('show');
        $('#hospitalizacion-config-tab').tab('show');
        $('#hospital-info-' + vista + '-tab').tab('show');
    });
}());

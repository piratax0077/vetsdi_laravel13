(function () {
    'use strict';
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.vet-operacion-form').forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (form.dataset.enviando === '1') { event.preventDefault(); return; }
                form.dataset.enviando = '1';
                form.querySelectorAll('button[type="submit"]').forEach(function (button) { button.disabled = true; });
            });
        });
    });
}());

{{-- Buscador de especie y raza (Select2 local) con "Agregar ... como otra raza". Ver public/js/selector_razas.js --}}
<script src="{{ asset('js/selector_razas.js') }}?v=4"
    data-select2-js="{{ asset('js/plugins/select2.full.min.js') }}"
    data-select2-css="{{ asset('css/plugins/select2.min.css') }}"
    data-crear-raza="{{ route('razas_mascotas.crear') }}"
    data-listar-razas="{{ route('razas_mascotas.listar') }}"
    data-token="{{ csrf_token() }}"></script>

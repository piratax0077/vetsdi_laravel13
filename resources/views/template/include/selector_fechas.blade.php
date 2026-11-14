{{-- Calendario moderno para todos los campos de fecha (flatpickr local, sin CDN) --}}
<script src="{{ asset('js/selector_fechas.js') }}?v=1"
    data-flatpickr-js="{{ asset('js/flatpickr/flatpickr.min.js') }}"
    data-flatpickr-css="{{ asset('css/flatpickr/flatpickr.min.css') }}"></script>
{{-- Va junto al calendario para que llegue a las mismas plantillas: los campos de especie y raza con buscador --}}
@include('template.include.selector_razas')

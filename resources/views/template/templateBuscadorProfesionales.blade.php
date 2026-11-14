<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>SDI | Buscador de Profesionales</title>
    <meta name="description" content="" />
    <meta name="keywords" content="">
    <meta name="author" content="SDI | Buscador de Profesionales" />

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"> </script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js" integrity="sha384-7+zCNj/IqJ95wo16oMtfsKbZ9ccEh31eOz1HGyDuCQ6wgnyJNSYdrPa03rtR1zdB" crossorigin="anonymous"> </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js" integrity="sha384-QJHtvGhmr9XOIpI6YVutG+2QOK9T+ZnN4kzFN1RtK3zEFEIsxhlmWl5/YESvpZ13" crossorigin="anonymous"> </script>

    <link rel="icon" href="{{ asset('images/favicon.ico') }}" type="image/x-icon" />
    <link rel="stylesheet" href="{{ asset('css/style.css') }}?t={{ time() }}" />
    <link rel="stylesheet" href="{{ asset('css/escritorio_asistente.css') }}?t={{ time() }} /">
    <link rel="stylesheet" href="{{ asset('css/escritorio_profesional.css') }}">

    <link rel="stylesheet" href="{{ asset('css/plugins/ekko-lightbox.css') }}"/>
	<link rel="stylesheet" href="{{ asset('css/plugins/lightbox.min.css') }}"/>

    <!-- data tables css -->
    <link rel="stylesheet" href="{{ asset('css/plugins/dataTables.bootstrap4.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/plugins/responsive.bootstrap4.min.css') }}"/>

    <link rel="stylesheet" href="{{ asset('css/pills_modals.css') }}"/>

    <!-- fancy box -->
    <link rel="stylesheet" href="{{ asset('css/fancybox/fancybox.css') }}" />


    <link rel="stylesheet" href="{{ asset('js/fullcalendar-5.10.1/lib/main.css') }}" />

    <!-- select2 -->
    <link rel="stylesheet" type="text/css" href='{{ asset('css/plugins/select2.min.css') }}'/>


   {{-- autocomplete
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>--}}
    <script src="{{ asset('js/jquery-ui/jquery-ui.min.js') }}"></script>



    @yield('page-styles')

    <!-- flatpickr -->
    <link rel="stylesheet" href="{{ asset('css/flatpickr/flatpickr.min.css') }}">

    <style>

        #calendario_reserva_buscador {
            {{--  max-width: 900px;  --}}
            max-height: 488px;
            {{--  margin: 40px auto;  --}}
        }

        /* kill the scrollbars and allow natural height */
        .fc-scroller,
        .fc-day-grid-container,
        /* these divs might be assigned height, which we need to cleared */
        .fc-time-grid-container {
            /* */
            overflow-x: hidden;
            overflow-y: auto !important;
            height: auto !important;
        }

        /* kill the horizontal border/padding used to compensate for scrollbars */
        .fc-row {
            border: 0 !important;
            margin: 0 !important;
        }

        .fc .fc-timegrid-slot {
            height: 3.5em;
        }
        .fc .fc-toolbar.fc-header-toolbar {
            margin: 0px;
        }
        .fc .fc-toolbar-title {
            font-size: 1.0em;
        }
    </style>
</head>
<body>

    <div class="loader-bg">
        <div class="loader-track">
            <div class="loader-fill">
            </div>
        </div>
    </div>

    @if(!empty($es_veterinaria_busqueda) && isset($mascota))
        @include('template.paciente_dependiente.menu')
        @include('template.paciente_dependiente.header')
    @elseif(!empty($es_veterinaria_busqueda) && isset($paciente))
        @include('template.usuario.menu')
        @include('template.usuario.header')
    @elseif(Auth::user()->hasRole('Asistente'))
        @include('template.asistente.menu')
        @include('template.asistente.header')
    @elseif(Auth::user()->hasRole('Institucion'))
        @include('template.administracion.menu')
        @include('template.administracion.header')
    @elseif(Auth::user()->hasRole('Servicio'))
        @include('template.administracion.menu')
        @include('template.administracion.header')
    @elseif(Auth::user()->hasRole('Paciente'))
        @if(isset($id_dependiente_activo))
            @include('template.paciente_dependiente.menu')
            @include('template.paciente_dependiente.header')
        @else
            @include('template.paciente.menu')
            @include('template.paciente.header')
        @endif
    @endif



    @yield('content')
    @include('template.partials.boton_volver_auto')
    <footer>
        {{--  @include('template.include.footer')  --}}
    </footer>

    @include('template.include.nocomplatible')

    <!-- Scripts -->
    <script src="{{ asset('js/vendor-all.min.js') }}"></script>
    <script src="{{ asset('js/plugins/bootstrap.min.js') }}"></script>
    <script src="{{ asset('js/ripple.js') }}"></script>
    <script src="{{ asset('js/pcoded.min.js') }}"></script>

    <!-- ekko-lightbox Js -->
	<script src="{{ asset('js/plugins/ekko-lightbox.min.js') }}"></script>
	<script src="{{ asset('js/plugins/lightbox.min.js') }}"></script>
	<script src="{{ asset('js/pages/ac-lightbox.js') }}"></script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ asset('js/plugins/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('js/plugins/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('js/plugins/dataTables.responsive.min.js') }}"></script>
    {{--  <script src="{{ asset('js/pages/data-responsive-custom.js') }}"></script>  --}}
    <script src="{{ asset('js/pages/data-basic-custom.js') }}"></script>

    <!-- mensajes -->
    <script src="{{ asset('js/plugins/sweetalert.min.js') }}"></script>

    <!-- fancy box -->
    <script src="{{ asset('css/fancybox/fancybox.umd.js') }}"></script>

    <!-- momnent -->
    <script src="{{ asset('js/moment.min.js') }}"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.3/jquery.validate.min.js"></script>

    <!--full calendar 5-->
    <script src='{{ asset('js/fullcalendar-5.10.1/lib/main.js') }}'></script>
    <script src='{{ asset('js/fullcalendar-5.10.1/lib/locales/es.js') }}'></script>

    <!-- autocomplete -->
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>

    <!--select2 -->
    <script src="{{ asset('js/plugins/select2.full.min.js')}}"></script>

    <!-- flatpickr -->
    <script src="{{ asset('js/flatpickr/flatpickr.min.js') }}"></script>

    <!-- TEMPLATE BUSCADOR DE PROFESIONAL -->
    <script>

        /*-Cierre de modales-*/
        // Esta plantilla carga Bootstrap 5 (que solo entiende data-bs-dismiss) y los modales del
        // sistema usan data-dismiss de Bootstrap 4; sin este puente el botón X no cerraba nada.
        $(document).on('click', '[data-dismiss="modal"]', function () {
            $(this).closest('.modal').modal('hide');
        });
    </script>

    @include("template.partials.buscador_reserva_script")

    @yield('page-script')


</body>
</html>




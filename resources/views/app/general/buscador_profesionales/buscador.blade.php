@extends('template.templateBuscadorProfesionales')

@section('content')
    <!--Container Completo-->
    <div class="pcoded-main-container">
        <div class="pcoded-content">
            <!--Reserva de hora por pasos: todo ocurre dentro de esta tarjeta.
                Hasta LG ocupa todo el ancho; desde XL se angosta y queda centrada.
                Es el mismo flujo del modal "Reservar hora" del escritorio del tutor,
                pero sin el paso Mascota: la mascota ya viene elegida desde su escritorio-->
            <div class="row justify-content-center">
                <div class="col-12 col-xl-10 col-xxl-8">
                    <div class="card" id="vista_reserva_veterinaria">
                        <div class="card-header encabezado-card">
                            <div>
                                <h5 class="encabezado-card-titulo"><span class="encabezado-card-icono"><i class="feather icon-calendar"></i></span>Reservar hora</h5>                            </div>
                        </div>
                        <div class="card-body">
                            @include("app.general.buscador_profesionales.partials.pasos_reserva")
                        </div>
                        @if(!empty($flujo_tutor))
                            <!--Los botones del paso que se está viendo se traen acá, así siempre quedan en el pie-->
                            <div class="card-footer reserva-vet-pie d-none" id="reserva_hora_pie"></div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--Cierre: Container Completo-->

    <!--Modals buscador -->
    @include("app.general.buscador_profesionales.modals.ficha_profesional")

@endsection

@if(!empty($flujo_tutor))
    @section('page-styles')
        <style>
            @include('app.general.buscador_profesionales.partials.reserva_tutor_estilos', ['ambito' => '#vista_reserva_veterinaria'])

            /* Pie de la tarjeta: volver a la izquierda, avanzar a la derecha (como el pie del modal del escritorio) */
            #vista_reserva_veterinaria .card-footer.reserva-vet-pie {
                padding: 14px 24px;
                background: #fff;
                border-top: 1px solid #edf0f5;
            }

            #vista_reserva_veterinaria .reserva-vet-pie .reserva-vet-acciones {
                display: -webkit-box;
                display: -ms-flexbox;
                display: flex;
                -ms-flex-wrap: wrap;
                flex-wrap: wrap;
                -webkit-box-align: center;
                -ms-flex-align: center;
                align-items: center;
                -webkit-box-pack: end;
                -ms-flex-pack: end;
                justify-content: flex-end;
                gap: 10px;
                margin: 0;
                padding: 0;
                border: 0;
            }

            @media (max-width: 575.98px) {
                #vista_reserva_veterinaria .card-footer.reserva-vet-pie {
                    padding: 12px;
                }
            }
        </style>
    @endsection
@endif


@section('page-script')
    @if(!empty($flujo_tutor))
        <script>
            // Las tarjetas de veterinario muestran el valor del servicio elegido, igual que en el modal
            window.mostrarValoresReserva = true;
        </script>
    @endif
    <script>
     @include("app.general.buscador_profesionales.partials.pasos_reserva_script")

     @if(!empty($flujo_tutor))
        @include('app.general.buscador_profesionales.partials.reserva_tutor_script', ['ambito' => '#vista_reserva_veterinaria'])

        // Reservada la hora se vuelve a la búsqueda, sin resultados anteriores
        window.reservaHoraAgendada = function () {
            $('#div_resultado_busqueda').html('');
            if (typeof window.reiniciarServiciosReserva === 'function') {
                window.reiniciarServiciosReserva();
            }
            reservaHoraPasos.alcanzado = PASO_BUSQUEDA_RESERVA_HORA;
            irPasoReservaHora(PASO_BUSQUEDA_RESERVA_HORA);
        };
     @endif

     @if( $filtros['id_profesion'] !=0 && $filtros['id_especialidad'] !=0 )
        buscar_profesional_especialidad();
     @endif
    </script>

    @if(!empty($flujo_tutor))
        {{-- Peluquería, hotel y cementerio: va después de los pasos porque usa sus funciones --}}
        @include('app.paciente.partials.reserva_servicios_tutor_script')
        <script>
            // Al cargar se parte en la búsqueda, sin servicio elegido y con los botones en el pie
            $(function () {
                if (typeof window.reiniciarServiciosReserva === 'function') {
                    window.reiniciarServiciosReserva();
                }
                actualizarPasosReservaHora();
            });
        </script>
    @endif
@endsection

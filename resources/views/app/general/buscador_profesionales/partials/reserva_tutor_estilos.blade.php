{{-- Estilos del flujo "Reservar hora" del tutor: resultado (valor, especialidad y lugares desplegables),
     hora elegida y servicios. Los usan el modal del escritorio del tutor y la página de reservar hora
     de cada mascota. $ambito es el contenedor donde aplican (ej. '#modal_agenda_cita_tutor'). --}}
        /* Valores: en pequeño bajo cada veterinario, servicio u hotel; sin valor cargado va en gris */
        {{ $ambito }} .reserva-vet-veterinario-valor {
            -webkit-box-flex: 0;
            -ms-flex: 0 0 100%;
            flex: 0 0 100%;
            max-width: 100%;
            margin-top: 8px;
            color: #6f42c1;
            font-size: 13px;
            font-weight: 700;
        }

        {{ $ambito }} .reserva-vet-veterinario-valor.sin-valor,
        {{ $ambito }} .reserva-servicio-sin-valor {
            color: #8a93a6;
            font-weight: 600;
        }

        {{ $ambito }} .reserva-servicio-total {
            margin: 4px 0 0;
            color: #272727;
            font-size: 14px;
            font-weight: 700;
        }

        {{ $ambito }} .reserva-servicio-fecha .titulo-item {
            border-bottom-width: 1px;
        }

        /* Especialidad del veterinario en el resultado: pastilla morada clara */
        {{ $ambito }} .reserva-vet-especialidad {
            -ms-flex-item-align: start;
            align-self: flex-start;
            display: inline-block;
            margin-top: 6px;
            padding: 3px 10px;
            border-radius: 999px;
            background: #f0e9ff;
            color: #6f42c1;
            font-size: 12px;
            font-weight: 700;
            line-height: 1.4;
        }

        /* Al desplegar los lugares de una tarjeta, la de al lado no se estira */
        {{ $ambito }} .reserva-vet-veterinarios {
            -webkit-box-align: start;
            -ms-flex-align: start;
            align-items: start;
        }

        {{ $ambito }} .reserva-vet-veterinarios .reserva-vet-veterinario {
            height: auto;
            -ms-flex-line-pack: start;
            align-content: flex-start;
        }

        /* Lugares de atención desplegables en la tarjeta del veterinario */
        {{ $ambito }} .reserva-vet-lugares-atencion {
            -webkit-box-flex: 0;
            -ms-flex: 0 0 100%;
            flex: 0 0 100%;
            max-width: 100%;
            margin-top: 10px;
        }

        {{ $ambito }} .reserva-vet-lugares-boton {
            display: -webkit-inline-box;
            display: -ms-inline-flexbox;
            display: inline-flex;
            -webkit-box-align: center;
            -ms-flex-align: center;
            align-items: center;
            gap: 6px;
            padding: 0;
            border: 0;
            background: transparent;
            color: #6f42c1;
            font-family: inherit;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
        }

        {{ $ambito }} .reserva-vet-lugares-boton:hover,
        {{ $ambito }} .reserva-vet-lugares-boton:focus-visible {
            text-decoration: underline;
            outline: none;
        }

        {{ $ambito }} .reserva-vet-lugares-flecha {
            -webkit-transition: -webkit-transform .2s ease;
            transition: transform .2s ease;
        }

        {{ $ambito }} .reserva-vet-lugares-boton.abierto .reserva-vet-lugares-flecha {
            -webkit-transform: rotate(180deg);
            transform: rotate(180deg);
        }

        {{ $ambito }} .reserva-vet-lugares-lista {
            margin-top: 8px;
            padding: 10px 12px;
            border-radius: 10px;
            background: #f7f8fb;
        }

        /* Cada lugar: nombre y dirección a la izquierda, próxima hora a la derecha (debajo en pantallas chicas) */
        {{ $ambito }} .reserva-vet-lugar-atencion {
            display: -webkit-box;
            display: -ms-flexbox;
            display: flex;
            -ms-flex-wrap: wrap;
            flex-wrap: wrap;
            -webkit-box-align: center;
            -ms-flex-align: center;
            align-items: center;
            -webkit-box-pack: justify;
            -ms-flex-pack: justify;
            justify-content: space-between;
            gap: 6px 10px;
            font-size: 13px;
            color: #5b6478;
        }

        {{ $ambito }} .reserva-vet-lugar-atencion-datos {
            display: -webkit-box;
            display: -ms-flexbox;
            display: flex;
            -webkit-box-orient: vertical;
            -webkit-box-direction: normal;
            -ms-flex-direction: column;
            flex-direction: column;
            gap: 2px;
            -webkit-box-flex: 1;
            -ms-flex: 1 1 180px;
            flex: 1 1 180px;
            min-width: 0;
        }

        {{ $ambito }} .reserva-vet-lugar-proxima {
            -ms-flex-negative: 0;
            flex-shrink: 0;
            padding: 3px 10px;
            border-radius: 999px;
            background: #e6f7f7;
            color: #239a9a;
            font-size: 12px;
            font-weight: 700;
            white-space: nowrap;
        }

        {{ $ambito }} .reserva-vet-lugar-proxima.sin-hora {
            background: #eef0f4;
            color: #8a93a6;
        }

        {{ $ambito }} .reserva-vet-lugar-atencion + .reserva-vet-lugar-atencion {
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px solid #e9ecf2;
        }

        {{ $ambito }} .reserva-vet-lugar-atencion strong {
            color: #272727;
            font-weight: 700;
        }

        {{ $ambito }} .reserva-vet-lugares-vacio {
            margin: 0;
            color: #8a93a6;
            font-size: 13px;
        }

        /* Hora elegida sin sombra (veterinario y peluquería) */
        {{ $ambito }} .reserva-vet-hora.active,
        {{ $ambito }} .reserva-vet-hora.active:hover,
        {{ $ambito }} .reserva-vet-hora.active:focus {
            -webkit-box-shadow: none;
            box-shadow: none;
        }

        /* "Próxima hora" del veterinario: solo el texto en color, sin pastilla */
        {{ $ambito }} .reserva-vet-veterinario-proxima .reserva-vet-horario-rango {
            padding: 0;
            border-radius: 0;
            background: transparent;
        }

        /* Alertas de la reserva: el botón OK en turquesa */
        body.reserva-hora-alertas .swal-button,
        body.reserva-hora-alertas .swal-button:not([disabled]):hover {
            background-color: #31bebe;
            color: #fff;
        }

        body.reserva-hora-alertas .swal-button:not([disabled]):hover {
            filter: brightness(.95);
        }

        body.reserva-hora-alertas .swal-button:focus {
            box-shadow: 0 0 0 1px #fff, 0 0 0 3px rgba(49, 190, 190, .45);
        }

        /* Ícono de peluquería u hotel en la tarjeta de resultado, en el lugar de la foto del veterinario */
        {{ $ambito }} .reserva-vet-veterinario .reserva-servicio-icono {
            -ms-flex-negative: 0;
            flex-shrink: 0;
            margin-right: 12px;
        }

        {{ $ambito }} .reserva-vet-lugar.deshabilitada {
            cursor: not-allowed;
            opacity: .6;
        }

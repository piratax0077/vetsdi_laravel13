@extends('app.peluqueria.layout')
@section('page-styles')<link rel="stylesheet" href="{{ asset('js/fullcalendar-5.10.1/lib/main.min.css') }}">@endsection
@section('content')
@if($seccion!=='clientes')<div class="page-header"><h4>{{ ['agenda'=>'Mi agenda','registro'=>'Historial de atenciones'][$seccion] }}</h4></div>@endif
@if($seccion==='agenda')
<div class="card"><div class="card-header encabezado-card"><h5 class="encabezado-card-titulo"><span class="encabezado-card-icono"><i class="feather icon-calendar"></i></span>Agenda de peluquería y belleza</h5><div class="encabezado-card-acciones"><button type="button" class="btn btn-info btn-sm" id="boton_abrir_agendar_hora"><i class="feather icon-plus mr-1"></i>Agendar hora</button><a href="{{ route('peluqueria.configuracion') }}" class="btn btn-info btn-sm"><i class="feather icon-settings mr-1"></i>Configurar procedimientos</a></div></div><div class="card-body"><div id="peluqueria-calendario" data-fecha="{{ $fecha }}" data-eventos="{{ route('peluqueria.agenda.eventos') }}" data-agenda="{{ route('peluqueria.agenda.ver') }}"></div><small class="text-muted">Elige un horario en el calendario para agendar una hora. Pulsa una atención para ver su estado y entrar a la ficha.</small></div></div>
@include('app.peluqueria.modal_reserva')
@elseif($seccion==='clientes')
<div class="row">
    <div class="col-md-12 mb-3 encabezado-pagina">
        <div>
            <h5 class="f-26 d-block mb-0">Mascotas y tutores</h5>
        </div>
        <div class="ml-auto">
            {{-- Registrar tutor y mascota queda oculto por ahora --}}
            <button type="button" class="btn btn-purple d-none" onclick="abrir_modal_tutor_mascota();"><i class="feather icon-plus"></i> Registrar tutor y mascota</button>
            <button type="button" class="btn btn-purple" onclick="abrir_difusion_tutores();"><i class="feather icon-mail"></i> Enviar mensaje de difusión</button>
        </div>
    </div>
    <div class="col-12">
        <div class="card" id="clientes">
            <div class="card-body">
                {{-- El buscador filtra la tabla en la misma página --}}
                <form id="form_buscar_cliente" class="mb-4" role="search">
                    <div class="form-row align-items-end">
                        <div class="col-md-4 mb-2">
                            <label class="floating-label-activo-sm mb-0" for="lugar_cliente">Lugar de atención</label>
                            <select id="lugar_cliente" class="form-control form-control-sm">
                                <option value="">Peluquería · {{ $perfil->nombre }}</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="floating-label-activo-sm mb-0" for="buscar_cliente">Buscar</label>
                            <input type="search" id="buscar_cliente" class="form-control form-control-sm" placeholder="Buscar por tutor, RUT o nombre de mascota">
                        </div>
                        <div class="col-md-2 mb-2">
                            <button class="btn btn-sm btn-block btn-info" type="submit"><i class="feather icon-search"></i> Buscar</button>
                        </div>
                    </div>
                </form>
                @if($clientes->isEmpty())
                    <div class="aviso-sin-registros">
                        <span class="aviso-sin-registros-icono"><i class="feather icon-users"></i></span>
                        <div>
                            <p class="aviso-sin-registros-titulo">Aún no hay mascotas registradas</p>
                            <p class="aviso-sin-registros-texto">Usa «Registrar tutor y mascota» para agregar al primer tutor con su mascota.</p>
                        </div>
                    </div>
                @else
                    <div class="table-responsive" id="contenedor_tabla_clientes">
                        <table class="table table-striped nowrap tabla-configuracion mb-0" id="tabla_clientes">
                            <thead>
                                <tr>
                                    <th class="align-middle">Mascota</th>
                                    <th class="align-middle">Tutor</th>
                                    <th class="align-middle">Contacto</th>
                                    <th class="align-middle">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($clientes as $cliente)
                                    @php
                                        // Un celular chileno (9 dígitos que parten en 9, con o sin +56) se abre en WhatsApp; otro número, como llamada
                                        $digitos_tel = preg_replace('/\D/', '', (string) $cliente->telefono);
                                        $whatsapp_tel = preg_match('/^(?:56)?(9\d{8})$/', $digitos_tel, $celular) ? '56' . $celular[1] : null;
                                        $rut_tutor = $cliente->rut ?? null;
                                        // Las mascotas agendadas guardan el tipo de exótico aparte; se muestra igual que "Exótico (Conejo)"
                                        $especie_texto = $cliente->especie === 'Exótico' && !empty($cliente->especie_exotico) ? 'Exótico (' . $cliente->especie_exotico . ')' : $cliente->especie;
                                        // Los datos van en la fila para llenar los modales de acciones
                                        $datos_cliente = [
                                            'tutor' => $cliente->tutor,
                                            'rut' => $rut_tutor,
                                            'telefono' => $cliente->telefono,
                                            'telefono_digitos' => $digitos_tel,
                                            'whatsapp' => $whatsapp_tel,
                                            'email' => $cliente->email,
                                            'mascota' => $cliente->mascota,
                                            'especie' => $especie_texto,
                                            'raza' => $cliente->raza,
                                        ];
                                    @endphp
                                    <tr class="fila-cliente" data-cliente="{{ json_encode($datos_cliente) }}">
                                        <td>
                                            <strong>{{ $cliente->mascota }}</strong>
                                            <span class="config-detalle">{{ collect([$especie_texto, $cliente->raza])->filter()->implode(' · ') }}</span>
                                        </td>
                                        <td>
                                            {{ $cliente->tutor }}
                                            <span class="config-detalle">{{ $rut_tutor ? 'RUT ' . $rut_tutor : 'RUT no registrado' }}</span>
                                        </td>
                                        <td>
                                            @if($cliente->telefono)
                                                <span class="dato-icono">
                                                    @if($whatsapp_tel)
                                                        <i class="feather icon-message-circle icono-purple" aria-hidden="true"></i>
                                                        <a class="dato-enlace" href="https://wa.me/{{ $whatsapp_tel }}" target="_blank" rel="noopener" title="Escribir por WhatsApp">{{ $cliente->telefono }}</a>
                                                    @else
                                                        <i class="feather icon-phone icono-purple" aria-hidden="true"></i>
                                                        <a class="dato-enlace" href="tel:{{ $digitos_tel }}" title="Llamar">{{ $cliente->telefono }}</a>
                                                    @endif
                                                </span>
                                            @endif
                                            @if($cliente->email)
                                                <span class="dato-icono">
                                                    <i class="feather icon-mail icono-purple" aria-hidden="true"></i>
                                                    <span class="dato-correo">{{ $cliente->email }}</span>
                                                    <button type="button" class="boton-copiar" data-copiar="{{ $cliente->email }}" title="Copiar correo" aria-label="Copiar el correo {{ $cliente->email }}"><i class="feather icon-copy" aria-hidden="true"></i></button>
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="acciones-tabla">
                                                <a href="{{ route('peluqueria.mascotas.tutor', $cliente->id) }}" class="btn btn-icon btn-info btn-sm js-tooltip-accion" title="Información del tutor" aria-label="Información del tutor">
                                                    <i class="feather icon-info"></i>
                                                </a>
                                                <button type="button" class="btn btn-icon btn-primary btn-sm js-tooltip-accion" onclick="abrir_notas_tutor();" title="Notas internas del tutor" aria-label="Notas internas del tutor">
                                                    <i class="feather icon-clock"></i>
                                                </button>
                                                <button type="button" class="btn btn-icon btn-purple btn-sm js-tooltip-accion" onclick="abrir_mensaje_tutor(this);" title="Enviar mensaje al tutor" aria-label="Enviar mensaje al tutor">
                                                    <i class="feather icon-mail"></i>
                                                </button>
                                                <button type="button" class="btn btn-icon btn-danger btn-sm js-tooltip-accion" onclick="abrir_bloquear_tutor(this);" title="Bloquear tutor" aria-label="Bloquear tutor">
                                                    <i class="feather icon-lock"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="aviso-sin-registros d-none" id="clientes_sin_resultados">
                        <span class="aviso-sin-registros-icono"><i class="feather icon-search"></i></span>
                        <div>
                            <p class="aviso-sin-registros-titulo">Sin resultados</p>
                            <p class="aviso-sin-registros-texto">Ninguna mascota o tutor coincide con la búsqueda.</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@include('app.peluqueria.modal_tutor_mascota')
@include('app.peluqueria.modal_notas_tutor')
@include('app.peluqueria.modal_mensaje_tutor')
@include('app.peluqueria.modal_bloquear_tutor')
@include('app.peluqueria.modal_difusion_tutores')
@else
<section class="card" id="registro"><div class="card-body"><h2>Servicios realizados</h2><div class="table-responsive"><table class="table table-sm table-striped"><thead><tr><th>Fecha</th><th>Mascota / tutor</th><th>Servicio</th><th>Registro</th><th>Valor</th></tr></thead><tbody>@forelse($historial as $atencion)<tr><td>{{ \Carbon\Carbon::parse($atencion->inicio)->format('d-m-Y H:i') }}</td><td>{{ $atencion->mascota }}<br>{{ $atencion->tutor }}</td><td>{{ $atencion->servicio }}<br><a href="{{ route('peluqueria.ficha',$atencion->id) }}">Ver ficha</a></td><td class="texto-con-saltos">{{ $atencion->registro }}</td><td>${{ number_format($atencion->valor,0,',','.') }}</td></tr>@empty<tr><td colspan="5">Las atenciones finalizadas aparecerán aquí.</td></tr>@endforelse</tbody></table></div>{{ $historial->withQueryString()->links() }}</div></section>

@endif
@endsection
@section('page-script')
<script src="{{ asset('js/fullcalendar-5.10.1/lib/main.min.js') }}"></script><script src="{{ asset('js/fullcalendar-5.10.1/lib/locales/es.js') }}"></script><script src="{{ asset('js/peluqueria-agenda.js') }}?v=20261023-16"></script>
@if($seccion==='clientes')
<script>
    // Abre el registro; si el guardado anterior volvió con error, el formulario conserva lo escrito
    function abrir_modal_tutor_mascota() {
        var formulario = document.getElementById('form_tutor_mascota');
        formulario.reset();
        formulario.classList.remove('was-validated');
        mostrar_exotico_cliente();
        document.getElementById('boton_guardar_tutor_mascota').disabled = false;
        $('#modal_tutor_mascota').modal('show');
    }

    // Con "Exótico" se pide qué animal es; oculto queda deshabilitado para que no se revise
    function mostrar_exotico_cliente() {
        var exotico = $('#cliente_especie').val() === 'Exótico';
        $('#cliente_exotico_grupo').toggleClass('d-none', !exotico);
        $('#cliente_exotico').prop('disabled', !exotico);
    }
    $('#cliente_especie').on('change', function () {
        mostrar_exotico_cliente();
        if (this.value === 'Exótico') $('#cliente_exotico').trigger('focus');
    });

    // Si falta algo se marca el campo en vez de enviar; si está todo, se arman el tutor y la especie que se guardan
    document.getElementById('form_tutor_mascota').addEventListener('submit', function (evento) {
        var especie = $('#cliente_especie').val();
        $(this).find('input[type="text"], input[type="email"], textarea').each(function () { this.value = this.value.trim(); });
        if (!this.checkValidity()) {
            evento.preventDefault();
            this.classList.add('was-validated');
            return;
        }
        $('#cliente_tutor').val([$('#cliente_nombres').val(), $('#cliente_apellido_uno').val(), $('#cliente_apellido_dos').val()].filter(Boolean).join(' '));
        $('#cliente_especie_valor').val(especie === 'Exótico' ? 'Exótico (' + $('#cliente_exotico').val() + ')' : especie);
        document.getElementById('boton_guardar_tutor_mascota').disabled = true;
    });

    // Copiar el correo del tutor; si el navegador no deja usar el portapapeles moderno, se copia a la antigua
    $(document).on('click', '.boton-copiar', function () {
        var boton = $(this);
        var texto = boton.data('copiar');

        function avisar_copiado() {
            boton.addClass('copiado').attr('title', 'Copiado').html('<i class="feather icon-check" aria-hidden="true"></i>');
            setTimeout(function () {
                boton.removeClass('copiado').attr('title', 'Copiar correo').html('<i class="feather icon-copy" aria-hidden="true"></i>');
            }, 1500);
        }

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(texto).then(avisar_copiado);
            return;
        }
        var campo = $('<textarea readonly>').val(texto).css({ position: 'fixed', top: '-1000px' }).appendTo('body');
        campo.get(0).select();
        try {
            document.execCommand('copy');
            avisar_copiado();
        } catch (e) {}
        campo.remove();
    });

    // Buscador de la tabla: compara sin mayúsculas ni tildes, mientras se escribe o al pulsar Buscar
    function texto_sin_tildes(texto) {
        texto = texto.toLowerCase();
        return texto.normalize ? texto.normalize('NFD').replace(/[̀-ͯ]/g, '') : texto;
    }
    function filtrar_clientes() {
        var buscado = texto_sin_tildes($.trim($('#buscar_cliente').val()));
        var encontrados = 0;
        $('#tabla_clientes .fila-cliente').each(function () {
            var coincide = texto_sin_tildes(this.textContent).indexOf(buscado) !== -1;
            $(this).toggleClass('d-none', !coincide);
            if (coincide) encontrados++;
        });
        $('#contenedor_tabla_clientes').toggleClass('d-none', encontrados === 0);
        $('#clientes_sin_resultados').toggleClass('d-none', encontrados > 0);
    }
    $('#buscar_cliente').on('input', filtrar_clientes);
    $('#form_buscar_cliente').on('submit', function (evento) {
        evento.preventDefault();
        filtrar_clientes();
    });

    // Datos de la fila desde la que se abrió una acción
    function datos_cliente(boton) {
        return $(boton).closest('.fila-cliente').data('cliente');
    }
    // Notas internas del tutor: el mismo bloque de la pantalla del tutor, siempre parte vacío
    function abrir_notas_tutor() {
        reiniciar_notas_tutor();
        $('#modal_notas_tutor').modal('show');
    }

    // Mensaje al tutor: se arma el correo o el WhatsApp con lo escrito y se abre en la aplicación del equipo
    var tutor_mensaje = null;
    function abrir_mensaje_tutor(boton) {
        var formulario = document.getElementById('form_mensaje_tutor');
        tutor_mensaje = datos_cliente(boton);
        formulario.reset();
        formulario.classList.remove('was-validated');
        $('#mensaje_tutor_nombre').text(tutor_mensaje.tutor);
        $('#mensaje_tutor_contacto').text([tutor_mensaje.email, tutor_mensaje.telefono].filter(Boolean).join(' · '));
        $('#mensaje_tutor_asunto').val('Mensaje sobre ' + tutor_mensaje.mascota);
        $('#boton_mensaje_whatsapp').toggleClass('d-none', !tutor_mensaje.whatsapp);
        $('#boton_mensaje_correo').toggleClass('d-none', !tutor_mensaje.email);
        $('#mensaje_tutor_sin_medio').toggleClass('d-none', !!(tutor_mensaje.whatsapp || tutor_mensaje.email));
        $('#modal_mensaje_tutor').modal('show');
    }
    $('.boton-enviar-mensaje').on('click', function () {
        var formulario = document.getElementById('form_mensaje_tutor');
        var texto = $.trim($('#mensaje_tutor_texto').val());
        var asunto = $.trim($('#mensaje_tutor_asunto').val());

        $('#mensaje_tutor_texto').val(texto);
        if (!formulario.checkValidity()) {
            formulario.classList.add('was-validated');
            return;
        }
        if ($(this).data('via') === 'whatsapp') {
            window.open('https://wa.me/' + tutor_mensaje.whatsapp + '?text=' + encodeURIComponent(texto), '_blank', 'noopener');
        } else {
            window.location.href = 'mailto:' + tutor_mensaje.email + '?subject=' + encodeURIComponent(asunto) + '&body=' + encodeURIComponent(texto);
        }
        $('#modal_mensaje_tutor').modal('hide');
    });
    $('#form_mensaje_tutor').on('submit', function (evento) { evento.preventDefault(); });

    // Bloquear tutor: todavía no hay dónde guardarlo, así que se confirma y se avisa
    function abrir_bloquear_tutor(boton) {
        $('#bloquear_tutor_nombre').text(datos_cliente(boton).tutor);
        $('#aviso_bloquear_tutor').addClass('d-none').text('');
        $('#bloquear_tutor_motivo').val('').removeClass('is-invalid');
        $('#modal_bloquear_tutor').modal('show');
    }
    $('#boton_bloquear_tutor').on('click', function () {
        var motivo = $('#bloquear_tutor_motivo');
        motivo.val($.trim(motivo.val())).toggleClass('is-invalid', motivo.val() === '');
        if (motivo.val() === '') return;
        $('#aviso_bloquear_tutor').removeClass('d-none').text('El bloqueo de tutores aún no está disponible.');
    });

    // Difusión: se arma un correo con asunto y mensaje, y todos los tutores en copia oculta
    function abrir_difusion_tutores() {
        var formulario = document.getElementById('form_difusion_tutores');
        formulario.reset();
        formulario.classList.remove('was-validated');
        $('#modal_difusion_tutores').modal('show');
    }
    $('#form_difusion_tutores').on('submit', function (evento) {
        evento.preventDefault();
        $('#difusion_asunto, #difusion_mensaje').each(function () { this.value = $.trim(this.value); });
        if (!this.checkValidity()) {
            this.classList.add('was-validated');
            return;
        }
        window.location.href = 'mailto:?bcc=' + encodeURIComponent($(this).data('correos'))
            + '&subject=' + encodeURIComponent($('#difusion_asunto').val())
            + '&body=' + encodeURIComponent($('#difusion_mensaje').val());
        $('#modal_difusion_tutores').modal('hide');
    });

    // Tooltips de los botones de acción, solo si está el de Bootstrap
    $(function () {
        if (!$.fn.tooltip || !$.fn.tooltip.Constructor) return;
        $('.js-tooltip-accion').tooltip({ container: 'body', trigger: 'hover' }).on('click', function () { $(this).tooltip('hide'); });
    });

    @if($errors->any() && old('mascota') !== null)
        $(function () { $('#modal_tutor_mascota').modal('show'); });
    @endif
</script>
@endif

@endsection
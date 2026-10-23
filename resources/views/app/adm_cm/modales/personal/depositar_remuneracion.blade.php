<!--Depositar remuneración: datos del profesional y la cuenta donde se deposita-->
<div id="depositar_remuneracion" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="depositar_remuneracion_titulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header modal-header-purple">
                <h5 class="modal-title d-inline mt-1" id="depositar_remuneracion_titulo">Depositar remuneraci&oacute;n</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <h6 class="t-modal" id="depositar_persona_titulo">Profesional</h6>
                <div class="ficha-profesional__datos mb-3">
                    <div class="ficha-profesional__dato ficha-profesional__dato--ancho">
                        <span class="ficha-profesional__icono"><i class="feather icon-user" aria-hidden="true"></i></span>
                        <div>
                            <span class="ficha-profesional__etiqueta">Nombre</span>
                            <span class="ficha-profesional__valor" id="depositar_nombre"></span>
                        </div>
                    </div>
                    <div class="ficha-profesional__dato">
                        <span class="ficha-profesional__icono"><i class="feather icon-credit-card" aria-hidden="true"></i></span>
                        <div>
                            <span class="ficha-profesional__etiqueta">RUT</span>
                            <span class="ficha-profesional__valor" id="depositar_rut"></span>
                        </div>
                    </div>
                    <div class="ficha-profesional__dato">
                        <span class="ficha-profesional__icono"><i class="feather icon-mail" aria-hidden="true"></i></span>
                        <div>
                            <span class="ficha-profesional__etiqueta">Correo electr&oacute;nico</span>
                            <span class="ficha-profesional__valor" id="depositar_email"></span>
                        </div>
                    </div>
                </div>

                <h6 class="t-modal">Cuenta para el dep&oacute;sito</h6>
                <div class="ficha-profesional__datos" id="depositar_cuenta">
                    <div class="ficha-profesional__dato ficha-profesional__dato--ancho">
                        <span class="ficha-profesional__icono"><i class="fas fa-university" aria-hidden="true"></i></span>
                        <div>
                            <span class="ficha-profesional__etiqueta">Banco</span>
                            <span class="ficha-profesional__valor" id="depositar_banco"></span>
                        </div>
                    </div>
                    <div class="ficha-profesional__dato">
                        <span class="ficha-profesional__icono"><i class="feather icon-hash" aria-hidden="true"></i></span>
                        <div>
                            <span class="ficha-profesional__etiqueta">N&deg; de cuenta</span>
                            <span class="ficha-profesional__valor" id="depositar_numero"></span>
                        </div>
                    </div>
                    <div class="ficha-profesional__dato">
                        <span class="ficha-profesional__icono"><i class="feather icon-file-text" aria-hidden="true"></i></span>
                        <div>
                            <span class="ficha-profesional__etiqueta" id="depositar_detalle_etiqueta">Tipo de cuenta</span>
                            <span class="ficha-profesional__valor" id="depositar_detalle"></span>
                        </div>
                    </div>
                </div>
                <div class="aviso-sin-registros d-none" id="depositar_sin_cuenta">
                    <span class="aviso-sin-registros-icono"><i class="feather icon-alert-circle"></i></span>
                    <div>
                        <p class="aviso-sin-registros-titulo">Sin cuenta bancaria</p>
                        <p class="aviso-sin-registros-texto">El profesional a&uacute;n no tiene una cuenta registrada para recibir el dep&oacute;sito.</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-dark" data-dismiss="modal">Cancelar</button>
                {{-- Pendiente para programación: el depósito todavía no tiene proceso en el servidor --}}
                <button type="button" class="btn btn-info" id="depositar_boton">Depositar</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Nombres de banco para la cuenta del contrato (el contrato solo guarda el id)
    const bancosDeposito = {{ \Illuminate\Support\Js::from(collect($bancos ?? [])->pluck('nombre', 'id')) }};

    // Muestra los datos de la persona y su cuenta; los asistentes solo tienen las cuentas de su perfil
    function mostrar_deposito(persona, cuentas, contrato)
    {
        $('#depositar_persona_titulo').text(persona.titulo);
        $('#depositar_nombre').text(persona.nombre || 'No informado');
        $('#depositar_rut').text(persona.rut ? String(persona.rut).toUpperCase() : 'No informado');
        $('#depositar_email').text(persona.email || 'No informado');

        // Primero la cuenta principal del perfil; si no tiene, la que quedó en el contrato
        let cuenta = cuentas.find(function(c) { return c.principal == 1; }) || cuentas[0];
        let hayCuenta = true;
        if(cuenta)
        {
            $('#depositar_banco').text(cuenta.banco ? cuenta.banco.nombre : 'No informado');
            $('#depositar_numero').text(cuenta.numero_control || 'No informado');
            $('#depositar_detalle_etiqueta').text('Tipo de cuenta');
            $('#depositar_detalle').text(cuenta.otro || 'No informado');
        }
        else if(contrato && contrato.numero_cuenta)
        {
            $('#depositar_banco').text(bancosDeposito[contrato.id_banco] || 'No informado');
            $('#depositar_numero').text(contrato.numero_cuenta);
            $('#depositar_detalle_etiqueta').text('Sucursal');
            $('#depositar_detalle').text(contrato.sucursal || 'No informado');
        }
        else
        {
            hayCuenta = false;
        }

        $('#depositar_cuenta').toggleClass('d-none', !hayCuenta);
        $('#depositar_sin_cuenta').toggleClass('d-none', hayCuenta);
        $('#depositar_sin_cuenta .aviso-sin-registros-texto').text(persona.aviso_sin_cuenta);
        $('#depositar_boton').prop('disabled', !hayCuenta);
        $('#depositar_remuneracion').modal('show');
    }

    function depositar_remuneracion(id_persona, tipo)
    {
        if(tipo === 'asistente')
        {
            depositar_remuneracion_asistente(id_persona);
            return;
        }

        let id_profesional = id_persona;
        let url = "{{ route('laboratorio.profesional_buscar', ['id_profesional' => '__id__']) }}".replace('__id__', id_profesional);
        $.ajax({ url: url, type: 'get' })
        .done(function(data) {
            if(data.estado != 1)
            {
                swal({ title: 'Depositar remuneración', text: 'No fue posible cargar la información del profesional.', icon: 'error', buttons: 'Aceptar' });
                return;
            }

            let prof = data.registro;
            mostrar_deposito({
                titulo: 'Profesional',
                nombre: [prof.nombre, prof.apellido_uno, prof.apellido_dos].filter(Boolean).join(' '),
                rut: prof.rut,
                email: prof.email,
                aviso_sin_cuenta: 'El profesional aún no tiene una cuenta registrada para recibir el depósito.',
            }, data.cuentas || [], prof.contrato || {});
        })
        .fail(function(jqXHR, ajaxOptions, thrownError) {
            console.log(jqXHR, ajaxOptions, thrownError);
        });
    }

    // Asistente: sus datos vienen de la ficha del asistente y las cuentas, de su perfil de usuario
    function depositar_remuneracion_asistente(id_asistente)
    {
        $.get("{{ route('adm_cm.asistente_buscar') }}", { id: id_asistente })
        .done(function(data) {
            if(!data || data.estado != 1)
            {
                swal({ title: 'Depositar remuneración', text: 'No fue posible cargar la información del asistente.', icon: 'error', buttons: 'Aceptar' });
                return;
            }

            let asistente = data.registro;
            let persona = {
                titulo: 'Asistente',
                nombre: [asistente.nombres, asistente.apellido_uno, asistente.apellido_dos].filter(Boolean).join(' '),
                rut: asistente.rut,
                email: asistente.email,
                aviso_sin_cuenta: 'El asistente aún no tiene una cuenta registrada para recibir el depósito.',
            };

            $.get("{{ route('profesional.liquidacion_ver_profesional') }}", { id_seccion: asistente.id_usuario })
            .done(function(respuesta) {
                mostrar_deposito(persona, (respuesta && respuesta.estado == 1 && respuesta.registros) ? respuesta.registros : [], null);
            })
            .fail(function() {
                mostrar_deposito(persona, [], null);
            });
        })
        .fail(function(jqXHR, ajaxOptions, thrownError) {
            console.log(jqXHR, ajaxOptions, thrownError);
        });
    }
</script>

{{-- Diseño compartido del modal "Cuenta bancaria" (#dat_bancarios): estilos y pintado de las cuentas --}}
<style>
    /* Cuenta bancaria: tarjeta limpia, sin íconos en los datos */
    #dat_bancarios .cuenta-pestanas { border: 0; margin: 0 0 14px; gap: 6px; padding: 0; }
    #dat_bancarios .cuenta-pestanas:empty { display: none; }
    #dat_bancarios .cuenta-pestanas .nav-link {
        border-radius: 20px; padding: 5px 14px; font-size: 13px; font-weight: 700;
        color: #272727; background: #f1f1f6; border: 1px solid transparent;
    }
    #dat_bancarios .cuenta-pestanas .nav-link.active { background: #6f42c1; color: #fff; }

    #dat_bancarios .cuenta-tarjeta { border: 1px solid #e6e6ee; border-radius: 10px; overflow: hidden; background: #fff; }
    #dat_bancarios .cuenta-resumen {
        display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between;
        gap: 8px 12px; padding: 16px 18px; background: #f7f5fc; border-bottom: 1px solid #e6e6ee;
    }
    #dat_bancarios .cuenta-banco { margin: 0; font-size: 18px; font-weight: 800; color: #272727; line-height: 1.2; }
    #dat_bancarios .cuenta-numero { margin: 2px 0 0; font-size: 14px; color: #5b5b66; word-break: break-all; }
    #dat_bancarios .cuenta-numero strong { color: #272727; letter-spacing: .5px; }
    #dat_bancarios .cuenta-etiqueta {
        display: inline-block; padding: 3px 12px; border-radius: 20px; font-size: 12px; font-weight: 700;
        background: #e9e3f7; color: #5a35a0; white-space: nowrap;
    }
    #dat_bancarios .cuenta-etiqueta.opcional { background: #eeeeee; color: #5b5b66; }

    #dat_bancarios .cuenta-datos { display: grid; grid-template-columns: 1fr 1fr; margin: 0; padding: 6px 18px 10px; }
    #dat_bancarios .cuenta-dato { padding: 10px 12px 10px 0; border-bottom: 1px solid #f0f0f5; min-width: 0; }
    #dat_bancarios .cuenta-dato.ancho-completo { grid-column: 1 / -1; }
    #dat_bancarios .cuenta-dato:last-child { border-bottom: 0; }
    #dat_bancarios .cuenta-dato dt { margin: 0 0 2px; font-size: 12px; font-weight: 700; color: #272727; text-transform: uppercase; letter-spacing: .4px; opacity: .65; }
    #dat_bancarios .cuenta-dato dd { margin: 0; font-size: 15px; color: #272727; word-break: break-word; }

    @media (max-width: 575.98px) {
        #dat_bancarios .cuenta-datos { grid-template-columns: 1fr; }
        #dat_bancarios .cuenta-dato { padding-right: 0; }
    }
</style>
<script>
    /** Dibuja las cuentas bancarias dentro del modal #dat_bancarios */
    function pintar_cuentas_bancarias(registros)
    {
        $('#liquidaciones').html('');
        $('#liquidacion_pills').html('');

        /** evita que un dato con caracteres especiales rompa el HTML */
        function limpiar(valor)
        {
            return $('<div>').text(valor === null || valor === undefined || valor === '' ? '-' : valor).html();
        }

        function dato(titulo, valor, ancho)
        {
            return '<div class="cuenta-dato' + (ancho ? ' ancho-completo' : '') + '"><dt>' + titulo + '</dt><dd>' + limpiar(valor) + '</dd></div>';
        }

        /** pestaña activa: la cuenta principal, o la primera si ninguna lo es */
        var activa = 0;
        $.each(registros, function(index, element) {
            if (element.principal == 1) { activa = index; return false; }
        });

        $.each(registros, function(index, element) {
            var es_activa = index === activa;
            var nombre_banco = limpiar(element.banco.nombre);

            /** pestañas: solo se muestran si hay más de una cuenta */
            if (registros.length > 1)
            {
                $('#liquidacion_pills').append(
                    '<li class="nav-item"><a class="nav-link' + (es_activa ? ' active' : '') + '" id="liquidacion_'+index+'-tab" data-toggle="tab" href="#liquidacion_'+index+'" role="tab" aria-controls="liquidacion_'+index+'" aria-selected="' + es_activa + '">' + nombre_banco + '</a></li>'
                );
            }

            var html = '';
            html += '<div class="tab-pane fade' + (es_activa ? ' active show' : '') + '" id="liquidacion_'+index+'" role="tabpanel" aria-labelledby="liquidacion_'+index+'-tab">';
            html += '  <div class="cuenta-tarjeta">';
            html += '    <div class="cuenta-resumen">';
            html += '      <div>';
            html += '        <h6 class="cuenta-banco">' + nombre_banco + '</h6>';
            html += '        <p class="cuenta-numero">' + limpiar(element.otro) + ' · N° <strong>' + limpiar(element.numero_control) + '</strong></p>';
            html += '      </div>';
            html += element.principal == 1
                ? '      <span class="cuenta-etiqueta">Principal</span>'
                : '      <span class="cuenta-etiqueta opcional">Opcional</span>';
            html += '    </div>';
            html += '    <dl class="cuenta-datos">';
            html +=        dato('Titular', element.autor, true);
            html +=        dato('RUT', element.serie, false);
            html +=        dato('Correo electrónico', element.email, false);
            html += '    </dl>';
            html += '  </div>';
            html += '</div>';
            $('#liquidaciones').append(html);
        });
    }
</script>

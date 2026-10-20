(function () {
    'use strict';
    function iniciar() {
        const $ = window.jQuery;
        if (!$) return;
        window.conveniosVeterinariosListos = function () {
            const $select = $('.modal:visible .bono-convenio-veterinario');
            if ($select.data('cargando') || $select.data('errorConvenios')) {
                const mensaje = $select.data('cargando') ? 'Espera a que termine la consulta de convenios.' : 'No se pudieron consultar los convenios. Cierra y vuelve a abrir la recepción para reintentar.';
                if (window.swal) window.swal({ title: 'Convenios veterinarios', text: mensaje, icon: 'warning' });
                return false;
            }
            return true;
        };
        function resumen($select) {
            const $modal = $select.closest('.modal');
            const valor = Math.max(0, Number($modal.find('#bono_valor_consulta').val()) || 0);
            const abono = Math.max(0, Number($modal.find('#bono_valor_abono_consulta').val()) || 0);
            const porcentaje = Number($select.find('option:selected').attr('data-porcentaje')) || 0;
            const descuento = Math.round(valor * porcentaje / 100);
            const total = valor - descuento;
            $modal.find('#bono_valor_saldo_consulta').val(Math.max(0, total - abono));
            $modal.find('.resumen-convenio-veterinario').text('Descuento: $' + descuento.toLocaleString('es-CL') + ' · Total: $' + total.toLocaleString('es-CL'));
        }
        $(document).on('input change', '#bono_valor_consulta, #bono_valor_abono_consulta, .bono-convenio-veterinario', function () {
            $(this).closest('.modal').find('.bono-convenio-veterinario').each(function () { resumen($(this)); });
        });
        $(document).on('shown.bs.modal', '.modal', function () {
            const $modal = $(this);
            $modal.find('.bono-convenio-veterinario').each(function () {
                const $select = $(this);
                const hora = $modal.find('#bono_hora_medica').val();
                if (!hora) return;
                const solicitud = {};
                $select.data('solicitud', solicitud).data('cargando', true).data('errorConvenios', false).prop('disabled', true).empty().append(new Option('Sin convenio', ''));
                $.getJSON($select.attr('data-url'), { id_hora_medica: hora }).done(function (data) {
                    if ($select.data('solicitud') !== solicitud) return;
                    const opciones = data.convenios || [];
                    if (data.aplicado && !opciones.some(function (c) { return Number(c.id) === Number(data.aplicado.id); })) opciones.push(data.aplicado);
                    opciones.forEach(function (c) {
                        // Usar el porcentaje histórico para el convenio ya acordado.
                        const porcentaje = data.aplicado && Number(c.id) === Number(data.aplicado.id) ? data.aplicado.porcentaje : c.porcentaje;
                        const opcion = new Option(c.nombre + ' (' + porcentaje + '%)', c.id);
                        opcion.setAttribute('data-porcentaje', porcentaje);
                        $select.append(opcion);
                    });
                    $select.val(data.seleccionado ? String(data.seleccionado) : '');
                    resumen($select);
                }).fail(function () {
                    if ($select.data('solicitud') !== solicitud) return;
                    $select.data('errorConvenios', true);
                    $modal.find('.resumen-convenio-veterinario').text('No se pudieron consultar los convenios. Cierra y vuelve a abrir la recepción para reintentar.');
                }).always(function () {
                    if ($select.data('solicitud') === solicitud) $select.data('cargando', false).prop('disabled', false);
                });
            });
        });
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', iniciar);
    else iniciar();
}());

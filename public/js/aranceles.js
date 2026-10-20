/* ==========================================================================
   Módulo de Aranceles (Vet SDI) — datos por profesional en el servidor
   Vista: resources/views/app/profesional/aranceles_veterinaria_general.blade.php

   Este archivo tiene 3 partes independientes:
     1. CÁLCULO Y FORMATO  -> calcularPrecioFinal(), formatearCLP(), calcularMargen()
     2. SERVICIO DE DATOS  -> listar / crear / editar / cambiarEstado por cuenta
     3. INTERFAZ           -> pestañas, tabla, modales y validaciones

   Para conectar la API real solo se reemplaza la parte 2 (el objeto "servicio"),
   manteniendo los mismos nombres de función y la misma forma de los datos.
   ========================================================================== */
(function (window, $) {
    'use strict';

    /* ======================================================================
       1. CÁLCULO Y FORMATO
       ====================================================================== */

    function numero(valor) {
        var n = Number(valor);
        return isFinite(n) ? n : 0;
    }

    function buscar(lista, campo, valor) {
        lista = lista || [];
        for (var i = 0; i < lista.length; i++) {
            if (String(lista[i][campo]) === String(valor)) return lista[i];
        }
        return null;
    }

    /**
     * Formateador único de moneda: CLP sin decimales y con punto de miles.
     * formatearCLP(45000) -> "$45.000"
     */
    function formatearCLP(valor) {
        if (valor === null || valor === undefined || valor === '' || isNaN(Number(valor))) return '—';
        var n = Math.round(Number(valor));
        var texto = String(Math.abs(n)).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        return (n < 0 ? '-' : '') + '$' + texto;
    }

    /**
     * Función única para calcular el precio final de un ítem.
     *   1. Precio base: valor único, o el del tamaño del paciente si varía por tamaño.
     *   2. Se suma el recargo del lugar de atención.
     *   3. Se agrega el IVA solo si el ítem (o la fila del tamaño) es afecto.
     *
     * @param {Object} opciones
     * @param {Object} opciones.item      Ítem del catálogo (ver "Forma de los datos").
     * @param {Object} opciones.config    Configuración (lugares, tamaños, parámetros).
     * @param {string} [opciones.tamanoId] Tamaño del paciente (obligatorio si el ítem varía por tamaño).
     * @param {string} [opciones.lugarId]  Lugar de atención. Sin lugar = sin recargo.
     * @returns {{disponible:boolean, base:number, recargoPorcentaje:number, recargo:number,
     *            neto:number, iva:number, total:number}}
     *          disponible=false cuando el ítem no tiene precio activo para ese tamaño.
     */
    function calcularPrecioFinal(opciones) {
        opciones = opciones || {};
        var item = opciones.item || {};
        var config = opciones.config || {};
        var parametros = config.parametros || {};
        var base, afecto;

        if (item.variaPorTamano) {
            var fila = buscar(item.preciosTamano, 'tamanoId', opciones.tamanoId);
            if (!fila || !fila.activo || !(numero(fila.valorNeto) > 0)) {
                return { disponible: false, base: 0, recargoPorcentaje: 0, recargo: 0, neto: 0, iva: 0, total: 0 };
            }
            base = numero(fila.valorNeto);
            afecto = !!fila.afectoIva;
        } else {
            base = numero(item.valorNeto);
            afecto = !!item.afectoIva;
        }

        var lugar = opciones.lugarId ? buscar(config.lugares, 'id', opciones.lugarId) : null;
        var porcentaje = lugar ? numero(lugar.recargo) : 0; // recargo "por definir" (null) cuenta como 0
        var recargo = Math.round(base * porcentaje / 100);
        var neto = base + recargo;
        var iva = afecto ? Math.round(neto * numero(parametros.iva) / 100) : 0;

        return {
            disponible: true,
            base: base,
            recargoPorcentaje: porcentaje,
            recargo: recargo,
            neto: neto,
            iva: iva,
            total: neto + iva
        };
    }

    /**
     * Margen sobre el valor neto. Devuelve null si no hay costo ingresado.
     * @returns {{monto:number, porcentaje:number}|null}
     */
    function calcularMargen(valorNeto, costo) {
        if (costo === null || costo === undefined || costo === '') return null;
        var neto = numero(valorNeto);
        if (!(neto > 0)) return null;
        var monto = neto - numero(costo);
        return { monto: monto, porcentaje: Math.round(monto / neto * 1000) / 10 };
    }

    /* ======================================================================
       2. SERVICIO DE DATOS (API autenticada por profesional)

       Forma de los datos
       ------------------
       Ítem (campos comunes a los 3 catálogos):
         id              string   Lo genera el servicio.
         codigo          string   Único entre los 3 catálogos. Ej: "CON-001".
         nombre          string
         valorNeto       number   CLP entero. Se usa cuando variaPorTamano = false.
         afectoIva       boolean
         costoInterno    number|null
         variaPorTamano  boolean
         preciosTamano   Array<{ tamanoId:string, valorNeto:number, afectoIva:boolean,
                                 costoInterno:number|null, activo:boolean }>
         activo          boolean

       Solo aranceles:      categoria, descripcion, especie, lugarId ('' = todos),
                            vigenteDesde, vigenteHasta ("AAAA-MM-DD" o '')
       Solo exámenes:       tipo, realizacion ('propio'|'externo'), laboratorio, muestra,
                            tiempoEntrega, preparacion, costoLaboratorio (number|null)
       Solo procedimientos: tipo, duracionMin (number|null), requiereAnestesia (boolean), insumos

       Configuración:
         lugares     Array<{ id, nombre, recargo:number|null }>   recargo en %, null = por definir
         tamanos     Array<{ id, nombre, pesoDesde:number|null, pesoHasta:number|null }>
         parametros  { iva:number, retencion:number }             ambos en %

       Todas las funciones devuelven una promesa (jQuery), igual que $.ajax.
       ====================================================================== */

    function nuevoId(prefijo) {
        return prefijo + '_' + Date.now().toString(36) + Math.floor(Math.random() * 1e6).toString(36);
    }
    var api = window.arancelesVeterinariosConfig || {};
    var cargaRemota = null;
    function cargarRemoto() {
        if (!cargaRemota) {
            cargaRemota = $.ajax({url:api.listar,headers:{Accept:'application/json'}}).then(function(respuesta){return respuesta;});
            cargaRemota.fail(function(){cargaRemota=null;});
        }
        return cargaRemota;
    }
    function enviar(accion, catalogo, id, datos, activo) {
        var diferido=$.Deferred();
        $.ajax({url:api.guardar,method:'POST',contentType:'application/json',headers:{Accept:'application/json','X-CSRF-TOKEN':api.csrf},
            data:JSON.stringify({accion:accion,catalogo:catalogo,id:id,datos:datos,activo:activo})
        }).done(function(respuesta){cargaRemota=null;diferido.resolve(respuesta);})
        .fail(function(xhr){var respuesta=xhr.responseJSON||{},errores=respuesta.errors||{},campo=Object.keys(errores)[0];
            diferido.reject({campo:campo,mensaje:campo?errores[campo].join(' '):(respuesta.message||'No se pudo guardar. Inténtalo nuevamente.')});});
        return diferido.promise();
    }
    var servicio = {
        listar:function(catalogo){return cargarRemoto().then(function(estado){return estado[catalogo];});},
        crear:function(catalogo,datos){return enviar('crear',catalogo,null,datos);},
        editar:function(catalogo,id,datos){return enviar('editar',catalogo,id,datos);},
        cambiarEstado:function(catalogo,id,activo){return enviar('estado',catalogo,id,null,activo);},
        obtenerConfiguracion:function(){return cargarRemoto().then(function(estado){return estado.config;});},
        guardarConfiguracion:function(config){return enviar('config',null,null,config);},
        importar:function(datos){return enviar('importar',null,null,datos);}
    };

    // Disponible para otras pantallas (presupuestos, atención, etc.)
    window.Aranceles = {
        calcularPrecioFinal: calcularPrecioFinal,
        calcularMargen: calcularMargen,
        formatearCLP: formatearCLP,
        servicio: servicio
    };

    /* ======================================================================
       3. INTERFAZ
       ====================================================================== */

    var CATALOGOS = {
        aranceles: { singular: 'arancel', plural: 'aranceles', nuevo: 'Nuevo arancel', editar: 'Editar arancel', columnaTipo: 'Categoría', campoTipo: 'categoria', vacio: 'Aún no hay aranceles, crea el primero.' },
        examenes: { singular: 'examen', plural: 'exámenes', nuevo: 'Nuevo examen', editar: 'Editar examen', columnaTipo: 'Tipo', campoTipo: 'tipo', vacio: 'Aún no hay exámenes, crea el primero.' },
        procedimientos: { singular: 'procedimiento', plural: 'procedimientos', nuevo: 'Nuevo procedimiento', editar: 'Editar procedimiento', columnaTipo: 'Tipo', campoTipo: 'tipo', vacio: 'Aún no hay procedimientos, crea el primero.' }
    };

    var catalogo = 'aranceles';
    var datos = { aranceles: [], examenes: [], procedimientos: [] };
    var config = { lugares: [], tamanos: [], parametros: { iva: 19, retencion: 0 } };
    var filtros = {
        aranceles: { texto: '', estado: 'todos', soloVaria: false },
        examenes: { texto: '', estado: 'todos', soloVaria: false },
        procedimientos: { texto: '', estado: 'todos', soloVaria: false }
    };
    var detallesAbiertos = {};
    var cargando = true;
    var editandoId = null;

    function esc(valor) {
        return $('<div>').text(valor === null || valor === undefined ? '' : valor).html().replace(/"/g, '&quot;');
    }

    function sinTildes(texto) {
        return String(texto || '').toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '');
    }

    function enteroONulo(valor) {
        if (valor === '' || valor === null || valor === undefined) return null;
        return Math.round(numero(valor));
    }

    function decimalONulo(valor) {
        if (valor === '' || valor === null || valor === undefined) return null;
        return numero(String(valor).replace(',', '.'));
    }

    function kilos(valor) {
        return String(valor).replace('.', ',');
    }

    function rangoDePeso(tamano) {
        var desde = tamano.pesoDesde, hasta = tamano.pesoHasta;
        var hayDesde = desde !== null && desde !== undefined && desde !== '';
        var hayHasta = hasta !== null && hasta !== undefined && hasta !== '';
        if (!hayDesde && hayHasta) return 'hasta ' + kilos(hasta) + ' kg';
        if (hayDesde && !hayHasta) return 'más de ' + kilos(desde) + ' kg';
        if (hayDesde && hayHasta) return kilos(desde) + ' a ' + kilos(hasta) + ' kg';
        return '';
    }

    function avisarError(error, porDefecto) {
        swal({ title: 'Error', text: (error && error.mensaje) || porDefecto, icon: 'error' });
    }

    /* ---------- Lista ---------- */

    function precioMinimo(item) {
        var minimo = null;
        config.tamanos.forEach(function (tamano) {
            var precio = calcularPrecioFinal({ item: item, tamanoId: tamano.id, config: config });
            if (precio.disponible && (minimo === null || precio.total < minimo)) minimo = precio.total;
        });
        return minimo;
    }

    function htmlPrecio(item) {
        if (!item.variaPorTamano) {
            return '<span class="arv-precio">' + formatearCLP(calcularPrecioFinal({ item: item, config: config }).total) + '</span>';
        }
        var minimo = precioMinimo(item);
        var abierto = !!detallesAbiertos[item.id];
        return '<button type="button" class="arv-precio-varia js-detalle" aria-expanded="' + abierto + '" title="Ver precios por tamaño">' +
            '<span class="arv-precio">' + (minimo === null ? 'Sin precios' : 'Desde ' + formatearCLP(minimo)) + '</span>' +
            '<span class="arv-etiqueta">Varía por tamaño <i class="feather icon-chevron-' + (abierto ? 'up' : 'down') + '"></i></span>' +
            '</button>';
    }

    function htmlDetalle(item) {
        var filas = config.tamanos.map(function (tamano) {
            var precio = calcularPrecioFinal({ item: item, tamanoId: tamano.id, config: config });
            var fila = buscar(item.preciosTamano, 'tamanoId', tamano.id);
            if (!precio.disponible) {
                return '<tr class="arv-detalle-inactivo"><td>' + esc(tamano.nombre) + '</td><td>' + esc(rangoDePeso(tamano)) + '</td><td colspan="3">No disponible</td></tr>';
            }
            return '<tr><td>' + esc(tamano.nombre) + '</td><td>' + esc(rangoDePeso(tamano)) + '</td>' +
                '<td>' + formatearCLP(precio.base) + '</td>' +
                '<td>' + (fila.afectoIva ? formatearCLP(precio.iva) : 'Exento') + '</td>' +
                '<td><strong>' + formatearCLP(precio.total) + '</strong></td></tr>';
        }).join('');
        return '<tr class="arv-detalle"><td colspan="6"><div class="arv-detalle-caja">' +
            '<table class="arv-detalle-tabla"><thead><tr><th>Tamaño</th><th>Peso</th><th>Neto</th><th>IVA</th><th>Valor final</th></tr></thead>' +
            '<tbody>' + filas + '</tbody></table></div></td></tr>';
    }

    function filtrar(lista) {
        var filtro = filtros[catalogo];
        var texto = sinTildes($.trim(filtro.texto));
        return lista.filter(function (item) {
            if (filtro.estado === 'activos' && !item.activo) return false;
            if (filtro.estado === 'inactivos' && item.activo) return false;
            if (filtro.soloVaria && !item.variaPorTamano) return false;
            if (texto && sinTildes(item.codigo).indexOf(texto) === -1 && sinTildes(item.nombre).indexOf(texto) === -1) return false;
            return true;
        });
    }

    function filaVacia(html) {
        return '<tr class="arv-fila-mensaje"><td colspan="6"><div class="arv-vacio">' + html + '</div></td></tr>';
    }

    function pintarContadores() {
        $('.arv-tab').each(function () {
            var nombre = $(this).data('catalogo');
            $(this).find('.arv-tab-contador').text(cargando ? '' : datos[nombre].length);
        });
    }

    function pintarLista() {
        var definicion = CATALOGOS[catalogo];
        var lista = datos[catalogo] || [];
        var $cuerpo = $('#arvTabla tbody').empty();

        $('#arvColTipo').text(definicion.columnaTipo);
        $('#arvNuevo .js-texto').text(definicion.nuevo);
        $('#arvBuscar').attr('placeholder', 'Buscar por código o nombre');
        pintarContadores();

        if (cargando) {
            $('#arvResumen').text('');
            $cuerpo.html(filaVacia('<i class="fas fa-spinner fa-spin"></i><p>Cargando ' + definicion.plural + '…</p>'));
            return;
        }
        if (!lista.length) {
            $('#arvResumen').text('');
            $cuerpo.html(filaVacia('<i class="feather icon-inbox"></i><p>' + definicion.vacio + '</p>' +
                '<button type="button" class="btn btn-info btn-sm js-nuevo"><i class="feather icon-plus"></i> ' + definicion.nuevo + '</button>'));
            return;
        }

        var visibles = filtrar(lista);
        $('#arvResumen').text('Mostrando ' + visibles.length + ' de ' + lista.length + ' ' + definicion.plural);
        if (!visibles.length) {
            $cuerpo.html(filaVacia('<i class="feather icon-search"></i><p>No encontramos ' + definicion.plural + ' con esos filtros.</p>' +
                '<button type="button" class="btn btn-outline-info btn-sm js-limpiar-filtros">Limpiar filtros</button>'));
            return;
        }

        var html = '';
        visibles.forEach(function (item) {
            html += '<tr class="arv-fila' + (item.activo ? '' : ' arv-fila-inactiva') + '" data-id="' + esc(item.id) + '" tabindex="0" title="Clic para editar">' +
                '<td data-label="Código"><span class="arv-codigo">' + esc(item.codigo) + '</span></td>' +
                '<td data-label="Nombre" class="arv-celda-nombre">' + esc(item.nombre) + '</td>' +
                '<td data-label="' + definicion.columnaTipo + '">' + esc(item[definicion.campoTipo]) + '</td>' +
                '<td data-label="Precio">' + htmlPrecio(item) + '</td>' +
                '<td data-label="Estado"><span class="arv-estado ' + (item.activo ? 'arv-estado-activo' : 'arv-estado-inactivo') + '">' + (item.activo ? 'Activo' : 'Inactivo') + '</span></td>' +
                '<td class="arv-acciones">' +
                    '<button type="button" class="btn btn-icon btn-outline-info btn-sm js-editar" title="Editar"><i class="feather icon-edit-2"></i></button> ' +
                    '<button type="button" class="btn btn-icon btn-sm js-estado ' + (item.activo ? 'btn-outline-danger' : 'btn-outline-success') + '" title="' + (item.activo ? 'Desactivar' : 'Activar') + '"><i class="feather icon-' + (item.activo ? 'slash' : 'check') + '"></i></button>' +
                '</td></tr>';
            if (item.variaPorTamano && detallesAbiertos[item.id]) html += htmlDetalle(item);
        });
        $cuerpo.html(html);
    }

    function recargar(nombre) {
        return servicio.listar(nombre).done(function (lista) {
            datos[nombre] = lista;
            pintarLista();
        }).fail(function (error) { avisarError(error, 'No fue posible cargar los datos.'); });
    }

    function cambiarPestana(nombre) {
        catalogo = nombre;
        $('.arv-tab').removeClass('active').attr('aria-selected', 'false')
            .filter('[data-catalogo="' + nombre + '"]').addClass('active').attr('aria-selected', 'true');
        var filtro = filtros[nombre];
        $('#arvBuscar').val(filtro.texto);
        $('#arvFiltroEstado').val(filtro.estado);
        $('#arvSoloVaria').prop('checked', filtro.soloVaria);
        pintarLista();
    }

    function cambiarEstado(item) {
        var aplicar = function () {
            servicio.cambiarEstado(catalogo, item.id, !item.activo)
                .done(function () { recargar(catalogo); })
                .fail(function (error) { avisarError(error, 'No fue posible cambiar el estado.'); });
        };
        if (!item.activo) { aplicar(); return; }
        confirmarDesactivar(item.nombre).then(function (acepta) { if (acepta) aplicar(); });
    }

    function confirmarDesactivar(nombre) {
        return swal({
            title: '¿Desactivar "' + nombre + '"?',
            text: 'Dejará de estar disponible para nuevas atenciones. Puedes volver a activarlo cuando quieras.',
            icon: 'warning',
            buttons: ['Cancelar', 'Sí, desactivar'],
            dangerMode: true
        });
    }

    /* ---------- Modal de ítem ---------- */

    function limpiarErrores() {
        $('#arvFormulario .is-invalid').removeClass('is-invalid');
        $('#arvFormulario [data-error-de]').text('');
    }

    function mostrarErrores(errores) {
        limpiarErrores();
        $.each(errores, function (campo, mensaje) {
            $('#arvFormulario [data-campo="' + campo + '"]').addClass('is-invalid');
            $('#arvFormulario [data-error-de="' + campo + '"]').text(mensaje);
        });
        var $primero = $('#arvFormulario .is-invalid:visible').first();
        if ($primero.length) $primero.focus();
    }

    function pintarTamanos(item) {
        var html = config.tamanos.map(function (tamano) {
            var fila = buscar(item.preciosTamano, 'tamanoId', tamano.id) ||
                { valorNeto: '', afectoIva: !!item.afectoIva, costoInterno: null, activo: true };
            return '<tr data-tamano="' + esc(tamano.id) + '">' +
                '<td class="text-center"><input type="checkbox" class="js-t-activo" title="Atiendo este tamaño"' + (fila.activo ? ' checked' : '') + '></td>' +
                '<td><strong>' + esc(tamano.nombre) + '</strong><small>' + esc(rangoDePeso(tamano)) + '</small></td>' +
                '<td><input type="number" min="0" step="1" inputmode="numeric" class="form-control form-control-sm arv-monto js-t-neto" placeholder="0" value="' + (fila.valorNeto || '') + '"></td>' +
                '<td class="text-center"><input type="checkbox" class="js-t-iva" title="Afecto a IVA"' + (fila.afectoIva ? ' checked' : '') + '></td>' +
                '<td class="arv-tamano-final js-t-final"></td>' +
                '<td><input type="number" min="0" step="1" inputmode="numeric" class="form-control form-control-sm arv-monto js-t-costo" placeholder="Opcional" value="' + (fila.costoInterno === null || fila.costoInterno === undefined ? '' : fila.costoInterno) + '"></td>' +
                '</tr>';
        }).join('');
        $('#arvTablaTamanos tbody').html(html);
        $('#arvTablaTamanos tbody tr').each(function () { recalcularFilaTamano($(this)); });
    }

    function recalcularFilaTamano($fila) {
        var activo = $fila.find('.js-t-activo').prop('checked');
        var neto = numero($fila.find('.js-t-neto').val());
        var precio = calcularPrecioFinal({ item: { valorNeto: neto, afectoIva: $fila.find('.js-t-iva').prop('checked') }, config: config });
        $fila.toggleClass('arv-tamano-inactivo', !activo);
        $fila.find('.js-t-neto, .js-t-iva, .js-t-costo').prop('disabled', !activo);
        $fila.find('.js-t-final').text(!activo ? 'No se atiende' : (neto > 0 ? formatearCLP(precio.total) : '—'));
    }

    function recalcularPrecioUnico() {
        var neto = numero($('#arvValorNeto').val());
        var precio = calcularPrecioFinal({ item: { valorNeto: neto, afectoIva: $('#arvAfectoIva').prop('checked') }, config: config });
        $('#arvValorFinal').val(neto > 0 ? formatearCLP(precio.total) : '');
        $('#arvEtiquetaIva').text(numero(config.parametros.iva) + '%');

        var costo = $('#arvCostoInterno').val();
        if (catalogo === 'examenes' && $('#arvCostoLaboratorio').val() !== '') {
            costo = numero(costo) + numero($('#arvCostoLaboratorio').val());
        }
        var margen = calcularMargen(neto, costo);
        $('#arvMargen').val(margen ? formatearCLP(margen.monto) + ' (' + String(margen.porcentaje).replace('.', ',') + '%)' : '');
    }

    function alternarBloques() {
        var varia = $('#arvVaria').prop('checked');
        $('#arvPrecioUnico').toggle(!varia);
        $('#arvPrecioTamanos').toggle(varia);
        $('#arvLaboratorioGrupo').toggle($('#arvRealizacion').val() === 'externo');
    }

    function abrirModal(id) {
        var definicion = CATALOGOS[catalogo];
        var item = id ? buscar(datos[catalogo], 'id', id) : null;
        editandoId = item ? item.id : null;
        item = item || { activo: true, afectoIva: false, variaPorTamano: false, preciosTamano: [], realizacion: 'propio', especie: 'Todas', lugarId: '' };

        $('#arvFormulario')[0].reset();
        limpiarErrores();
        $('#arvModalTitulo').text(editandoId ? definicion.editar : definicion.nuevo);
        $('.arv-propios').hide().filter('[data-catalogo="' + catalogo + '"]').show();

        $('#arvLugar').html('<option value="">Todos los lugares</option>' + config.lugares.map(function (lugar) {
            return '<option value="' + esc(lugar.id) + '">' + esc(lugar.nombre) + '</option>';
        }).join(''));

        $('#arvCodigo').val(item.codigo || '');
        $('#arvNombre').val(item.nombre || '');
        $('#arvActivo').prop('checked', !!item.activo);
        $('#arvVaria').prop('checked', !!item.variaPorTamano);
        $('#arvValorNeto').val(item.valorNeto || '');
        $('#arvAfectoIva').prop('checked', !!item.afectoIva);
        $('#arvCostoInterno').val(item.costoInterno === null || item.costoInterno === undefined ? '' : item.costoInterno);

        if (catalogo === 'aranceles') {
            $('#arvCategoria').val(item.categoria || 'Consulta');
            $('#arvEspecie').val(item.especie || 'Todas');
            $('#arvLugar').val(item.lugarId || '');
            $('#arvDescripcion').val(item.descripcion || '');
            $('#arvVigenteDesde').val(item.vigenteDesde || '');
            $('#arvVigenteHasta').val(item.vigenteHasta || '');
        } else if (catalogo === 'examenes') {
            $('#arvExaTipo').val(item.tipo || 'Laboratorio');
            $('#arvRealizacion').val(item.realizacion || 'propio');
            $('#arvLaboratorio').val(item.laboratorio || '');
            $('#arvMuestra').val(item.muestra || '');
            $('#arvTiempoEntrega').val(item.tiempoEntrega || '');
            $('#arvPreparacion').val(item.preparacion || '');
            $('#arvCostoLaboratorio').val(item.costoLaboratorio === null || item.costoLaboratorio === undefined ? '' : item.costoLaboratorio);
        } else {
            $('#arvProTipo').val(item.tipo || 'Cirugía');
            $('#arvDuracion').val(item.duracionMin || '');
            $('#arvAnestesia').prop('checked', !!item.requiereAnestesia);
            $('#arvInsumos').val(item.insumos || '');
        }

        pintarTamanos(item);
        alternarBloques();
        recalcularPrecioUnico();
        $('#arvModalItem').modal('show');
    }

    function leerFormulario() {
        var item = {
            codigo: $.trim($('#arvCodigo').val()).toUpperCase(),
            nombre: $.trim($('#arvNombre').val()),
            activo: $('#arvActivo').prop('checked'),
            variaPorTamano: $('#arvVaria').prop('checked'),
            valorNeto: Math.round(numero($('#arvValorNeto').val())),
            afectoIva: $('#arvAfectoIva').prop('checked'),
            costoInterno: enteroONulo($('#arvCostoInterno').val()),
            preciosTamano: $('#arvTablaTamanos tbody tr').map(function () {
                var $fila = $(this);
                return {
                    tamanoId: String($fila.data('tamano')),
                    valorNeto: Math.round(numero($fila.find('.js-t-neto').val())),
                    afectoIva: $fila.find('.js-t-iva').prop('checked'),
                    costoInterno: enteroONulo($fila.find('.js-t-costo').val()),
                    activo: $fila.find('.js-t-activo').prop('checked')
                };
            }).get()
        };
        if (catalogo === 'aranceles') {
            item.categoria = $('#arvCategoria').val();
            item.especie = $('#arvEspecie').val();
            item.lugarId = $('#arvLugar').val();
            item.descripcion = $.trim($('#arvDescripcion').val());
            item.vigenteDesde = $('#arvVigenteDesde').val();
            item.vigenteHasta = $('#arvVigenteHasta').val();
        } else if (catalogo === 'examenes') {
            item.tipo = $('#arvExaTipo').val();
            item.realizacion = $('#arvRealizacion').val();
            item.laboratorio = item.realizacion === 'externo' ? $.trim($('#arvLaboratorio').val()) : '';
            item.muestra = $.trim($('#arvMuestra').val());
            item.tiempoEntrega = $.trim($('#arvTiempoEntrega').val());
            item.preparacion = $.trim($('#arvPreparacion').val());
            item.costoLaboratorio = enteroONulo($('#arvCostoLaboratorio').val());
        } else {
            item.tipo = $('#arvProTipo').val();
            item.duracionMin = enteroONulo($('#arvDuracion').val());
            item.requiereAnestesia = $('#arvAnestesia').prop('checked');
            item.insumos = $.trim($('#arvInsumos').val());
        }
        return item;
    }

    function validar(item) {
        var errores = {};

        if (!item.codigo) {
            errores.codigo = 'Ingresa un código, por ejemplo CON-001.';
        } else {
            $.each(datos, function (nombre, lista) {
                lista.forEach(function (otro) {
                    if (otro.id !== editandoId && String(otro.codigo).toUpperCase() === item.codigo) {
                        errores.codigo = 'El código ' + item.codigo + ' ya está en uso por "' + otro.nombre + '".';
                    }
                });
            });
        }
        if (!item.nombre) errores.nombre = 'Ingresa el nombre.';

        if (!item.variaPorTamano) {
            if (!(item.valorNeto > 0)) errores.valorNeto = 'El valor neto debe ser mayor a 0.';
            if (item.costoInterno !== null && item.costoInterno < 0) errores.costoInterno = 'El costo no puede ser negativo.';
        } else {
            var activos = item.preciosTamano.filter(function (fila) { return fila.activo; });
            if (!activos.length) {
                errores.tamanos = 'Activa al menos un tamaño.';
            } else if (activos.some(function (fila) { return !(fila.valorNeto > 0); })) {
                errores.tamanos = 'Cada tamaño activo necesita un valor neto mayor a 0. Desmarca los tamaños que no atiendes.';
            }
        }

        if (catalogo === 'aranceles' && item.vigenteDesde && item.vigenteHasta && item.vigenteHasta < item.vigenteDesde) {
            errores.vigenteHasta = 'La fecha "hasta" no puede ser anterior a la fecha "desde".';
        }
        if (catalogo === 'examenes') {
            if (item.realizacion === 'externo' && !item.laboratorio) errores.laboratorio = 'Ingresa el nombre del laboratorio externo.';
            if (item.costoLaboratorio !== null && item.costoLaboratorio < 0) errores.costoLaboratorio = 'El costo no puede ser negativo.';
        }
        if (catalogo === 'procedimientos' && item.duracionMin !== null && !(item.duracionMin > 0)) {
            errores.duracionMin = 'La duración debe ser mayor a 0 minutos.';
        }
        return errores;
    }

    function guardarItem() {
        var item = leerFormulario();
        var errores = validar(item);
        mostrarErrores(errores);
        if (errores.tamanos) {
            $('#arvTablaTamanos tbody tr').each(function () {
                var $fila = $(this);
                if ($fila.find('.js-t-activo').prop('checked') && !(numero($fila.find('.js-t-neto').val()) > 0)) {
                    $fila.find('.js-t-neto').addClass('is-invalid');
                }
            });
        }
        if (!$.isEmptyObject(errores)) return;

        var original = editandoId ? buscar(datos[catalogo], 'id', editandoId) : null;
        var enviar = function () {
            var $boton = $('#arvGuardar').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando…');
            var peticion = editandoId ? servicio.editar(catalogo, editandoId, item) : servicio.crear(catalogo, item);
            peticion.done(function () {
                $('#arvModalItem').modal('hide');
                recargar(catalogo);
            }).fail(function (error) {
                if (error && error.campo) {
                    var respuesta = {};
                    respuesta[error.campo] = error.mensaje;
                    mostrarErrores(respuesta);
                } else {
                    avisarError(error, 'No fue posible guardar.');
                }
            }).always(function () {
                $boton.prop('disabled', false).html('<i class="feather icon-save"></i> Guardar');
            });
        };

        if (original && original.activo && !item.activo) {
            confirmarDesactivar(item.nombre).then(function (acepta) { if (acepta) enviar(); });
        } else {
            enviar();
        }
    }

    function filasActivasDeTamano() {
        return $('#arvTablaTamanos tbody tr').filter(function () { return $(this).find('.js-t-activo').prop('checked'); });
    }

    function copiarValorATodos() {
        var $filas = filasActivasDeTamano();
        var $origen = $filas.filter(function () { return numero($(this).find('.js-t-neto').val()) > 0; }).first();
        if (!$origen.length) {
            $('[data-error-de="tamanos"]').text('Ingresa primero un valor en algún tamaño para copiarlo al resto.');
            return;
        }
        $('[data-error-de="tamanos"]').text('');
        var neto = $origen.find('.js-t-neto').val();
        var iva = $origen.find('.js-t-iva').prop('checked');
        $filas.each(function () {
            $(this).find('.js-t-neto').val(neto).removeClass('is-invalid');
            $(this).find('.js-t-iva').prop('checked', iva);
            recalcularFilaTamano($(this));
        });
    }

    function aplicarPorcentajePorEscalon() {
        var porcentaje = numero(String($('#arvPorcentaje').val()).replace(',', '.'));
        var $filas = filasActivasDeTamano();
        var anterior = numero($filas.first().find('.js-t-neto').val());
        if (!(anterior > 0)) {
            $('[data-error-de="tamanos"]').text('Ingresa primero el valor del tamaño más pequeño; los demás se calculan desde ahí.');
            return;
        }
        $('[data-error-de="tamanos"]').text('');
        $filas.each(function (indice) {
            if (indice > 0) {
                anterior = Math.round(anterior * (1 + porcentaje / 100));
                $(this).find('.js-t-neto').val(anterior).removeClass('is-invalid');
            }
            recalcularFilaTamano($(this));
        });
    }

    /* ---------- Modal de configuración ---------- */

    function valorCampo(valor) {
        return valor === null || valor === undefined ? '' : valor;
    }

    function filaLugar(lugar) {
        return '<tr data-id="' + esc(lugar.id) + '">' +
            '<td><input type="text" class="form-control form-control-sm js-c-nombre" value="' + esc(lugar.nombre) + '" placeholder="Nombre del lugar"></td>' +
            '<td><div class="input-group input-group-sm"><input type="number" min="0" step="0.5" class="form-control js-c-recargo" value="' + valorCampo(lugar.recargo) + '" placeholder="Por definir"><div class="input-group-append"><span class="input-group-text">%</span></div></div></td>' +
            '<td class="text-right"><button type="button" class="btn btn-icon btn-outline-danger btn-sm js-c-quitar" title="Quitar"><i class="feather icon-x"></i></button></td></tr>';
    }

    function filaTamano(tamano) {
        return '<tr data-id="' + esc(tamano.id) + '">' +
            '<td><input type="text" class="form-control form-control-sm js-c-nombre" value="' + esc(tamano.nombre) + '" placeholder="Nombre"></td>' +
            '<td><input type="number" min="0" step="0.1" class="form-control form-control-sm js-c-desde" value="' + valorCampo(tamano.pesoDesde) + '" placeholder="—"></td>' +
            '<td><input type="number" min="0" step="0.1" class="form-control form-control-sm js-c-hasta" value="' + valorCampo(tamano.pesoHasta) + '" placeholder="—"></td>' +
            '<td class="text-right"><button type="button" class="btn btn-icon btn-outline-danger btn-sm js-c-quitar" title="Quitar"><i class="feather icon-x"></i></button></td></tr>';
    }

    function abrirConfiguracion() {
        $('#arvCfgError').text('');
        $('#arvCfgLugares tbody').html(config.lugares.map(filaLugar).join(''));
        $('#arvCfgTamanos tbody').html(config.tamanos.map(filaTamano).join(''));
        $('#arvCfgIva').val(config.parametros.iva);
        $('#arvCfgRetencion').val(config.parametros.retencion);
        $('#arvModalConfig').modal('show');
    }

    function guardarConfiguracion() {
        var nueva = {
            lugares: $('#arvCfgLugares tbody tr').map(function () {
                return { id: String($(this).data('id')), nombre: $.trim($(this).find('.js-c-nombre').val()), recargo: decimalONulo($(this).find('.js-c-recargo').val()) };
            }).get(),
            tamanos: $('#arvCfgTamanos tbody tr').map(function () {
                return { id: String($(this).data('id')), nombre: $.trim($(this).find('.js-c-nombre').val()), pesoDesde: decimalONulo($(this).find('.js-c-desde').val()), pesoHasta: decimalONulo($(this).find('.js-c-hasta').val()) };
            }).get(),
            parametros: { iva: numero($('#arvCfgIva').val()), retencion: numero($('#arvCfgRetencion').val()) }
        };

        var error = '';
        if (nueva.lugares.concat(nueva.tamanos).some(function (fila) { return !fila.nombre; })) error = 'Todos los lugares y tamaños necesitan un nombre.';
        else if (!nueva.tamanos.length) error = 'Debe existir al menos un tamaño.';
        else if (nueva.lugares.some(function (lugar) { return lugar.recargo !== null && lugar.recargo < 0; })) error = 'El recargo no puede ser negativo.';
        else if ($('#arvCfgIva').val() === '' || nueva.parametros.iva < 0 || nueva.parametros.iva > 100) error = 'El IVA debe estar entre 0 y 100%.';
        else if (nueva.parametros.retencion < 0 || nueva.parametros.retencion > 100) error = 'La retención debe estar entre 0 y 100%.';
        $('#arvCfgError').text(error);
        if (error) return;

        var $boton = $('#arvCfgGuardar').prop('disabled', true);
        servicio.guardarConfiguracion(nueva).done(function (guardada) {
            config = guardada;
            $('#arvModalConfig').modal('hide');
            pintarLista();
        }).fail(function (fallo) {
            avisarError(fallo, 'No fue posible guardar la configuración.');
        }).always(function () { $boton.prop('disabled', false); });
    }

    /* ---------- Inicio ---------- */

    function cargarTodo() {
        cargando = true;
        pintarLista();
        $.when(
            servicio.obtenerConfiguracion(),
            servicio.listar('aranceles'),
            servicio.listar('examenes'),
            servicio.listar('procedimientos')
        ).done(function (configuracion, aranceles, examenes, procedimientos) {
            config = configuracion;
            datos = { aranceles: aranceles, examenes: examenes, procedimientos: procedimientos };
            cargando = false;
            pintarLista();
        }).fail(function () {
            cargando = false;
            $('#arvTabla tbody').html(filaVacia('<i class="feather icon-alert-triangle"></i><p>No fue posible cargar los aranceles.</p>' +
                '<button type="button" class="btn btn-outline-info btn-sm js-reintentar">Reintentar</button>'));
        });
    }

    function filtrarCatalogoBase() {
        var texto=String($('#arvBuscarBase').val()||'').toLocaleLowerCase('es');
        var categoria=$('#arvCategoriaBase').val();
        var html=(api.catalogo||[]).map(function(item,i){
            if(categoria && item.categoria!==categoria)return '';
            if(texto && (item.codigo+' '+item.nombre+' '+item.categoria).toLocaleLowerCase('es').indexOf(texto)===-1)return '';
            return '<tr><td>'+esc(item.codigo)+'</td><td>'+esc(item.nombre)+'</td><td>'+esc(item.categoria)+'</td><td><button type="button" class="btn btn-outline-info btn-sm" data-base="'+i+'">Definir mi arancel</button></td></tr>';
        }).join('');
        $('#arvListaBase').html(html||'<tr><td colspan="4">No hay prestaciones que coincidan.</td></tr>');
    }

    $(function () {
        if (!$('#arvTabla').length) return; // el archivo solo actúa en la pantalla de aranceles

        // Pestañas y filtros
        $('.arv-tab').on('click', function () { cambiarPestana($(this).data('catalogo')); });
        $('#arvBuscar').on('input', function () { filtros[catalogo].texto = this.value; pintarLista(); });
        $('#arvFiltroEstado').on('change', function () { filtros[catalogo].estado = this.value; pintarLista(); });
        $('#arvSoloVaria').on('change', function () { filtros[catalogo].soloVaria = this.checked; pintarLista(); });
        $('#arvNuevo').on('click', function () { if (!cargando) abrirModal(null); });
        $('#arvCatalogoBase').on('click',function(){if(!cargando)$('#arvModalCatalogo').modal('show');});
        $('#arvBuscarBase, #arvCategoriaBase').on('input change',filtrarCatalogoBase);
        $('#arvListaBase').on('click','[data-base]',function(){
            var base=api.catalogo[Number($(this).attr('data-base'))];if(!base)return;
            $('#arvModalCatalogo').one('hidden.bs.modal',function(){
                cambiarPestana(base.catalogo);abrirModal(null);
                $('#arvCodigo').val(base.codigo);$('#arvNombre').val(base.nombre);
                $(base.catalogo==='examenes'?'#arvExaTipo':'#arvProTipo').val(base.tipo);
                $('#arvValorNeto').val('');$('#arvVaria').prop('checked',false);alternarBloques();
                $('#arvActivo').prop('checked',true);$('#arvValorNeto').trigger('focus');
            }).modal('hide');
        });
        filtrarCatalogoBase();
        try {
            if(window.localStorage.getItem('vetsdi_aranceles_v1'))$('#arvImportarNavegador').prop('hidden',false);
        } catch(error) {}
        $('#arvImportarNavegador').on('click',function(){
            swal({title:'Importar a mi cuenta',text:'Revisa que los aranceles guardados en este navegador sean tuyos. Se copiarán a tu cuenta sin borrar el respaldo local. Solo se puede importar si la cuenta aún no tiene aranceles.',icon:'info',buttons:['Cancelar','Importar mis aranceles']}).then(function(aceptado){
                if(!aceptado)return;
                var respaldo;
                try{respaldo=JSON.parse(window.localStorage.getItem('vetsdi_aranceles_v1'));}catch(error){avisarError(null,'No se pudo leer el respaldo del navegador.');return;}
                $('#arvImportarNavegador').prop('disabled',true);
                servicio.importar(respaldo).done(function(){cargarTodo();$('#arvImportarNavegador').prop('hidden',true);})
                .fail(function(error){avisarError(error,'No se pudieron importar los aranceles.');})
                .always(function(){$('#arvImportarNavegador').prop('disabled',false);});
            });
        });

        $('#arvAbrirConfig').on('click', function () { if (!cargando) abrirConfiguracion(); });

        // Tabla
        $('#arvTabla tbody')
            .on('click', '.js-nuevo', function () { abrirModal(null); })
            .on('click', '.js-reintentar', cargarTodo)
            .on('click', '.js-limpiar-filtros', function () {
                filtros[catalogo] = { texto: '', estado: 'todos', soloVaria: false };
                cambiarPestana(catalogo);
            })
            .on('click', '.js-detalle', function (evento) {
                evento.stopPropagation();
                var id = $(this).closest('tr').data('id');
                detallesAbiertos[id] = !detallesAbiertos[id];
                pintarLista();
            })
            .on('click', '.js-estado', function (evento) {
                evento.stopPropagation();
                var item = buscar(datos[catalogo], 'id', $(this).closest('tr').data('id'));
                if (item) cambiarEstado(item);
            })
            .on('click', 'tr.arv-fila', function () { abrirModal($(this).data('id')); })
            .on('keydown', 'tr.arv-fila', function (evento) {
                if (evento.key === 'Enter' && evento.target === this) abrirModal($(this).data('id'));
            });

        // Modal de ítem
        $('#arvFormulario').on('submit', function (evento) { evento.preventDefault(); guardarItem(); });
        $('#arvFormulario').on('input change', '[data-campo]', function () {
            $(this).removeClass('is-invalid');
            $('#arvFormulario [data-error-de="' + $(this).data('campo') + '"]').text('');
        });
        $('#arvVaria, #arvRealizacion').on('change', alternarBloques);
        $('#arvValorNeto, #arvCostoInterno, #arvCostoLaboratorio').on('input', recalcularPrecioUnico);
        $('#arvAfectoIva').on('change', recalcularPrecioUnico);
        $('#arvTablaTamanos tbody').on('input change', 'input', function () {
            $(this).removeClass('is-invalid');
            recalcularFilaTamano($(this).closest('tr'));
        });
        $('#arvCopiarTodos').on('click', copiarValorATodos);
        $('#arvAplicarEscalon').on('click', aplicarPorcentajePorEscalon);

        // Modal de configuración
        $('#arvCfgAgregarLugar').on('click', function () {
            $('#arvCfgLugares tbody').append(filaLugar({ id: nuevoId('lug'), nombre: '', recargo: 0 })).find('tr:last .js-c-nombre').focus();
        });
        $('#arvCfgAgregarTamano').on('click', function () {
            $('#arvCfgTamanos tbody').append(filaTamano({ id: nuevoId('tam'), nombre: '', pesoDesde: null, pesoHasta: null })).find('tr:last .js-c-nombre').focus();
        });
        $('#arvModalConfig').on('click', '.js-c-quitar', function () { $(this).closest('tr').remove(); });
        $('#arvCfgGuardar').on('click', guardarConfiguracion);

        cargarTodo();
    });

})(window, jQuery);

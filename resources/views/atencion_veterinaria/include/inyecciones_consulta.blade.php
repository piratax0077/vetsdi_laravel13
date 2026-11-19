<div class="tab-pane fade" id="inyeccion_consulta" role="tabpanel" aria-labelledby="inyeccion_consulta_tab">
    @php
        [$titularInventario, $administradoresInventario] = app(\App\Services\ConsumoInventarioVeterinario::class)->contexto($profesional->id, $hora_medica->id_lugar_atencion);
        $lotesConsulta = \Illuminate\Support\Facades\DB::table('vet_inventario_lotes')->where('titular', $titularInventario)
            ->where('saldo', '>', 0)->whereDate('vencimiento', '>=', now()->toDateString())->orderBy('vencimiento')->get();
    @endphp
    <div class="form-row pt-2" id="inyeccion-consulta-campos">
        <div class="form-group col-md-6">
            <label for="inyeccion-lote">Producto del inventario / lote</label>
            <select id="inyeccion-lote" data-campo="lote_id" class="form-control form-control-sm">
                <option value="">Seleccione si utiliza existencias del inventario</option>
                @foreach($lotesConsulta as $lote)
                    <option value="{{ $lote->id }}" data-producto="{{ $lote->producto }}">{{ $lote->producto }} · {{ $lote->lote }} · {{ $lote->saldo }} {{ $lote->unidad }} · {{ $lote->ubicacion }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group col-md-6">
            <label for="inyeccion-cantidad">Cantidad utilizada en la unidad del inventario</label>
            <input id="inyeccion-cantidad" data-campo="cantidad_stock" type="number" min="0.001" step="0.001" max="999999999" class="form-control form-control-sm">
        </div>
        <div class="form-group col-md-6">
            <label for="inyeccion-administrador">Administrado por</label>
            <select id="inyeccion-administrador" data-campo="administrador_id" class="form-control form-control-sm">
                <option value="">Seleccione</option>
                @foreach($administradoresInventario as $persona)
                    <option value="{{ $persona->id }}">{{ $persona->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group col-md-6">
            <label for="inyeccion-receta">Referencia receta / autorización</label>
            <input id="inyeccion-receta" data-campo="receta_stock" maxlength="150" class="form-control form-control-sm">
        </div>
        @foreach(['medicamento' => ['Medicamento administrado', 255], 'dosis' => ['Dosis administrada', 120], 'via' => ['Vía de administración', 120], 'procedimiento' => ['Procedimiento realizado', 255]] as $campo => [$etiqueta, $maximo])
            <div class="form-group col-md-6">
                <label class="floating-label-activo-sm" for="inyeccion-{{ $campo }}">{{ $etiqueta }}</label>
                <input type="text" class="form-control form-control-sm" id="inyeccion-{{ $campo }}" data-campo="{{ $campo }}" maxlength="{{ $maximo }}">
            </div>
        @endforeach
        @foreach(['precio_medicamento' => 'Precio del medicamento ($ CLP)', 'precio_procedimiento' => 'Precio del procedimiento ($ CLP)'] as $campo => $etiqueta)
            <div class="form-group col-md-6">
                <label class="floating-label-activo-sm" for="inyeccion-{{ $campo }}">{{ $etiqueta }}</label>
                <input type="number" class="form-control form-control-sm" id="inyeccion-{{ $campo }}" data-campo="{{ $campo }}" min="0" max="999999999" step="1">
            </div>
        @endforeach
        <div class="form-group col-md-6">
            <label class="floating-label-activo-sm" for="inyeccion-observaciones">Observaciones</label>
            <textarea class="form-control form-control-sm caja-texto" id="inyeccion-observaciones" data-campo="observaciones" maxlength="1000" rows="1"></textarea>
        </div>
    </div>
    <button type="button" class="btn btn-info btn-sm" id="agregar-inyeccion-consulta"><i class="feather icon-plus"></i> Agregar registro</button>
    <small class="form-text text-muted">Para un procedimiento sin medicamento, complete el procedimiento y su precio. El registro se guarda junto con la ficha clínica.</small>
    <ul class="list-group list-group-flush mt-3" id="inyecciones-consulta-lista">
        @foreach(data_get($fichaVeterinariaGeneralData ?? [], 'inyecciones_consulta', []) as $inyeccion)
            <li class="list-group-item">
                @if(!empty($inyeccion['medicamento']))
                    {{ $inyeccion['medicamento'] }} · {{ $inyeccion['dosis'] }} · {{ $inyeccion['via'] }}
                    @if(!empty($inyeccion['procedimiento'])) · @endif
                @endif
                {{ $inyeccion['procedimiento'] ?? '' }}
                @if(!empty($inyeccion['lote'])) · Lote {{ $inyeccion['lote'] }} · {{ $inyeccion['cantidad_stock'] }} {{ $inyeccion['unidad_stock'] }} · Indicado por {{ $inyeccion['indicado_por'] }} · Administrado por {{ $inyeccion['administrado_por'] }} @endif
                @if(!empty($inyeccion['medicamento']) && isset($inyeccion['precio_medicamento'])) · Medicamento ${{ number_format($inyeccion['precio_medicamento'], 0, ',', '.') }} @endif
                @if(isset($inyeccion['precio_procedimiento'])) · Procedimiento ${{ number_format($inyeccion['precio_procedimiento'], 0, ',', '.') }} @endif
                @if(!empty($inyeccion['observaciones'])) · {{ $inyeccion['observaciones'] }} @endif
            </li>
        @endforeach
    </ul>
</div>
<script>
$(function () {
    const formulario = document.getElementById('descripcion_hipotesis')?.closest('form');
    if (!formulario) return;
    const lista = $('#inyecciones-consulta-lista');
    const campos = $('#inyeccion-consulta-campos [data-campo]');
    $('#inyeccion-lote').on('change', function () {
        const nombre = $(this).find(':selected').attr('data-producto');
        if (nombre) $('#inyeccion-medicamento').val(nombre);
    });
    const formatoPrecio = new Intl.NumberFormat('es-CL', {style: 'currency', currency: 'CLP'});
    let indice = 0;
    function agregar(inyeccion) {
        const grupo = $('<div hidden>').appendTo(formulario);
        campos.each(function () {
            const campo = this.dataset.campo;
            $('<input>', {type: 'hidden', name: 'nuevas_inyecciones[' + indice + '][' + campo + ']', value: inyeccion[campo] || ''}).appendTo(grupo);
        });
        $('<input>', {type: 'hidden', name: 'nuevas_inyecciones[' + indice + '][registro]', value: inyeccion.registro}).appendTo(grupo);
        const campoRegistro = 'nuevas_inyecciones[' + indice + '][registro]';
        indice++;
        const fila = $('<li class="list-group-item d-flex justify-content-between align-items-center">').appendTo(lista);
        $('<span>').text([
            inyeccion.medicamento, inyeccion.dosis, inyeccion.via, inyeccion.procedimiento,
            inyeccion.medicamento && 'Medicamento ' + formatoPrecio.format(inyeccion.precio_medicamento),
            'Procedimiento ' + formatoPrecio.format(inyeccion.precio_procedimiento), inyeccion.observaciones
        ].filter(Boolean).join(' · ')).appendTo(fila);
        const quitar = $('<button type="button" class="btn btn-outline-danger btn-sm ml-2">Quitar</button>').on('click', function () {
            fila.remove();
            grupo.remove();
            $(formulario).off('fichaVeterinariaGuardada', confirmarRegistro);
        }).appendTo(fila);
        function confirmarRegistro(evento, datos) {
            if (datos.get(campoRegistro) === inyeccion.registro) {
                grupo.remove();
                quitar.remove();
                $(formulario).off('fichaVeterinariaGuardada', confirmarRegistro);
            }
        }
        $(formulario).on('fichaVeterinariaGuardada', confirmarRegistro);
    }
    Object.values(@js((array) old('nuevas_inyecciones', []))).filter(function (registro) {
        return registro && typeof registro.registro === 'string';
    }).forEach(agregar);
    $('#agregar-inyeccion-consulta').on('click', function () {
        const inyeccion = {};
        campos.each(function () { inyeccion[this.dataset.campo] = this.value.trim(); });
        const requeridos = ['procedimiento', 'precio_procedimiento'];
        if (inyeccion.lote_id) requeridos.push('cantidad_stock', 'administrador_id');
        if (inyeccion.medicamento) requeridos.push('dosis', 'via', 'precio_medicamento');
        else inyeccion.precio_medicamento = '0';
        if (requeridos.some(campo => !inyeccion[campo])) {
            swal({icon: 'warning', text: 'Complete el procedimiento y su precio. Si administró un medicamento, indique también la dosis, la vía y su precio.'});
            return;
        }
        if (campos.toArray().some(campo => !campo.reportValidity())) return;
        inyeccion.precio_medicamento = String(Number(inyeccion.precio_medicamento));
        inyeccion.precio_procedimiento = String(Number(inyeccion.precio_procedimiento));
        if (formulario.querySelectorAll('[name^="nuevas_inyecciones["][name$="[medicamento]"]').length >= 20) {
            swal({icon: 'warning', text: 'Puede agregar hasta 20 inyecciones antes de guardar la ficha.'});
            return;
        }
        inyeccion.registro = Array.from(crypto.getRandomValues(new Uint8Array(16)), byte => byte.toString(16).padStart(2, '0')).join('');
        agregar(inyeccion);
        campos.val('');
    });
    const pesoInicial = $('#recetario-vet-peso').text();
    $('#indicar_recetario').on('show.bs.modal', function () {
        const peso = $('#peso_examen_fisico').val() || $('#peso').val();
        $('#recetario-vet-peso').text(peso ? peso + ' kg' : pesoInicial);
    });
});
</script>

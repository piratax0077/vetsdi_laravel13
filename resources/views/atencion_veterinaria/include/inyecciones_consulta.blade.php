<div class="tab-pane fade" id="inyeccion_consulta" role="tabpanel" aria-labelledby="inyeccion_consulta_tab">
    <div class="form-row pt-2" id="inyeccion-consulta-campos">
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
    <button type="button" class="btn btn-info btn-sm" id="agregar-inyeccion-consulta"><i class="feather icon-plus"></i> Agregar medicamento administrado</button>
    <small class="form-text text-muted">El registro se guarda junto con la ficha clínica.</small>
    <ul class="list-group list-group-flush mt-3" id="inyecciones-consulta-lista">
        @foreach(data_get($fichaVeterinariaGeneralData ?? [], 'inyecciones_consulta', []) as $inyeccion)
            <li class="list-group-item">
                {{ $inyeccion['medicamento'] }} · {{ $inyeccion['dosis'] }} · {{ $inyeccion['via'] }}
                @if(!empty($inyeccion['procedimiento'])) · {{ $inyeccion['procedimiento'] }} @endif
                @if(isset($inyeccion['precio_medicamento'])) · Medicamento ${{ number_format($inyeccion['precio_medicamento'], 0, ',', '.') }} @endif
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
            'Medicamento ' + formatoPrecio.format(inyeccion.precio_medicamento),
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
        if (['medicamento', 'dosis', 'via', 'procedimiento', 'precio_medicamento', 'precio_procedimiento'].some(campo => !inyeccion[campo])) {
            swal({icon: 'warning', text: 'Complete el medicamento, la dosis, la vía, el procedimiento y ambos precios.'});
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

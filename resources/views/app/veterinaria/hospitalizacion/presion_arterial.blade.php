@php
    $activa = old('presion_pas') !== null || old('presion_pad') !== null || old('presion_metodo') !== null;
@endphp
<div class="col-12" id="{{ $prefijoPresion }}-bloque-presion">
    <div class="form-group mb-1">
        <label for="{{ $prefijoPresion }}-activar-presion"><strong>Presión arterial veterinaria</strong></label>
        <div class="switch switch-success d-inline m-r-10">
            <input type="checkbox" id="{{ $prefijoPresion }}-activar-presion" aria-controls="{{ $prefijoPresion }}-campos-presion" @checked($activa)>
            <label for="{{ $prefijoPresion }}-activar-presion" class="cr"></label>
        </div>
    </div>
    <fieldset id="{{ $prefijoPresion }}-campos-presion" @disabled(!$activa) @if(!$activa) hidden @endif>
        <div class="form-row">
            @foreach(['pas' => 'Sistólica (PAS)', 'pad' => 'Diastólica (PAD)', 'pam' => 'Media (PAM)'] as $campo => $etiqueta)
                <div class="form-group col-12 col-sm-6 col-md-3">
                    <label class="floating-label-activo-sm" for="{{ $prefijoPresion }}-presion-{{ $campo }}">{{ $etiqueta }}</label>
                    <input class="form-control form-control-sm" type="number" id="{{ $prefijoPresion }}-presion-{{ $campo }}"
                        @if($campo === 'pam') step="0.1" readonly @else name="presion_{{ $campo }}" min="1" max="999" step="1" value="{{ old('presion_'.$campo) }}" @endif
                        placeholder="mmHg">
                </div>
            @endforeach
            <div class="form-group col-12 col-sm-6 col-md-3">
                <label class="floating-label-activo-sm" for="{{ $prefijoPresion }}-presion-metodo">Sitio, manguito y método</label>
                <input class="form-control form-control-sm" id="{{ $prefijoPresion }}-presion-metodo" name="presion_metodo"
                    maxlength="255" placeholder="Ej.: cola, manguito 3, Doppler" value="{{ old('presion_metodo') }}">
            </div>
        </div>
    </fieldset>
</div>
<script>
(() => {
    const bloque = document.getElementById(@json($prefijoPresion.'-bloque-presion'));
    const interruptor = bloque.querySelector('input[type="checkbox"]');
    const campos = bloque.querySelector('fieldset');
    const pas = bloque.querySelector('[name="presion_pas"]');
    const pad = bloque.querySelector('[name="presion_pad"]');
    const pam = bloque.querySelector('input[readonly]');
    function calcularPam() {
        const sistolica = pas.valueAsNumber;
        const diastolica = pad.valueAsNumber;
        pam.value = sistolica > 0 && diastolica > 0 && sistolica >= diastolica
            ? ((sistolica + 2 * diastolica) / 3).toFixed(1) : '';
    }
    interruptor.addEventListener('change', () => {
        campos.hidden = campos.disabled = !interruptor.checked;
    });
    pas.addEventListener('input', calcularPam);
    pad.addEventListener('input', calcularPam);
    calcularPam();
})();
</script>

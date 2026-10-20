<div class="form-group">
    <label>Convenio externo</label>
    <select class="form-control form-control-sm bono-convenio-veterinario" name="convenio_veterinario_id" data-url="{{ route('agenda.convenios_veterinarios') }}">
        <option value="">Sin convenio</option>
        @foreach(($conveniosVeterinarios ?? collect()) as $convenio)
            <option value="{{ $convenio->id }}" data-porcentaje="{{ $convenio->porcentaje }}" data-lugar="{{ $convenio->id_lugar_atencion }}">{{ $convenio->nombre }} ({{ $convenio->porcentaje }}%)</option>
        @endforeach
    </select>
    <small class="text-muted resumen-convenio-veterinario">El descuento se aplica al valor total, antes de descontar abonos.</small>
    @unless($soloSeleccion ?? false)
    <a class="small d-block" href="{{ route('profesional.mis_propios_convenios') }}">Administrar convenios</a>
    @endunless
</div>

@once
<script src="{{ asset('js/convenios-veterinarios.js') }}" defer></script>
@endonce

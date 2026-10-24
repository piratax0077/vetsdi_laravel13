{{-- Editor de días y horario de atención: lo usan Mi perfil y el modal de lugares de atención (lo maneja peluqueria-horario.js) --}}
@php
    $prefijo = $prefijo ?? 'horario';
    $horario = $horario ?? [];
    if (is_string($horario)) {
        $horario = json_decode($horario, true) ?: [];
    }
    $horario = collect($horario)->map(fn ($dia) => (array) $dia)->all();

    $dias_semana = [
        'lunes' => 'Lunes', 'martes' => 'Martes', 'miercoles' => 'Miércoles', 'jueves' => 'Jueves',
        'viernes' => 'Viernes', 'sabado' => 'Sábado', 'domingo' => 'Domingo',
    ];
    $dias_cortos_largo = ['lunes' => 'Lun', 'martes' => 'Mar', 'miercoles' => 'Mié', 'jueves' => 'Jue', 'viernes' => 'Vie', 'sabado' => 'Sáb', 'domingo' => 'Dom'];
    $dias_laborales = ['martes', 'miercoles', 'jueves', 'viernes'];

    // Si de lunes a viernes tienen el mismo horario (o no hay horario) se parte con la semana junta
    $dato_dia = fn ($dia) => [!empty($horario[$dia]['abierto']), $horario[$dia]['desde'] ?? null, $horario[$dia]['hasta'] ?? null];
    $semana_igual = collect($dias_laborales)->every(fn ($dia) => $dato_dia($dia) === $dato_dia('lunes'));
    $modo = ($modo ?? null) === 'dias' || !$semana_igual ? 'dias' : 'semana';
@endphp
<div class="horario-editor" data-modo-inicial="{{ $modo }}">
    <input type="hidden" name="horario_modo" class="horario-modo-valor" value="{{ $modo }}">

    {{-- 1. Forma de ingresar el horario: la semana junta (con sábado y domingo aparte) o cada día por separado --}}
    <h6 class="titulo-sm">¿Cómo quieres ingresarlo?</h6>
    <ul class="nav pill-moderno-sm horario-modo" role="tablist" aria-label="Forma de ingresar el horario">
        <li class="nav-item"><a href="#" class="nav-link {{ $modo === 'semana' ? 'active' : '' }}" data-modo="semana" role="tab" aria-selected="{{ $modo === 'semana' ? 'true' : 'false' }}">Lunes a viernes</a></li>
        <li class="nav-item"><a href="#" class="nav-link {{ $modo === 'dias' ? 'active' : '' }}" data-modo="dias" role="tab" aria-selected="{{ $modo === 'dias' ? 'true' : 'false' }}">Día por día</a></li>
    </ul>

    {{-- 2. Días que atiende: cada botón abre o cierra el día --}}
    <h6 class="titulo-sm">Días que atiendes</h6>
    <div class="horario-chips">
        @foreach ($dias_semana as $clave => $dia)
            <span class="horario-chip-item {{ in_array($clave, $dias_laborales) ? 'horario-solo-dia' : '' }}">
                <input type="checkbox" class="horario-chip-check horario-abierto" id="{{ $prefijo }}_{{ $clave }}" name="horario[{{ $clave }}][abierto]" value="1" data-dia="{{ $clave }}" @checked(!empty($horario[$clave]['abierto']))>
                <label class="horario-chip" for="{{ $prefijo }}_{{ $clave }}" title="{{ $dia }}" data-corto="{{ $dias_cortos_largo[$clave] }}">{{ $dias_cortos_largo[$clave] }}</label>
            </span>
        @endforeach
    </div>

    {{-- 3. Horario de cada día abierto --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between">
        <h6 class="titulo-sm">Horario</h6>
        <button type="button" class="btn btn-link btn-sm p-0 mb-2 horario-copiar">
            <i class="feather icon-copy mr-1"></i>Usar el mismo horario en todos
        </button>
    </div>
    @foreach ($dias_semana as $clave => $dia)
        <div class="horario-fila {{ in_array($clave, $dias_laborales) ? 'horario-solo-dia' : '' }}" data-dia="{{ $clave }}">
            <span class="horario-fila-nombre" data-dia-nombre="{{ $dia }}">{{ $dia }}</span>
            <div class="horario-fila-horas">
                <input type="time" class="form-control form-control-sm" name="horario[{{ $clave }}][desde]" aria-label="{{ $dia }}: hora de apertura" value="{{ $horario[$clave]['desde'] ?? '09:00' }}" required>
                <span class="horario-fila-separador">a</span>
                <input type="time" class="form-control form-control-sm" name="horario[{{ $clave }}][hasta]" aria-label="{{ $dia }}: hora de cierre" value="{{ $horario[$clave]['hasta'] ?? '18:00' }}" required>
            </div>
            <div class="invalid-feedback">La hora de cierre debe ser posterior a la de apertura.</div>
        </div>
    @endforeach
    <p class="horario-sin-dias">Selecciona al menos un día para indicar su horario.</p>
</div>

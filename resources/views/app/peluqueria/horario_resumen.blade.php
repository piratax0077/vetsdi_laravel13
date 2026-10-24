{{-- Horario de un lugar en la tabla: los días en círculos como en Mi perfil y debajo los tramos abiertos --}}
@php
    $horario = $horario ?? [];
    if (is_string($horario)) {
        $horario = json_decode($horario, true) ?: [];
    }
    $horario = collect($horario)->map(fn ($dia) => (array) $dia)->all();

    $dias_resumen = [
        'lunes' => ['L', 'Lun', 'Lunes'], 'martes' => ['M', 'Mar', 'Martes'], 'miercoles' => ['M', 'Mié', 'Miércoles'],
        'jueves' => ['J', 'Jue', 'Jueves'], 'viernes' => ['V', 'Vie', 'Viernes'], 'sabado' => ['S', 'Sáb', 'Sábado'], 'domingo' => ['D', 'Dom', 'Domingo'],
    ];

    // Los días seguidos con las mismas horas se juntan en un solo tramo (ej. "Lun a Vie")
    $tramos = [];
    $seguido = false;
    foreach ($dias_resumen as $clave => $nombres) {
        $dato = $horario[$clave] ?? [];
        if (empty($dato['abierto'])) {
            $seguido = false;
            continue;
        }
        $horas = ($dato['desde'] ?? '') . ' – ' . ($dato['hasta'] ?? '');
        $ultimo = count($tramos) - 1;
        if ($seguido && $tramos[$ultimo]['horas'] === $horas) {
            $tramos[$ultimo]['hasta'] = $nombres[1];
            $tramos[$ultimo]['dias']++;
        } else {
            $tramos[] = ['desde' => $nombres[1], 'hasta' => $nombres[1], 'dias' => 1, 'horas' => $horas];
        }
        $seguido = true;
    }
@endphp
@if (count($tramos))
    <div class="horario-semana" aria-label="Días de atención">
        @foreach ($dias_resumen as $clave => $nombres)
            @php($abierto_resumen = !empty($horario[$clave]['abierto']))
            <span class="horario-semana-dia {{ $abierto_resumen ? 'abierto' : '' }}" title="{{ $nombres[2] }}: {{ $abierto_resumen ? 'abierto' : 'cerrado' }}">{{ $nombres[0] }}</span>
        @endforeach
    </div>
    <ul class="horario-lista">
        @foreach ($tramos as $tramo)
            <li>
                <span class="horario-lista-dia">{{ $tramo['dias'] === 1 ? $tramo['desde'] : $tramo['desde'] . ($tramo['dias'] === 2 ? ' y ' : ' a ') . $tramo['hasta'] }}</span>
                <span class="horario-lista-horas">{{ $tramo['horas'] }}</span>
            </li>
        @endforeach
    </ul>
@else
    <span class="text-muted">Sin horario</span>
@endif

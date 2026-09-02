<aside class="sw-agenda" id="agenda">
    <p class="sw-agenda-kicker">Agenda en línea</p>
    <h2>Reservar hora</h2>
    <p class="sw-muted">{{ $agendaAyuda ?? 'Horas tomadas de la agenda del lugar de atención.' }}</p>

    <label>Lugar de atención
        <select id="sw-lugar">
            @foreach($lugares as $lugar)
                <option value="{{ $lugar->id }}">{{ $lugar->nombre }}</option>
            @endforeach
        </select>
    </label>

    @if(!empty($ocultarProfesional) && $equipo->count() === 1)
        <input type="hidden" id="sw-profesional" value="{{ $equipo->first()->id }}">
    @else
        <label>Profesional
            <select id="sw-profesional"></select>
        </label>
    @endif

    <label>Fecha
        <input type="date" id="sw-fecha" min="{{ now()->toDateString() }}">
    </label>

    <div id="sw-slots" class="sw-slots"></div>
    <p id="sw-slots-msg" class="sw-muted"></p>
</aside>

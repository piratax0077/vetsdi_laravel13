@php($seccionUsuarios = $seccionUsuarios ?? 'profesionales')
<div class="row">
    <div class="col-md-12">
        <ul class="nav pill-moderno pill-moderno-white" role="tablist">
            {{-- Médicos, odontólogos y otros profesionales quedan juntos en "Profesionales veterinarios" --}}
            {{-- Todas las pestañas viven en la página de profesionales; desde otras páginas se llega con ?tab= --}}
            @if($seccionUsuarios === 'profesionales')
                <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#prof-vet" role="tab">Profesionales veterinarios</a></li>
                <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#asistentes" role="tab">Asistentes</a></li>
                <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#otros_trabajadores" role="tab">Otros</a></li>
            @else
                <li class="nav-item"><a class="nav-link" href="{{ route('adm_cm.profesionales') }}">Profesionales veterinarios</a></li>
                <li class="nav-item"><a class="nav-link {{ $seccionUsuarios === 'asistentes' ? 'active' : '' }}" href="{{ route('adm_cm.profesionales', ['tab' => 'asistentes']) }}">Asistentes</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('adm_cm.profesionales', ['tab' => 'otros_trabajadores']) }}">Otros</a></li>
            @endif
        </ul>
    </div>
</div>

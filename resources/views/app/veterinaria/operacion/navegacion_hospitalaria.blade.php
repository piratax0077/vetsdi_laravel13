<nav aria-label="Configuración del área hospitalaria" class="mb-3">
    <ul class="nav pill-moderno pill-moderno-white">
        @foreach(['pacientes'=>'Pacientes del establecimiento','hospitalizacion'=>'Hospitalización','plazas'=>'Plazas de hospitalización'] as $claveHospital=>$tituloHospital)
        <li class="nav-item"><a class="nav-link {{ ($hospitalSeccionActiva ?? null) === $claveHospital ? 'active' : '' }}" @if(($hospitalSeccionActiva ?? null) === $claveHospital) aria-current="page" @endif href="{{ route('vet.operacion.index',['id_institucion'=>$hospitalInstitucionId,'seccion'=>$claveHospital]) }}">{{ $tituloHospital }}</a></li>
        @endforeach
    </ul>
</nav>

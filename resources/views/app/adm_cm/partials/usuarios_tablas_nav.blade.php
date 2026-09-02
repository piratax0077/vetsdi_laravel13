@php($seccionUsuarios = $seccionUsuarios ?? 'profesionales')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <ul class="nav nav-pills bg-white align-items-center" role="tablist">
                    @if($seccionUsuarios === 'profesionales')
                        <li class="nav-item"><a class="btn btn-outline-info btn-sm mr-1 my-1 active" data-toggle="tab" href="#prof-salud" role="tab">M&eacute;dicos</a></li>
                        <li class="nav-item"><a class="btn btn-outline-info btn-sm mr-1 my-1" data-toggle="tab" href="#odontologos" role="tab">Odont&oacute;logos</a></li>
                        <li class="nav-item"><a class="btn btn-outline-info btn-sm mr-1 my-1" data-toggle="tab" href="#otros_prof" role="tab">Otros Profesionales de la salud</a></li>
                    @else
                        <li class="nav-item"><a class="btn btn-outline-info btn-sm mr-1 my-1" href="{{ route('adm_cm.profesionales') }}">M&eacute;dicos</a></li>
                        <li class="nav-item"><a class="btn btn-outline-info btn-sm mr-1 my-1" href="{{ route('adm_cm.profesionales', ['tab' => 'odontologos']) }}">Odont&oacute;logos</a></li>
                        <li class="nav-item"><a class="btn btn-outline-info btn-sm mr-1 my-1" href="{{ route('adm_cm.profesionales', ['tab' => 'otros_prof']) }}">Otros Profesionales de la salud</a></li>
                    @endif
                    <li class="nav-item flex-fill mx-lg-2"><a class="btn btn-outline-info btn-sm btn-block my-1 {{ $seccionUsuarios === 'contratos' ? 'active' : '' }}" href="{{ route('adm_cm.area_contratos_nuevos') }}">Contratos</a></li>
                    <li class="nav-item flex-fill mx-lg-2"><a class="btn btn-outline-info btn-sm btn-block my-1 {{ $seccionUsuarios === 'asistentes' ? 'active' : '' }}" href="{{ route('adm_cm.personal') }}">Asistentes</a></li>
                    <li class="nav-item flex-fill mx-lg-2"><a class="btn btn-outline-info btn-sm btn-block my-1 {{ $seccionUsuarios === 'pacientes' ? 'active' : '' }}" href="{{ route('adm_cm.pacientes') }}">Tutores / Mascotas</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>

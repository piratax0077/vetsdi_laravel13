@extends('template.adm_cm.template')

@section('content')
<div class="pcoded-main-container">
    <div class="pcoded-content">
        <div class="page-header">
            <div class="page-block">
                <div class="row align-items-center">
                    <div class="col-md-12">
                        <div class="page-header-title"><h5 class="m-b-10 font-weight-bold">Administración dental</h5></div>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('adm_cm.home') }}"><i class="feather icon-home"></i></a></li>
                            <li class="breadcrumb-item">Dental</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-info text-center">
                <h4 class="text-white mb-0">Área Dental · {{ $institucion->nombre ?? 'Centro veterinario' }}</h4>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="card subir h-100">
                    <a href="{{ route('adm_cm.profesionales', ['tab' => 'odontologos']) }}">
                        <div class="card-body text-center">
                            <img class="wid-70" src="{{ asset('images/iconos/dental.svg') }}" onerror="this.src='{{ asset('images/iconos/examen.svg') }}'">
                            <h5 class="mt-3">Odontólogos</h5>
                            <p class="text-muted mb-0">Inscribir, buscar, editar y asociar profesionales.</p>
                        </div>
                    </a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card subir h-100">
                    <a href="{{ route('adm_cm.examenes') }}">
                        <div class="card-body text-center">
                            <img class="wid-70" src="{{ asset('images/iconos/examen.svg') }}">
                            <h5 class="mt-3">Procedimientos y aranceles</h5>
                            <p class="text-muted mb-0">Configurar prestaciones, duración y precios.</p>
                        </div>
                    </a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body text-center d-flex flex-column justify-content-center">
                        <h2 class="text-info mb-1">{{ count($odontologos) }}</h2>
                        <h5 class="mb-1">Odontólogos asociados</h5>
                        <p class="text-muted mb-0">Personal activo en el lugar de atención.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header bg-info">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="text-white f-20 mb-0">Equipo odontológico</h4>
                    <a class="btn btn-sm btn-outline-light" href="{{ route('adm_cm.profesionales', ['tab' => 'odontologos']) }}">
                        <i class="feather icon-plus"></i> Administrar odontólogos
                    </a>
                </div>
            </div>
            <div class="card-body">
                <table id="tabla_odontologos_dental" class="display table table-striped table-hover dt-responsive nowrap" style="width:100%">
                    <thead>
                        <tr>
                            <th>Profesional</th>
                            <th>RUT</th>
                            <th>Especialidad</th>
                            <th>Correo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($odontologos as $odontologo)
                        <tr>
                            <td>{{ trim(($odontologo->nombre ?? $odontologo->nombres ?? '').' '.($odontologo->apellido_uno ?? '').' '.($odontologo->apellido_dos ?? '')) ?: 'Sin nombre' }}</td>
                            <td>{{ $odontologo->rut ?? 'Sin información' }}</td>
                            <td>{{ optional($tipos_odontologos->firstWhere('id', $odontologo->id_tipo_especialidad ?? null))->nombre ?? 'Odontología' }}</td>
                            <td>{{ $odontologo->email ?? optional($odontologo->Usuario)->email ?? 'Sin información' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-script')
<script>
$(function () {
    $('#tabla_odontologos_dental').DataTable({ responsive: true, language: { search: 'Buscar:' } });
});
</script>
@endsection

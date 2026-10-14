@extends('template.asistente_unificado.template')
@section('content')
<div class="pcoded-main-container"><div class="pcoded-content">
    <div class="page-header"><h4>Escritorio del asistente</h4><p>Seleccione el lugar y el profesional con quienes está trabajando.</p></div>
    @if(session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
    @if($contextos->isEmpty())
        <div class="alert alert-info">Aún no tiene una vinculación activa. Solicite al profesional o al centro médico que lo vincule y configure sus autorizaciones.</div>
    @else
        <div class="card"><div class="card-body">
            <form method="POST" action="{{ route('asistentes.contexto') }}" class="form-row align-items-end">
                @csrf
                <div class="form-group col-md-9"><label for="ambito_asistente">Lugar de atención y profesional</label>
                    <select id="ambito_asistente" class="form-control" required onchange="const opcion=this.selectedOptions[0];document.getElementById('lugar_asistente').value=opcion.dataset.lugar;document.getElementById('profesional_asistente').value=opcion.dataset.profesional;">
                        @foreach($contextos as $contexto)
                            <option data-lugar="{{ $contexto['id_lugar_atencion'] }}" data-profesional="{{ $contexto['id_profesional'] }}" @selected($actual['id_lugar_atencion'] === $contexto['id_lugar_atencion'] && $actual['id_profesional'] === $contexto['id_profesional'])>{{ $contexto['lugar'] }} — {{ $contexto['profesional'] }}</option>
                        @endforeach
                    </select>
                    <input type="hidden" id="lugar_asistente" name="id_lugar_atencion" value="{{ $actual['id_lugar_atencion'] }}">
                    <input type="hidden" id="profesional_asistente" name="id_profesional" value="{{ $actual['id_profesional'] }}">
                </div>
                <div class="form-group col-md-3"><button class="btn btn-info btn-block">Trabajar en este contexto</button></div>
            </form>
        </div></div>
        @if(!in_array(true, $permisos, true))
            <div class="alert alert-info">Su vinculación está activa. El profesional o el centro médico debe habilitar las funciones que podrá utilizar.</div>
        @endif
        <div class="row">
            @foreach(config('asistentes.modulos') as $modulo)
                @if(($permisos[$modulo['permiso']] ?? false) && \Illuminate\Support\Facades\Route::has($modulo['ruta']))
                    <div class="col-sm-6 col-lg-4"><a class="card text-dark" href="{{ route($modulo['ruta']) }}"><div class="card-body"><i class="feather icon-{{ $modulo['icono'] }} text-info mr-2"></i>{{ $modulo['nombre'] }}</div></a></div>
                @endif
            @endforeach
        </div>
        <div class="card"><div class="card-body"><h6>Sus autorizaciones actuales</h6>
            @foreach(config('asistentes.permisos') as $clave => $etiqueta)
                @if($permisos[$clave] ?? false)<span class="badge badge-light border mr-1 mb-2">{{ $etiqueta }}</span>@endif
            @endforeach
        </div></div>
    @endif
</div></div>
@endsection

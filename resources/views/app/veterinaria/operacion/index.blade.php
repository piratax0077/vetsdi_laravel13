@extends('template.adm_cm.template')
@section('content')
@php
    // Las fechas con hora se muestran como HH:MM, sin segundos
    $sinSegundos = fn ($valor) => preg_replace('/(\d{2}:\d{2}):\d{2}(\.\d+)?/', '$1', (string) $valor);
    $urlSeccion = fn (array $extra = []) => route('vet.operacion.index', $ctx + ['seccion' => $seccion] + $extra);
@endphp
<div class="pcoded-main-container"><div class="pcoded-content configuracion-administrador">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <div><h4>{{ $titulo }}</h4><span class="text-muted">{{ $institucion->nombre }} · {{ $configuracion->tipo === 'hospital' ? 'Hospital veterinario' : 'Centro veterinario' }}</span></div>
    </div>
    <nav class="nav nav-pills mb-3" aria-label="Operación del establecimiento">
        @foreach(\App\Services\OperacionVeterinaria::SECCIONES + \App\Services\OperacionVeterinaria::SERVICIOS as $clave=>$etiqueta)
        <a class="nav-link {{ $seccion === $clave ? 'active' : '' }}" href="{{ route('vet.operacion.index',$ctx+['seccion'=>$clave]) }}">{{ $etiqueta }}</a>
        @endforeach
    </nav>
    @if(session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger" role="alert"><strong>No se guardaron los cambios.</strong><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    @if($seccion === 'configuracion')
    <div class="alert alert-info">Seleccione el tipo de establecimiento y sus áreas. Un centro puede habilitar observación u hospitalización. Los cambios conservan la historia registrada.</div>
    @endif
    @if($seccion === 'hospitalizacion')
    <a class="btn btn-outline-info btn-sm mb-3" href="{{ route('vet.operacion.index',$ctx+['seccion'=>'plazas']) }}">Administrar plazas</a>
    @if(!$configuracion->hospitalizacion_habilitada)<div class="alert alert-warning">Hospitalización deshabilitada. Puede consultar su historia y finalizar ingresos existentes.</div>@endif
    @endif
    @if(isset($mascotas) && !$mascotas)
    <div class="alert alert-info">No hay mascotas vinculadas a este establecimiento. <a href="{{ route('vet.operacion.index',$ctx+['seccion'=>'pacientes']) }}">Vincular una mascota</a> para reservar o registrar su ingreso.</div>
    @endif
    @if(isset(\App\Services\OperacionVeterinaria::SERVICIOS[$seccion]) && !in_array($seccion,$configuracion->servicios_habilitados))
    <div class="alert alert-warning">Esta área está deshabilitada para nuevas reservas. Puede consultar y finalizar las reservas existentes.</div>
    @endif
    @if($seccion === 'asistentes')<p>Gestione vacantes y candidatos del establecimiento. La selección se registra aquí; el alta del empleado se realiza en <a href="{{ route('adm_cm.personal') }}">Personal</a>.</p>@endif
    @if($seccion === 'impuestos')<p>Registro de obligaciones y comprobantes ingresados por administración. Los montos están expresados en CLP.</p>@endif
    @if($seccion === 'pacientes')
    <div class="card"><div class="card-header encabezado-card"><h5 class="encabezado-card-titulo">Vincular mascota por RUT del tutor</h5></div><div class="card-body">
        <p>Permite recibir mascotas sin una hora previa, conservando su ficha existente.</p>
        <form method="get" class="form-row mb-3"><input type="hidden" name="id_institucion" value="{{ $institucion->id }}"><div class="form-group col-md-6"><label for="rut-busqueda-tutor">RUT del tutor</label><input id="rut-busqueda-tutor" name="rut_tutor" value="{{ request('rut_tutor') }}" class="form-control form-control-sm" required maxlength="20"></div><div class="col-md-6 align-self-end form-group"><button type="submit" class="btn btn-info btn-sm">Buscar mascotas</button></div></form>
        @if(request()->filled('rut_tutor'))
            @if($resultadosTutor->isNotEmpty())
            @include('app.veterinaria.operacion.formulario',['accionForm'=>'vincular','registroForm'=>null,'camposForm'=>[['nombre'=>'id_mascota','etiqueta'=>'Mascota del tutor','tipo'=>'select','opciones'=>$resultadosTutor->mapWithKeys(fn($m)=>[$m->id=>$m->nombre.' · #'.$m->id])->all()]],'ocultos'=>['rut_tutor'=>request('rut_tutor')],'valores'=>[]])
            @else<p>No se encontraron mascotas para ese RUT. Verifique los datos o <a href="{{ route('adm_cm.pacientes') }}">abra pacientes y tutores</a> para registrar la ficha.</p>@endif
        @endif
    </div></div>
    <div class="card"><div class="card-header encabezado-card"><h5 class="encabezado-card-titulo">Mascotas del establecimiento</h5></div><div class="card-body"><div class="table-responsive"><table class="table table-sm"><thead><tr><th>ID</th><th>Mascota</th></tr></thead><tbody>@forelse($pacientes as $paciente)<tr><td>#{{ $paciente->id }}</td><td>{{ $paciente->nombre }}</td></tr>@empty<tr><td colspan="2">No hay mascotas vinculadas.</td></tr>@endforelse</tbody></table></div>{{ $pacientes->links('pagination::bootstrap-4') }}</div></div>
    @endif
    @include('app.veterinaria.operacion.registros')
    @if($seccion === 'asistentes' && ($detalle ?? null))
    <div class="card"><div class="card-header encabezado-card"><h5 class="encabezado-card-titulo">Candidatos · {{ $detalle->cargo }}</h5></div><div class="card-body">
        <p>{{ $detalle->descripcion }}</p>
        <div class="table-responsive"><table class="table table-sm"><thead><tr><th>Nombre</th><th>Correo</th><th>Teléfono</th><th>Etapa</th><th></th></tr></thead><tbody>@forelse($postulaciones as $p)<tr><td>{{ $p->nombre }}</td><td>{{ $p->email }}</td><td>{{ $p->telefono }}</td><td>{{ $p->estado }}</td><td><a class="btn btn-outline-info btn-sm" href="{{ route('vet.operacion.index',$ctx+['seccion'=>$seccion,'detalle'=>$detalle->id,'postulacion'=>$p->id]) }}">Editar</a></td></tr>@empty<tr><td colspan="5">Sin candidatos registrados.</td></tr>@endforelse</tbody></table></div>
        @if($detalle->estado === 'abierta' || $postulacionEditada)
        <h6 class="mb-3">{{ $postulacionEditada ? 'Actualizar candidato' : 'Registrar candidato' }}</h6>
        @include('app.veterinaria.operacion.formulario',['accionForm'=>'postulacion','registroForm'=>$postulacionEditada,'camposForm'=>$camposPostulacion,'ocultos'=>['convocatoria_id'=>$detalle->id],'valores'=>['estado'=>'recibida']])
        @endif
    </div></div>
    @endif
    @if($seccion === 'resumen')
    <div class="row">@foreach($resumen as $label=>$valor)<div class="col-md-4"><div class="card"><div class="card-body"><h6>{{ $label }}</h6><h3>{{ number_format($valor,0,',','.') }}</h3></div></div></div>@endforeach</div>
    @endif
</div></div>
@endsection
@section('page-script')
<script src="{{ asset('js/operacion-veterinaria.js') }}"></script>
@endsection

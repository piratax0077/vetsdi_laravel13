@extends('template.adm_cm.template')
@section('content')
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
    @if($accion)
    <div class="card"><div class="card-header encabezado-card"><h5 class="encabezado-card-titulo"><span class="encabezado-card-icono"><i class="feather icon-edit"></i></span>{{ $registro ? 'Editar registro' : 'Nuevo registro' }}</h5></div><div class="card-body">
        @if($seccion === 'hospitalizacion' && $registro && $registro->estado !== 'hospitalizada')
        <p>Internación cerrada. Consulte el ingreso y sus evoluciones en el detalle.</p>
        @elseif(isset(\App\Services\OperacionVeterinaria::SERVICIOS[$seccion]) && $registro && in_array($registro->estado,['completada','cancelada','no_asiste']))
        <p><strong>{{ $registro->servicio }}</strong> · {{ $mascotas[$registro->id_mascota] ?? '#'.$registro->id_mascota }} · {{ $registro->inicio }} a {{ $registro->fin }}</p>
        <p><strong>Responsable:</strong> {{ $registro->responsable }} · <strong>Estado:</strong> {{ $registro->estado }}</p>
        <p><strong>Observaciones:</strong> {{ $registro->observaciones }}</p><p class="text-wrap"><strong>Resultado:</strong> {{ $registro->resultado }}</p>
        @else
        @include('app.veterinaria.operacion.formulario',['accionForm'=>$accion,'registroForm'=>$registro,'camposForm'=>$campos,'ocultos'=>[],'valores'=>['ingreso'=>now()->format('Y-m-d\TH:i'),'inicio'=>now()->format('Y-m-d\TH:i'),'estado'=>$seccion === 'hospitalizacion' ? 'hospitalizada' : ($seccion === 'impuestos' ? 'pendiente' : ($seccion === 'asistentes' ? 'borrador' : 'reservada')),'activa'=>1]])
        @endif
    </div></div>
    @endif
    @if(count($columnas))
    <div class="card"><div class="card-header encabezado-card"><h5 class="encabezado-card-titulo">Registros del establecimiento</h5></div><div class="card-body">
        @if(in_array($seccion,['hospitalizacion','impuestos']) || isset(\App\Services\OperacionVeterinaria::SERVICIOS[$seccion]))
        <form method="get" class="form-row mb-3">
            <input type="hidden" name="id_institucion" value="{{ $institucion->id }}">
            <div class="col-md-3 mb-2"><label>Estado</label><select name="estado" class="form-control form-control-sm"><option value="">Todos</option>@foreach(collect($campos)->firstWhere('nombre','estado')['opciones'] ?? [] as $key=>$label)<option value="{{ $key }}" @selected(request('estado') === $key)>{{ $label }}</option>@endforeach</select></div>
            @if($seccion !== 'hospitalizacion')
            <div class="col-md-3 mb-2"><label>Desde</label><input name="desde" type="date" value="{{ request('desde') }}" class="form-control form-control-sm"></div><div class="col-md-3 mb-2"><label>Hasta</label><input name="hasta" type="date" value="{{ request('hasta') }}" class="form-control form-control-sm"></div>
            @endif
            <div class="col-md-3 align-self-end mb-2"><button class="btn btn-info btn-sm" type="submit">Filtrar</button><a href="{{ route('vet.operacion.index',$ctx+['seccion'=>$seccion]) }}" class="btn btn-light btn-sm">Limpiar</a></div>
        </form>
        @endif
        <div class="table-responsive"><table class="table table-striped table-sm"><thead><tr>@foreach($columnas as $label)<th>{{ $label }}</th>@endforeach<th>Acciones</th></tr></thead><tbody>
        @forelse($filas as $fila)<tr>
            @foreach($columnas as $key=>$label)<td>
                @if($key === 'id_mascota'){{ $mascotas[$fila->{$key}] ?? '#'.$fila->{$key} }}
                @elseif($key === 'plaza_id'){{ $nombresPlazas[$fila->{$key}] ?? '#'.$fila->{$key} }}
                @elseif(in_array($key,['precio','monto']))${{ number_format($fila->{$key},0,',','.') }}
                @elseif($key === 'activa'){{ $fila->activa ? 'Sí' : 'No' }}{{ in_array($fila->id,$ocupadas ?? []) ? ' · Ocupada' : '' }}
                @else{{ $fila->{$key} ?: '—' }}@endif
            </td>@endforeach
            <td><a class="btn btn-outline-info btn-sm" href="{{ route('vet.operacion.index',$ctx+['seccion'=>$seccion,'editar'=>$fila->id]) }}">Ver / editar</a>
                @if(in_array($seccion,['hospitalizacion','asistentes']))<a class="btn btn-light btn-sm" href="{{ route('vet.operacion.index',$ctx+['seccion'=>$seccion,'detalle'=>$fila->id]) }}">{{ $seccion === 'hospitalizacion' ? 'Evoluciones' : 'Candidatos' }}</a>@endif
            </td>
        </tr>@empty<tr><td colspan="{{ count($columnas)+1 }}" class="text-center text-muted">No hay registros para esta selección.</td></tr>@endforelse
        </tbody></table></div>
        {{ $filas->links('pagination::bootstrap-4') }}
    </div></div>
    @endif
    @if($seccion === 'hospitalizacion' && ($detalle ?? null))
    <div class="card"><div class="card-header encabezado-card"><h5 class="encabezado-card-titulo">Internación #{{ $detalle->id }} · {{ $mascotas[$detalle->id_mascota] ?? '#'.$detalle->id_mascota }}</h5></div><div class="card-body">
        <p><strong>Ingreso:</strong> {{ $detalle->ingreso }} · <strong>Estado:</strong> {{ $detalle->estado }} · <strong>Responsable:</strong> {{ $detalle->responsable }}</p>
        <p><strong>Motivo:</strong> {{ $detalle->motivo }}</p><p><strong>Indicaciones:</strong> {{ $detalle->indicaciones }}</p>
        @if($detalle->egreso)<p><strong>Egreso:</strong> {{ $detalle->egreso }} · {{ $detalle->observaciones_alta }}</p>@endif
        @if($detalle->estado === 'hospitalizada')
        <h6>Agregar evolución</h6>
        @include('app.veterinaria.operacion.formulario',['accionForm'=>'evolucion','registroForm'=>null,'camposForm'=>$camposEvolucion,'ocultos'=>['internacion_id'=>$detalle->id],'valores'=>['fecha'=>now()->format('Y-m-d\TH:i')]])
        @endif
        <div class="table-responsive mt-3"><table class="table table-sm"><thead><tr><th>Fecha</th><th>Peso kg</th><th>Temperatura °C</th><th>Evolución</th><th>Registrado por</th></tr></thead><tbody>@forelse($evoluciones as $e)<tr><td>{{ $e->fecha }}</td><td>{{ $e->peso ?? '—' }}</td><td>{{ $e->temperatura ?? '—' }}</td><td class="text-wrap">{{ $e->observaciones }}</td><td>Usuario #{{ $e->id_usuario }}</td></tr>@empty<tr><td colspan="5">Sin evoluciones registradas.</td></tr>@endforelse</tbody></table></div>
    </div></div>
    @endif
    @if(isset($catalogo))
    <div class="card"><div class="card-header encabezado-card"><h5 class="encabezado-card-titulo">Catálogo de servicios · {{ $titulo }}</h5></div><div class="card-body">
        <div class="table-responsive"><table class="table table-sm"><thead><tr><th>Servicio</th><th>Duración</th><th>Precio CLP</th><th>Activo</th><th></th></tr></thead><tbody>@forelse($catalogo as $c)<tr><td>{{ $c->nombre }}</td><td>{{ $c->duracion_minutos }} min</td><td>${{ number_format($c->precio,0,',','.') }}</td><td>{{ $c->activo ? 'Sí' : 'No' }}</td><td><a class="btn btn-outline-info btn-sm" href="{{ route('vet.operacion.index',$ctx+['seccion'=>$seccion,'catalogo'=>$c->id]) }}">Editar</a></td></tr>@empty<tr><td colspan="5">Agregue el primer servicio para habilitar reservas.</td></tr>@endforelse</tbody></table></div>
        <h6>{{ $servicioEditado ? 'Editar servicio' : 'Agregar servicio' }}</h6>
        @include('app.veterinaria.operacion.formulario',['accionForm'=>'servicio','registroForm'=>$servicioEditado,'camposForm'=>$camposServicio,'ocultos'=>['modulo'=>$seccion],'valores'=>['duracion_minutos'=>30,'precio'=>0,'activo'=>1]])
    </div></div>
    @endif
    @if($seccion === 'asistentes' && ($detalle ?? null))
    <div class="card"><div class="card-header encabezado-card"><h5 class="encabezado-card-titulo">Candidatos · {{ $detalle->cargo }}</h5></div><div class="card-body">
        <p>{{ $detalle->descripcion }}</p>
        <div class="table-responsive"><table class="table table-sm"><thead><tr><th>Nombre</th><th>Correo</th><th>Teléfono</th><th>Etapa</th><th></th></tr></thead><tbody>@forelse($postulaciones as $p)<tr><td>{{ $p->nombre }}</td><td>{{ $p->email }}</td><td>{{ $p->telefono }}</td><td>{{ $p->estado }}</td><td><a class="btn btn-outline-info btn-sm" href="{{ route('vet.operacion.index',$ctx+['seccion'=>$seccion,'detalle'=>$detalle->id,'postulacion'=>$p->id]) }}">Editar</a></td></tr>@empty<tr><td colspan="5">Sin candidatos registrados.</td></tr>@endforelse</tbody></table></div>
        @if($detalle->estado === 'abierta' || $postulacionEditada)
        <h6>{{ $postulacionEditada ? 'Actualizar candidato' : 'Registrar candidato' }}</h6>
        @include('app.veterinaria.operacion.formulario',['accionForm'=>'postulacion','registroForm'=>$postulacionEditada,'camposForm'=>$camposPostulacion,'ocultos'=>['convocatoria_id'=>$detalle->id],'valores'=>['estado'=>'recibida']])
        @endif
    </div></div>
    @endif
    @if($seccion === 'resumen')
    <div class="row">@foreach($resumen as $label=>$valor)<div class="col-md-4"><div class="card"><div class="card-body"><h6>{{ $label }}</h6><h3>{{ number_format($valor,0,',','.') }}</h3></div></div></div>@endforeach</div>
    <div class="card"><div class="card-header encabezado-card"><h5 class="encabezado-card-titulo">Historial de cambios</h5></div><div class="card-body"><div class="table-responsive"><table class="table table-sm"><thead><tr><th>Fecha</th><th>Sección</th><th>Registro</th><th>Usuario</th></tr></thead><tbody>@forelse($auditoria as $a)<tr><td>{{ $a->created_at }}</td><td>{{ $a->tabla }}</td><td>#{{ $a->registro_id }}</td><td>#{{ $a->id_usuario }}</td></tr>@empty<tr><td colspan="4">Sin cambios registrados.</td></tr>@endforelse</tbody></table></div>{{ $auditoria->links('pagination::bootstrap-4') }}</div></div>
    @endif
</div></div>
@endsection
@section('page-script')
<script src="{{ asset('js/operacion-veterinaria.js') }}"></script>
@endsection

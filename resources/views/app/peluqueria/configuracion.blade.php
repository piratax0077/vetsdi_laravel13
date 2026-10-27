@extends('app.peluqueria.layout')
@section('page-styles')
@if($seccion==='agenda')@include('app.peluqueria.estilos_agenda')@endif
@endsection
@section('content')
<div class="page-header"><h4>Panel de configuración</h4><p>Administra los procedimientos, el equipo de trabajo, las agendas y las sucursales de {{ $perfil->nombre }}.</p></div>
<nav aria-label="Secciones de configuración" class="mb-4"><ul class="nav nav-tabs-secciones">
@foreach(['procedimientos'=>'Procedimientos y precios','personal'=>'Personal y permisos','agenda'=>'Configurar agenda','sucursales'=>'Sucursales','negocio'=>'Datos del negocio'] as $clave=>$etiqueta)
@if($peluqueriaPermisos[$clave]??false)<li class="nav-item-secciones"><a class="nav-secciones {{ $seccion===$clave?'active':'' }}" href="{{ route(($peluqueriaRutaBase??'peluqueria').'.configuracion',['seccion'=>$clave]) }}" @if($seccion===$clave) aria-current="page" @endif>{{ $etiqueta }}</a></li>@endif
@endforeach</ul></nav>
@if($seccion==='procedimientos')
<div class="card"><div class="card-header"><h5>Nuevo procedimiento</h5></div><div class="card-body">@include('app.peluqueria.procedimiento_form',['procedimiento'=>null])</div></div>
<div class="row">@forelse($procedimientos as $procedimiento)<div class="col-lg-6"><div class="card"><div class="card-header"><h5>{{ $procedimiento->nombre }}</h5><span class="badge {{ $procedimiento->activo?'badge-success':'badge-secondary' }} float-right">{{ $procedimiento->activo?'Activo':'Inactivo' }}</span></div><div class="card-body">@include('app.peluqueria.procedimiento_form')</div></div></div>@empty<div class="col-12"><div class="alert alert-info">Agrega baño, belleza, peluquería u otros procedimientos con su duración y precio.</div></div>@endforelse</div>
@elseif($seccion==='personal')
<div class="alert alert-info">Agrega personal con el correo de una cuenta ya registrada. Cada integrante entra con su propia contraseña y verá el perfil de peluquería en su selector de cuentas. El propietario conserva todos los permisos y administra los accesos del equipo.</div>
<div class="card"><div class="card-header"><h5>Agregar integrante</h5></div><div class="card-body">@include('app.peluqueria.configuracion_personal',['integrante'=>null])</div></div>
@foreach($personal as $integrante)<div class="card"><div class="card-header"><h5>{{ $integrante->name }} · {{ $integrante->cargo }}</h5><span class="badge {{ $integrante->activo?'badge-success':'badge-secondary' }}">{{ $integrante->activo?'Acceso activo':'Acceso desactivado' }}</span></div><div class="card-body">@include('app.peluqueria.configuracion_personal')</div></div>@endforeach
@elseif($seccion==='sucursales')
<div class="card"><div class="card-header"><h5>Nueva sucursal</h5></div><div class="card-body">@include('app.peluqueria.configuracion_sucursal',['sucursal'=>null])</div></div>
@foreach($sucursales as $sucursal)<div class="card"><div class="card-header"><h5>{{ $sucursal->nombre }}</h5></div><div class="card-body">@include('app.peluqueria.configuracion_sucursal')</div></div>@endforeach
@elseif($seccion==='agenda')
<p class="text-muted">Los horarios usan la zona de Chile (America/Santiago). El horario, el descanso y los bloqueos se aplican a las nuevas reservas. Las citas existentes se conservan para que puedas revisarlas.</p>
@if($sucursales->isEmpty())<div class="alert alert-info">Primero registra una sucursal en la sección Sucursales.</div>@endif
@foreach($sucursales as $sucursal)@php($horario=$sucursal->agenda?json_decode($sucursal->agenda,true):['dias'=>[1,2,3,4,5,6],'apertura'=>'09:00','cierre'=>'18:00','anticipacion_horas'=>0,'anticipacion_dias'=>180])
<div class="card"><div class="card-header"><h5>Horario · {{ $sucursal->nombre }}</h5></div><div class="card-body">
@if(!$sucursal->agenda)<p class="alert alert-warning">Esta sucursal aún no tiene restricciones horarias. Guarda el horario para aplicarlas.</p>@endif
@include('app.peluqueria.configuracion_horario')
<hr><h6 id="bloqueos-agenda">Bloquear un horario: vacaciones, feriados o mantenimiento</h6><form method="post" action="{{ route(($peluqueriaRutaBase??'peluqueria').'.configuracion.bloqueo') }}">@csrf<input type="hidden" name="sucursal_id" value="{{ $sucursal->id }}"><div class="form-row"><div class="form-group col-md-4"><label>Desde</label><input class="form-control form-control-sm" type="datetime-local" name="inicio" required></div><div class="form-group col-md-4"><label>Hasta</label><input class="form-control form-control-sm" type="datetime-local" name="fin" required></div><div class="form-group col-md-4"><label>Motivo</label><input class="form-control form-control-sm" name="motivo" maxlength="255" required></div></div><button class="btn btn-outline-info">Agregar bloqueo</button></form>
@foreach($bloqueos->where('sucursal_id',$sucursal->id) as $bloqueo)<div class="d-flex align-items-center justify-content-between border-top mt-3 pt-3"><span>{{ \Carbon\Carbon::parse($bloqueo->inicio)->format('d-m-Y H:i') }} a {{ \Carbon\Carbon::parse($bloqueo->fin)->format('d-m-Y H:i') }} · {{ $bloqueo->motivo }}</span><form method="post" action="{{ route(($peluqueriaRutaBase??'peluqueria').'.configuracion.bloqueo.quitar',$bloqueo->id) }}">@csrf<button class="btn btn-outline-danger btn-sm">Retirar bloqueo</button></form></div>@endforeach
</div></div>@endforeach
@else
<div class="card"><div class="card-header"><h5>Datos del negocio</h5></div><div class="card-body"><form method="post" action="{{ route(($peluqueriaRutaBase??'peluqueria').'.perfil') }}">@csrf<div class="form-row">@foreach(['nombre'=>'Nombre','telefono'=>'Teléfono','direccion'=>'Dirección'] as $campo=>$etiqueta)<div class="form-group col-md-4"><label>{{ $etiqueta }}</label><input class="form-control form-control-sm" name="{{ $campo }}" value="{{ $perfil->$campo }}" maxlength="{{ $campo==='telefono'?30:255 }}" required></div>@endforeach</div>@if($esCentroEstetico??false)
<div class="form-row">@foreach(['rut_empresa'=>'RUT de la empresa','razon_social'=>'Razón social','giro'=>'Giro','email_contacto'=>'Correo del centro'] as $campo=>$etiqueta)
<div class="form-group col-md-6"><label for="centro-{{ $campo }}">{{ $etiqueta }}</label><input id="centro-{{ $campo }}" class="form-control form-control-sm" name="{{ $campo }}" value="{{ old($campo,$perfil->$campo) }}" type="{{ $campo==='email_contacto'?'email':'text' }}" maxlength="{{ $campo==='rut_empresa'?12:255 }}"></div>
@endforeach</div>
@endif
<button class="btn btn-info">Guardar datos</button></form></div></div>
@endif
@endsection

@section('page-script')
@if($seccion==='agenda')<script src="{{ asset('js/peluqueria-horarios.js') }}?v={{ filemtime(public_path('js/peluqueria-horarios.js')) }}"></script>@endif
@endsection

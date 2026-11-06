@extends('app.peluqueria.layout')
@section('page-styles')
@if($peluqueriaPermisos['agenda']??false)@include('app.peluqueria.estilos_agenda')
@endif
@endsection
@section('content')
<div class="page-header"><h4>Panel de configuración</h4><p>Administra los servicios, el equipo de trabajo, las agendas y las sucursales de {{ $perfil->nombre }}.</p></div>
{{-- Todas las secciones vienen en la página y se cambian sin recargar; ?seccion= solo indica cuál se abre primero --}}
<nav aria-label="Secciones de configuración" class="mb-4"><ul class="nav pill-moderno pill-moderno-white pill-sin-subrayado" role="tablist">
@foreach(['procedimientos'=>'Servicios y precios','personal'=>'Personal y permisos','agenda'=>'Configurar agenda','sucursales'=>'Sucursales','negocio'=>'Datos del negocio'] as $clave=>$etiqueta)
@if($peluqueriaPermisos[$clave]??false)<li class="nav-item"><a class="nav-link {{ $seccion===$clave?'active':'' }}" id="pestana-{{ $clave }}" data-toggle="pill" data-seccion="{{ $clave }}" href="#seccion-{{ $clave }}" role="tab" aria-controls="seccion-{{ $clave }}" aria-selected="{{ $seccion===$clave?'true':'false' }}">{{ $etiqueta }}</a></li>@endif
@endforeach</ul></nav>
<div class="tab-content p-0 bg-transparent shadow-none">
@if($peluqueriaPermisos['procedimientos']??false)
<div class="tab-pane fade {{ $seccion==='procedimientos'?'show active':'' }}" id="seccion-procedimientos" role="tabpanel" aria-labelledby="pestana-procedimientos">
{{-- Catálogo de servicios con la misma tabla del área de peluquería de la clínica; agregar y editar se hacen en modales --}}
<div class="card"><div class="card-header encabezado-card"><h5 class="encabezado-card-titulo">Servicios y precios</h5><div class="encabezado-card-acciones"><button type="button" class="btn btn-info" data-toggle="modal" data-target="#modal_servicio_nuevo"><i class="feather icon-plus"></i> Agregar servicio</button></div></div><div class="card-body">
<div class="table-responsive"><table class="table table-sm tabla-sin-hover"><thead><tr><th>Servicio</th><th>Duración</th><th>Precio CLP</th><th>Estado</th><th>Acciones</th></tr></thead><tbody>
@forelse($procedimientos as $procedimiento)<tr><td>{{ $procedimiento->nombre }}</td><td>{{ $procedimiento->duracion }} min</td><td>${{ number_format($procedimiento->precio,0,',','.') }}</td><td><span class="badge {{ $procedimiento->activo?'badge-light-success':'badge-light-secondary' }}">{{ $procedimiento->activo?'Activo':'Inactivo' }}</span></td><td><button type="button" class="btn btn-warning btn-icon" data-toggle="modal" data-target="#modal_servicio_{{ $procedimiento->id }}" title="Editar" aria-label="Editar {{ $procedimiento->nombre }}"><i class="feather icon-edit"></i></button></td></tr>
@empty<tr><td colspan="5">Agrega baño, belleza, peluquería u otros servicios con su duración y precio.</td></tr>@endforelse
</tbody></table></div></div></div>
</div>
@endif
@if($peluqueriaPermisos['personal']??false)
<div class="tab-pane fade {{ $seccion==='personal'?'show active':'' }}" id="seccion-personal" role="tabpanel" aria-labelledby="pestana-personal">
<div class="alert alert-info">Agrega personal con el correo de una cuenta ya registrada. Cada integrante entra con su propia contraseña y verá el perfil de peluquería en su selector de cuentas. El propietario conserva todos los permisos y administra los accesos del equipo.</div>
<div class="card"><div class="card-header"><h5>Agregar integrante</h5></div><div class="card-body">@include('app.peluqueria.configuracion_personal',['integrante'=>null])</div></div>
@foreach($personal as $integrante)<div class="card"><div class="card-header"><h5>{{ $integrante->name }} · {{ $integrante->cargo }}</h5><span class="badge {{ $integrante->activo?'badge-success':'badge-secondary' }}">{{ $integrante->activo?'Acceso activo':'Acceso desactivado' }}</span></div><div class="card-body">@include('app.peluqueria.configuracion_personal')</div></div>@endforeach
</div>
@endif
@if($peluqueriaPermisos['sucursales']??false)
<div class="tab-pane fade {{ $seccion==='sucursales'?'show active':'' }}" id="seccion-sucursales" role="tabpanel" aria-labelledby="pestana-sucursales">
<div class="card"><div class="card-header"><h5>Nueva sucursal</h5></div><div class="card-body">@include('app.peluqueria.configuracion_sucursal',['sucursal'=>null])</div></div>
@foreach($sucursales as $sucursal)<div class="card"><div class="card-header"><h5>{{ $sucursal->nombre }}</h5></div><div class="card-body">@include('app.peluqueria.configuracion_sucursal')</div></div>@endforeach
</div>
@endif
@if($peluqueriaPermisos['agenda']??false)
<div class="tab-pane fade {{ $seccion==='agenda'?'show active':'' }}" id="seccion-agenda" role="tabpanel" aria-labelledby="pestana-agenda">
<div class="card"><div class="card-header encabezado-card"><h5 class="encabezado-card-titulo"><span class="encabezado-card-icono icono-sin-fondo"><i class="feather icon-info"></i></span>Configurar agenda</h5></div><div class="card-body">
<p class="text-muted">Los horarios usan la zona de Chile (America/Santiago). El horario, el descanso y los bloqueos se aplican a las nuevas reservas. Las citas existentes se conservan para que puedas revisarlas.</p>
@if($sucursales->isEmpty())<div class="alert alert-info mb-0">Primero registra una sucursal en la sección Sucursales.</div>@endif
</div></div>
@foreach($sucursales as $sucursal)@php($horario=$sucursal->agenda?json_decode($sucursal->agenda,true):['dias'=>[1,2,3,4,5,6],'apertura'=>'09:00','cierre'=>'18:00','anticipacion_horas'=>0,'anticipacion_dias'=>180])
<div class="card"><div class="card-header"><h5>Horario · {{ $sucursal->nombre }}</h5></div><div class="card-body">
@if(!$sucursal->agenda)<p class="alert alert-warning">Esta sucursal aún no tiene restricciones horarias. Guarda el horario para aplicarlas.</p>@endif
@include('app.peluqueria.configuracion_horario')
<hr><h6 id="bloqueos-agenda">Bloquear un horario: vacaciones, feriados o mantenimiento</h6><form method="post" action="{{ route(($peluqueriaRutaBase??'peluqueria').'.configuracion.bloqueo') }}">@csrf<input type="hidden" name="sucursal_id" value="{{ $sucursal->id }}"><div class="form-row"><div class="form-group col-md-4"><label>Desde</label><input class="form-control form-control-sm" type="datetime-local" name="inicio" required></div><div class="form-group col-md-4"><label>Hasta</label><input class="form-control form-control-sm" type="datetime-local" name="fin" required></div><div class="form-group col-md-4"><label>Motivo</label><input class="form-control form-control-sm" name="motivo" maxlength="255" required></div></div><button class="btn btn-outline-info">Agregar bloqueo</button></form>
@foreach($bloqueos->where('sucursal_id',$sucursal->id) as $bloqueo)<div class="d-flex align-items-center justify-content-between border-top mt-3 pt-3"><span>{{ \Carbon\Carbon::parse($bloqueo->inicio)->format('d-m-Y H:i') }} a {{ \Carbon\Carbon::parse($bloqueo->fin)->format('d-m-Y H:i') }} · {{ $bloqueo->motivo }}</span><form method="post" action="{{ route(($peluqueriaRutaBase??'peluqueria').'.configuracion.bloqueo.quitar',$bloqueo->id) }}">@csrf<button class="btn btn-outline-danger btn-sm">Retirar bloqueo</button></form></div>@endforeach
</div></div>@endforeach
</div>
@endif
@if($peluqueriaPermisos['negocio']??false)
<div class="tab-pane fade {{ $seccion==='negocio'?'show active':'' }}" id="seccion-negocio" role="tabpanel" aria-labelledby="pestana-negocio">
<div class="card"><div class="card-header"><h5>Datos del negocio</h5></div><div class="card-body"><form method="post" action="{{ route(($peluqueriaRutaBase??'peluqueria').'.perfil') }}">@csrf<div class="form-row">@foreach(['nombre'=>'Nombre','telefono'=>'Teléfono','direccion'=>'Dirección'] as $campo=>$etiqueta)<div class="form-group col-md-4"><label>{{ $etiqueta }}</label><input class="form-control form-control-sm" name="{{ $campo }}" value="{{ $perfil->$campo }}" maxlength="{{ $campo==='telefono'?30:255 }}" required></div>@endforeach</div>@if($esCentroEstetico??false)
<div class="form-row">@foreach(['rut_empresa'=>'RUT de la empresa','razon_social'=>'Razón social','giro'=>'Giro','email_contacto'=>'Correo del centro'] as $campo=>$etiqueta)
<div class="form-group col-md-6"><label for="centro-{{ $campo }}">{{ $etiqueta }}</label><input id="centro-{{ $campo }}" class="form-control form-control-sm" name="{{ $campo }}" value="{{ old($campo,$perfil->$campo) }}" type="{{ $campo==='email_contacto'?'email':'text' }}" maxlength="{{ $campo==='rut_empresa'?12:255 }}"></div>
@endforeach</div>
@endif
<button class="btn btn-info">Guardar datos</button></form></div></div>
</div>
@endif
</div>
{{-- Fuera de las pestañas: un modal dentro de una pestaña oculta no se vería --}}
@if($peluqueriaPermisos['procedimientos']??false)
@foreach(collect([null])->concat($procedimientos) as $procedimiento)
<div id="modal_servicio_{{ $procedimiento->id ?? 'nuevo' }}" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modal_servicio_{{ $procedimiento->id ?? 'nuevo' }}_titulo" aria-hidden="true"><div class="modal-dialog modal-dialog-centered modal-lg" role="document"><div class="modal-content">
<div class="modal-header modal-header-purple"><h5 class="modal-title" id="modal_servicio_{{ $procedimiento->id ?? 'nuevo' }}_titulo">{{ $procedimiento?'Editar servicio':'Agregar servicio' }}</h5><button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">×</span></button></div>
@include('app.peluqueria.procedimiento_form')
</div></div></div>
@endforeach
@endif
@endsection

@section('page-script')
<script>
// Al cambiar de pestaña se actualiza ?seccion= en la dirección, así al recargar se abre la misma
document.querySelectorAll('[data-seccion]').forEach(function(pestana){
    pestana.addEventListener('click',function(){
        var url=new URL(window.location.href);url.searchParams.set('seccion',pestana.dataset.seccion);url.hash='';
        window.history.replaceState(null,'',url);
    });
});
</script>
@if($peluqueriaPermisos['agenda']??false)<script src="{{ asset('js/peluqueria-horarios.js') }}?v={{ filemtime(public_path('js/peluqueria-horarios.js')) }}"></script>@endif
@endsection

@extends('app.peluqueria.layout')
@section('content')
<div class="page-header encabezado-escritorio"><div class="page-block"><div class="row"><div class="col-md-12">@include('template.include.saludo_escritorio',['nombre_saludo'=>$perfil->nombre,'texto_saludo'=>($esCentroEstetico??false)?'Administra el equipo, las sucursales y los servicios de tu centro estético.':'Tu agenda y tus atenciones de estética de mascotas.'])</div></div></div></div>
@if($esCentroEstetico??false)
<div class="row mb-3">
@foreach(['Sucursales'=>$peluqueriaSucursales->count(),'Profesionales'=>$opcionesAgenda->count()+($puedeVerTitular?1:0),'Procedimientos'=>$procedimientos->count(),'Reservas de hoy'=>$citas->count()] as $titulo=>$cantidad)
<div class="col-sm-6 col-xl-3"><div class="card mb-2"><div class="card-body py-3"><small class="text-muted">{{ $titulo }}</small><h3 class="mb-0 text-purple">{{ $cantidad }}</h3></div></div></div>
@endforeach
</div>
@endif
<div class="row mt-n3 mb-3"><div class="col-md-12"><div class="card-deck">
@php($tarjetaPermisos=['agenda.ver'=>'ver_agenda','mascotas'=>'ver_clientes','historial'=>'ver_historial'])
@foreach(['agenda.ver'=>['agenda.svg',($esCentroEstetico??false)?'Agendas del centro':'Mi agenda'],'mascotas'=>['mascotas.svg','Mascotas y tutores'],'configuracion'=>['panel_configuracion.svg','Panel de configuración'],'historial'=>['estadisticas.svg','Servicios realizados']] as $ruta=>[$icono,$titulo])
@if(($ruta==='configuracion'&&array_intersect(array_keys($peluqueriaPermisos),['procedimientos','personal','agenda','sucursales','negocio']))||($peluqueriaPermisos[$tarjetaPermisos[$ruta]??'']??false))<div class="card subir"><a href="{{ route(($peluqueriaRutaBase??'peluqueria').'.'.$ruta) }}" @if($ruta==='agenda.ver') data-toggle="modal" data-target="#seleccionar-agenda-peluqueria" @endif><div class="card-body text-center px-2"><img class="wid-40" src="{{ asset('images/iconos/'.$icono) }}" alt=""><h6 class="mt-2">{{ $titulo }}</h6></div></a></div>@endif
@endforeach
</div></div></div>
<div class="row"><div class="col-lg-8"><div class="card"><div class="card-header"><h5>Agenda del día</h5>@if($peluqueriaPermisos['ver_agenda']??false)<a class="btn btn-info btn-sm float-right" href="{{ route(($peluqueriaRutaBase??'peluqueria').'.agenda.ver') }}" data-toggle="modal" data-target="#seleccionar-agenda-peluqueria">Abrir agenda</a>@endif</div><div class="card-body"><div class="table-responsive"><table class="table table-sm"><thead><tr><th>Hora</th><th>Mascota / tutor</th><th>Procedimiento</th><th>Atención</th></tr></thead><tbody>@forelse($citas as $cita)<tr><td>{{ \Carbon\Carbon::parse($cita->inicio)->format('H:i') }}</td><td>{{ $cita->mascota }}<br><small>{{ $cita->tutor }}</small></td><td>{{ $cita->servicio }}</td><td>@if((($peluqueriaPermisos['atender']??false)&&$cita->estado==='en_atencion')||(($peluqueriaPermisos['ver_historial']??false)&&$cita->estado==='finalizada'))<a class="btn btn-purple btn-sm" href="{{ route(($peluqueriaRutaBase??'peluqueria').'.ficha',$cita->id) }}">Abrir ficha</a>@else<span>{{ $cita->estado }}</span>@endif</td></tr>@empty<tr><td colspan="4">No hay reservas para esta fecha.</td></tr>@endforelse</tbody></table></div></div></div></div>
<div class="col-lg-4"><div class="card"><div class="card-header"><h5>Procedimientos configurados</h5></div><div class="card-body">@forelse($procedimientos as $procedimiento)<p>{{ $procedimiento->nombre }}<br><small>{{ $procedimiento->duracion }} minutos · ${{ number_format($procedimiento->precio,0,',','.') }}</small></p>@empty<p>Configura la duración y el precio de cada procedimiento para comenzar.</p>@endforelse
@if($peluqueriaPermisos['procedimientos']??false)<a href="{{ route(($peluqueriaRutaBase??'peluqueria').'.configuracion') }}" class="btn btn-outline-info btn-sm">Configurar procedimientos</a>@endif</div></div>@if($peluqueriaPermisos['editar_clientes']??false)<a class="btn btn-info" href="{{ route(($peluqueriaRutaBase??'peluqueria').'.mascotas') }}">Registrar tutor y mascota</a>@endif</div></div>
<div class="card"><div class="card-header"><h5>Últimas atenciones finalizadas</h5></div><div class="card-body">@forelse($historial as $atencion)<div class="border-bottom pb-2 mb-2"><a href="{{ route(($peluqueriaRutaBase??'peluqueria').'.ficha',$atencion->id) }}">{{ $atencion->mascota }} · {{ $atencion->servicio }}</a><p class="mb-0">{{ $atencion->registro }}</p></div>@empty<p class="mb-0">Las atenciones finalizadas aparecerán aquí.</p>@endforelse</div></div>
@if($peluqueriaPermisos['ver_agenda']??false)@include('app.peluqueria.seleccionar_agenda')@endif
@endsection

@section('page-script')
<script>
(function(){const lugar=document.getElementById('seleccionar-agenda-lugar'),profesional=document.getElementById('seleccionar-agenda-profesional');if(!lugar||!profesional)return;
function filtrar(){Array.from(profesional.options).forEach(o=>{o.disabled=!!o.dataset.sucursal&&o.dataset.sucursal!==lugar.value;o.hidden=o.disabled;});if(profesional.selectedOptions[0]?.disabled)profesional.value='titular';}
lugar.addEventListener('change',filtrar);filtrar();
@if(request()->boolean('elegir_agenda'))window.jQuery('#seleccionar-agenda-peluqueria').modal('show');@endif
})();
</script>
@endsection

@extends('app.peluqueria.layout')
@section('page-styles')
@include('app.peluqueria.estilos_agenda')
@endsection
@section('content')
<div class="page-header"><h4>{{ ['agenda'=>'Mi agenda','clientes'=>'Mascotas y tutores','registro'=>'Historial de atenciones'][$seccion] }}</h4></div>
@if($seccion==='clientes')@include('app.peluqueria.selector_sucursal')@endif
@if($seccion==='agenda')
<div class="agenda-panel mb-3">
<div class="encabezado-agenda mb-3"><div class="encabezado-agenda-lugar"><span class="encabezado-agenda-icono"><i class="feather icon-calendar" aria-hidden="true"></i></span><div class="encabezado-agenda-textos"><h5 class="titulo-agenda" id="titulo_tipo_agenda">Agenda de peluquería y belleza</h5><div class="lugar-atencion-agenda">{{ $profesionalAgenda['nombre']??$perfil->nombre }} · {{ $sucursal->nombre??$perfil->nombre }}</div></div></div>
<div class="encabezado-agenda-acciones">
@if(($peluqueriaPermisos['reservar']??false)&&(!$sucursal||$sucursal->activo))<button type="button" class="btn btn-info btn-sm" id="peluqueria-reservar-hora"><i class="feather icon-plus mr-1"></i>Reservar hora</button>@endif
@if($peluqueriaPermisos['agenda']??false)<a href="{{ route(($peluqueriaRutaBase??'peluqueria').'.configuracion',['seccion'=>'agenda']) }}" class="btn btn-outline-purple btn-sm"><i class="feather icon-settings mr-1"></i>Configurar horario</a><a href="{{ route(($peluqueriaRutaBase??'peluqueria').'.configuracion',['seccion'=>'agenda']) }}#bloqueos-agenda" class="btn btn-outline-danger btn-sm"><i class="feather icon-slash mr-1"></i>Bloquear horario</a>@endif
</div></div>
<p id="peluqueria-seleccionar-hora" class="alert alert-info" role="status" hidden>Selecciona en el calendario la hora de inicio para reservar.</p>
<div class="card agenda-calendar-card"><div id="peluqueria-calendario" data-fecha="{{ $fecha }}" data-eventos="{{ route(($peluqueriaRutaBase??'peluqueria').'.agenda.eventos',['sucursal_id'=>$sucursal->id??null,'agenda_personal'=>$agendaPersonal]) }}" data-agenda="{{ route(($peluqueriaRutaBase??'peluqueria').'.agenda.ver') }}" data-horario="{{ json_encode($agendaConfigurada) }}" data-reservar="{{ (($peluqueriaPermisos['reservar']??false)&&(!$sucursal||$sucursal->activo))?'1':'0' }}"></div></div>
<div class="d-flex flex-wrap align-items-center mb-3">
@foreach(config('peluqueria.estados') as $estado=>$presentacion)
@if($estado!=='cancelada')<span class="status-circle mr-3"><span class="circle" style="background:{{ $presentacion['color'] }}"></span>{{ $presentacion['nombre'] }}</span>@endif
@endforeach
</div>
<p class="text-muted">Selecciona la hora de inicio y el procedimiento. La cita ocupará su duración exacta. Pulsa una reserva para confirmar, recepcionar su pago, registrar la espera y atender.</p>
</div>
@if(($peluqueriaPermisos['reservar']??false)&&(!$sucursal||$sucursal->activo))@include('app.peluqueria.modal_reserva')@endif
@include('app.peluqueria.modal_estado')
@include('app.peluqueria.agenda_reservas')

@elseif($seccion==='clientes')
<section class="card" id="clientes"><div class="card-body"><h2>Tutores y mascotas</h2>@if($peluqueriaPermisos['editar_clientes']??false)<details @if(old('mascota')) open @endif><summary>Registrar tutor y mascota</summary><form method="post" action="{{ route(($peluqueriaRutaBase??'peluqueria').'.clientes') }}" class="mt-3">@csrf<div class="row">
@foreach(['rut'=>'RUT del tutor','tutor'=>'Nombre del tutor','telefono'=>'Teléfono','email'=>'Correo (opcional)','mascota'=>'Nombre de la mascota','especie'=>'Especie','raza'=>'Raza (opcional)'] as $campo=>$etiqueta)<label class="form-group col-md-4">{{ $etiqueta }}<input class="form-control form-control-sm" name="{{ $campo }}" value="{{ old($campo) }}" type="{{ $campo==='email'?'email':'text' }}" maxlength="{{ $campo==='telefono'?30:(in_array($campo,['mascota','raza'])?100:($campo==='especie'?60:255)) }}" @required(!in_array($campo,['email','raza']))></label>@endforeach</div><label class="mt-3">Cuidados y advertencias comunicados por el tutor<textarea class="form-control caja-texto form-control-sm" name="cuidados" maxlength="3000" rows="1" onfocus="this.rows=3" onblur="this.rows=1;">{{ old('cuidados') }}</textarea></label><button class="btn btn-primary mt-3">Registrar</button></form></details>@endif
<div class="table-responsive mt-3"><table class="table table-sm table-striped"><thead><tr><th>Mascota</th><th>Especie / raza</th><th>Tutor</th><th>Contacto</th></tr></thead><tbody>@forelse($clientes as $cliente)<tr><td>{{ $cliente->mascota }}</td><td>{{ $cliente->especie }} {{ $cliente->raza }}</td><td>{{ $cliente->tutor }}</td><td>{{ $cliente->telefono }}<br>{{ $cliente->email }}</td></tr>@empty<tr><td colspan="4">Aún no hay mascotas registradas.</td></tr>@endforelse</tbody></table></div></div></section>

@else
<section class="card" id="registro"><div class="card-body"><h2>Servicios realizados</h2><div class="table-responsive"><table class="table table-sm table-striped"><thead><tr><th>Fecha</th><th>Mascota / tutor</th><th>Sucursal</th><th>Servicio</th><th>Registro</th><th>Valor</th></tr></thead><tbody>@forelse($historial as $atencion)<tr><td>{{ \Carbon\Carbon::parse($atencion->inicio)->format('d-m-Y H:i') }}</td><td>{{ $atencion->mascota }}<br>{{ $atencion->tutor }}</td><td>{{ $atencion->sucursal_nombre ?? 'Sin sucursal registrada' }}@if($atencion->sucursal_direccion)<br><small class="text-muted">{{ $atencion->sucursal_direccion }}</small>@endif</td><td>{{ $atencion->servicio }}<br><a href="{{ route(($peluqueriaRutaBase??'peluqueria').'.ficha',$atencion->id) }}">Ver ficha</a></td><td style="white-space:pre-wrap">{{ $atencion->registro }}</td><td>${{ number_format($atencion->valor,0,',','.') }}</td></tr>@empty<tr><td colspan="6">Las atenciones finalizadas aparecerán aquí.</td></tr>@endforelse</tbody></table></div>{{ $historial->withQueryString()->links() }}</div></section>

@endif
@endsection
@section('page-script')
<script src="{{ asset('js/fullcalendar-5.10.1/lib/main.min.js') }}"></script><script src="{{ asset('js/fullcalendar-5.10.1/lib/locales/es.js') }}"></script><script src="{{ asset('js/peluqueria-agenda.js') }}?v={{ filemtime(public_path('js/peluqueria-agenda.js')) }}"></script>

@endsection
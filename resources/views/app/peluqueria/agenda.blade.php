@extends('app.peluqueria.layout')
@section('page-styles')<link rel="stylesheet" href="{{ asset('js/fullcalendar-5.10.1/lib/main.min.css') }}">@endsection
@section('content')
<div class="page-header"><h4>{{ ['agenda'=>'Mi agenda','clientes'=>'Mascotas y tutores','registro'=>'Historial de atenciones'][$seccion] }}</h4></div>
@if($seccion==='agenda')
<div class="card"><div class="card-header"><h5>Agenda de peluquería y belleza</h5><button type="button" class="btn btn-info btn-sm ml-2" data-toggle="modal" data-target="#peluqueria-reserva-modal">Reservar hora</button><a href="{{ route('peluqueria.configuracion') }}" class="btn btn-outline-info btn-sm float-right">Configurar procedimientos</a></div><div class="card-body"><div id="peluqueria-calendario" data-fecha="{{ $fecha }}" data-eventos="{{ route('peluqueria.agenda.eventos') }}" data-agenda="{{ route('peluqueria.agenda.ver') }}"></div><small class="text-muted">Selecciona un horario para reservar. Pulsa una atención para abrir su ficha.</small></div></div>
@include('app.peluqueria.modal_reserva')
@elseif($seccion==='clientes')
<section class="card" id="clientes"><div class="card-body"><h2>Tutores y mascotas</h2><details @if(old('mascota')) open @endif><summary>Registrar tutor y mascota</summary><form method="post" action="{{ route('peluqueria.clientes') }}" class="mt-3">@csrf<div class="row">
@foreach(['tutor'=>'Nombre del tutor','telefono'=>'Teléfono','email'=>'Correo (opcional)','mascota'=>'Nombre de la mascota','especie'=>'Especie','raza'=>'Raza (opcional)'] as $campo=>$etiqueta)<label class="form-group col-md-4">{{ $etiqueta }}<input class="form-control form-control-sm" name="{{ $campo }}" value="{{ old($campo) }}" type="{{ $campo==='email'?'email':'text' }}" maxlength="{{ $campo==='telefono'?30:(in_array($campo,['mascota','raza'])?100:($campo==='especie'?60:255)) }}" @required(!in_array($campo,['email','raza']))></label>@endforeach</div><label class="mt-3">Cuidados y advertencias comunicados por el tutor<textarea class="form-control" name="cuidados" maxlength="3000">{{ old('cuidados') }}</textarea></label><button class="btn btn-primary mt-3">Registrar</button></form></details>
<div class="table-responsive mt-3"><table class="table table-sm table-striped"><thead><tr><th>Mascota</th><th>Especie / raza</th><th>Tutor</th><th>Contacto</th></tr></thead><tbody>@forelse($clientes as $cliente)<tr><td>{{ $cliente->mascota }}</td><td>{{ $cliente->especie }} {{ $cliente->raza }}</td><td>{{ $cliente->tutor }}</td><td>{{ $cliente->telefono }}<br>{{ $cliente->email }}</td></tr>@empty<tr><td colspan="4">Aún no hay mascotas registradas.</td></tr>@endforelse</tbody></table></div></div></section>

@else
<section class="card" id="registro"><div class="card-body"><h2>Servicios realizados</h2><div class="table-responsive"><table class="table table-sm table-striped"><thead><tr><th>Fecha</th><th>Mascota / tutor</th><th>Servicio</th><th>Registro</th><th>Valor</th></tr></thead><tbody>@forelse($historial as $atencion)<tr><td>{{ \Carbon\Carbon::parse($atencion->inicio)->format('d-m-Y H:i') }}</td><td>{{ $atencion->mascota }}<br>{{ $atencion->tutor }}</td><td>{{ $atencion->servicio }}<br><a href="{{ route('peluqueria.ficha',$atencion->id) }}">Ver ficha</a></td><td style="white-space:pre-wrap">{{ $atencion->registro }}</td><td>${{ number_format($atencion->valor,0,',','.') }}</td></tr>@empty<tr><td colspan="5">Las atenciones finalizadas aparecerán aquí.</td></tr>@endforelse</tbody></table></div>{{ $historial->withQueryString()->links() }}</div></section>

@endif
@endsection
@section('page-script')
<script src="{{ asset('js/fullcalendar-5.10.1/lib/main.min.js') }}"></script><script src="{{ asset('js/fullcalendar-5.10.1/lib/locales/es.js') }}"></script><script src="{{ asset('js/peluqueria-agenda.js') }}"></script>

@endsection